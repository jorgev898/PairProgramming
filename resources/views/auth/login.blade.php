@extends('layouts.auth')

@section('title', 'Log in')

@section('content')
    <h1>Log in</h1>
    <p class="lead">Use your email and password to continue.</p>

    <form method="POST" action="{{ route('login.store') }}" novalidate>
        @csrf
        <div class="form-group">
            <label for="email">Email</label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}"
                required
                autocomplete="username"
                autofocus
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
                autocomplete="current-password"
            >
            @error('password')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </div>
        <div class="remember-row">
            <input type="checkbox" name="remember" id="remember" value="1" @checked(old('remember'))>
            <label for="remember" style="margin:0;text-transform:none;letter-spacing:normal;">Remember me</label>
        </div>
        <button type="submit" class="btn">Sign in</button>
    </form>

    <p class="footer-links">
        No account?
        <a href="{{ route('register') }}">Create one</a>
    </p>
@endsection
