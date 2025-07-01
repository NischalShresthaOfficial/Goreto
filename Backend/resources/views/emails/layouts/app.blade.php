<!DOCTYPE html>
<html>
<head>
    <title>@yield('title', config('app.name'))</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #F0F0F0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Helvetica Neue', Arial, sans-serif;
            color: #333333;
        }
        .container {
            background-color: #ffffff;
            max-width: 500px;
            margin: 2rem auto;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #F4C430 0%, #E6A824 100%);
            padding: 2rem;
            text-align: center;
            position: relative;
        }
        .header img {
            max-height: 80px;
            margin-bottom: 1rem;
        }
        .logo-placeholder {
            width: 80px;
            height: 80px;
            margin: 0 auto 1rem;
            background-color: rgba(0,0,0,0.2);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
        }
        .content {
            padding: 2.5rem 2rem;
            text-align: center;
        }
        .welcome-title {
            font-size: 1.8rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 1.5rem;
            line-height: 1.3;
        }
        .content p {
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 1.2rem;
            color: #555;
        }
        .content strong {
            color: #333;
        }
        .content a {
            color: #007bff;
            text-decoration: none;
        }
        .content a:hover {
            text-decoration: underline;
        }
        .footer {
            background-color: #f8f9fa;
            text-align: center;
            padding: 1.5rem 2rem;
            font-size: 0.8rem;
            color: #666;
            border-top: 1px solid #e9ecef;
        }
        .social-links {
            margin: 1rem 0;
        }
        .social-links a {
            display: inline-block;
            margin: 0 0.5rem;
            text-decoration: none;
        }
        .social-icon {
            width: 32px;
            height: 32px;
            background-color: #ddd;
            border-radius: 50%;
            display: inline-block;
        }
        .instagram { background-color: #E4405F; }
        .twitter { background-color: #1DA1F2; }
        .linkedin { background-color: #0077B5; }

        /* Utility classes for content styling */
        .highlight-box {
            background-color: #f8f9fa;
            padding: 1rem;
            border-radius: 6px;
            margin: 1.5rem 0;
            border-left: 4px solid #F4C430;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: #F4C430;
            color: #333;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin: 1rem 0;
        }
        .btn:hover {
            background-color: #E6A824;
            text-decoration: none;
        }
        .signature {
            margin-top: 2rem;
            font-weight: 500;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            @if(isset($message) && file_exists(public_path('assets/logo.png')))
                <img src="{{ $message->embed(public_path('assets/logo.png')) }}" alt="{{ config('app.name') }} Logo">
            @else
                <div class="logo-placeholder">
                    {{ strtoupper(substr(config('app.name'), 0, 1)) }}
                </div>
            @endif
        </div>
        <div class="content">
            <h2 class="welcome-title">@yield('header_title', 'Welcome back to ' . config('app.name'))</h2>
            @yield('content')
        </div>
        <div class="footer">
            <div class="social-links">
                <a href="#"><span class="social-icon instagram"></span></a>
                <a href="#"><span class="social-icon twitter"></span></a>
                <a href="#"><span class="social-icon linkedin"></span></a>
            </div>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}</p>
            <p>Explore Experience Share</p>
        </div>
    </div>
</body>
</html>
