@extends('layouts.app')

@section('content')
<div class="container mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Add Category</h4>
        <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">Back to Categories</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.categories.store') }}" method="POST">
                @include('admin.categories.form')

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Save Category</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

