@extends('layouts.app')
@section('title', 'Analytics')
@section('content')
    <section class="page">
        <div class="page-heading"><div><span class="eyebrow">Admin insights</span><h1>Support analytics</h1><p>Understand your queue and how your team is performing.</p></div></div>
        <div class="stats-grid">
            <div class="stat-card"><div class="stat-label">Total tickets</div><div class="stat-number">{{ $total }}</div><div class="stat-foot">All time</div></div>
            <div class="stat-card"><div class="stat-label">Open tickets</div><div class="stat-number">{{ $open }}</div><div class="stat-foot">Awaiting resolution</div></div>
            <div class="stat-card"><div class="stat-label">Resolved / closed</div><div class="stat-number">{{ $closed }}</div><div class="stat-foot">All time</div></div>
            <div class="stat-card"><div class="stat-label">Average resolution</div><div class="stat-number" style="font-size:23px">@if($averageMinutes !== null)@php($minutes = (int) round($averageMinutes)){{ intdiv($minutes, 1440) ? intdiv($minutes, 1440).'d ' : '' }}{{ intdiv($minutes % 1440, 60) }}h {{ $minutes % 60 }}m @else — @endif</div><div class="stat-foot">For resolved tickets</div></div>
        </div>
        <div class="panel">
            <h2 style="font:700 16px Manrope;margin:0 0 16px">Agent performance</h2>
            @if($agents->isEmpty())<div class="empty">No support agents have been added yet.</div>@else
                <div class="table-wrap"><table><thead><tr><th>Agent</th><th>Email</th><th>Resolved</th><th>Open queue</th><th>Role</th></tr></thead><tbody>@foreach($agents as $agent)<tr><td class="table-title">{{ $agent->name }}</td><td>{{ $agent->email }}</td><td>{{ $agent->resolved_tickets_count }}</td><td>{{ $agent->open_tickets_count }}</td><td><span class="pill">{{ ucfirst($agent->role) }}</span></td></tr>@endforeach</tbody></table></div>
            @endif
        </div>
        <div class="panel"><h2 style="font:700 16px Manrope;margin:0 0 16px">Latest tickets</h2>
            @if($recentTickets->isEmpty())<div class="empty">No support tickets have been created.</div>@else
                <div class="table-wrap"><table><thead><tr><th>Ticket</th><th>Customer</th><th>Priority</th><th>Status</th><th>Assigned agent</th></tr></thead><tbody>@foreach($recentTickets as $ticket)<tr><td><a class="table-title" href="{{ route('tickets.show', $ticket) }}">#{{ $ticket->id }} · {{ $ticket->subject }}</a></td><td>{{ $ticket->owner->name }}</td><td><span class="pill pill-{{ $ticket->priority }}">{{ ucfirst($ticket->priority) }}</span></td><td><span class="pill pill-{{ $ticket->status }}">{{ str_replace('_',' ',ucfirst($ticket->status)) }}</span></td><td>{{ $ticket->assignee?->name ?? 'Unassigned' }}</td></tr>@endforeach</tbody></table></div>
            @endif
        </div>
    </section>
@endsection
