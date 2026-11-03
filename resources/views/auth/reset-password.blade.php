@extends('layouts.guest')

@section('title', 'Choose a new password')
@section('heading', 'Choose a new password')
@section('description', 'Set a new password for your Dormwise account.')

@section('content')
    <form class="auth-form" method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <label class="auth-field">
            <span>Email address</span>
            <input type="email" name="email" value="{{ old('email', $email) }}" autocomplete="email" required autofocus>
        </label>
        <label class="auth-field">
            <span>New password</span>
            <input type="password" name="password" autocomplete="new-password" minlength="8" required>
        </label>
        <label class="auth-field">
            <span>Confirm new password</span>
            <input type="password" name="password_confirmation" autocomplete="new-password" minlength="8" required>
        </label>
        <button class="button button-primary button-full" type="submit">Save new password</button>
    </form>
@endsection
