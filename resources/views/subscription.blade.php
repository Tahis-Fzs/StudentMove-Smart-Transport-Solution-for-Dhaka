<x-app-layout title="Subscription">
@push('styles')
<link rel="stylesheet" href="/css/subscription.css">
@endpush

<div class="container">
    @if(session('success'))
        <div class="alert alert-success" style="background:linear-gradient(135deg,#0b6e6a,#1e2630);color:#fff;padding:1rem 1.25rem;border-radius:0.75rem;margin-bottom:1.25rem;box-shadow:0 12px 28px rgba(11,110,106,0.25);display:flex;align-items:center;gap:0.75rem;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            <div>
                <strong style="font-size:16px;display:block;margin-bottom:4px;">Purchase successful</strong>
                <span style="font-size:14px;opacity:0.95;">{{ session('success') }}</span>
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

    <div class="sub-masthead" data-reveal>
        <p class="sub-masthead__eyebrow">StudentMove · Passes</p>
        <h1 class="sub-masthead__title">Plans built for the commute</h1>
        <p class="sub-masthead__lede">Weekly, monthly, or a single ride — checkout via SSLCommerz when configured.</p>
    </div>

    <div class="card" data-reveal>
        <div class="plans-grid">
            @foreach($plans as $plan)
                <div class="plan-card" data-plan="{{ $plan['key'] }}">
                    @if(!empty($plan['tag']))
                        <span class="pill">{{ $plan['tag'] }}</span>
                    @endif
                    <div class="plan-title">{{ $plan['name'] }}</div>
                    <div class="plan-price">৳ {{ $plan['price'] }}</div>
                    <div class="plan-desc">{{ $plan['desc'] }}</div>
                    <button type="button" class="plan-cta choose-plan" data-plan="{{ $plan['key'] }}">Choose Plan</button>
                </div>
            @endforeach
        </div>
    </div>

<div class="card" style="margin-top: 20px;">
        <h2 class="mb-2">Checkout</h2>

        @if($activeSubscription ?? null)
            <div style="background:#ecfdf5;border:1px solid #a7f3d0;border-radius:0.75rem;padding:1rem 1.25rem;margin-bottom:1rem;">
                <strong style="color:#047857;">Active plan:</strong>
                {{ $activeSubscription->plan_name }}
                until {{ $activeSubscription->ends_at?->format('j M Y') }}.
                <a href="{{ route('subscription.history') }}" style="margin-left:0.5rem;">View history</a>
            </div>
        @endif

        @guest
            <p style="color:#5b6572;margin-bottom:1rem;">
                <a href="{{ route('login') }}" style="color:#0b6e6a;font-weight:600;">Sign in</a>
                or <a href="{{ route('register') }}" style="color:#0b6e6a;font-weight:600;">create an account</a>
                to purchase a pass.
            </p>
        @endguest

        @auth
        <form method="POST" action="{{ route('subscription.store') }}" class="checkout-form" id="checkout-form">
            @csrf
            <input type="hidden" name="plan_type" id="plan_type" value="weekly">

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Selected Plan</label>
                    <div id="selected-plan-label" class="pill">{{ $plans[0]['name'] ?? 'Weekly Pass' }}</div>
                </div>
                @if($sslEnabled)
                    <div class="form-group">
                        <label class="form-label">Payment</label>
                        <input type="hidden" name="payment_method" value="sslcommerz">
                        <div class="pill" style="background:#0b6e6a;color:#fff;">SSLCommerz (bKash · Nagad · Card)</div>
                        @if($sslSandbox)
                            <div class="pill" style="margin-top:8px;background:#fef3c7;color:#92400e;font-size:12px;">Sandbox mode — test payments only</div>
                        @else
                            <div class="pill" style="margin-top:8px;background:#ecfdf5;color:#047857;font-size:12px;">Live gateway — real charges</div>
                        @endif
                        <p class="academic-hint" style="margin-top:8px;font-size:13px;color:#5b6572;">
                            You’ll be redirected to SSLCommerz to pay — same flow as Star Cineplex ticket booking.
                            @if($sslSandbox)
                                Use sandbox test cards or mobile wallets on the SSLCommerz page.
                            @endif
                        </p>
                    </div>
                @else
                    <div class="form-group">
                        <label class="form-label">Payment Method</label>
                        <select name="payment_method" id="payment_method" class="form-input">
                            <option value="mobile_banking">Mobile Banking (demo)</option>
                            <option value="card">Card (demo)</option>
                        </select>
                        <p style="margin-top:8px;font-size:12px;color:#b42318;">
                            SSLCommerz not configured — demo checkout only. Add store credentials in <code>.env</code> for live gateway.
                        </p>
                    </div>
                @endif
            </div>

            @unless($sslEnabled)
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
            @endunless

            <button type="submit" class="plan-cta" id="submit-btn" style="margin-top:12px; min-width: 220px;">
                {{ $sslEnabled ? 'Pay with SSLCommerz' : 'Complete Purchase' }}
            </button>
            <div id="checkout-hint" style="margin-top:8px; font-size:13px; color:#b42318; display:none;">Please complete all required fields.</div>
        </form>
        @endauth
    </div>
