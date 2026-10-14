@extends('layouts.app')
@section('title', 'Ticket #'.$ticket->id)
@section('content')
    <section class="page">
        <div class="page-heading">
            <div><span class="eyebrow">Support ticket #{{ $ticket->id }}</span><h1>{{ $ticket->subject }}</h1><p>Opened {{ $ticket->created_at->format('M j, Y') }} by {{ $ticket->owner->name }}</p></div>
            @if($ticket->status !== 'closed')<a class="button button-quiet" href="{{ route('tickets.edit', $ticket) }}">Edit ticket</a>@endif
        </div>
        <div class="ticket-layout">
            <div>
                <div class="panel">
                    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:17px">
                        <span class="pill pill-{{ $ticket->status }}">{{ str_replace('_', ' ', ucfirst($ticket->status)) }}</span>
                        <span class="pill pill-{{ $ticket->priority }}">{{ ucfirst($ticket->priority) }} priority</span>
                        <span class="pill">{{ $ticket->category }}</span>
                    </div>
                    <div class="ticket-description">{{ $ticket->description }}</div>
                    @if($ticket->attachments->isNotEmpty())
                        <div class="attachment-list">@foreach($ticket->attachments as $attachment)<a class="attachment-link" href="{{ route('attachments.download', $attachment) }}">↧ {{ $attachment->original_name }}</a>@endforeach</div>
                    @endif
                </div>
                <div class="panel">
                    <h2 style="font:700 16px Manrope;margin:0 0 16px">Conversation <span class="muted" style="font:400 11px 'DM Sans'">({{ $ticket->replies->count() }} replies)</span></h2>
                    @forelse($ticket->replies as $reply)
                        <article class="reply"><div class="reply-head"><span class="avatar">{{ strtoupper(substr($reply->author->name, 0, 1)) }}</span>{{ $reply->author->name }} <span class="pill">{{ $reply->author->roleLabel() }}</span><time>{{ $reply->created_at->format('M j, Y · g:i a') }}</time></div><div class="reply-body">{{ $reply->body }}</div>
                            @if($reply->attachments->isNotEmpty())<div class="attachment-list">@foreach($reply->attachments as $attachment)<a class="attachment-link" href="{{ route('attachments.download', $attachment) }}">↧ {{ $attachment->original_name }}</a>@endforeach</div>@endif
                        </article>
                    @empty
                        <div class="empty">No replies yet. Add a message below to keep the conversation going.</div>
                    @endforelse
                    @if(!in_array($ticket->status, ['closed','resolved']) || auth()->user()->isStaff())
                        <form method="POST" action="{{ route('tickets.reply', $ticket) }}" enctype="multipart/form-data" style="border-top:1px solid var(--line);padding-top:18px">
                            @csrf
                            <div class="field"><label for="body">Add a reply</label><textarea id="body" name="body" maxlength="10000" required placeholder="Share an update or ask a question...">{{ old('body') }}</textarea></div>
                            <div class="field" style="margin-top:13px"><label for="reply-attachments">Attach files <span class="muted">(optional)</span></label><input id="reply-attachments" type="file" name="attachments[]" multiple accept=".jpg,.jpeg,.png,.pdf,.txt,.log,.zip"></div>
                            <div class="form-actions"><button class="button button-primary" type="submit">Send reply</button></div>
                        </form>
                    @else
                        <p class="muted" style="font-size:11px;margin-top:16px">This ticket is {{ $ticket->status }}. Contact support if you need to reopen it.</p>
                    @endif
                </div>
            </div>
            <aside>
                <div class="panel">
                    <h2 style="font:700 14px Manrope;margin:0 0 15px">Ticket details</h2>
                    <div class="stat-label">CUSTOMER</div><p style="font-size:12px;margin:6px 0 17px">{{ $ticket->owner->name }}<br><span class="muted">{{ $ticket->owner->email }}</span></p>
                    <div class="stat-label">ASSIGNED TO</div><p style="font-size:12px;margin:6px 0 17px">{{ $ticket->assignee?->name ?? 'Unassigned' }}</p>
                    <div class="stat-label">CREATED</div><p style="font-size:12px;margin:6px 0 0">{{ $ticket->created_at->format('M j, Y · g:i a') }}</p>
                    @if($ticket->resolved_at)<div class="stat-label" style="margin-top:17px">RESOLVED</div><p style="font-size:12px;margin:6px 0 0">{{ $ticket->resolved_at->format('M j, Y · g:i a') }}</p>@endif
                </div>
                <div class="panel"><h2 style="font:700 14px Manrope;margin:0 0 9px">Need to add context?</h2><p class="muted" style="font-size:11px;line-height:1.6;margin:0">Reply to this ticket and your support team will be notified by email.</p></div>
            </aside>
        </div>
    </section>
@endsection
