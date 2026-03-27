@extends('layouts.store')

@section('title', ($product['name'] ?? 'Product') . ' | Arun Naturals')

@section('content')
@php
    $phone = $whatsappPhone ? ltrim($whatsappPhone, '+') : null;
    $whatsappUrl = $phone
        ? 'https://wa.me/' . $phone . '?text=' . rawurlencode($whatsappText)
        : null;
@endphp

<div class="container">
    <div class="row g-4 align-items-center mb-5">
        <div class="col-lg-6">
            <div class="mb-3 d-flex align-items-center gap-3">
                <span class="brand-pill">Arun Naturals · Herbal Care</span>
                <span class="trust-badge d-inline-flex align-items-center">
                    <span class="me-1">★</span> Thoughtfully crafted, honest ingredients
                </span>
            </div>

            <h1 class="display-5 fw-semibold mb-3" style="letter-spacing: -0.03em;">
                {{ $product['name'] ?? 'Product' }}
            </h1>

            @if (!empty($product['tagline']))
                <p class="fs-5 text-muted mb-3">
                    {{ $product['tagline'] }}
                </p>
            @endif

            @if (!empty($product['short_description']))
                <p class="mb-4" style="color:#4b5563;">
                    {{ $product['short_description'] }}
                </p>
            @endif

            <div class="d-flex flex-wrap align-items-center gap-3 mb-4">
                @if ($whatsappUrl)
                    <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener" class="btn btn-lg btn-whatsapp-main px-4">
                        WhatsApp Us
                    </a>
                    <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener" class="btn btn-lg btn-outline-soft px-4">
                        Buy Now on WhatsApp
                    </a>
                @else
                    <span class="text-danger small">
                        WhatsApp phone is not configured. Set <code>WHATSAPP_PHONE</code> in your <code>.env</code>.
                    </span>
                @endif
            </div>

            <div class="d-flex flex-wrap gap-3 text-muted small">
                <span class="trust-badge">No mineral oil</span>
                <span class="trust-badge">No parabens</span>
                <span class="trust-badge">Made in small batches</span>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card card-soft border-0 p-4 p-md-5">
                <div class="ratio ratio-4x3 mb-3 rounded-4 overflow-hidden bg-light">
                    @if (!empty($product['image']))
                        <img id="mainProductImage" src="{{ $product['image'] }}" alt="{{ $product['name'] ?? '' }}" class="w-100 h-100" style="object-fit: cover;">
                    @else
                        <div class="w-100 h-100 d-flex align-items-center justify-content-center text-muted">
                            Product image coming soon
                        </div>
                    @endif
                </div>
                <p class="mb-0 text-muted" style="font-size: 0.85rem;">
                    Every batch is blended with care. Colour and aroma may vary slightly as we avoid artificial stabilisers.
                </p>

                @if (!empty($galleryImages))
                    <div class="mt-3">
                        <div class="section-label mb-2">More views</div>
                        <div class="d-flex gap-2 overflow-auto pb-1">
                            @foreach ($galleryImages as $galleryImage)
                                <button type="button"
                                        class="btn p-0 border-0 bg-transparent gallery-thumb-btn"
                                        data-image="{{ $galleryImage }}">
                                    <img src="{{ $galleryImage }}"
                                         alt="Product angle"
                                         class="rounded border"
                                         style="width: 72px; height: 72px; object-fit: cover;">
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card card-soft border-0 p-4 mb-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="me-2 section-label">What we use</div>
                    <div class="flex-grow-1" style="height: 1px; background: rgba(209, 213, 219, 0.8);"></div>
                </div>

                @if (!empty($product['ingredients']) && is_array($product['ingredients']))
                    <ul class="mb-0">
                        @foreach ($product['ingredients'] as $ingredient)
                            <li class="mb-1" style="color:#374151;">{{ $ingredient }}</li>
                        @endforeach
                    </ul>
                @else
                    <p class="mb-0 text-muted">Gentle, carefully selected ingredients. Add your full ingredient story here.</p>
                @endif
            </div>

            <div class="card card-soft border-0 p-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="me-2 section-label">Good to know</div>
                    <div class="flex-grow-1" style="height: 1px; background: rgba(209, 213, 219, 0.8);"></div>
                </div>

                @if (!empty($product['highlights']) && is_array($product['highlights']))
                    <ul class="mb-0">
                        @foreach ($product['highlights'] as $point)
                            <li class="mb-1" style="color:#374151;">{{ $point }}</li>
                        @endforeach
                    </ul>
                @else
                    <p class="mb-0 text-muted">Use this space to talk about batch size, sourcing, certifications or any care you put into the product.</p>
                @endif
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card card-soft border-0 p-4 mb-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="me-2 section-label">How to use</div>
                    <div class="flex-grow-1" style="height: 1px; background: rgba(209, 213, 219, 0.8);"></div>
                </div>

                @if (!empty($product['usage']) && is_array($product['usage']))
                    <ol class="mb-0">
                        @foreach ($product['usage'] as $step)
                            <li class="mb-1" style="color:#374151;">{{ $step }}</li>
                        @endforeach
                    </ol>
                @else
                    <p class="mb-0 text-muted">Explain how often to use the product, how much to apply and any important safety notes.</p>
                @endif
            </div>

            <div class="card card-soft border-0 p-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="me-2 section-label">Questions?</div>
                    <div class="flex-grow-1" style="height: 1px; background: rgba(209, 213, 219, 0.8);"></div>
                </div>
                <p class="mb-3 text-muted">
                    Not sure if this is right for you, or want help choosing between products? Send us a short note on WhatsApp and we’ll respond as soon as we can.
                </p>

                @if ($whatsappUrl)
                    <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener" class="btn btn-sm btn-whatsapp-main px-3">
                        Ask us on WhatsApp
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var mainImg = document.getElementById('mainProductImage');
        if (!mainImg) return;

        document.querySelectorAll('.gallery-thumb-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var src = this.getAttribute('data-image');
                if (src) {
                    mainImg.src = src;
                }
            });
        });
    });
</script>
@endsection
