@extends('layouts.app')
@section('title', 'Tickets')
@section('content')
    <section class="page">
        <div class="page-heading"><div><span class="eyebrow">Support requests</span><h1>Your tickets</h1><p>Keep track of every issue and conversation.</p></div><a class="button button-primary" href="{{ route('tickets.create') }}">＋ New ticket</a></div>
        <div class="panel">
            <div class="toolbar">
                <form method="GET" action="{{ route('tickets.index') }}"><input class="search-input" name="q" value="{{ request('q') }}" placeholder="Search tickets by subject or ID"><select name="status"><option value="">All statuses</option>@foreach(['open','in_progress','resolved','closed'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ str_replace('_', ' ', ucfirst($status)) }}</option>@endforeach</select><button class="button button-quiet" type="submit">Filter</button></form>
                <span class="muted" style="font-size:11px">{{ $tickets->total() }} tickets</span>
            </div>
            @if($tickets->isEmpty())
                <div class="empty"><strong>No tickets match</strong>Try changing your filters, or open a new support ticket.</div>
            @else
                <div class="table-wrap"><table><thead><tr><th>Ticket</th>@if(auth()->user()->isStaff())<th>Customer</th>@endif<th>Category</th><th>Priority</th><th>Status</th><th>Assigned to</th><th>Updated</th></tr></thead><tbody>
                @foreach($tickets as $ticket)<tr>
                    <td><a class="table-title" href="{{ route('tickets.show', $ticket) }}">#{{ $ticket->id }} · {{ $ticket->subject }}</a></td>
                    @if(auth()->user()->isStaff())<td>{{ $ticket->owner->name }}</td>@endif
                    <td>{{ $ticket->category }}</td><td><span class="pill pill-{{ $ticket->priority }}">{{ ucfirst($ticket->priority) }}</span></td>
                    <td><span class="pill pill-{{ $ticket->status }}">{{ str_replace('_', ' ', ucfirst($ticket->status)) }}</span></td><td>{{ $ticket->assignee?->name ?? 'Unassigned' }}</td><td class="muted">{{ $ticket->updated_at->diffForHumans() }}</td>
                </tr>@endforeach
                </tbody></table></div>
                <div class="pagination">{{ $tickets->links() }}</div>
            @endif
        </div>
    </section>
@endsection
