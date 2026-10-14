@extends('layouts.app')
@section('title', 'Sign in')
@section('content')
    <section class="auth-wrap">
        <div class="panel auth-card">
            <h1>Welcome back</h1><p>Sign in to manage your support requests.</p>
            <form method="POST" action="{{ route('login.store') }}">
                @csrf
                <div class="field"><label for="login">Username or email address</label><input id="login" name="login" value="{{ old('login') }}" required autofocus autocomplete="username"></div>
                <div class="field"><label for="password">Password</label><input id="password" name="password" type="password" required autocomplete="current-password"></div>
                <label class="muted" style="display:flex;align-items:center;gap:7px;font-size:11px"><input type="checkbox" name="remember" value="1"> Remember me</label>
                <div class="form-actions"><button class="button button-primary" type="submit">Sign in</button><a class="button button-quiet" href="{{ route('register') }}">Create account</a></div>
            </form>
        </div>
    </section>
@endsection
