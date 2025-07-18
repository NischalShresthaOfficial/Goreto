<!DOCTYPE html>
<html>
<head>
    <title>Invoice #{{ $payment->id }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto&display=swap');

        body {
            font-family: 'Roboto', DejaVu Sans, sans-serif;
            background-color: #F7F8F9; /* secondary */
            color: #333333;
            margin: 0;
            padding: 2rem;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            padding: 2rem 3rem;
        }
        .header {
            text-align: center;
            border-bottom: 4px solid #FFB23F; /* primary */
            padding-bottom: 1rem;
            margin-bottom: 2rem;
        }
        .header img {
            max-height: 80px;
            margin-bottom: 0.5rem;
        }
        h1 {
            color: #FFB23F; /* primary */
            margin: 0;
            font-weight: 700;
            font-size: 2rem;
        }
        .details p {
            margin: 0.5rem 0;
            font-size: 1.1rem;
        }
        .details strong {
            color: #FFB23F; /* primary */
        }
        .footer {
            margin-top: 3rem;
            font-size: 0.85rem;
            color: #666666;
            text-align: center;
        }
        .highlight-box {
            background-color: #FFB23F; /* primary */
            color: white;
            border-radius: 6px;
            padding: 1rem;
            margin: 2rem 0;
            font-weight: 600;
            text-align: center;
            letter-spacing: 0.03em;
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
            <h1>Invoice #{{ $payment->id }}</h1>
        </div>

        <div class="details">
            <p><strong>User:</strong> {{ $payment->user->name }}</p>
            <p><strong>Subscription:</strong> {{ $payment->subscription->name }}</p>
            <p><strong>Amount:</strong> NPR {{ number_format($payment->amount, 2) }}</p>
            <p><strong>Paid At:</strong> {{ $payment->paid_at->format('Y-m-d H:i') }}</p>
            <p><strong>Expires At:</strong> {{ $payment->expires_at->format('Y-m-d H:i') }}</p>
        </div>

        <div class="highlight-box">
            Thank you for your subscription!
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name') }} — Explore Experience Share
        </div>
    </div>
</body>
</html>
