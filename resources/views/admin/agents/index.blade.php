@extends('layouts.app')
@section('title', 'Agent management')
@section('content')
<section class="page">
    @include('admin.partials.nav')
    <div class="page-heading"><div><span class="eyebrow">Support team</span><h1>Agent management</h1><p>Monitor assignment workload and resolution counts for your agents.</p></div><a class="button button-primary" href="{{ route('admin.users.create') }}">＋ Add agent</a></div>
    <div class="panel">
        @if($agents->isEmpty())<div class="empty"><strong>No agents found</strong>Create an account and assign it the Support Agent role.</div>@else
            <div class="table-wrap"><table><thead><tr><th>Agent</th><th>Account</th><th>Open tickets</th><th>Resolved tickets</th><th>Status</th><th></th></tr></thead><tbody>
            @foreach($agents as $agent)<tr>
                <td class="table-title">{{ $agent->name }}</td>
                <td>{{ '@'.$agent->username }}<div class="stat-foot">{{ $agent->email }}</div></td>
                <td><span class="pill pill-in_progress">{{ $agent->open_tickets_count }}</span></td>
                <td><span class="pill pill-resolved">{{ $agent->resolved_tickets_count }}</span></td>
                <td><span class="pill {{ $agent->is_active ? 'pill-resolved' : 'pill-high' }}">{{ $agent->is_active ? 'Active' : 'Inactive' }}</span></td>
                <td><a class="button button-quiet" style="padding:7px 10px;font-size:10px" href="{{ route('admin.tickets.index', ['assignee' => $agent->id]) }}">Assigned tickets</a></td>
            </tr>@endforeach
            </tbody></table></div><div class="pagination">{{ $agents->links() }}</div>
        @endif
    </div>
</section>
@endsection
