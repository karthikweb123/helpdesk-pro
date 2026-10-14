@extends('layouts.app')
@section('title', 'Update ticket')
@section('content')
    <section class="page">
        <div class="page-heading"><div><span class="eyebrow">Ticket #{{ $ticket->id }}</span><h1>Update ticket</h1><p>Make changes to the request or manage its status.</p></div><a class="button button-quiet" href="{{ route('tickets.show', $ticket) }}">Back to ticket</a></div>
        <div class="panel">
            <form method="POST" action="{{ route('tickets.update', $ticket) }}" enctype="multipart/form-data">
                @csrf @method('PATCH')
                <div class="form-grid">
                    <div class="field full"><label for="subject">Subject</label><input id="subject" name="subject" value="{{ old('subject', $ticket->subject) }}" maxlength="160" required></div>
                    <div class="field"><label for="category">Category</label><select id="category" name="category" required>@foreach(['Hardware','Software','Network'] as $category)<option value="{{ $category }}" @selected(old('category', $ticket->category) === $category)>{{ $category }}</option>@endforeach</select></div>
                    <div class="field"><label for="priority">Priority</label><select id="priority" name="priority" required>@foreach(['low','medium','high','urgent'] as $priority)<option value="{{ $priority }}" @selected(old('priority', $ticket->priority) === $priority)>{{ ucfirst($priority) }}</option>@endforeach</select></div>
                    @if(auth()->user()->isStaff())
                        <div class="field"><label for="status">Status</label><select id="status" name="status" required>@foreach(['open','in_progress','resolved','closed'] as $status)<option value="{{ $status }}" @selected(old('status', $ticket->status) === $status)>{{ str_replace('_', ' ', ucfirst($status)) }}</option>@endforeach</select></div>
                        <div class="field"><label for="assigned_to">Assign to</label><select id="assigned_to" name="assigned_to"><option value="">Unassigned</option>@foreach($agents as $agent)<option value="{{ $agent->id }}" @selected((string) old('assigned_to', $ticket->assigned_to) === (string) $agent->id)>{{ $agent->name }} · {{ $agent->roleLabel() }}</option>@endforeach</select></div>
                    @endif
                    <div class="field full"><label for="description">Description</label><textarea id="description" name="description" required maxlength="10000">{{ old('description', $ticket->description) }}</textarea></div>
                    <div class="field full"><label for="attachments">Add attachments <span class="muted">(optional; up to 5 files, 10 MB each)</span></label><input id="attachments" type="file" name="attachments[]" multiple accept=".jpg,.jpeg,.png,.pdf,.txt,.log,.zip"></div>
                </div>
                <div class="form-actions"><button class="button button-primary" type="submit">Save changes</button><a class="button button-quiet" href="{{ route('tickets.show', $ticket) }}">Cancel</a></div>
            </form>
        </div>
    </section>
@endsection
