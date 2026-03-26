@extends('layouts.store')

@section('title', 'Welcome to Arun Naturals')

@section('content')
<div class="container">
    <div class="row align-items-center g-4 mb-5">
        <div class="col-lg-7">
            <div class="mb-3 d-flex align-items-center gap-3">
                <span class="brand-pill">Welcome to Arun Naturals</span>
                <span class="trust-badge d-inline-flex align-items-center">
                    <span class="me-1">✓</span> Honest, small‑batch herbal care
                </span>
            </div>

            <h1 class="display-4 fw-semibold mb-3" style="letter-spacing:-0.03em;">
                Peaceful, trustworthy care for everyday rituals.
            </h1>

            <p class="fs-5 text-muted mb-4">
                Arun Naturals brings together traditional herbal wisdom and gentle modern science to create simple, effective products you can feel calm and confident using every day.
            </p>

            <div class="d-flex flex-wrap gap-3 mb-4">
                <a href="{{ route('products.show', ['slug' => 'herbal-face-oil']) }}" class="btn btn-whatsapp-main btn-lg px-4">
                    View Face Oil
                </a>
                <a href="{{ route('products.show', ['slug' => 'herbal-hair-oil']) }}" class="btn btn-outline-soft btn-lg px-4">
                    View Hair Oil
                </a>
            </div>

            <div class="d-flex flex-wrap gap-3 text-muted small">
                <span class="trust-badge">No mineral oil</span>
                <span class="trust-badge">No harsh parabens</span>
                <span class="trust-badge">Formulated in small batches</span>
                <span class="trust-badge">Made with care in India</span>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card card-soft border-0 p-4 p-md-5">
                <h2 class="h5 mb-3 text-muted text-uppercase" style="letter-spacing:.15em;">Why people trust us</h2>
                <ul class="mb-3" style="color:#374151;">
                    <li class="mb-2">Clear ingredient lists you can understand.</li>
                    <li class="mb-2">Balanced, non‑aggressive formulations suitable for daily use.</li>
                    <li class="mb-2">Conscious production with a focus on consistency and care.</li>
                </ul>
                <p class="mb-0 text-muted small">
                    Every product page explains what we use, why we chose it, and exactly how to use it, so you always know what you are putting on your skin and hair.
                </p>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-6">
            <div class="card card-soft border-0 p-4 h-100">
                <div class="d-flex align-items-center mb-2">
                    <span class="section-label me-2">Our products</span>
                    <div class="flex-grow-1" style="height:1px;background:rgba(209,213,219,0.9);"></div>
                </div>
                <p class="mb-3 text-muted">
                    Simple routines, built around a few thoughtful products. Start with a calming face oil and a nourishing hair oil, then add more only if your skin and hair truly need it.
                </p>
                <div class="d-flex flex-column gap-3">
                    <div>
                        <h3 class="h6 mb-1">Herbal Face Oil</h3>
                        <p class="mb-2 small text-muted">Lightweight daily oil that supports glow, softness and barrier comfort.</p>
                        <a href="{{ route('products.show', ['slug' => 'herbal-face-oil']) }}" class="small text-success text-decoration-none">
                            View details &rarr;
                        </a>
                    </div>
                    <div>
                        <h3 class="h6 mb-1">Herbal Hair Oil</h3>
                        <p class="mb-2 small text-muted">Traditional herbs in a modern texture for calmer scalp and stronger‑feeling lengths.</p>
                        <a href="{{ route('products.show', ['slug' => 'herbal-hair-oil']) }}" class="small text-success text-decoration-none">
                            View details &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card card-soft border-0 p-4 h-100">
                <div class="d-flex align-items-center mb-2">
                    <span class="section-label me-2">How we think</span>
                    <div class="flex-grow-1" style="height:1px;background:rgba(209,213,219,0.9);"></div>
                </div>
                <ul class="mb-3" style="color:#374151;">
                    <li class="mb-2"><strong>Fewer, better products</strong> – we focus on essentials instead of overwhelming you with choices.</li>
                    <li class="mb-2"><strong>Transparency first</strong> – we share ingredient stories and usage guidance clearly.</li>
                    <li class="mb-2"><strong>Rituals, not rush</strong> – our products are designed to create a few quiet minutes just for you.</li>
                </ul>
                <p class="mb-0 text-muted small">
                    Start by picking one product that matches your current need. You can always message us on WhatsApp from any product page if you are unsure where to begin.
                </p>
            </div>
        </div>
    </div>

    <div class="card card-soft border-0 p-4 p-md-5 mb-4">
        <div class="row align-items-center g-3">
            <div class="col-md-8">
                <div class="d-flex align-items-center mb-2">
                    <span class="section-label me-2">Ready to explore?</span>
                    <div class="flex-grow-1" style="height:1px;background:rgba(209,213,219,0.9);"></div>
                </div>
                <p class="mb-2" style="color:#374151;">
                    Visit a product page to see full details – ingredients, usage, and what to expect – and reach us instantly on WhatsApp for personalised guidance.
                </p>
                <p class="mb-0 text-muted small">
                    Your order is prepared carefully and shipped with tracking. We are always one message away if you have questions.
                </p>
            </div>
            <div class="col-md-4 d-flex justify-content-md-end mt-3 mt-md-0">
                <a href="{{ route('products.show', ['slug' => 'herbal-face-oil']) }}" class="btn btn-whatsapp-main px-4 me-2 mb-2 mb-md-0">
                    View products
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
