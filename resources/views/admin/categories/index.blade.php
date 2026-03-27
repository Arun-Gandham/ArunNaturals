@extends('layouts.app')

@section('content')
<div class="container-fluid mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Categories</h4>
        <a href="{{ route('admin.categories.create') }}" class="btn btn-primary btn-sm">Add Category</a>
    </div>

    @if (session('success'))
        <div class="alert alert-success py-2">{{ session('success') }}</div>
    @endif

    @if ($categories->isEmpty())
        <div class="alert alert-info mb-0">
            No categories found. Click "Add Category" to create one.
        </div>
    @else
        <div class="card dashboard-section-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <div class="mini-label text-uppercase">Catalog</div>
                    <h6 class="mb-0 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-tags"></i>
                        Category List
                    </h6>
                </div>
                <span class="badge bg-light text-muted">{{ $categories->total() }} total</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0 align-middle">
                        <thead class="table-light small text-uppercase text-muted">
                        <tr>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th class="text-end">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($categories as $category)
                            <tr>
                                <td>{{ $category->name }}</td>
                                <td class="text-muted small">{{ $category->slug }}</td>
                                <td>
                                    <span class="badge {{ $category->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $category->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>{{ $category->created_at?->format('d M Y') }}</td>
                                <td class="text-end">
                                    <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-sm btn-outline-secondary" title="Edit category">
                                        <i class="fa-regular fa-pen-to-square"></i>
                                    </a>
                                    <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this category?')" title="Delete category">
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
                {{ $categories->links() }}
            </div>
        </div>
    @endif
</div>
@endsection