</div>

@push('scripts')
<script>
    const submitBtn = document.querySelector('#submit-btn');
    const hint = document.getElementById('checkout-hint');
    const sslEnabled = @json($sslEnabled);
    const planMap = @json(collect($plans)->pluck('name', 'key'));

    document.querySelectorAll('.choose-plan').forEach(btn => {
        btn.addEventListener('click', () => {
            const plan = btn.dataset.plan;
            const planInput = document.getElementById('plan_type');
            const planLabel = document.getElementById('selected-plan-label');
            const checkoutForm = document.getElementById('checkout-form');
            if (planInput) planInput.value = plan;
            if (planLabel) planLabel.textContent = planMap[plan] || plan;
            if (checkoutForm) checkoutForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    const paymentMethod = document.getElementById('payment_method');
    const mobileFields = document.getElementById('mobile-fields');
    const cardFields = document.getElementById('card-fields');

    if (paymentMethod && mobileFields && cardFields) {
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
    }

    const validateForm = () => {
        const planInput = document.getElementById('plan_type');
        if (!planInput) return;

        let ok = true;

        if (!planInput.value) ok = false;

        if (!sslEnabled && paymentMethod) {
            const method = paymentMethod.value;
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
        }

        if (submitBtn) {
            submitBtn.disabled = !ok;
            submitBtn.style.opacity = ok ? '1' : '0.5';
            submitBtn.style.cursor = ok ? 'pointer' : 'not-allowed';
        }
        if (hint) {
            hint.style.display = ok ? 'none' : 'block';
        }
    };

    document.querySelectorAll('#checkout-form input, #checkout-form select').forEach(el => {
        el.addEventListener('input', validateForm);
        el.addEventListener('change', validateForm);
    });

    if (document.getElementById('checkout-form')) {
        validateForm();
    }

    const form = document.getElementById('checkout-form');
    if (form) {
        form.addEventListener('submit', function() {
            const btn = document.getElementById('submit-btn');
            if (btn) {
                btn.disabled = true;
                btn.textContent = sslEnabled ? 'Redirecting to SSLCommerz…' : 'Processing...';
                btn.style.opacity = '0.7';
            }
        });
    }

    @if(session('invoice_id'))
        (function() {
            const invoiceId = {{ session('invoice_id') }};
            if (invoiceId) {
                setTimeout(() => {
                    window.location.href = '{{ route("subscription.invoice.download", ["invoice" => session("invoice_id")]) }}';
                }, 500);
            }
        })();
    @endif

    @if(session('success'))
        setTimeout(() => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }, 100);
    @endif
</script>
@endpush
</x-app-layout>
