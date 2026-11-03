@extends('layouts.guest')

@section('title', 'Sign in')
@section('heading', 'Welcome back')
@section('description', 'Sign in to manage university housing.')

@section('content')
    <form class="auth-form" method="POST" action="{{ route('login') }}">
        @csrf
        <label class="auth-field">
            <span>Email address</span>
            <input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
        </label>
        <label class="auth-field">
            <span>Password</span>
            <input type="password" name="password" autocomplete="current-password" required>
        </label>
        <label class="auth-remember">
            <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
            <span>Remember me</span>
        </label>
        <button class="button button-primary button-full" type="submit">Sign in</button>
    </form>

    <div class="auth-links">
        <a href="{{ route('password.request') }}">Forgot your password?</a>
        <span>New to Dormwise? <a href="{{ route('register') }}">Create an account</a></span>
    </div>
@endsection
