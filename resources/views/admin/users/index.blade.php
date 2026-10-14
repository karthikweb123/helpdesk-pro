@extends('layouts.app')
@section('title', 'User management')
@section('content')
<section class="page">
    @include('admin.partials.nav')
    <div class="page-heading"><div><span class="eyebrow">Directory</span><h1>User management</h1><p>Search accounts, manage access, and assign support roles.</p></div><a class="button button-primary" href="{{ route('admin.users.create') }}">＋ Add user</a></div>
    <div class="stats-grid admin-stats">
        <div class="stat-card"><div class="stat-label">Customers</div><div class="stat-number">{{ (int) $totals->get('customer', 0) }}</div></div>
        <div class="stat-card"><div class="stat-label">Support agents</div><div class="stat-number">{{ (int) $totals->get('agent', 0) }}</div></div>
        <div class="stat-card"><div class="stat-label">Administrators</div><div class="stat-number">{{ (int) $totals->get('admin', 0) }}</div></div>
        <div class="stat-card"><div class="stat-label">Filtered results</div><div class="stat-number">{{ $users->total() }}</div></div>
    </div>
    <div class="panel">
        <div class="toolbar">
            <form method="GET" action="{{ route('admin.users.index') }}" class="admin-tools">
                <input class="search-input" name="q" value="{{ request('q') }}" placeholder="Search name, username, email">
                <select name="role"><option value="">All roles</option>@foreach(['admin'=>'Admin','agent'=>'Support Agent','customer'=>'Customer'] as $value=>$label)<option value="{{ $value }}" @selected(request('role') === $value)>{{ $label }}</option>@endforeach</select>
                <select name="active"><option value="">Any status</option><option value="yes" @selected(request('active') === 'yes')>Active</option><option value="no" @selected(request('active') === 'no')>Inactive</option></select>
                <button class="button button-quiet" type="submit">Filter</button>
            </form>
        </div>
        @if($users->isEmpty())<div class="empty"><strong>No matching accounts</strong>Adjust the filters or create a new user account.</div>@else
            <div class="table-wrap"><table><thead><tr><th>User</th><th>Username / email</th><th>Role</th><th>Status</th><th>Joined</th><th>Actions</th></tr></thead><tbody>
                @foreach($users as $user)
                    <tr>
                        <td class="table-title">{{ $user->name }} @if($user->id === auth()->id())<span class="pill">You</span>@endif</td>
                        <td>{{ $user->username ?? '—' }}<div class="stat-foot">{{ $user->email }}</div></td>
                        <td><span class="pill">{{ $user->roleLabel() }}</span></td>
                        <td><span class="pill {{ $user->is_active ? 'pill-resolved' : 'pill-high' }}">{{ $user->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td class="muted">{{ $user->created_at->format('M j, Y') }}</td>
                        <td><div class="admin-actions">
                            <a class="button button-quiet" style="padding:7px 10px;font-size:10px" href="{{ route('admin.users.edit', $user) }}">Edit</a>
                            @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('admin.users.active', $user) }}">@csrf @method('PATCH')
                                    <button class="status-toggle {{ $user->is_active ? 'inactive' : '' }}" type="submit">{{ $user->is_active ? 'Deactivate' : 'Activate' }}</button>
                                </form>
                            @endif
                        </div></td>
                    </tr>
                @endforeach
            </tbody></table></div>
            <div class="pagination">{{ $users->links() }}</div>
        @endif
    </div>
</section>
@endsection
