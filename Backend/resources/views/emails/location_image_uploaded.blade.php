@extends('emails.layouts.app')

@section('title', 'New Location Image Uploaded')

@section('header_title', 'New Image Submitted for Review')

@section('content')
    <div class="highlight-box">
        <p><strong>{{ $user->name }}</strong> has uploaded a new image for the location <strong>{{ $location->name }}</strong>.</p>
        <p>Status: <strong>{{ $image->status }}</strong></p>
    </div>

    <p>You can review the image and take appropriate action.</p>

    <div class="signature">
        <p>Thanks,<br>{{ config('app.name') }} Team</p>
    </div>
@endsection
