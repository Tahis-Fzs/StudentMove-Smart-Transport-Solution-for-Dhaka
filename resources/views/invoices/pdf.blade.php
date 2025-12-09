<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 40px;
            color: #333;
        }
        .invoice-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid #667eea;
        }
        .company-info h1 {
            margin: 0;
            color: #667eea;
            font-size: 28px;
        }
        .invoice-info {
            text-align: right;
        }
        .invoice-info h2 {
            margin: 0 0 10px 0;
            color: #333;
        }
        .invoice-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 40px;
        }
        .detail-section h3 {
            margin-top: 0;
            color: #667eea;
            font-size: 16px;
            text-transform: uppercase;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .detail-label {
            font-weight: 600;
            color: #666;
        }
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .invoice-table th {
            background: #667eea;
            color: white;
            padding: 12px;
            text-align: left;
        }
        .invoice-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        .invoice-table tr:last-child td {
            border-bottom: none;
        }
        .total-section {
            text-align: right;
            margin-top: 20px;
        }
        .total-row {
            display: flex;
            justify-content: flex-end;
            padding: 10px 0;
        }
        .total-label {
            width: 200px;
            font-weight: 600;
            text-align: right;
            padding-right: 20px;
        }
        .total-value {
            width: 150px;
            text-align: right;
        }
        .grand-total {
            font-size: 20px;
            font-weight: bold;
            color: #667eea;
            border-top: 2px solid #667eea;
            padding-top: 10px;
        }
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            text-align: center;
            color: #999;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="invoice-header">
        <div class="company-info">
            <h1>StudentMove</h1>
            <p>Smart Transport Solution for Dhaka City</p>
            <p>Email: support@studentmove.com</p>
        </div>
        <div class="invoice-info">
            <h2>INVOICE</h2>
            <p><strong>Invoice #:</strong> {{ $invoice->invoice_number }}</p>
            <p><strong>Date:</strong> {{ $invoice->issued_at->format('F d, Y') }}</p>
        </div>
    </div>

    <div class="invoice-details">
        <div class="detail-section">
            <h3>Bill To</h3>
            <div class="detail-row">
                <span class="detail-label">Name:</span>
                <span>{{ $invoice->user->name }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Email:</span>
                <span>{{ $invoice->user->email }}</span>
            </div>
            @if($invoice->user->phone)
            <div class="detail-row">
                <span class="detail-label">Phone:</span>
                <span>{{ $invoice->user->phone }}</span>
            </div>
            @endif
        </div>
        <div class="detail-section">
            <h3>Payment Information</h3>
            <div class="detail-row">
                <span class="detail-label">Payment Method:</span>
                <span>{{ ucfirst(str_replace('_', ' ', $invoice->payment_method)) }}</span>
            </div>
            @if($invoice->payment_provider)
            <div class="detail-row">
                <span class="detail-label">Provider:</span>
                <span>{{ ucfirst($invoice->payment_provider) }}</span>
            </div>
            @endif
            @if($invoice->transaction_id)
            <div class="detail-row">
                <span class="detail-label">Transaction ID:</span>
                <span>{{ $invoice->transaction_id }}</span>
            </div>
            @endif
            <div class="detail-row">
                <span class="detail-label">Status:</span>
                <span style="color: green; font-weight: 600;">Paid</span>
            </div>
        </div>
    </div>

    <table class="invoice-table">
        <thead>
            <tr>
                <th>Description</th>
                <th>Plan</th>
                <th>Duration</th>
                <th style="text-align: right;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $subscription->plan_name }}</td>
                <td>{{ ucfirst($subscription->plan_type) }}</td>
                <td>
                    @if($subscription->plan_type === 'monthly')
                        1 Month
                    @elseif($subscription->plan_type === '6months')
                        6 Months
                    @else
                        1 Year
                    @endif
                </td>
                <td style="text-align: right;">৳{{ number_format($invoice->amount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="total-section">
        <div class="total-row">
            <span class="total-label">Subtotal:</span>
            <span class="total-value">৳{{ number_format($invoice->amount, 2) }}</span>
        </div>
        <div class="total-row">
            <span class="total-label">Tax:</span>
            <span class="total-value">৳0.00</span>
        </div>
        <div class="total-row grand-total">
            <span class="total-label">Total:</span>
            <span class="total-value">৳{{ number_format($invoice->amount, 2) }}</span>
        </div>
    </div>

    <div class="footer">
        <p>Thank you for your subscription!</p>
        <p>This is a computer-generated invoice. No signature required.</p>
        <p>&copy; {{ date('Y') }} StudentMove. All rights reserved.</p>
    </div>
</body>
</html>

