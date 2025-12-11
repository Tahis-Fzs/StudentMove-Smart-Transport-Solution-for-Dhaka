<x-app-layout title="Subscription">
@push('styles')
<link rel="stylesheet" href="/css/subscription.css">
@endpush

<div class="container">
    @if(session('success'))
        <div class="alert alert-success" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 16px 20px; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3); display: flex; align-items: center; gap: 12px;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            <div>
                <strong style="font-size: 16px; display: block; margin-bottom: 4px;">Purchase Successful!</strong>
                <span style="font-size: 14px; opacity: 0.95;">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if(isset($errors) && $errors->any())
        <div class="alert alert-error" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white; padding: 16px 20px; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);">
            <div style="display: flex; align-items: flex-start; gap: 12px;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink: 0; margin-top: 2px;">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <div>
                    <strong style="font-size: 16px; display: block; margin-bottom: 8px;">Purchase Failed</strong>
                    <ul style="margin: 0; padding-left: 20px; list-style: disc;">
                        @foreach($errors->all() as $error)
                            <li style="margin-bottom: 4px;">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-error" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white; padding: 16px 20px; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);">
            <div style="display: flex; align-items: flex-start; gap: 12px;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink: 0; margin-top: 2px;">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <div>
                    <strong style="font-size: 16px; display: block; margin-bottom: 4px;">Purchase Failed</strong>
                    <span style="font-size: 14px; opacity: 0.95;">{{ session('error') }}</span>
                </div>
            </div>
        </div>
    @endif

    <div class="card">
        <h1 class="mb-2">Subscriptions</h1>
        <p>Select a plan to continue.</p>

        @php
            $plans = [
                ['key' => 'monthly', 'title' => 'Weekly Pass', 'price' => 350, 'desc' => 'Unlimited rides for 7 days', 'tag' => 'Most Popular'],
                ['key' => '6months', 'title' => 'Monthly Pass', 'price' => 1200, 'desc' => 'Best for regular commuters', 'tag' => 'Best Value'],
                ['key' => 'yearly', 'title' => 'Single Ride', 'price' => 30, 'desc' => 'Pay as you go', 'tag' => null],
            ];
        @endphp

        <div class="plans-grid">
            @foreach($plans as $plan)
                <div class="plan-card" data-plan="{{ $plan['key'] }}">
                    @if($plan['tag'])
                        <span class="pill">{{ $plan['tag'] }}</span>
                    @endif
                    <div class="plan-title">{{ $plan['title'] }}</div>
                    <div class="plan-price">৳ {{ $plan['price'] }}</div>
                    <div class="plan-desc">{{ $plan['desc'] }}</div>
                    <button type="button" class="plan-cta choose-plan" data-plan="{{ $plan['key'] }}">Choose Plan</button>
                </div>
            @endforeach
        </div>
    </div>

