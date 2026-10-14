@extends('layouts.app')
@section('title', $article->title)
@section('content')
    <section class="page">
        <div class="page-heading"><div><a class="feature-link" href="{{ route('knowledge.index') }}">← Knowledge base</a><div style="margin-top:14px"><span class="pill">{{ $article->category }}</span></div><h1 style="margin-top:12px">{{ $article->title }}</h1><p>{{ $article->summary }}</p></div>
            @auth @if(auth()->user()->isStaff())<a class="button button-quiet" href="{{ route('knowledge.edit', $article) }}">Edit article</a>@endif @endauth
        </div>
        <article class="panel article-body">{{ $article->body }}</article>
        <div class="panel" style="display:flex;justify-content:space-between;align-items:center;gap:15px"><span class="muted" style="font-size:11px">Was this not quite what you needed?</span><a class="button button-primary" href="{{ auth()->check() ? route('tickets.create') : route('login') }}">Contact support</a></div>
    </section>
@endsection
