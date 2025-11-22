<x-app-layout>
    @push('styles')
    <link rel="stylesheet" href="{{ asset('css/notification.css') }}">
    <link rel="stylesheet" href="{{ asset('css/subscription.css') }}">
    @endpush

    <div class="subscription-container">
        <div class="subscription-title"><i class="bi bi-star-fill"></i> Choose Your Plan</div>
        <div class="subscription-desc">Select the perfect plan for your journey. All plans include real-time tracking and notifications.</div>

        @if(session('success'))
            <div class="alert alert-success" style="background: #d4edda; color: #155724; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
                <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger" style="background: #f8d7da; color: #721c24; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
                <i class="bi bi-exclamation-triangle-fill"></i> Please fix the following errors:
                <ul style="margin: 8px 0 0 20px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(isset($activeSubscription) && $activeSubscription)
            <div class="alert alert-info" style="background: #d1ecf1; color: #0c5460; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #bee5eb;">
                <i class="bi bi-info-circle-fill"></i> You have an active subscription: <strong>{{ $activeSubscription->plan_name }}</strong> 
                (Valid until {{ $activeSubscription->ends_at->format('F d, Y') }})
            </div>
        @endif

        <!-- Package Selection -->
        <div class="packages-grid">
            @foreach($plans as $planKey => $plan)
                <div class="package-card {{ $planKey === '6months' ? 'popular' : '' }}" 
                     data-plan="{{ $planKey }}" 
                     data-price="{{ $plan['price'] }}" 
                     data-name="{{ $plan['name'] }}">
                    @if($planKey === '6months')
                        <div class="popular-badge">Most Popular</div>
                    @endif
                    <div class="package-name">{{ $plan['name'] }}</div>
                    <div class="package-price">৳{{ number_format($plan['price'], 0) }}</div>
                    <div class="package-period">/{{ $plan['period'] }}</div>
                    <div class="package-daily">
                        BDT {{ $plan['daily_price'] }} per day
                        @if(isset($plan['savings']))
                            <span style="color: #22c55e; font-weight: 600;">({{ $plan['savings'] }})</span>
                        @endif
                    </div>
                    <ul class="package-features">
                        @foreach($plan['features'] as $feature)
                            <li><i class="bi bi-check-circle-fill"></i>{{ $feature }}</li>
                        @endforeach
                    </ul>
                    <button class="choose-btn" onclick="selectPackage('{{ $planKey }}', '{{ $plan['name'] }}', '৳{{ number_format($plan['price'], 0) }}')">
                        Choose {{ $planKey === 'monthly' ? 'Monthly' : ($planKey === '6months' ? '6 Months' : 'Yearly') }}
                    </button>
                </div>
            @endforeach
        </div>

        <!-- Payment Section -->
        @auth
        <div class="payment-section" id="payment-section" style="display: none;">
            <div class="selected-package-info">
                <h4>Selected Plan: <span id="selected-plan-name">-</span></h4>
                <p>Total Amount: <span id="selected-plan-price">-</span></p>
            </div>

            <div class="payment-tabs">
                <button type="button" class="payment-tab active" id="tab-card" onclick="showTab('card')">
                    <i class="bi bi-credit-card"></i> Card Payment
                </button>
                <button type="button" class="payment-tab" id="tab-mobile" onclick="showTab('mobile')">
                    <i class="bi bi-phone"></i> Mobile Banking
                </button>
            </div>

            <form class="payment-form" id="form-card" method="POST" action="{{ route('subscription.store') }}">
                @csrf
                <input type="hidden" name="plan_type" id="plan-type-card" value="">
                <input type="hidden" name="payment_method" value="card">
                
                <div class="form-group">
                    <label class="form-label">Card Number</label>
                    <input type="text" name="card_number" class="form-input card-number-input" placeholder="1234 5678 9012 3456" maxlength="19" required />
                </div>
                <div class="form-group" style="display:flex; gap:10px;">
                    <div style="flex:1;">
                        <label class="form-label">Expiry</label>
                        <input type="text" name="card_expiry" class="form-input card-expiry-input" placeholder="MM/YY" maxlength="5" required />
                    </div>
                    <div style="flex:1;">
                        <label class="form-label">CVV</label>
                        <input type="password" name="card_cvv" class="form-input" placeholder="123" maxlength="4" required />
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Name on Card</label>
                    <input type="text" name="card_name" class="form-input" placeholder="Full Name" required />
                </div>
                <button type="submit" class="subscribe-btn">
                    <i class="bi bi-lock-fill"></i> Subscribe Now
                </button>
            </form>

            <form class="payment-form" id="form-mobile" style="display:none;" method="POST" action="{{ route('subscription.store') }}">
                @csrf
                <input type="hidden" name="plan_type" id="plan-type-mobile" value="">
                <input type="hidden" name="payment_method" value="mobile_banking">
                
                <div class="form-group">
                    <label class="form-label">Provider</label>
                    <select name="payment_provider" class="form-select" required>
                        <option value="">Select Provider</option>
                        <option value="bkash">bKash</option>
                        <option value="nagad">Nagad</option>
                        <option value="rocket">Rocket</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Account/Number</label>
                    <input type="text" name="account_number" class="form-input" placeholder="01XXXXXXXXX" maxlength="15" />
                </div>
                <div class="form-group">
                    <label class="form-label">Transaction ID</label>
                    <input type="text" name="transaction_id" class="form-input" placeholder="Enter Transaction ID" required />
                </div>
                <button type="submit" class="subscribe-btn">
                    <i class="bi bi-lock-fill"></i> Subscribe Now
                </button>
            </form>
        </div>
        @else
        <div class="alert alert-warning" style="background: #fff3cd; color: #856404; padding: 12px 16px; border-radius: 8px; margin-top: 20px; border: 1px solid #ffeaa7; text-align: center;">
            <i class="bi bi-lock-fill"></i> Please <a href="{{ route('login') }}" style="color: #0d6efd; font-weight: 600;">login</a> to subscribe to a plan.
        </div>
        @endauth
    </div>

    @push('scripts')
    <script>
        let selectedPlan = null;

        function selectPackage(plan, name, price) {
            selectedPlan = plan;
            
            // Update selected package info
            document.getElementById('selected-plan-name').textContent = name;
            document.getElementById('selected-plan-price').textContent = price;
            
            // Set plan type in forms
            document.getElementById('plan-type-card').value = plan;
            document.getElementById('plan-type-mobile').value = plan;
            
            // Show payment section
            document.getElementById('payment-section').style.display = 'block';
            
            // Scroll to payment section
            document.getElementById('payment-section').scrollIntoView({ behavior: 'smooth' });
        }

        function showTab(tabName) {
            // Hide all forms
            document.getElementById('form-card').style.display = 'none';
            document.getElementById('form-mobile').style.display = 'none';
            
            // Remove active class from all tabs
            document.querySelectorAll('.payment-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show selected form and activate tab
            if (tabName === 'card') {
                document.getElementById('form-card').style.display = 'block';
                document.getElementById('tab-card').classList.add('active');
            } else {
                document.getElementById('form-mobile').style.display = 'block';
                document.getElementById('tab-mobile').classList.add('active');
            }
        }

        // Card number formatting
        document.addEventListener('DOMContentLoaded', function() {
            const cardNumberInput = document.querySelector('.card-number-input');
            if (cardNumberInput) {
                cardNumberInput.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\s/g, '').replace(/[^0-9]/gi, '');
                    let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
                    e.target.value = formattedValue;
                });
            }

            // Expiry date formatting
            const cardExpiryInput = document.querySelector('.card-expiry-input');
            if (cardExpiryInput) {
                cardExpiryInput.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    if (value.length >= 2) {
                        value = value.substring(0, 2) + '/' + value.substring(2, 4);
                    }
                    e.target.value = value;
                });
            }
        });
    </script>
    @endpush
</x-app-layout>