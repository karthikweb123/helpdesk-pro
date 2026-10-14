@extends('layouts.app')
@section('title', $mode.' article')
@section('content')
    <section class="page">
        <div class="page-heading"><div><span class="eyebrow">Knowledge base</span><h1>{{ $mode }} article</h1><p>Publish clear, practical guidance for your team.</p></div></div>
        <div class="panel">
            <form method="POST" action="{{ $article->exists ? route('knowledge.update', $article) : route('knowledge.store') }}">
                @csrf @if($article->exists)@method('PATCH')@endif
                <div class="form-grid">
                    <div class="field full"><label for="title">Article title</label><input id="title" name="title" maxlength="180" value="{{ old('title', $article->title) }}" required></div>
                    <div class="field"><label for="category">Category</label><select id="category" name="category" required>@foreach(['Hardware','Software','Network','Account & access','Other'] as $category)<option value="{{ $category }}" @selected(old('category', $article->category) === $category)>{{ $category }}</option>@endforeach</select></div>
                    <div class="field full"><label for="summary">Short summary</label><input id="summary" name="summary" maxlength="500" value="{{ old('summary', $article->summary) }}" required></div>
                    <div class="field full"><label for="body">Instructions</label><textarea id="body" name="body" maxlength="30000" required style="min-height:250px">{{ old('body', $article->body) }}</textarea></div>
                </div>
                <div class="form-actions"><button class="button button-primary" type="submit">{{ $article->exists ? 'Save changes' : 'Publish article' }}</button><a class="button button-quiet" href="{{ $article->exists ? route('knowledge.show', $article) : route('knowledge.index') }}">Cancel</a></div>
            </form>
        </div>
    </section>
@endsection
