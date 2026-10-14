@extends('layouts.app')
@section('title', 'Change password')
@section('content')
<section class="auth-wrap">
    <div class="panel auth-card">
        <span class="eyebrow">{{ $forced ? 'First sign-in security' : 'Account security' }}</span>
        <h1 style="margin-top:12px">{{ $forced ? 'Set a new password' : 'Change your password' }}</h1>
        <p>{{ $forced ? 'Before continuing, replace the temporary password with a private password only you know.' : 'Choose a strong password you do not use elsewhere.' }}</p>
        @if($forced)<div class="admin-note">Your temporary password must be changed before accessing the dashboard.</div>@endif
        <form method="POST" action="{{ route('account.password.update') }}">@csrf @method('PUT')
            <div class="field"><label for="current_password">Current password</label><input id="current_password" name="current_password" type="password" required autocomplete="current-password"></div>
            <div class="field" style="margin-top:15px"><label for="password">New password</label><input id="password" name="password" type="password" required autocomplete="new-password"></div>
            <div class="field" style="margin-top:15px"><label for="password_confirmation">Confirm new password</label><input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"></div>
            <p class="muted" style="font-size:10px;line-height:1.6">At least 12 characters with uppercase and lowercase letters, a number, and a symbol.</p>
            <div class="form-actions"><button class="button button-primary" type="submit">Save new password</button>@unless($forced)<a class="button button-quiet" href="{{ route('admin.profile.edit') }}">Cancel</a>@endunless</div>
        </form>
    </div>
</section>
@endsection
