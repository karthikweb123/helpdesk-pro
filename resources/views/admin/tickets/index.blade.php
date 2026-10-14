@extends('layouts.app')
@section('title', 'Ticket management')
@section('content')
<section class="page">
    @include('admin.partials.nav')
    <div class="page-heading"><div><span class="eyebrow">Service desk</span><h1>All tickets</h1><p>Review conversations, update status, priorities, and assignments.</p></div><a class="button button-quiet" href="{{ route('admin.reports.index') }}">View reports →</a></div>
    <div class="panel">
        <div class="toolbar"><form method="GET" action="{{ route('admin.tickets.index') }}" class="admin-tools">
            <input class="search-input" name="q" value="{{ request('q') }}" placeholder="Search ID, subject, customer">
            <select name="status"><option value="">All statuses</option>@foreach(['open','in_progress','resolved','closed'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ str_replace('_',' ',ucfirst($status)) }}</option>@endforeach</select>
            <select name="priority"><option value="">All priorities</option>@foreach(['low','medium','high','urgent'] as $priority)<option value="{{ $priority }}" @selected(request('priority') === $priority)>{{ ucfirst($priority) }}</option>@endforeach</select>
            <select name="assignee"><option value="">All assignees</option><option value="0" @selected(request('assignee') === '0')>Unassigned</option>@foreach($agents as $agent)<option value="{{ $agent->id }}" @selected((string) request('assignee') === (string) $agent->id)>{{ $agent->name }}</option>@endforeach</select>
            <button class="button button-quiet" type="submit">Filter</button>
        </form></div>
        @if($tickets->isEmpty())<div class="empty"><strong>No tickets found</strong>There are no tickets matching those filters.</div>@else
            <div class="table-wrap"><table><thead><tr><th>Ticket</th><th>Customer</th><th>Priority</th><th>Status</th><th>Assigned agent</th><th>Updated</th><th></th></tr></thead><tbody>
            @foreach($tickets as $ticket)<tr>
                <td><a class="table-title" href="{{ route('tickets.show', $ticket) }}">#{{ $ticket->id }} · {{ $ticket->subject }}</a><div class="stat-foot">{{ $ticket->category }}</div></td>
                <td>{{ $ticket->owner->name }}<div class="stat-foot">{{ $ticket->owner->email }}</div></td>
                <td><span class="pill pill-{{ $ticket->priority }}">{{ ucfirst($ticket->priority) }}</span></td>
                <td><span class="pill pill-{{ $ticket->status }}">{{ str_replace('_',' ',ucfirst($ticket->status)) }}</span></td>
                <td>{{ $ticket->assignee?->name ?? 'Unassigned' }}</td>
                <td class="muted">{{ $ticket->updated_at->diffForHumans() }}</td>
                <td><a class="button button-quiet" style="padding:7px 10px;font-size:10px" href="{{ route('tickets.show', $ticket) }}">Conversation</a></td>
            </tr>@endforeach
            </tbody></table></div>
            <div class="pagination">{{ $tickets->links() }}</div>
        @endif
    </div>
</section>
@endsection
