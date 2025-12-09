<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Confirmation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .content {
            background: #f9f9f9;
            padding: 30px;
            border-radius: 0 0 10px 10px;
        }
        .success-icon {
            font-size: 48px;
            margin-bottom: 20px;
        }
        .info-box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #667eea;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .label {
            font-weight: bold;
            color: #666;
        }
        .value {
            color: #333;
        }
        .button {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            color: #999;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="success-icon">✓</div>
        <h1>Subscription Confirmed!</h1>
        <p>Thank you for subscribing to StudentMove</p>
    </div>
    
    <div class="content">
        <p>Dear {{ $subscription->user->name }},</p>
        
        <p>Your subscription has been successfully activated. Here are your subscription details:</p>
        
        <div class="info-box">
            <div class="info-row">
                <span class="label">Plan:</span>
                <span class="value">{{ $subscription->plan_name }}</span>
            </div>
            <div class="info-row">
                <span class="label">Amount Paid:</span>
                <span class="value">৳{{ number_format($subscription->amount, 2) }}</span>
            </div>
            <div class="info-row">
                <span class="label">Payment Method:</span>
                <span class="value">
                    @if($subscription->payment_method === 'card')
                        Card (****{{ $subscription->card_last_four }})
                    @else
                        {{ ucfirst($subscription->payment_provider) }}
                    @endif
                </span>
            </div>
            @if($subscription->transaction_id)
            <div class="info-row">
                <span class="label">Transaction ID:</span>
                <span class="value">{{ $subscription->transaction_id }}</span>
            </div>
            @endif
            <div class="info-row">
                <span class="label">Start Date:</span>
                <span class="value">{{ $subscription->starts_at->format('F d, Y') }}</span>
            </div>
            <div class="info-row">
                <span class="label">Valid Until:</span>
                <span class="value">{{ $subscription->ends_at->format('F d, Y') }}</span>
            </div>
        </div>

        @if($invoice)
        <p>Your invoice number is: <strong>{{ $invoice->invoice_number }}</strong></p>
        <p>You can download your invoice from your subscription history.</p>
        @endif

        <p>You can now enjoy all the features of your {{ $subscription->plan_name }}!</p>
        
        <a href="{{ route('subscription.history') }}" class="button">View Subscription History</a>
        
        <p>If you have any questions, please don't hesitate to contact our support team.</p>
        
        <p>Best regards,<br>StudentMove Team</p>
    </div>
    
    <div class="footer">
        <p>This is an automated email. Please do not reply to this message.</p>
        <p>&copy; {{ date('Y') }} StudentMove. All rights reserved.</p>
    </div>
</body>
</html>

