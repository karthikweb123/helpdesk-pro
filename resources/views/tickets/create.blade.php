@extends('layouts.app')
@section('title', 'New ticket')
@section('content')
    <section class="page">
        <div class="page-heading"><div><span class="eyebrow">We’re here to help</span><h1>New support ticket</h1><p>Tell us what’s going on and we’ll take it from here.</p></div></div>
        <div class="panel">
            <form method="POST" action="{{ route('tickets.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="form-grid">
                    <div class="field full"><label for="subject">What do you need help with?</label><input id="subject" name="subject" value="{{ old('subject') }}" maxlength="160" required placeholder="A short description of the issue"></div>
                    <div class="field"><label for="category">Category</label><select id="category" name="category" required>@foreach(['Hardware','Software','Network'] as $category)<option value="{{ $category }}" @selected(old('category') === $category)>{{ $category }}</option>@endforeach</select></div>
                    <div class="field"><label for="priority">Priority</label><select id="priority" name="priority" required>@foreach(['low','medium','high','urgent'] as $priority)<option value="{{ $priority }}" @selected(old('priority', 'medium') === $priority)>{{ ucfirst($priority) }}</option>@endforeach</select></div>
                    <div class="field full"><label for="description">Describe the issue</label><textarea id="description" name="description" required maxlength="10000" placeholder="What happened? What have you tried so far?">{{ old('description') }}</textarea></div>
                    <div class="field full"><label for="attachments">Attachments <span class="muted">(optional; up to 5 files, 10 MB each)</span></label><input id="attachments" type="file" name="attachments[]" multiple accept=".jpg,.jpeg,.png,.pdf,.txt,.log,.zip"><span class="muted" style="font-size:10px">Accepted: images, PDF, text/log, ZIP.</span></div>
                </div>
                <div class="form-actions"><button class="button button-primary" type="submit">Submit ticket</button><a class="button button-quiet" href="{{ route('tickets.index') }}">Cancel</a></div>
            </form>
        </div>
    </section>
@endsection
