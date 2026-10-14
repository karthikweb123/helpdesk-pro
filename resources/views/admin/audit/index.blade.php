@extends('layouts.app')
@section('title', 'Audit log')
@section('content')
<section class="page">
    @include('admin.partials.nav')
    <div class="page-heading"><div><span class="eyebrow">Accountability</span><h1>Audit log</h1><p>Review administrative changes and ticket activity.</p></div><span class="pill">{{ $logs->total() }} events</span></div>
    <div class="panel">
        <form method="GET" action="{{ route('admin.audit.index') }}" style="display:flex;gap:8px;margin-bottom:12px"><input class="search-input" name="q" value="{{ request('q') }}" placeholder="Search actions, users, or details"><button class="button button-quiet" type="submit">Search</button></form>
        @forelse($logs as $log)
            <article class="audit-entry">
                <span class="audit-icon">{{ str_starts_with($log->action, 'user.') ? '♙' : (str_starts_with($log->action, 'ticket.') ? '▤' : '↗') }}</span>
                <div class="audit-copy"><strong>{{ $log->actor?->name ?? 'System / command line' }}</strong> {{ $log->description }}
                    <small>{{ str_replace('.', ' · ', ucfirst($log->action)) }} · {{ $log->ip_address ?? 'CLI' }} · {{ $log->created_at->format('M j, Y · g:i a') }}</small>
                </div>
                @if($log->subject_type === \App\Models\Ticket::class && $log->subject_id)<a class="feature-link" href="{{ route('tickets.show', $log->subject_id) }}">Ticket →</a>@elseif($log->subject_type === \App\Models\User::class && $log->subject_id)<a class="feature-link" href="{{ route('admin.users.edit', $log->subject_id) }}">User →</a>@endif
            </article>
        @empty
            <div class="empty"><strong>No activity recorded</strong>Account, ticket, and profile changes will appear here.</div>
        @endforelse
        <div class="pagination">{{ $logs->links() }}</div>
    </div>
</section>
@endsection
