@extends('layouts.guest')

@section('title', 'Create account')
@section('heading', 'Create an account')
@section('description', 'Register to access the dormitory management workspace.')

@section('content')
    <form class="auth-form" method="POST" action="{{ route('register') }}">
        @csrf
        <label class="auth-field">
            <span>Name</span>
            <input type="text" name="name" value="{{ old('name') }}" autocomplete="name" maxlength="255" required autofocus>
        </label>
        <label class="auth-field">
            <span>Email address</span>
            <input type="email" name="email" value="{{ old('email') }}" autocomplete="email" maxlength="255" required>
        </label>
        <label class="auth-field">
            <span>Password</span>
            <input type="password" name="password" autocomplete="new-password" minlength="8" required>
        </label>
        <label class="auth-field">
            <span>Confirm password</span>
            <input type="password" name="password_confirmation" autocomplete="new-password" minlength="8" required>
        </label>
        <button class="button button-primary button-full" type="submit">Create account</button>
    </form>

    <div class="auth-links">
        <span>Already registered? <a href="{{ route('login') }}">Sign in</a></span>
    </div>
@endsection
