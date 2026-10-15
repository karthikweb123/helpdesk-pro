@extends('layouts.app')
@section('title', 'Create account')
@section('content')
    <section class="auth-wrap">
        <div class="panel auth-card">
            <h1>Let's get you sorted.</h1>
            <p>Create an account to submit tickets and follow progress. Verify your email to finish registering.</p>

            @if (session('status'))
                <div class="flash" role="status">{{ session('status') }}</div>
            @endif

            @php($verificationSent = session('verification_sent') || $errors->has('verification_code'))

            <form method="POST" action="{{ route('register.store') }}">
                @csrf

                <div class="field">
                    <label for="name">Your name</label>
                    <input id="name" name="name" value="{{ old('name') }}" required maxlength="255"
                           autocomplete="name"
                           @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
                    @error('name') <p class="field-error" id="name-error">{{ $message }}</p> @enderror
                </div>

                <div class="field">
                    <label for="username">Username</label>
                    <input id="username" name="username" value="{{ old('username') }}" required
                           minlength="3" maxlength="40" autocomplete="username"
                           @error('username') aria-invalid="true" aria-describedby="username-error" @enderror>
                    @error('username') <p class="field-error" id="username-error">{{ $message }}</p> @enderror
                </div>

                <div class="field">
                    <label for="email">Email address</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required
                           maxlength="255" autocomplete="email"
                           @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                    @error('email') <p class="field-error" id="email-error">{{ $message }}</p> @enderror
                </div>

                @if ($verificationSent)
                    <div class="field">
                        <label for="verification_code">Email verification code</label>
                        <input id="verification_code" name="verification_code" type="text" inputmode="numeric"
                               autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" required
                               aria-describedby="verification-hint{{ $errors->has('verification_code') ? ' verification-error' : '' }}"
                               @error('verification_code') aria-invalid="true" @enderror>
                        <p class="field-hint" id="verification-hint">Enter the six-digit code sent to your email. It expires in 10 minutes.</p>
                        @error('verification_code') <p class="field-error" id="verification-error">{{ $message }}</p> @enderror
                    </div>
                @endif

                <div class="field">
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" required minlength="8"
                           autocomplete="new-password"
                           aria-describedby="password-hint{{ $errors->has('password') ? ' password-error' : '' }}"
                           @error('password') aria-invalid="true" @enderror>
                    <p class="field-hint" id="password-hint">At least 8 characters.</p>
                    @error('password') <p class="field-error" id="password-error">{{ $message }}</p> @enderror
                </div>

                <div class="field">
                    <label for="password_confirmation">Confirm password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password"
                           required autocomplete="new-password">
                </div>

                <div class="form-actions">
                    <button class="button button-primary" type="submit">
                        {{ $verificationSent ? 'Verify email and create account' : 'Send verification code' }}
                    </button>
                    @if ($verificationSent)
                        <button class="button button-quiet" type="submit" name="resend_code" value="1" formnovalidate>Resend code</button>
                    @endif
                    <a class="button button-quiet" href="{{ route('login') }}">Sign in instead</a>
                </div>
            </form>
        </div>
    </section>
@endsection