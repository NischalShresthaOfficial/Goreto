@extends('emails.layouts.app')

@section('title', 'Password Reset Token')

@section('content')
<h2 style="color: #333;">Hello, {{ $name }}</h2>
<p style="color: #555;">You requested a password reset. Here is your reset token:</p>
<p style="font-size: 18px; font-weight: bold; color: #003366;">{{ $token }}</p>
<p style="color: #555;">Use this token to reset your password on the portal. If you did not request this, please ignore this message or contact our support team.</p>
@endsection
