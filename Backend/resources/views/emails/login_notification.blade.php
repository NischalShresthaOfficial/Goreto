@extends('emails.layouts.app')

@section('title', 'Login Notification')

@section('header_title', 'Welcome back to ' . config('app.name'))

@section('content')
    <div class="highlight-box">
        <p>You've successfully logged into your account on <strong>{{ $loginTime }}</strong>.</p>
    </div>

    <p>If this wasn't you, please <a href="#">secure your account</a> immediately or contact our support team.</p>

    <div class="signature">
        <p>Thanks for staying connected,<br>
        The {{ config('app.name') }} Team</p>
    </div>
@endsection
