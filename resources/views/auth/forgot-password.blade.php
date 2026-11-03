@extends('layouts.guest')

@section('title', 'Forgot password')
@section('heading', 'Reset your password')
@section('description', 'Enter your email and, if an account exists, we will send a reset link.')

@section('content')
    <form class="auth-form" method="POST" action="{{ route('password.email') }}">
        @csrf
        <label class="auth-field">
            <span>Email address</span>
            <input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
        </label>
        <button class="button button-primary button-full" type="submit">Send reset link</button>
    </form>

    <div class="auth-links">
        <a href="{{ route('login') }}">Back to sign in</a>
    </div>
@endsection
