@extends('layouts.app')
@section('title', 'Knowledge base')
@section('content')
    <section class="page">
        <div class="page-heading"><div><span class="eyebrow">Self-service support</span><h1>Knowledge base</h1><p>Clear answers to common IT questions, whenever you need them.</p></div>@auth @if(auth()->user()->isStaff())<a class="button button-primary" href="{{ route('knowledge.create') }}">＋ Add article</a>@endif @endauth</div>
        <div class="panel">
            <form method="GET" action="{{ route('knowledge.index') }}" style="display:flex;gap:9px;margin-bottom:18px"><input class="search-input" name="q" value="{{ request('q') }}" placeholder="Search articles, topics, or keywords"><button class="button button-primary" type="submit">Search</button></form>
            @if($articles->isEmpty())
                <div class="empty"><strong>No articles found</strong>Try another search or check back later for more support guides.</div>
            @else
                <div class="article-grid">@foreach($articles as $article)<a class="feature-card article-card" href="{{ route('knowledge.show', $article) }}"><span class="pill">{{ $article->category }}</span><h3>{{ $article->title }}</h3><p>{{ $article->summary }}</p><span class="feature-link">Read guide →</span></a>@endforeach</div>
                <div class="pagination">{{ $articles->links() }}</div>
            @endif
        </div>
    </section>
@endsection