<div class="card" style="margin-top: 20px;">
        <h2 class="mb-2">Checkout</h2>
        <form method="POST" action="{{ route('subscription.store') }}" class="checkout-form" id="checkout-form">
            @csrf
            <input type="hidden" name="plan_type" id="plan_type" value="monthly">

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Selected Plan</label>
                    <div id="selected-plan-label" class="pill">Weekly Pass</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Payment Method</label>
                    <select name="payment_method" id="payment_method" class="form-input">
                        <option value="mobile_banking">Mobile Banking</option>
                        <option value="card">Card</option>
                    </select>
                </div>
            </div>

            <div id="mobile-fields" class="form-row">
                <div class="form-group">
                    <label class="form-label">Provider</label>
                    <select name="payment_provider" id="payment_provider" class="form-input">
                        <option value="">Select Provider</option>
                        <option value="bkash" {{ old('payment_provider') == 'bkash' ? 'selected' : '' }}>bKash</option>
                        <option value="nagad" {{ old('payment_provider') == 'nagad' ? 'selected' : '' }}>Nagad</option>
                        <option value="rocket" {{ old('payment_provider') == 'rocket' ? 'selected' : '' }}>Rocket</option>
                    </select>
                    @error('payment_provider')
                        <div class="error-message" style="color:#ef4444; font-size:12px; margin-top:4px;">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Transaction ID</label>
                    <input type="text" name="transaction_id" id="transaction_id" class="form-input" placeholder="Enter transaction ID (min 10 chars)" value="{{ old('transaction_id') }}">
                    @error('transaction_id')
                        <div class="error-message" style="color:#ef4444; font-size:12px; margin-top:4px;">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div id="card-fields" class="form-row" style="display:none;">
                <div class="form-group">
                    <label class="form-label">Card Number</label>
                    <input type="text" name="card_number" class="form-input" placeholder="1234 5678 9012 3456">
                </div>
                <div class="form-group">
                    <label class="form-label">Name on Card</label>
                    <input type="text" name="card_name" class="form-input" placeholder="Full name">
                </div>
                <div class="form-group">
                    <label class="form-label">Expiry (MM/YY)</label>
                    <input type="text" name="card_expiry" class="form-input" placeholder="12/29">
                </div>
                <div class="form-group">
                    <label class="form-label">CVV</label>
                    <input type="text" name="card_cvv" class="form-input" placeholder="123">
                </div>
            </div>

            <button type="submit" class="plan-cta" id="submit-btn" style="margin-top:12px; width: 220px;">Complete Purchase</button>
            <div id="checkout-hint" style="margin-top:8px; font-size:13px; color:#fca5a5; display:none;">Please complete all required fields.</div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    const submitBtn = document.querySelector('#submit-btn');
    const hint = document.getElementById('checkout-hint');
    const planMap = {
        monthly: 'Weekly Pass',
        '6months': 'Monthly Pass',
        yearly: 'Single Ride'
    };

    document.querySelectorAll('.choose-plan').forEach(btn => {
        btn.addEventListener('click', () => {
            const plan = btn.dataset.plan;
            document.getElementById('plan_type').value = plan;
            document.getElementById('selected-plan-label').textContent = planMap[plan] || plan;
        });
    });

    const paymentMethod = document.getElementById('payment_method');
    const mobileFields = document.getElementById('mobile-fields');
    const cardFields = document.getElementById('card-fields');

    paymentMethod.addEventListener('change', () => {
        const method = paymentMethod.value;
        if (method === 'card') {
            mobileFields.style.display = 'none';
            cardFields.style.display = 'grid';
        } else {
            mobileFields.style.display = 'grid';
            cardFields.style.display = 'none';
        }
        validateForm();
    });

    const validateForm = () => {
        const method = paymentMethod.value;
        let ok = true;

        if (!document.getElementById('plan_type').value) ok = false;

        if (method === 'mobile_banking') {
            const provider = document.querySelector('[name="payment_provider"]').value;
            const txn = document.querySelector('[name="transaction_id"]').value.trim();
            if (!provider) ok = false;
            if (!txn || txn.length < 10) ok = false;
        } else if (method === 'card') {
            const cardNumber = document.querySelector('[name="card_number"]').value.replace(/\s+/g, '');
            const cardName = document.querySelector('[name="card_name"]').value.trim();
            const cardExpiry = document.querySelector('[name="card_expiry"]').value.trim();
            const cardCvv = document.querySelector('[name="card_cvv"]').value.trim();
            if (!cardNumber || cardNumber.length < 13) ok = false;
            if (!cardName) ok = false;
            if (!cardExpiry || cardExpiry.length < 4) ok = false;
            if (!cardCvv || cardCvv.length < 3) ok = false;
        } else {
            ok = false;
        }

        if (submitBtn) {
            submitBtn.disabled = !ok;
            submitBtn.style.opacity = ok ? '1' : '0.5';
            submitBtn.style.cursor = ok ? 'pointer' : 'not-allowed';
        }
        if (hint) {
            hint.style.display = ok ? 'none' : 'block';
            if (!ok && method === 'mobile_banking') {
                hint.textContent = 'Enter provider and transaction ID (min 10 chars).';
            } else if (!ok && method === 'card') {
                hint.textContent = 'Fill all card fields with valid values.';
            } else {
                hint.textContent = 'Please complete all required fields.';
            }
        }
    };

    document.querySelectorAll('#checkout-form input, #checkout-form select').forEach(el => {
        el.addEventListener('input', validateForm);
        el.addEventListener('change', validateForm);
    });

    validateForm();

    // Handle form submission feedback
    const form = document.getElementById('checkout-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submit-btn');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Processing...';
                submitBtn.style.opacity = '0.7';
            }
        });
    }

    // Auto-download invoice PDF if invoice_id is present
    @if(session('invoice_id'))
        (function() {
            const invoiceId = {{ session('invoice_id') }};
            if (invoiceId) {
                // Trigger download after a short delay to ensure page loads
                setTimeout(() => {
                    window.location.href = '{{ route("subscription.invoice.download", ["invoice" => session("invoice_id")]) }}';
                }, 500);
            }
        })();
    @endif

    // Show success message if present
    @if(session('success'))
        // Scroll to top to show success message
        setTimeout(() => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }, 100);
    @endif
</script>
@endpush
</x-app-layout>
