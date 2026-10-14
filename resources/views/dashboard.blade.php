@extends('layouts.app')
@section('title', 'Overview')
@section('content')
    <section class="page">
        <div class="page-heading">
            <div><span class="eyebrow">Your support desk</span><h1>Good to see you, {{ $user->name }}.</h1><p>Here’s the latest on your support activity.</p></div>
            <a class="button button-primary" href="{{ route('tickets.create') }}">＋ New ticket</a>
        </div>
        <div class="stats-grid">
            <div class="stat-card"><div class="stat-label">Open tickets</div><div class="stat-number">{{ $openCount }}</div><div class="stat-foot">Needs attention</div></div>
            <div class="stat-card"><div class="stat-label">Resolved tickets</div><div class="stat-number">{{ $closedCount }}</div><div class="stat-foot">Successfully completed</div></div>
            <div class="stat-card"><div class="stat-label">Your role</div><div class="stat-number" style="font-size:22px">{{ $user->roleLabel() }}</div><div class="stat-foot">Helpdesk access</div></div>
            <div class="stat-card"><div class="stat-label">Need an answer?</div><div class="stat-number" style="font-size:19px">Knowledge base</div><div class="stat-foot"><a href="{{ route('knowledge.index') }}" style="color:var(--purple)">Browse help articles →</a></div></div>
        </div>
        <div class="panel">
            <div class="page-heading" style="margin-bottom:15px"><div><h2 style="font:700 16px Manrope;margin:0">Recent tickets</h2><p>Updates from your support queue</p></div><a class="feature-link" href="{{ route('tickets.index') }}">View all tickets →</a></div>
            @if($recentTickets->isEmpty())
                <div class="empty"><strong>No tickets yet</strong>When you need a hand, create a ticket and your support team will take it from there.</div>
            @else
                <div class="table-wrap"><table><thead><tr><th>Ticket</th>@if($user->isStaff())<th>Customer</th>@endif<th>Category</th><th>Priority</th><th>Status</th><th>Updated</th></tr></thead><tbody>
                    @foreach($recentTickets as $ticket)
                        <tr><td><a class="table-title" href="{{ route('tickets.show', $ticket) }}">#{{ $ticket->id }} · {{ $ticket->subject }}</a></td>@if($user->isStaff())<td>{{ $ticket->owner->name }}</td>@endif<td>{{ $ticket->category }}</td><td><span class="pill pill-{{ $ticket->priority }}">{{ ucfirst($ticket->priority) }}</span></td><td><span class="pill pill-{{ $ticket->status }}">{{ str_replace('_', ' ', ucfirst($ticket->status)) }}</span></td><td class="muted">{{ $ticket->updated_at->diffForHumans() }}</td></tr>
                    @endforeach
                </tbody></table></div>
            @endif
        </div>
    </section>
@endsection
