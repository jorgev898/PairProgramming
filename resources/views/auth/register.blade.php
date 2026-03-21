@extends('layouts.auth')

@section('title', 'Register')

@section('content')
    <h1>Create account</h1>
    <p class="lead">Sign up with your name, email, and a secure password.</p>

    <form method="POST" action="{{ route('register.store') }}" novalidate>
        @csrf
        <div class="form-group">
            <label for="name">Name</label>
            <input
                type="text"
                id="name"
                name="name"
                value="{{ old('name') }}"
                required
                autocomplete="name"
                autofocus
                maxlength="255"
            >
            @error('name')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}"
                required
                autocomplete="email"
            >
            @error('email')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                required
                autocomplete="new-password"
            >
            @error('password')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </div>
        <div class="form-group">
            <label for="password_confirmation">Confirm password</label>
            <input
                type="password"
                id="password_confirmation"
                name="password_confirmation"
                required
                autocomplete="new-password"
            >
        </div>
        <button type="submit" class="btn">Register</button>
    </form>

    <p class="footer-links">
        Already have an account?
        <a href="{{ route('login') }}">Log in</a>
    </p>
@endsection
