@extends('layouts.app')

@section('content')
<style>
    .products-page {
        font-size: 0.9rem;
    }

    .products-page .products-card-header {
        background: #f9fafb;
        border-bottom: 1px solid rgba(209, 213, 219, 0.6);
    }

    .products-page .product-name-cell {
        font-weight: 600;
    }

    .products-page .product-meta {
        font-size: 0.75rem;
        color: #9ca3af;
    }

    .products-page .status-pill {
        padding: 0.15rem 0.6rem;
        border-radius: 999px;
        font-size: 0.75rem;
    }

    .products-page .product-thumb {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        object-fit: cover;
        border: 1px solid rgba(148, 163, 184, 0.3);
        background-color: #f3f4f6;
    }
</style>
<div class="container-fluid mb-5 products-page">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Products</h4>
        <a href="{{ route('admin.products.create') }}" class="btn btn-primary">Add Product</a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if ($products->isEmpty())
        <div class="alert alert-info mb-0">
            No products found. Click "Add Product" to create your first product.
        </div>
    @else
        <div class="card dashboard-section-card">
            <div class="card-header products-card-header d-flex justify-content-between align-items-center">
                <div>
                    <div class="mini-label text-uppercase">Catalog</div>
                    <h6 class="mb-0 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-box"></i>
                        Product List
                    </h6>
                </div>
                <span class="badge bg-light text-muted">
                    {{ $products->total() }} total
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0 align-middle">
                        <thead class="table-light small text-uppercase text-muted">
                            <tr>
                                <th>Product</th>
                                <th>SKU</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($products as $product)
                                <tr>
                                    <td>
                                        @php
                                            $thumb = $product->main_image_path
                                                ? asset($product->main_image_path)
                                                : 'https://via.placeholder.com/80x80.png?text=Product';
                                        @endphp
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="{{ $thumb }}" alt="{{ $product->name }}" class="product-thumb">
                                            <div>
                                                <div class="product-name-cell">{{ $product->name }}</div>
                                                <div class="product-meta">
                                                    <i class="fa-solid fa-hashtag me-1"></i>{{ $product->id }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $product->sku ?: '—' }}</td>
                                    <td>₹{{ number_format($product->price, 2) }}</td>
                                    <td>{{ $product->stock }}</td>
                                    <td>
                                        <span class="status-pill badge {{ $product->is_active ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $product->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>{{ $product->created_at?->format('d M Y') }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-outline-secondary" title="Edit product">
                                            <i class="fa-regular fa-pen-to-square"></i>
                                        </a>
                                        <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this product?')" title="Delete product">
                                                <i class="fa-regular fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                {{ $products->links() }}
            </div>
        </div>
    @endif
</div>
@endsection
