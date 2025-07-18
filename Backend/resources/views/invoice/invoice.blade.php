<!DOCTYPE html>
<html>
<head>
    <title>Invoice #{{ $payment->id }}</title>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans&family=Roboto:wght@400;500;700&display=swap');

    body {
        font-family: 'Plus Jakarta Sans', 'Roboto', DejaVu Sans, sans-serif;
        background-color: #F7F8F9;
        color: #333333;
        margin: 0;
        padding: 2rem;
    }

        .container {
            max-width: 440px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            padding: 2rem 1.5rem;
            text-align: center;
        }

        .header {
            border-bottom: 4px solid #FFB23F;
            padding-bottom: 1rem;
            margin-bottom: 2rem;
        }

        .header img {
            max-height: 80px;
            margin-bottom: 0.75rem;
        }

        .title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .invoice-id {
            color: #FFB23F;
            font-weight: 700;
            font-size: 1.2rem;
        }

        .details {
            margin-top: 1.5rem;
            font-size: 1rem;
            text-align: left;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #ccc;
        }

        .detail-key {
            font-weight: 500;
            color: #555;
        }

        .detail-value {
            font-weight: 600;
            color: #333;
            text-align: right;
            text-align: right;
            flex: 1;
        }

        .highlight-box {
            background-color: #FFB23F;
            color: white;
            border-radius: 10px;
            padding: 1rem;
            margin: 2rem 0 1rem;
            font-weight: 600;
            text-align: center;
        }

        .footer {
            font-size: 0.85rem;
            color: #888;
            text-align: center;
            margin-top: 2rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            @if(file_exists(public_path('assets/logo.png')))
                <img src="{{ public_path('assets/logo.png') }}" alt="{{ config('app.name') }} Logo">
            @else
                <h1>{{ strtoupper(substr(config('app.name'), 0, 1)) }}</h1>
            @endif
            <div class="title">Payment Details</div>
            <div class="invoice-id">Invoice #{{ $payment->id }}</div>
        </div>

        <div class="details">
            <div class="detail-row">
                <div class="detail-key">User</div>
                <div class="detail-value">{{ $payment->user->name }}</div>
            </div>
            <div class="detail-row">
                <div class="detail-key">Subscription</div>
                <div class="detail-value">{{ $payment->subscription->name }}</div>
            </div>
            <div class="detail-row">
                <div class="detail-key">Amount</div>
                <div class="detail-value">NPR {{ number_format($payment->amount, 2) }}</div>
            </div>
            <div class="detail-row">
                <div class="detail-key">Paid At</div>
                <div class="detail-value">{{ $payment->paid_at->format('Y-m-d H:i') }}</div>
            </div>
             <div class="detail-row" style="border-bottom: none;">
        <div class="detail-key">Expires At</div>
        <div class="detail-value">{{ $payment->expires_at->format('Y-m-d H:i') }}</div>
    </div>
        </div>
          <div style="border-bottom: 4px solid #FFB23F; padding-bottom: 1rem; margin-bottom: 2rem;"></div>
        <div class="highlight-box">
            Thank you for your subscription!
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name') }} — Explore Experience Share
        </div>
    </div>
</body>
</html>
