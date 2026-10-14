@extends('layouts.app')
@section('title', $editing ? 'Edit user' : 'Create user')
@section('content')
<section class="page">
    @include('admin.partials.nav')
    <div class="page-heading"><div><span class="eyebrow">User directory</span><h1>{{ $editing ? 'Edit account' : 'Create account' }}</h1><p>{{ $editing ? 'Update identity, role, and account access.' : 'Create a customer, support-agent, or admin account.' }}</p></div><a class="button button-quiet" href="{{ route('admin.users.index') }}">← All users</a></div>
    <div class="panel">
        @if(!$editing)<div class="admin-note">For a new account, provide a temporary password and select “Require password change” so the user must set a private password at their first sign-in.</div>@endif
        <form method="POST" action="{{ $editing ? route('admin.users.update', $user) : route('admin.users.store') }}">
            @csrf @if($editing)@method('PUT')@endif
            <div class="form-grid">
                <div class="field"><label for="name">Full name</label><input id="name" name="name" value="{{ old('name', $user->name) }}" maxlength="100" required></div>
                <div class="field"><label for="username">Username</label><input id="username" name="username" value="{{ old('username', $user->username) }}" minlength="3" maxlength="40" required></div>
                <div class="field full"><label for="email">Email address</label><input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" maxlength="255" required></div>
                <div class="field"><label for="role">Role</label><select id="role" name="role" required>@foreach(['customer'=>'Customer','agent'=>'Support Agent','admin'=>'Admin'] as $value=>$label)<option value="{{ $value }}" @selected(old('role', $user->role ?: 'customer') === $value)>{{ $label }}</option>@endforeach</select></div>
                <div class="field"><label for="is_active">Account status</label><select id="is_active" name="is_active" required><option value="1" @selected((string) old('is_active', $user->exists ? (int)$user->is_active : 1) === '1')>Active</option><option value="0" @selected((string) old('is_active', $user->exists ? (int)$user->is_active : 1) === '0')>Inactive</option></select></div>
                <div class="field"><label for="password">{{ $editing ? 'Reset password (optional)' : 'Temporary password' }}</label><input id="password" name="password" type="password" {{ $editing ? '' : 'required' }} autocomplete="new-password"><span class="muted" style="font-size:10px">Use at least 8 characters; Laravel password rules apply.</span></div>
                <div class="field"><label for="password_confirmation">Confirm password</label><input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" {{ $editing ? '' : 'required' }}></div>
                @if(!$editing)
                    <div class="field full"><label style="display:flex;align-items:center;gap:9px"><input type="checkbox" name="must_change_password" value="1" checked style="width:auto"> Require password change on first login</label></div>
                @endif
            </div>
            <div class="form-actions"><button class="button button-primary" type="submit">{{ $editing ? 'Save account' : 'Create account' }}</button><a class="button button-quiet" href="{{ route('admin.users.index') }}">Cancel</a></div>
        </form>
    </div>
</section>
@endsection
