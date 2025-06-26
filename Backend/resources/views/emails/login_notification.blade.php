<!DOCTYPE html>
<html>
<head>
    <title>Login Notification</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #F7F8F9;
            font-family: Arial, sans-serif;
            color: #333333;
        }
        .container {
            background-color: #ffffff;
            max-width: 600px;
            margin: 2rem auto;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow: hidden;
            border: 1px solid #ddd;
        }
        .header {
            background-color: #FFB23F;
            padding: 1rem 2rem;
            text-align: center;
        }
        .header img {
            max-height: 60px;
            margin-bottom: 0.5rem;
        }
        .header h1 {
            margin: 0;
            font-size: 1.5rem;
            color: #fff;
            font-weight: 700;
        }
        .content {
            padding: 2rem;
        }
        .content p {
            font-size: 1rem;
            line-height: 1.5;
            margin-bottom: 1rem;
        }
        .footer {
            background-color: #F7F8F9;
            text-align: center;
            padding: 1rem 2rem;
            font-size: 0.875rem;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ $message->embed(public_path('assets/logo.png')) }}" alt="{{ config('app.name') }} Logo">
            <h1>Login Notification</h1>
        </div>
        <div class="content">
            <p>Hello,</p>
            <p>You have successfully logged in at <strong>{{ $loginTime }}</strong>.</p>
            <p>If this was not you, please secure your account immediately.</p>
            <p>Thanks,<br>{{ config('app.name') }}</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>
    </div>
</body>
</html>
