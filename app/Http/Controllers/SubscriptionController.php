<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        } else {
            $cardNumber = str_replace(' ', '', $request->card_number);
            $subscriptionData['card_last_four'] = substr($cardNumber, -4);
        }

        $subscription = Subscription::create($subscriptionData);

        return redirect()->route('subscription')
            ->with('success', 'Subscription activated successfully! Your plan is active until ' . $endsAt->format('F d, Y'));
    }
}