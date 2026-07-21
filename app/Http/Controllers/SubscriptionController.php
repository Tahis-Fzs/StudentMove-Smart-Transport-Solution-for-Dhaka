<?php

namespace App\Http\Controllers;

use App\Mail\SubscriptionConfirmationMail;
use App\Models\Invoice;
use App\Models\PaymentAttempt;
use App\Models\Subscription;
use App\Services\SslCommerzService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SubscriptionController extends Controller
{
    public function __construct(protected SslCommerzService $sslCommerz)
    {
    }

    /** Canonical plan catalog — keys match names and durations. */
    public static function planCatalog(): array
    {
        return Subscription::planCatalog();
    }

    public function index()
    {
        $catalog = self::planCatalog();
        $plans = [];
        foreach ($catalog as $key => $plan) {
            $plans[] = array_merge($plan, ['key' => $key]);
        }

        $activeSubscription = Auth::check()
            ? Subscription::where('user_id', Auth::id())
                ->currentlyActive()
                ->latest()
                ->first()
            : null;

        $sslEnabled = $this->sslCommerz->isConfigured();
        $sslSandbox = $sslEnabled && $this->sslCommerz->isSandbox();

        return view('subscription', compact('plans', 'activeSubscription', 'sslEnabled', 'sslSandbox'));
    }

    /**
     * Start checkout. With SSLCommerz configured → hosted gateway (Star Cineplex style).
     * Without credentials → local demo/simulated path for development.
     */
    public function store(Request $request)
    {
        $request->validate([
            'plan_type' => 'required|in:weekly,monthly,single',
            'payment_method' => 'nullable|in:sslcommerz,card,mobile_banking',
            'payment_provider' => 'required_if:payment_method,mobile_banking|nullable|in:bkash,nagad,rocket',
            'transaction_id' => 'required_if:payment_method,mobile_banking|nullable|string|max:255',
            'card_number' => 'required_if:payment_method,card|nullable|string|max:19',
            'card_expiry' => 'required_if:payment_method,card|nullable|string|max:5',
            'card_cvv' => 'required_if:payment_method,card|nullable|string|max:4',
            'card_name' => 'required_if:payment_method,card|nullable|string|max:255',
        ]);

        $catalog = self::planCatalog();
        $planType = $request->plan_type;
        $plan = $catalog[$planType];
        $amount = $plan['price'];

        $method = $request->payment_method ?: ($this->sslCommerz->isConfigured() ? 'sslcommerz' : 'mobile_banking');

        if ($method === 'sslcommerz' || ($this->sslCommerz->isConfigured() && $method !== 'card' && $method !== 'mobile_banking')) {
            return $this->initiateSslCommerz($request, $planType, $amount, $plan);
        }

        // Demo / offline path when SSLCommerz keys are not set
        return $this->storeSimulated($request, $planType, $amount, $plan);
    }

    protected function initiateSslCommerz(Request $request, string $planType, float|int $amount, array $plan)
    {
        if (!$this->sslCommerz->isConfigured()) {
            return back()->with('error', 'SSLCommerz is not configured. Add SSLCOMMERZ_STORE_ID and SSLCOMMERZ_STORE_PASSWORD to .env.')->withInput();
        }

        $user = Auth::user();
        $tranId = 'SM' . $user->id . Str::upper(Str::random(10));

        $attempt = PaymentAttempt::create([
            'user_id' => $user->id,
            'plan_type' => $planType,
            'amount' => $amount,
            'payment_method' => 'card',
            'payment_provider' => 'sslcommerz',
            'transaction_id' => $tranId,
            'status' => 'pending',
            'checksum' => hash('sha256', implode('|', [$user->id, $planType, $amount, $tranId, now()->timestamp])),
            'meta' => [
                'client_ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'plan_name' => $plan['name'],
                'plan_days' => $plan['days'],
            ],
        ]);

        $session = $this->sslCommerz->initiate([
            'tran_id' => $tranId,
            'amount' => $amount,
            'product_name' => 'StudentMove ' . $plan['name'],
            'success_url' => route('subscription.ssl.success'),
            'fail_url' => route('subscription.ssl.fail'),
            'cancel_url' => route('subscription.ssl.cancel'),
            'ipn_url' => route('subscription.ssl.ipn'),
            'customer' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?: '01700000000',
                'address' => $user->current_address ?: 'Dhaka',
                'city' => 'Dhaka',
            ],
        ]);

        if (!$session['ok']) {
            $attempt->update([
                'status' => 'failed',
                'meta' => array_merge($attempt->meta ?? [], ['error' => $session['error'] ?? null]),
            ]);

            return back()->with('error', $session['error'] ?? 'Could not start payment.')->withInput();
        }

        $attempt->update([
            'gateway_ref' => $session['sessionkey'] ?? null,
            'meta' => array_merge($attempt->meta ?? [], ['sessionkey' => $session['sessionkey'] ?? null]),
        ]);

        return redirect()->away($session['gateway_url']);
    }

    /** SSLCommerz success redirect (browser). */
    public function sslSuccess(Request $request)
    {
        $result = $this->completeSslPayment($request);

        if ($result['ok']) {
            return redirect()->route('subscription')
                ->with('success', $result['message'])
                ->with('invoice_id', $result['invoice_id'] ?? null);
        }

        return redirect()->route('subscription')->with('error', $result['error'] ?? 'Payment could not be confirmed.');
    }

    /** SSLCommerz fail redirect. */
    public function sslFail(Request $request)
    {
        $this->markAttemptFailed($request->input('tran_id'), 'Payment failed at SSLCommerz.');

        return redirect()->route('subscription')->with('error', 'Payment failed. You were not charged. Please try again.');
    }

    /** SSLCommerz cancel redirect. */
    public function sslCancel(Request $request)
    {
        $this->markAttemptFailed($request->input('tran_id'), 'Payment cancelled by user.');

        return redirect()->route('subscription')->with('error', 'Payment cancelled. No charge was made.');
    }

    /** SSLCommerz Instant Payment Notification (server-to-server). */
    public function sslIpn(Request $request)
    {
        $result = $this->completeSslPayment($request);

        return response($result['ok'] ? 'IPN OK' : 'IPN FAIL', $result['ok'] ? 200 : 400);
    }

    /**
     * Validate with SSLCommerz and activate subscription (idempotent).
     *
     * @return array{ok:bool,message?:string,error?:string,invoice_id?:int}
     */
    protected function completeSslPayment(Request $request): array
    {
        $tranId = (string) $request->input('tran_id', '');
        $valId = (string) $request->input('val_id', '');
        $status = strtoupper((string) $request->input('status', ''));

        if ($tranId === '') {
            return ['ok' => false, 'error' => 'Missing transaction reference.'];
        }

        $attempt = PaymentAttempt::where('transaction_id', $tranId)
            ->where('payment_provider', 'sslcommerz')
            ->first();

        if (!$attempt) {
            return ['ok' => false, 'error' => 'Unknown payment attempt.'];
        }

        // Already fulfilled (success callback + IPN race)
        if ($attempt->status === 'success' && !empty($attempt->meta['invoice_id'])) {
            $invoice = Invoice::find($attempt->meta['invoice_id']);
            $ends = optional(Subscription::find($invoice?->subscription_id))->ends_at;

            return [
                'ok' => true,
                'message' => 'Subscription already active' . ($ends ? ' until ' . $ends->format('F d, Y') : '') . '.',
                'invoice_id' => $invoice?->id,
            ];
        }

        if ($status !== '' && !in_array($status, ['VALID', 'VALIDATED'], true)) {
            $this->markAttemptFailed($tranId, 'Gateway status: ' . $status);

            return ['ok' => false, 'error' => 'Payment was not successful.'];
        }

        if ($valId === '') {
            return ['ok' => false, 'error' => 'Missing validation id from SSLCommerz.'];
        }

        $validation = $this->sslCommerz->validateByValId($valId);
        if (!$validation['ok']) {
            $this->markAttemptFailed($tranId, $validation['error'] ?? 'Validation failed');

            return ['ok' => false, 'error' => $validation['error'] ?? 'Payment validation failed.'];
        }

        $data = $validation['data'];
        $paidAmount = (float) ($data['amount'] ?? $data['currency_amount'] ?? 0);
        $expected = (float) $attempt->amount;

        if (abs($paidAmount - $expected) > 0.5) {
            $this->markAttemptFailed($tranId, 'Amount mismatch');
            Log::warning('SSLCommerz amount mismatch', [
                'tran_id' => $tranId,
                'expected' => $expected,
                'paid' => $paidAmount,
            ]);

            return ['ok' => false, 'error' => 'Paid amount does not match the plan price.'];
        }

        if (($data['tran_id'] ?? '') !== $tranId) {
            return ['ok' => false, 'error' => 'Transaction id mismatch.'];
        }

        try {
            return DB::transaction(function () use ($attempt, $data, $tranId, $valId) {
                $attempt = PaymentAttempt::where('id', $attempt->id)->lockForUpdate()->first();

                if ($attempt->status === 'success' && !empty($attempt->meta['invoice_id'])) {
                    return [
                        'ok' => true,
                        'message' => 'Subscription activated successfully!',
                        'invoice_id' => $attempt->meta['invoice_id'],
                    ];
                }

                $catalog = self::planCatalog();
                $plan = $catalog[$attempt->plan_type] ?? null;
                if (!$plan) {
                    return ['ok' => false, 'error' => 'Unknown plan.'];
                }

                $startsAt = now();
                $endsAt = $startsAt->copy()->addDays((int) $plan['days']);

                $subscription = Subscription::create([
                    'user_id' => $attempt->user_id,
                    'plan_type' => $attempt->plan_type,
                    'amount' => $attempt->amount,
                    'payment_method' => 'card',
                    'payment_provider' => 'sslcommerz',
                    'transaction_id' => $tranId,
                    'status' => 'completed',
                    'starts_at' => $startsAt,
                    'ends_at' => $endsAt,
                ]);

                $invoice = Invoice::create([
                    'subscription_id' => $subscription->id,
                    'user_id' => $attempt->user_id,
                    'invoice_number' => Invoice::generateInvoiceNumber(),
                    'amount' => $attempt->amount,
                    'plan_type' => $attempt->plan_type,
                    'payment_method' => 'card',
                    'payment_provider' => 'sslcommerz',
                    'transaction_id' => $tranId,
                    'status' => 'paid',
                    'issued_at' => now(),
                ]);

                $this->generateInvoicePDF($invoice, $subscription);

                $attempt->update([
                    'status' => 'success',
                    'gateway_ref' => $valId,
                    'meta' => array_merge($attempt->meta ?? [], [
                        'invoice_id' => $invoice->id,
                        'subscription_id' => $subscription->id,
                        'ssl_status' => $data['status'] ?? null,
                        'card_type' => $data['card_type'] ?? null,
                        'bank_tran_id' => $data['bank_tran_id'] ?? null,
                    ]),
                ]);

                try {
                    $user = $subscription->user;
                    if ($user?->email) {
                        Mail::to($user->email)->send(new SubscriptionConfirmationMail($subscription, $invoice));
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to send subscription confirmation email: ' . $e->getMessage());
                }

                $message = 'Subscription activated successfully! Your plan is active until '
                    . $endsAt->format('F d, Y') . '.';

                return [
                    'ok' => true,
                    'message' => $message,
                    'invoice_id' => $invoice->id,
                ];
            });
        } catch (\Throwable $e) {
            Log::error('SSLCommerz complete failed', ['error' => $e->getMessage(), 'tran_id' => $tranId]);

            return ['ok' => false, 'error' => 'Could not activate subscription after payment.'];
        }
    }

    protected function markAttemptFailed(?string $tranId, string $reason): void
    {
        if (!$tranId) {
            return;
        }

        $attempt = PaymentAttempt::where('transaction_id', $tranId)
            ->where('payment_provider', 'sslcommerz')
            ->where('status', 'pending')
            ->first();

        if ($attempt) {
            $attempt->update([
                'status' => 'failed',
                'meta' => array_merge($attempt->meta ?? [], ['fail_reason' => $reason]),
            ]);
        }
    }

    /**
     * Local/demo checkout without a real gateway (when SSLCommerz is not configured).
     */
    protected function storeSimulated(Request $request, string $planType, float|int $amount, array $plan)
    {
        if ($request->payment_method === 'mobile_banking' && $request->transaction_id) {
            $exists = PaymentAttempt::where('payment_method', 'mobile_banking')
                ->where('transaction_id', $request->transaction_id)
                ->where('status', 'success')
                ->exists();
            if ($exists) {
                return back()->withErrors(['transaction_id' => 'This transaction ID was already used.'])->withInput();
            }
        }

        $subscriptionData = [
            'user_id' => Auth::id(),
            'plan_type' => $planType,
            'amount' => $amount,
            'payment_method' => $request->payment_method === 'card' ? 'card' : 'mobile_banking',
            'status' => 'completed',
            'starts_at' => now(),
            'ends_at' => now()->addDays((int) $plan['days']),
        ];

        if ($request->payment_method === 'mobile_banking') {
            if (!$request->transaction_id || strlen($request->transaction_id) < 10) {
                return back()->withErrors(['transaction_id' => 'Please enter a valid transaction ID.'])->withInput();
            }
            $subscriptionData['payment_provider'] = $request->payment_provider;
            $subscriptionData['transaction_id'] = $request->transaction_id;
        } else {
            $cardNumber = str_replace(' ', '', (string) $request->card_number);
            if (!$this->validateCardNumber($cardNumber)) {
                return back()->withErrors(['card_number' => 'Invalid card number.'])->withInput();
            }
            $subscriptionData['card_last_four'] = substr($cardNumber, -4);
        }

        $checksum = hash('sha256', implode('|', [
            Auth::id(),
            $planType,
            $amount,
            $request->payment_method,
            $request->payment_provider,
            $request->transaction_id,
            now()->timestamp,
        ]));

        $attempt = PaymentAttempt::create([
            'user_id' => Auth::id(),
            'plan_type' => $planType,
            'amount' => $amount,
            'payment_method' => $subscriptionData['payment_method'],
            'payment_provider' => $request->payment_provider,
            'transaction_id' => $request->transaction_id,
            'card_last_four' => $subscriptionData['card_last_four'] ?? null,
            'status' => 'pending',
            'checksum' => $checksum,
            'meta' => [
                'client_ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'mode' => 'simulated',
            ],
        ]);

        $subscription = Subscription::create($subscriptionData);

        $invoice = Invoice::create([
            'subscription_id' => $subscription->id,
            'user_id' => Auth::id(),
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'amount' => $amount,
            'plan_type' => $planType,
            'payment_method' => $subscriptionData['payment_method'],
            'payment_provider' => $request->payment_provider ?? null,
            'transaction_id' => $request->transaction_id ?? null,
            'status' => 'paid',
            'issued_at' => now(),
        ]);

        $this->generateInvoicePDF($invoice, $subscription);

        $attempt->update([
            'status' => 'success',
            'gateway_ref' => 'SIM-' . uniqid(),
            'meta' => array_merge($attempt->meta ?? [], [
                'invoice_id' => $invoice->id,
                'checksum_verified' => hash_equals($checksum, $attempt->checksum),
            ]),
        ]);

        try {
            Mail::to(Auth::user()->email)->send(new SubscriptionConfirmationMail($subscription, $invoice));
        } catch (\Exception $e) {
            Log::error('Failed to send subscription confirmation email: ' . $e->getMessage());
        }

        $endsAt = $subscription->ends_at;
        $successMessage = 'Subscription activated successfully! Your plan is active until ' . $endsAt->format('F d, Y') . '.';
        $disk = Storage::disk('local');
        $hasInvoice = $invoice->invoice_pdf_path && $disk->exists($invoice->invoice_pdf_path);

        return redirect()->route('subscription')
            ->with('success', $successMessage . ($hasInvoice ? ' Your receipt is ready for download.' : ' Check your email for confirmation.'))
            ->with('invoice_id', $hasInvoice ? $invoice->id : null);
    }

    public function history()
    {
        $subscriptions = Subscription::where('user_id', Auth::id())
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $invoices = Invoice::where('user_id', Auth::id())
            ->with('subscription')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('subscription-history', compact('subscriptions', 'invoices'));
    }

    public function downloadInvoice($invoiceId)
    {
        $invoice = Invoice::where('id', $invoiceId)
            ->where('user_id', Auth::id())
            ->with(['subscription', 'user'])
            ->firstOrFail();

        $disk = Storage::disk('local');
        if ($invoice->invoice_pdf_path && $disk->exists($invoice->invoice_pdf_path)) {
            $abs = $disk->path($invoice->invoice_pdf_path);
            if (file_exists($abs)) {
                return response()->download($abs, 'invoice-' . $invoice->invoice_number . '.pdf', [
                    'Content-Type' => 'application/pdf',
                ]);
            }
        }

        $this->generateInvoicePDF($invoice, $invoice->subscription);

        if ($invoice->invoice_pdf_path && $disk->exists($invoice->invoice_pdf_path)) {
            $abs = $disk->path($invoice->invoice_pdf_path);
            if (file_exists($abs)) {
                return response()->download($abs, 'invoice-' . $invoice->invoice_number . '.pdf', [
                    'Content-Type' => 'application/pdf',
                ]);
            }
        }

        return redirect()->back()->with('error', 'Invoice PDF could not be generated.');
    }

    private function generateInvoicePDF($invoice, $subscription)
    {
        try {
            $pdf = Pdf::loadView('invoices.pdf', compact('invoice', 'subscription'));
            $pdf->setPaper('a4', 'portrait');

            $disk = Storage::disk('local');
            $disk->makeDirectory('invoices');

            $filename = 'invoices/invoice-' . $invoice->invoice_number . '.pdf';
            $disk->put($filename, $pdf->output());

            $invoice->update(['invoice_pdf_path' => $filename]);
        } catch (\Exception $e) {
            Log::error('Failed to generate invoice PDF: ' . $e->getMessage());
        }
    }

    private function validateCardNumber($cardNumber)
    {
        $cardNumber = preg_replace('/\D/', '', $cardNumber);

        if (strlen($cardNumber) < 13 || strlen($cardNumber) > 19) {
            return false;
        }

        $sum = 0;
        $numDigits = strlen($cardNumber);

        for ($i = 0; $i < $numDigits; $i++) {
            $digit = (int) $cardNumber[$i];
            $positionFromRight = $numDigits - 1 - $i;

            if ($positionFromRight % 2 == 1) {
                $digit *= 2;
            }
            if ($digit > 9) {
                $digit -= 9;
            }
            $sum += $digit;
        }

        return ($sum % 10) == 0;
    }
}
