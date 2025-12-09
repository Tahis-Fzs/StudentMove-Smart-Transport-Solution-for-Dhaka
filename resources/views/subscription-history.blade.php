<x-app-layout>
    @push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .history-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .history-header {
            margin-bottom: 30px;
        }
        .history-header h1 {
            font-size: 2rem;
            color: #333;
            margin-bottom: 10px;
        }
        .tab-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 2px solid #e0e0e0;
        }
        .tab-btn {
            padding: 12px 24px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
            color: #666;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }
        .tab-btn.active {
            color: #667eea;
            border-bottom-color: #667eea;
            font-weight: 600;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .history-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .history-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        .card-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
        }
        .status-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-completed {
            background: #d4edda;
            color: #155724;
        }
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        .status-failed {
            background: #f8d7da;
            color: #721c24;
        }
        .status-cancelled {
            background: #e2e3e5;
            color: #383d41;
        }
        .card-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        .detail-item {
            display: flex;
            flex-direction: column;
        }
        .detail-label {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }
        .detail-value {
            font-size: 14px;
            color: #333;
            font-weight: 500;
        }
        .card-actions {
            display: flex;
            gap: 10px;
        }
        .btn {
            padding: 8px 16px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-primary {
            background: #667eea;
            color: white;
        }
        .btn-primary:hover {
            background: #5568d3;
        }
        .btn-outline {
            background: white;
            color: #667eea;
            border: 1px solid #667eea;
        }
        .btn-outline:hover {
            background: #f0f0f0;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }
        .empty-state i {
            font-size: 64px;
            margin-bottom: 20px;
        }
    </style>
    @endpush

    <div class="history-container">
        <div class="history-header">
            <h1><i class="bi bi-clock-history"></i> Subscription History</h1>
            <p>View all your subscriptions and invoices</p>
        </div>

        <div class="tab-buttons">
            <button class="tab-btn active" onclick="showTab('subscriptions')">
                <i class="bi bi-calendar-check"></i> Subscriptions
            </button>
            <button class="tab-btn" onclick="showTab('invoices')">
                <i class="bi bi-receipt"></i> Invoices
            </button>
        </div>

        <!-- Subscriptions Tab -->
        <div id="tab-subscriptions" class="tab-content active">
            @if($subscriptions->count() > 0)
                @foreach($subscriptions as $subscription)
                    <div class="history-card">
                        <div class="card-header">
                            <div class="card-title">{{ $subscription->plan_name }}</div>
                            <span class="status-badge status-{{ $subscription->status }}">
                                {{ ucfirst($subscription->status) }}
                            </span>
                        </div>
                        <div class="card-details">
                            <div class="detail-item">
                                <span class="detail-label">Amount Paid</span>
                                <span class="detail-value">৳{{ number_format($subscription->amount, 2) }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Payment Method</span>
                                <span class="detail-value">
                                    @if($subscription->payment_method === 'card')
                                        Card (****{{ $subscription->card_last_four }})
                                    @else
                                        {{ ucfirst($subscription->payment_provider) }}
                                    @endif
                                </span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Start Date</span>
                                <span class="detail-value">{{ $subscription->starts_at->format('M d, Y') }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Valid Until</span>
                                <span class="detail-value">{{ $subscription->ends_at->format('M d, Y') }}</span>
                            </div>
                            @if($subscription->transaction_id)
                            <div class="detail-item">
                                <span class="detail-label">Transaction ID</span>
                                <span class="detail-value">{{ $subscription->transaction_id }}</span>
                            </div>
                            @endif
                        </div>
                        <div class="card-actions">
                            @php
                                $invoice = \App\Models\Invoice::where('subscription_id', $subscription->id)->first();
                            @endphp
                            @if($invoice)
                                <a href="{{ route('subscription.invoice.download', $invoice->id) }}" class="btn btn-primary">
                                    <i class="bi bi-download"></i> Download Invoice
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach

                <div style="margin-top: 20px;">
                    {{ $subscriptions->links() }}
                </div>
            @else
                <div class="empty-state">
                    <i class="bi bi-inbox"></i>
                    <h3>No subscriptions found</h3>
                    <p>You haven't subscribed to any plan yet.</p>
                    <a href="{{ route('subscription') }}" class="btn btn-primary" style="margin-top: 20px;">
                        Browse Plans
                    </a>
                </div>
            @endif
        </div>

        <!-- Invoices Tab -->
        <div id="tab-invoices" class="tab-content">
            @if($invoices->count() > 0)
                @foreach($invoices as $invoice)
                    <div class="history-card">
                        <div class="card-header">
                            <div class="card-title">Invoice #{{ $invoice->invoice_number }}</div>
                            <span class="status-badge status-{{ $invoice->status }}">
                                {{ ucfirst($invoice->status) }}
                            </span>
                        </div>
                        <div class="card-details">
                            <div class="detail-item">
                                <span class="detail-label">Amount</span>
                                <span class="detail-value">৳{{ number_format($invoice->amount, 2) }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Plan</span>
                                <span class="detail-value">{{ $invoice->subscription->plan_name }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Issued Date</span>
                                <span class="detail-value">{{ $invoice->issued_at->format('M d, Y') }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Payment Method</span>
                                <span class="detail-value">{{ ucfirst(str_replace('_', ' ', $invoice->payment_method)) }}</span>
                            </div>
                        </div>
                        <div class="card-actions">
                            <a href="{{ route('subscription.invoice.download', $invoice->id) }}" class="btn btn-primary">
                                <i class="bi bi-download"></i> Download Invoice
                            </a>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="empty-state">
                    <i class="bi bi-receipt"></i>
                    <h3>No invoices found</h3>
                    <p>You don't have any invoices yet.</p>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });

            // Show selected tab
            document.getElementById('tab-' + tabName).classList.add('active');
            event.target.classList.add('active');
        }
    </script>
    @endpush
</x-app-layout>

