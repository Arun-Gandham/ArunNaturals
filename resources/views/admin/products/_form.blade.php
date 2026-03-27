@csrf

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Name *</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $product->name) }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Slug *</label>
        <input type="text" name="slug" class="form-control" value="{{ old('slug', $product->slug) }}" required>
        <div class="form-text">Used in URLs (e.g. herbal-face-oil).</div>
    </div>

    <div class="col-md-4">
        <label class="form-label">SKU</label>
        <input type="text" name="sku" class="form-control" value="{{ old('sku', $product->sku) }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Price (₹) *</label>
        <input type="number" name="price" class="form-control" step="0.01" min="0" value="{{ old('price', $product->price ?? 0) }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Stock *</label>
        <input type="number" name="stock" class="form-control" min="0" value="{{ old('stock', $product->stock ?? 0) }}" required>
    </div>

    <div class="col-md-6">
        <label class="form-label">Short Title</label>
        <input type="text" name="short_title" class="form-control" value="{{ old('short_title', $product->short_title) }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">Main Image</label>
        @php
            $existingImage = $product->main_image_path ?? null;
        @endphp
        @if ($existingImage)
            <div class="mb-2">
                <img src="{{ asset($existingImage) }}" alt="Current image" class="img-fluid rounded border" style="max-height: 160px;">
            </div>
        @endif
        <input type="file" name="main_image" id="main_image" class="form-control" accept="image/*">
        <div class="form-text">Upload a JPG/PNG image (max 2 MB).</div>
        <div class="mt-2">
            <img id="main_image_preview" src="#" alt="Preview" class="img-fluid rounded border d-none" style="max-height: 160px;">
        </div>
    </div>

    <div class="col-md-6">
        <label class="form-label">Gallery Images (optional)</label>
        @if ($product->exists && $product->images && $product->images->count())
            <div class="mb-2 d-flex flex-wrap gap-2">
                @foreach($product->images as $img)
                    <div class="position-relative">
                        <img src="{{ asset($img->image_path) }}" alt="Gallery image" class="img-thumbnail" style="width: 72px; height: 72px; object-fit: cover;">
                        <form action="{{ route('admin.products.images.destroy', $img) }}" method="POST" class="position-absolute" style="top: 2px; right: 2px;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger py-0 px-1" onclick="return confirm('Remove this image?')" title="Remove image">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif
        <input type="file" name="gallery_images[]" id="gallery_images" class="form-control" accept="image/*" multiple>
        <div class="form-text">Upload one or more extra images (max 2 MB each) to show different angles.</div>
        <div id="gallery_preview" class="mt-2 d-flex flex-wrap gap-2"></div>
    </div>

    <div class="col-12">
        <label class="form-label">Short Description</label>
        <textarea name="short_description" class="form-control" rows="2">{{ old('short_description', $product->short_description) }}</textarea>
    </div>

    <div class="col-12">
        <label class="form-label">Full Description</label>
        <textarea name="description" class="form-control" rows="4">{{ old('description', $product->description) }}</textarea>
    </div>

    <div class="col-md-6">
        <label class="form-label">Ingredients (one per line)</label>
        <textarea name="ingredients" class="form-control" rows="3">{{ old('ingredients', $product->ingredients) }}</textarea>
    </div>
    <div class="col-md-6">
        <label class="form-label">Usage / How to use</label>
        <textarea name="usage" class="form-control" rows="3">{{ old('usage', $product->usage) }}</textarea>
    </div>

    <div class="col-12">
        <label class="form-label">Highlights (benefits, key points)</label>
        <textarea name="highlights" class="form-control" rows="3">{{ old('highlights', $product->highlights) }}</textarea>
    </div>

    @isset($categories)
        <div class="col-12">
            <label class="form-label">Categories</label>
            @php
                $selected = old('category_ids', $selectedCategories ?? []);
            @endphp
            <select name="category_ids[]" class="form-select" multiple size="4">
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ in_array($category->id, $selected, true) ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            <div class="form-text">Hold Ctrl / Cmd to select multiple categories.</div>
        </div>
    @endisset

    <div class="col-12">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                   value="1" {{ old('is_active', $product->is_active ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">
                Active (visible / usable)
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
