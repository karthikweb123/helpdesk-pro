@extends('layouts.app')
@section('title', 'Reports')
@section('content')
<section class="page">
    @include('admin.partials.nav')
    <div class="page-heading"><div><span class="eyebrow">Performance</span><h1>Ticket reports</h1><p>Filter ticket data, review resolution times, and export a CSV.</p></div>
        <a class="button button-primary" href="{{ route('admin.reports.export', request()->only('from','to')) }}">↓ Export CSV</a>
    </div>
    <div class="panel">
        <form method="GET" action="{{ route('admin.reports.index') }}" class="admin-tools">
            <div class="field"><label for="from">Created from</label><input id="from" type="date" name="from" value="{{ $from?->toDateString() }}"></div>
            <div class="field"><label for="to">Created through</label><input id="to" type="date" name="to" value="{{ $to?->toDateString() }}"></div>
            <button class="button button-primary" type="submit">Apply dates</button>
            <a class="button button-quiet" href="{{ route('admin.reports.index') }}">Clear</a>
        </form>
    </div>
    <div class="report-stats">
        <div class="stat-card"><div class="stat-label">Tickets in period</div><div class="stat-number">{{ $tickets->total() }}</div></div>
        <div class="stat-card"><div class="stat-label">Open</div><div class="stat-number">{{ (int)$byStatus->get('open',0) + (int)$byStatus->get('in_progress',0) }}</div></div>
        <div class="stat-card"><div class="stat-label">Resolved / closed</div><div class="stat-number">{{ (int)$byStatus->get('resolved',0) + (int)$byStatus->get('closed',0) }}</div></div>
        <div class="stat-card"><div class="stat-label">Average resolution</div><div class="stat-number" style="font-size:22px">@if($averageMinutes !== null){{ round($averageMinutes / 60, 1) }} hrs @else — @endif</div></div>
    </div>
    <div class="admin-grid" style="margin-top:18px">
        <div class="panel"><h2 style="font:700 16px Manrope;margin:0 0 16px">Status breakdown</h2>
            @forelse(['open'=>'Open','in_progress'=>'In progress','resolved'=>'Resolved','closed'=>'Closed'] as $key=>$label)
                <div class="audit-entry"><div class="audit-copy"><strong>{{ $label }}</strong><small>Tickets in selected date range</small></div><span class="pill">{{ (int)$byStatus->get($key,0) }}</span></div>
            @empty
                <div class="empty">No tickets in this period.</div>
            @endforelse
        </div>
        <div class="panel"><h2 style="font:700 16px Manrope;margin:0 0 16px">Priority breakdown</h2>
            @forelse(['urgent'=>'Urgent','high'=>'High','medium'=>'Medium','low'=>'Low'] as $key=>$label)
                <div class="audit-entry"><div class="audit-copy"><strong>{{ $label }}</strong><small>Tickets in selected date range</small></div><span class="pill pill-{{ $key }}">{{ (int)$byPriority->get($key,0) }}</span></div>
            @empty
                <div class="empty">No tickets in this period.</div>
            @endforelse
        </div>
    </div>
    <div class="panel" style="margin-top:18px">
        <h2 style="font:700 16px Manrope;margin:0 0 15px">Ticket details</h2>
        @if($tickets->isEmpty())<div class="empty">No matching tickets.</div>@else
            <div class="table-wrap"><table><thead><tr><th>Ticket</th><th>Customer</th><th>Agent</th><th>Priority</th><th>Status</th><th>Resolution</th></tr></thead><tbody>
                @foreach($tickets as $ticket)<tr>
                    <td><a class="table-title" href="{{ route('tickets.show',$ticket) }}">#{{ $ticket->id }} · {{ $ticket->subject }}</a></td>
                    <td>{{ $ticket->owner->name }}</td><td>{{ $ticket->assignee?->name ?? 'Unassigned' }}</td>
                    <td><span class="pill pill-{{ $ticket->priority }}">{{ ucfirst($ticket->priority) }}</span></td>
                    <td><span class="pill pill-{{ $ticket->status }}">{{ str_replace('_',' ',ucfirst($ticket->status)) }}</span></td>
                    <td>{{ $ticket->resolved_at ? $ticket->created_at->diffForHumans($ticket->resolved_at, true) : '—' }}</td>
                </tr>@endforeach
            </tbody></table></div><div class="pagination">{{ $tickets->links() }}</div>
        @endif
    </div>
</section>
@endsection
