@extends('emails.layouts.app')

@section('title', 'Password Reset Successful')

@section('content')
<h2 style="color: #333;">Hello, {{ $name }}</h2>
<p style="color: #555;">This is a confirmation that your password has been successfully reset.</p>
<p style="color: #555;">If you did not request this change, please contact our support team immediately.</p>
@endsection
