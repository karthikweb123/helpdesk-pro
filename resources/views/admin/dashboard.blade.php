@extends('layouts.app')
@section('title', 'Admin Dashboard')
@section('content')
<section class="page">
    @include('admin.partials.nav')
    <div class="page-heading">
        <div><span class="eyebrow">Administration</span><h1>Dashboard overview</h1><p>Live snapshot of your users, queue, and support activity.</p></div>
        <a class="button button-primary" href="{{ route('admin.users.create') }}">＋ Add user</a>
    </div>
    <div class="stats-grid admin-stats">
        <div class="stat-card"><div class="stat-label">Total users</div><div class="stat-number">{{ (int) $userCounts->sum() }}</div><div class="stat-foot">{{ (int) $userCounts->get('customer', 0) }} customers</div></div>
        <div class="stat-card"><div class="stat-label">Active agents</div><div class="stat-number">{{ $activeAgents }}</div><div class="stat-foot">{{ (int) $userCounts->get('agent', 0) }} agent accounts total</div></div>
        <div class="stat-card"><div class="stat-label">Open tickets</div><div class="stat-number">{{ (int) $statusCounts->get('open', 0) + (int) $statusCounts->get('in_progress', 0) }}</div><div class="stat-foot">{{ (int) $statusCounts->get('in_progress', 0) }} in progress</div></div>
        <div class="stat-card"><div class="stat-label">Resolved tickets</div><div class="stat-number">{{ $resolvedCount }}</div><div class="stat-foot">Of {{ $totalTickets }} tickets total</div></div>
    </div>
    <div class="admin-grid">
        <div class="panel">
            <div class="page-heading" style="margin-bottom:12px"><div><h2 style="font:700 16px Manrope;margin:0">Ticket volume</h2><p>New requests over the last seven days</p></div></div>
            <canvas id="ticket-volume" class="admin-chart" role="img" aria-label="Ticket volumes during the last seven days"></canvas>
            <div class="chart-legend"><span><i class="legend-dot" style="background:#6654d9"></i>New tickets per day</span></div>
        </div>
        <div class="panel">
            <h2 style="font:700 16px Manrope;margin:0 0 17px">Queue by status</h2>
            @php($statusLabels = ['open' => 'Open', 'in_progress' => 'In progress', 'resolved' => 'Resolved', 'closed' => 'Closed'])
            @forelse($statusLabels as $key => $label)
                <div style="margin-bottom:16px"><div style="display:flex;justify-content:space-between;font-size:11px;margin-bottom:6px"><span>{{ $label }}</span><strong>{{ (int) $statusCounts->get($key, 0) }}</strong></div><div style="height:7px;background:#f0eff4;border-radius:9px;overflow:hidden"><div style="width:{{ $totalTickets ? round(((int) $statusCounts->get($key, 0) / $totalTickets) * 100) : 0 }}%;height:100%;background:{{ $key === 'open' ? '#edb85c' : ($key === 'in_progress' ? '#6654d9' : '#39a77d') }};border-radius:9px"></div></div></div>
            @empty
                <div class="empty">No tickets have been created yet.</div>
            @endforelse
            <div class="stat-foot">Average resolution: @if($averageResolutionMinutes !== null){{ round($averageResolutionMinutes / 60, 1) }} hours @else — @endif</div>
        </div>
    </div>
    <div class="admin-grid" style="margin-top:18px">
        <div class="panel">
            <div class="page-heading" style="margin-bottom:12px"><div><h2 style="font:700 16px Manrope;margin:0">Recent tickets</h2><p>Latest customer requests</p></div><a class="feature-link" href="{{ route('admin.tickets.index') }}">All tickets →</a></div>
            @if($recentTickets->isEmpty())<div class="empty">No tickets yet.</div>@else
                <div class="table-wrap"><table><thead><tr><th>Ticket</th><th>Status</th><th>Priority</th></tr></thead><tbody>@foreach($recentTickets as $ticket)<tr><td><a class="table-title" href="{{ route('tickets.show', $ticket) }}">#{{ $ticket->id }} · {{ $ticket->subject }}</a><div class="stat-foot">{{ $ticket->owner->name }} · {{ $ticket->created_at->diffForHumans() }}</div></td><td><span class="pill pill-{{ $ticket->status }}">{{ str_replace('_', ' ', ucfirst($ticket->status)) }}</span></td><td><span class="pill pill-{{ $ticket->priority }}">{{ ucfirst($ticket->priority) }}</span></td></tr>@endforeach</tbody></table></div>
            @endif
        </div>
        <div class="panel">
            <div class="page-heading" style="margin-bottom:12px"><div><h2 style="font:700 16px Manrope;margin:0">Recent activity</h2><p>Account and ticket events</p></div><a class="feature-link" href="{{ route('admin.audit.index') }}">Full audit log →</a></div>
            @forelse($recentActivity as $log)
                <div class="audit-entry"><span class="audit-icon">↗</span><div class="audit-copy"><strong>{{ $log->actor?->name ?? 'System' }}</strong> {{ $log->description }}<small>{{ $log->created_at->diffForHumans() }}</small></div></div>
            @empty
                <div class="empty">Admin activity will show here.</div>
            @endforelse
        </div>
    </div>
</section>
<script>
(() => {
    const canvas = document.getElementById('ticket-volume');
    if (!canvas) return;
    const labels = @json($chartLabels);
    const values = @json($chartValues);
    const context = canvas.getContext('2d');
    const draw = () => {
        const ratio = window.devicePixelRatio || 1;
        const box = canvas.getBoundingClientRect();
        canvas.width = box.width * ratio;
        canvas.height = box.height * ratio;
        context.scale(ratio, ratio);
        const width = box.width, height = box.height, pad = {top: 16, right: 14, bottom: 32, left: 26};
        const plotWidth = width - pad.left - pad.right, plotHeight = height - pad.top - pad.bottom;
        const max = Math.max(1, ...values), step = plotWidth / Math.max(1, values.length);
        context.font = '10px DM Sans'; context.textAlign = 'right';
        for (let line = 0; line <= 3; line++) {
            const y = pad.top + plotHeight * line / 3;
            context.strokeStyle = '#efedf3'; context.beginPath(); context.moveTo(pad.left, y); context.lineTo(width - pad.right, y); context.stroke();
            context.fillStyle = '#9298a6'; context.fillText(String(Math.round(max * (3-line) / 3)), pad.left - 7, y + 3);
        }
        values.forEach((value, index) => {
            const barHeight = value / max * plotHeight;
            const x = pad.left + index * step + step * .22;
            const y = pad.top + plotHeight - barHeight;
            context.fillStyle = '#7161df'; context.beginPath();
            context.roundRect(x, y, step * .56, Math.max(2, barHeight), 5); context.fill();
            context.textAlign = 'center'; context.fillStyle = '#7d8496';
            context.fillText(labels[index], x + step * .28, height - 9);
        });
    };
    draw();
    window.addEventListener('resize', draw);
})();
</script>
@endsection
