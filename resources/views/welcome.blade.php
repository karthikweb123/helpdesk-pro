@extends('layouts.app')

@section('content')
    <section class="hero">
        <div class="hero-copy">
            <span class="eyebrow">IT support, made simple</span>
            <h1>Get back to<br><span>what matters.</span></h1>
            <p>One place to report issues, track every update, and find answers from your team's knowledge base.</p>
            <div class="hero-actions">
                <a class="button button-primary" href="{{ route('register') }}">Create a support ticket <span>→</span></a>
                <a class="button button-quiet" href="{{ route('knowledge.index') }}">Browse help articles</a>
            </div>
            <div class="hero-note"><span class="online-dot"></span> Your support team is ready to help</div>
        </div>
        <div class="hero-art" aria-hidden="true">
            <div class="art-orbit orbit-one"></div><div class="art-orbit orbit-two"></div>
            <div class="art-card art-card-back"></div>
            <div class="art-card art-card-front">
                <div class="art-top"><span class="art-icon">✓</span><span class="art-tag">RESOLVED</span></div>
                <div class="art-line long"></div><div class="art-line medium"></div>
                <div class="art-bottom"><span class="avatar">S</span><span>Support team</span><span class="art-time">just now</span></div>
            </div>
            <div class="art-bubble bubble-one">↗</div><div class="art-bubble bubble-two">✦</div>
        </div>
    </section>
    <section class="home-features">
        <div class="section-heading"><span class="eyebrow">Everything in one place</span><h2>Support that moves you forward.</h2></div>
        <div class="feature-grid">
            <a class="feature-card" href="{{ route('tickets.index') }}"><span class="feature-icon lavender">▤</span><h3>Ticket tracking</h3><p>Follow your requests from first report to final resolution.</p><span class="feature-link">View your tickets →</span></a>
            <a class="feature-card" href="{{ route('knowledge.index') }}"><span class="feature-icon mint">⌕</span><h3>Answers, instantly</h3><p>Explore practical guides for common hardware, software, and network issues.</p><span class="feature-link">Explore the knowledge base →</span></a>
            <div class="feature-card"><span class="feature-icon peach">♡</span><h3>Real people, real help</h3><p>Get updates from your support team and reply right from your ticket.</p><span class="feature-link">Here when you need us</span></div>
        </div>
    </section>
@endsection
