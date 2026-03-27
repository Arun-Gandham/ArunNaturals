@csrf

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Name *</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $category->name) }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Slug</label>
        <input type="text" name="slug" class="form-control" value="{{ old('slug', $category->slug) }}">
        <div class="form-text">Leave blank to auto-generate from name.</div>
    </div>
    <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="3">{{ old('description', $category->description) }}</textarea>
    </div>
    <div class="col-12">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                   value="1" {{ old('is_active', $category->is_active ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">
                Active
            </label>
        </div>
    </div>
</div>

@if ($errors->any())
    <div class="alert alert-danger mt-3">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

