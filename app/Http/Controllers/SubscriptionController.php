<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\Invoice;
use App\Models\PaymentAttempt;
use App\Mail\SubscriptionConfirmationMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class SubscriptionController extends Controller
{
    // #region agent log helper
    private function dbg(array $payload): void
    {
        $line = json_encode([
            'sessionId' => 'debug-session',
            'runId' => 'prefetch',
            'hypothesisId' => $payload['h'] ?? 'SUB',
            'location' => $payload['loc'] ?? 'SubscriptionController',
            'message' => $payload['msg'] ?? '',
            'data' => $payload['data'] ?? [],
            'timestamp' => round(microtime(true) * 1000),
        ]);
        if ($line !== false) {
            @file_put_contents(base_path('.cursor/debug.log'), $line . PHP_EOL, FILE_APPEND | LOCK_EX);
        }
    }
    // #endregion
    public function index()
    {
        $plans = [
            'monthly' => [
                'name' => 'Monthly Plan',
                'price' => 1500,
                'duration' => 1,
                'period' => 'month',
                'daily_price' => 50,
                'features' => [
                    'Real-time bus tracking',
                    'Route planning & scheduling',
                    'Delay notifications',
                    '24/7 customer support',
                    'Mobile app access',
                ]
            ],
            '6months' => [
                'name' => '6 Months Plan',
                'price' => 8100,
                'duration' => 6,
                'period' => '6 months',
                'daily_price' => 45,
                'savings' => 'Save 10%',
                'features' => [
                    'Everything in Monthly',
                    'Priority customer support',
                    'Advanced route analytics',
                    'Custom notifications',
                    'Exclusive student discounts',
                ]
            ],
            'yearly' => [
                'name' => '1 Year Plan',
                'price' => 14400,
                'duration' => 12,
                'period' => 'year',
                'daily_price' => 40,
                'savings' => 'Save 20%',
                'features' => [
                    'Everything in 6 Months',
                    'VIP customer support',
                    'Personal route optimization',
                    'Early access to new features',
                    'Free premium workshops',
                ]
            ],
        ];

        $activeSubscription = Auth::check() 
            ? Subscription::where('user_id', Auth::id())
                ->where('status', 'completed')
                ->where('ends_at', '>', now())
                ->latest()
                ->first()
            : null;

        return view('subscription', compact('plans', 'activeSubscription'));
    }

    public function store(Request $request)
    {
        $this->dbg(['h' => 'S1', 'loc' => 'store.start', 'msg' => 'store called', 'data' => ['user' => Auth::id(), 'plan' => $request->plan_type, 'payment_method' => $request->payment_method, 'transaction_id' => $request->transaction_id ?? 'null', 'payment_provider' => $request->payment_provider ?? 'null']]);

        try {
            $request->validate([
                'plan_type' => 'required|in:monthly,6months,yearly',
                'payment_method' => 'required|in:card,mobile_banking',
                'payment_provider' => 'required_if:payment_method,mobile_banking|nullable|in:bkash,nagad,rocket',
                'transaction_id' => 'required_if:payment_method,mobile_banking|nullable|string|max:255',
                'card_number' => 'required_if:payment_method,card|nullable|string|max:19',
                'card_expiry' => 'required_if:payment_method,card|nullable|string|max:5',
                'card_cvv' => 'required_if:payment_method,card|nullable|string|max:4',
                'card_name' => 'required_if:payment_method,card|nullable|string|max:255',
            ]);
            $this->dbg(['h' => 'S1b', 'loc' => 'validation.passed', 'msg' => 'validation passed']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dbg(['h' => 'S1b', 'loc' => 'validation.failed', 'msg' => 'validation failed', 'data' => ['errors' => $e->errors()]]);
            throw $e;
        }

        // Prevent duplicate successful payments with same transaction id/method
        if ($request->payment_method === 'mobile_banking' && $request->transaction_id) {
            $exists = PaymentAttempt::where('payment_method', 'mobile_banking')
                ->where('transaction_id', $request->transaction_id)
                ->where('status', 'success')
                ->exists();
            $this->dbg(['h' => 'S1c', 'loc' => 'duplicate.check', 'msg' => 'duplicate check', 'data' => ['exists' => $exists, 'transaction_id' => $request->transaction_id]]);
            if ($exists) {
                return back()->withErrors(['transaction_id' => 'This transaction ID was already used.'])->withInput();
            }
        }

        $planPrices = [
            'monthly' => 1500,
            '6months' => 8100,
            'yearly' => 14400,
        ];

        $planDurations = [
            'monthly' => 1,
            '6months' => 6,
            'yearly' => 12,
        ];

        $amount = $planPrices[$request->plan_type];
        $duration = $planDurations[$request->plan_type];

        $startsAt = now();
        $endsAt = $startsAt->copy()->addMonths($duration);

        $subscriptionData = [
            'user_id' => Auth::id(),
            'plan_type' => $request->plan_type,
            'amount' => $amount,
            'payment_method' => $request->payment_method,
            'status' => 'completed',
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
        ];

        if ($request->payment_method === 'mobile_banking') {
            $subscriptionData['payment_provider'] = $request->payment_provider;
            $subscriptionData['transaction_id'] = $request->transaction_id;
            
            // FR-20: Payment validation for mobile banking (basic validation)
            // In production, you would validate with bKash/Nagad API here
            $this->dbg(['h' => 'S1d', 'loc' => 'txn_id.check', 'msg' => 'checking transaction id length', 'data' => ['txn_id' => $request->transaction_id, 'length' => strlen($request->transaction_id ?? '')]]);
            if (!$request->transaction_id || strlen($request->transaction_id) < 10) {
                $this->dbg(['h' => 'S1d', 'loc' => 'txn_id.invalid', 'msg' => 'transaction id too short', 'data' => ['length' => strlen($request->transaction_id ?? '')]]);
                return back()->withErrors(['transaction_id' => 'Please enter a valid transaction ID.'])->withInput();
            }
        } else {
            $cardNumber = str_replace(' ', '', $request->card_number);
            $this->dbg(['h' => 'S1e', 'loc' => 'card.validation', 'msg' => 'validating card', 'data' => ['card_number_length' => strlen($cardNumber ?? ''), 'card_name' => $request->card_name ?? 'null', 'card_expiry' => $request->card_expiry ?? 'null', 'card_cvv' => $request->card_cvv ?? 'null']]);
            
            // FR-20: Basic card validation (Luhn algorithm check)
            $isValid = $this->validateCardNumber($cardNumber);
            $this->dbg(['h' => 'S1e', 'loc' => 'card.validation.result', 'msg' => 'card validation result', 'data' => ['is_valid' => $isValid]]);
            if (!$isValid) {
                $this->dbg(['h' => 'S1e', 'loc' => 'card.invalid', 'msg' => 'card validation failed, returning error']);
                return back()->withErrors(['card_number' => 'Invalid card number.'])->withInput();
            }
            
            $subscriptionData['card_last_four'] = substr($cardNumber, -4);
            $this->dbg(['h' => 'S1e', 'loc' => 'card.validated', 'msg' => 'card validated successfully']);
        }

        // Record payment attempt for audit/cross-check dataset
        $checksum = hash('sha256', implode('|', [
            Auth::id(),
            $request->plan_type,
            $amount,
            $request->payment_method,
            $request->payment_provider,
            $request->transaction_id,
            $startsAt->timestamp,
        ]));

        $attempt = PaymentAttempt::create([
            'user_id' => Auth::id(),
            'plan_type' => $request->plan_type,
            'amount' => $amount,
            'payment_method' => $request->payment_method,
            'payment_provider' => $request->payment_provider,
            'transaction_id' => $request->transaction_id,
            'card_last_four' => $subscriptionData['card_last_four'] ?? null,
            'status' => 'pending',
            'checksum' => $checksum,
            'meta' => [
                'client_ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ],
        ]);
        $this->dbg(['h' => 'S1a', 'loc' => 'payment_attempt.created', 'msg' => 'payment attempt created', 'data' => ['attempt_id' => $attempt->id, 'checksum' => $checksum]]);

        $subscription = Subscription::create($subscriptionData);
        $this->dbg(['h' => 'S2', 'loc' => 'subscription.created', 'msg' => 'subscription created', 'data' => ['id' => $subscription->id, 'plan' => $subscription->plan_type]]);

        // Generate Invoice (FR-21)
        $invoice = Invoice::create([
            'subscription_id' => $subscription->id,
            'user_id' => Auth::id(),
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'amount' => $amount,
            'plan_type' => $request->plan_type,
            'payment_method' => $request->payment_method,
            'payment_provider' => $request->payment_provider ?? null,
            'transaction_id' => $request->transaction_id ?? null,
            'status' => 'paid',
            'issued_at' => now(),
        ]);
        $this->dbg(['h' => 'S3', 'loc' => 'invoice.created', 'msg' => 'invoice created', 'data' => ['id' => $invoice->id, 'number' => $invoice->invoice_number]]);

        // Generate PDF Invoice (FR-21)
        $this->generateInvoicePDF($invoice, $subscription);

        // Mark payment attempt success and link invoice
        $attempt->update([
            'status' => 'success',
            'gateway_ref' => 'SIM-' . uniqid(),
            'meta' => array_merge($attempt->meta ?? [], [
                'invoice_id' => $invoice->id,
                'checksum_verified' => hash_equals($checksum, $attempt->checksum),
            ]),
        ]);
        $this->dbg(['h' => 'S3a', 'loc' => 'payment_attempt.success', 'msg' => 'payment attempt completed', 'data' => ['attempt_id' => $attempt->id, 'invoice_id' => $invoice->id]]);

        // Send Email Confirmation (FR-22)
        try {
            Mail::to(Auth::user()->email)->send(new SubscriptionConfirmationMail($subscription, $invoice));
        } catch (\Exception $e) {
            // Log error but don't fail the subscription
            \Log::error('Failed to send subscription confirmation email: ' . $e->getMessage());
        }

        // Prepare success message
        $successMessage = 'Subscription activated successfully! Your plan is active until ' . $endsAt->format('F d, Y') . '.';

        // Always redirect with success message and invoice ID for auto-download
        $disk = Storage::disk('local');
        $hasInvoice = $invoice->invoice_pdf_path && $disk->exists($invoice->invoice_pdf_path) && file_exists($disk->path($invoice->invoice_pdf_path));
        
        $this->dbg(['h' => 'S4', 'loc' => 'redirect.prepare', 'msg' => 'preparing redirect', 'data' => ['has_invoice' => $hasInvoice, 'invoice_id' => $invoice->id]]);

        return redirect()->route('subscription')
            ->with('success', $successMessage . ($hasInvoice ? ' Your receipt is ready for download.' : ' Check your email for confirmation.'))
            ->with('invoice_id', $hasInvoice ? $invoice->id : null);
    }

    /**
     * Display subscription history (FR-24)
     */
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

    /**
     * Download invoice PDF (FR-21)
     */
    public function downloadInvoice($invoiceId)
    {
        $invoice = Invoice::where('id', $invoiceId)
            ->where('user_id', Auth::id())
            ->with(['subscription', 'user'])
            ->firstOrFail();

        // If file exists, serve it
        $disk = Storage::disk('local');
        if ($invoice->invoice_pdf_path && $disk->exists($invoice->invoice_pdf_path)) {
            $abs = $disk->path($invoice->invoice_pdf_path);
            if (file_exists($abs)) {
                return response()->download($abs, 'invoice-' . $invoice->invoice_number . '.pdf', [
                    'Content-Type' => 'application/pdf',
                ]);
            }
            $this->dbg(['h' => 'D1', 'loc' => 'downloadInvoice.missing-file', 'msg' => 'path missing on disk despite exists()', 'data' => ['abs' => $abs]]);
        }

        // Generate PDF if not exists or missing
        $this->generateInvoicePDF($invoice, $invoice->subscription);
        
        if ($invoice->invoice_pdf_path && $disk->exists($invoice->invoice_pdf_path)) {
            $abs = $disk->path($invoice->invoice_pdf_path);
            if (file_exists($abs)) {
                return response()->download($abs, 'invoice-' . $invoice->invoice_number . '.pdf', [
                    'Content-Type' => 'application/pdf',
                ]);
            }
            $this->dbg(['h' => 'D2', 'loc' => 'downloadInvoice.missing-after-generate', 'msg' => 'path missing on disk after regeneration', 'data' => ['abs' => $abs]]);
        }

        return redirect()->back()->with('error', 'Invoice PDF could not be generated.');
    }

    /**
     * Generate Invoice PDF (FR-21)
     */
    private function generateInvoicePDF($invoice, $subscription)
    {
        try {
            $this->dbg(['h' => 'S5', 'loc' => 'pdf.start', 'msg' => 'starting PDF generation', 'data' => ['invoice_number' => $invoice->invoice_number]]);
            
            // Generate PDF from invoice view
            $pdf = Pdf::loadView('invoices.pdf', compact('invoice', 'subscription'));
            $pdf->setPaper('a4', 'portrait');
            $this->dbg(['h' => 'S5', 'loc' => 'pdf.loaded', 'msg' => 'PDF object created']);

            // Use 'local' disk explicitly to ensure we're writing to storage/app
            $disk = Storage::disk('local');
            
            // Ensure directory exists
            $disk->makeDirectory('invoices');

            // Save PDF invoice
            $filename = 'invoices/invoice-' . $invoice->invoice_number . '.pdf';
            $pdfContent = $pdf->output();
            $this->dbg(['h' => 'S5', 'loc' => 'pdf.output', 'msg' => 'PDF content generated', 'data' => ['content_length' => strlen($pdfContent)]]);
            
            $disk->put($filename, $pdfContent);
            
            // Verify file was actually written (use disk->path() to get correct absolute path)
            $absPath = $disk->path($filename);
            $exists = file_exists($absPath);
            $this->dbg(['h' => 'S5', 'loc' => 'invoice.generated', 'msg' => 'invoice PDF saved', 'data' => ['path' => $filename, 'abs_path' => $absPath, 'file_exists' => $exists, 'disk_exists' => $disk->exists($filename)]]);

            $invoice->update(['invoice_pdf_path' => $filename]);
        } catch (\Exception $e) {
            \Log::error('Failed to generate invoice PDF: ' . $e->getMessage());
            $this->dbg(['h' => 'S5', 'loc' => 'invoice.error', 'msg' => 'generation failed', 'data' => ['error' => $e->getMessage(), 'class' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine()]]);
        }
    }

    /**
     * Validate card number using Luhn algorithm (FR-20)
     */
    private function validateCardNumber($cardNumber)
    {
        $cardNumber = preg_replace('/\D/', '', $cardNumber);
        
        if (strlen($cardNumber) < 13 || strlen($cardNumber) > 19) {
            return false;
        }

        $sum = 0;
        $numDigits = strlen($cardNumber);
        // Luhn algorithm: double every second digit from the RIGHT
        // For 16 digits: double at positions 0,2,4,6,8,10,12,14 (from left, 0-indexed)
        // This corresponds to positions 15,13,11,9,7,5,3,1 from right
        
        for ($i = 0; $i < $numDigits; $i++) {
            $digit = (int)$cardNumber[$i];
            $positionFromRight = $numDigits - 1 - $i;
            
            // Double every second digit from the right (positions 1,3,5,7... from right)
            if ($positionFromRight % 2 == 1) {
                $digit *= 2;
            }
            if ($digit > 9) {
                $digit -= 9;
            }
            $sum += $digit;
        }

        $isValid = ($sum % 10) == 0;
        $this->dbg(['h' => 'S1f', 'loc' => 'luhn.calc', 'msg' => 'luhn calculation', 'data' => ['card' => substr($cardNumber, 0, 4) . '****', 'sum' => $sum, 'sum_mod_10' => $sum % 10, 'is_valid' => $isValid]]);
        return $isValid;
    }
}