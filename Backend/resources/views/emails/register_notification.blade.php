@extends('emails.layouts.app')

@section('title', 'Email Verification')

@section('header_title', 'Welcome to ' . config('app.name'))

@section('content')
    <p>Hello {{ $user->name }},</p>

    <p>Thank you for registering with <strong>{{ config('app.name') }}</strong>!</p>

    <div class="highlight-box">
        <p>Your 6-digit verification code is:</p>
        <h2 style="letter-spacing: 4px;">{{ $token }}</h2>
    </div>

    <p>This code will expire in 10 minutes. Please verify your email address to activate your account.</p>

    <p>If you did not initiate this registration, you can ignore this email.</p>

    <div class="signature">
        <p>Best regards,<br>
        The {{ config('app.name') }} Team</p>
    </div>
@endsection
