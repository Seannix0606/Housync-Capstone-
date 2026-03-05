@extends('emails.layout')

@section('title', 'Welcome to HouseSync')

@section('body')
<h2>Welcome to HouseSync, {{ $userName }}!</h2>

<p>Your account has been successfully created. You're now part of our property management platform.</p>

<div class="info-box">
    <div class="info-row">
        <span class="info-label">Account Type</span>
        <span class="info-value">{{ ucfirst(str_replace('_', ' ', $role)) }}</span>
    </div>
</div>

@if($role === 'landlord')
<p>Your landlord account is pending approval. Our admin team will review your application and documents shortly. You'll receive a notification once your account is approved.</p>
@elseif($role === 'tenant')
<p>You can now browse available properties, apply for units, and manage your tenancy all in one place.</p>
@endif

<p style="text-align:center">
    <a href="{{ $loginUrl }}" class="btn">Login to Your Account</a>
</p>

<p>If you have any questions, don't hesitate to reach out to your landlord or property manager through our messaging system.</p>
@endsection
