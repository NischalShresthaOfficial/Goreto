@extends('emails.layouts.app')

@section('title', 'Login Notification')

@section('header_title', 'Login Notification')

@section('content')
    <p>Hello,</p>
    <p>You have successfully logged in at <strong>{{ $loginTime }}</strong>.</p>
    <p>If this was not you, please secure your account immediately.</p>
    <p>Thanks,<br>{{ config('app.name') }}</p>
@endsection
