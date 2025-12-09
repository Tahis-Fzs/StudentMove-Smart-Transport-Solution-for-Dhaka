<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\Invoice;
use App\Mail\SubscriptionConfirmationMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class SubscriptionController extends Controller
{
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
            if (!$request->transaction_id || strlen($request->transaction_id) < 10) {
                return back()->withErrors(['transaction_id' => 'Please enter a valid transaction ID.'])->withInput();
            }
        } else {
            $cardNumber = str_replace(' ', '', $request->card_number);
            
            // FR-20: Basic card validation (Luhn algorithm check)
            if (!$this->validateCardNumber($cardNumber)) {
                return back()->withErrors(['card_number' => 'Invalid card number.'])->withInput();
            }
            
            $subscriptionData['card_last_four'] = substr($cardNumber, -4);
        }

        $subscription = Subscription::create($subscriptionData);

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

        // Generate PDF Invoice (FR-21)
        $this->generateInvoicePDF($invoice, $subscription);

        // Send Email Confirmation (FR-22)
        try {
            Mail::to(Auth::user()->email)->send(new SubscriptionConfirmationMail($subscription, $invoice));
        } catch (\Exception $e) {
            // Log error but don't fail the subscription
            \Log::error('Failed to send subscription confirmation email: ' . $e->getMessage());
        }

        return redirect()->route('subscription')
            ->with('success', 'Subscription activated successfully! Your plan is active until ' . $endsAt->format('F d, Y') . '. Check your email for confirmation.');
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

        if ($invoice->invoice_pdf_path && Storage::exists($invoice->invoice_pdf_path)) {
            return Storage::download($invoice->invoice_pdf_path, 'invoice-' . $invoice->invoice_number . '.pdf');
        }

        // Generate PDF if not exists
        $this->generateInvoicePDF($invoice, $invoice->subscription);
        
        if ($invoice->invoice_pdf_path && Storage::exists($invoice->invoice_pdf_path)) {
            return Storage::download($invoice->invoice_pdf_path, 'invoice-' . $invoice->invoice_number . '.pdf');
        }

        return redirect()->back()->with('error', 'Invoice PDF could not be generated.');
    }

    /**
     * Generate Invoice PDF (FR-21)
     */
    private function generateInvoicePDF($invoice, $subscription)
    {
        try {
            $html = view('invoices.pdf', compact('invoice', 'subscription'))->render();
            
            // For now, save HTML as invoice (you can use DomPDF later)
            $filename = 'invoices/invoice-' . $invoice->invoice_number . '.html';
            Storage::put($filename, $html);
            
            $invoice->update(['invoice_pdf_path' => $filename]);
        } catch (\Exception $e) {
            \Log::error('Failed to generate invoice PDF: ' . $e->getMessage());
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
        $parity = $numDigits % 2;

        for ($i = 0; $i < $numDigits; $i++) {
            $digit = (int)$cardNumber[$i];
            if ($i % 2 == $parity) {
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