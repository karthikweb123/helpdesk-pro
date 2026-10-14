@extends('layouts.app')
@section('title', 'Admin profile')
@section('content')
<section class="page">
    @include('admin.partials.nav')
    <div class="page-heading"><div><span class="eyebrow">Your account</span><h1>Admin profile</h1><p>Manage your administrator identity and sign-in security.</p></div><a class="button button-quiet" href="{{ route('account.password.edit') }}">Change password</a></div>
    <div class="panel">
        <h2 style="font:700 16px Manrope;margin:0 0 18px">Profile information</h2>
        <form method="POST" action="{{ route('admin.profile.update') }}">@csrf @method('PUT')
            <div class="form-grid">
                <div class="field"><label for="name">Name</label><input id="name" name="name" value="{{ old('name',$user->name) }}" required maxlength="100"></div>
                <div class="field"><label for="username">Username</label><input id="username" name="username" value="{{ old('username',$user->username) }}" required minlength="3" maxlength="40"></div>
                <div class="field full"><label for="email">Email</label><input id="email" name="email" type="email" value="{{ old('email',$user->email) }}" required maxlength="255"></div>
            </div>
            <div class="form-actions"><button class="button button-primary" type="submit">Save profile</button></div>
        </form>
    </div>
    <div class="panel"><h2 style="font:700 16px Manrope;margin:0 0 8px">Password security</h2><p class="muted" style="font-size:12px;line-height:1.7">Change your password at any time. Passwords must be at least 12 characters and include mixed case, a number, and a symbol.</p><a class="button button-quiet" href="{{ route('account.password.edit') }}">Update password</a></div>
</section>
@endsection
