@extends('layouts.app')
@section('title', 'Create account')
@section('content')
    <section class="auth-wrap">
        <div class="panel auth-card">
            <h1>Let's get you sorted.</h1><p>Create an account to submit tickets and follow progress.</p>
            <form method="POST" action="{{ route('register.store') }}">
                @csrf
                <div class="field"><label for="name">Your name</label><input id="name" name="name" value="{{ old('name') }}" required autocomplete="name"></div>
                <div class="field"><label for="username">Username</label><input id="username" name="username" value="{{ old('username') }}" required minlength="3" maxlength="40" autocomplete="username"></div>
                <div class="field"><label for="email">Email address</label><input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email"></div>
                <div class="field"><label for="password">Password</label><input id="password" name="password" type="password" required autocomplete="new-password"></div>
                <div class="field"><label for="password_confirmation">Confirm password</label><input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"></div>
                <div class="form-actions"><button class="button button-primary" type="submit">Create account</button><a class="button button-quiet" href="{{ route('login') }}">Sign in instead</a></div>
            </form>
        </div>
    </section>
@endsection
