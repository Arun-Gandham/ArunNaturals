@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Add Product</h4>
        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">Back to Products</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
                @include('admin.products._form')

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Save Product</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Main image preview
        const mainInput = document.getElementById('main_image');
        const mainPreview = document.getElementById('main_image_preview');
        if (mainInput && mainPreview) {
            mainInput.addEventListener('change', function (event) {
                const file = event.target.files && event.target.files[0];
                if (!file) {
                    mainPreview.classList.add('d-none');
                    mainPreview.src = '#';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function (e) {
                    mainPreview.src = e.target.result;
                    mainPreview.classList.remove('d-none');
                };
                reader.readAsDataURL(file);
            });
        }

        // Gallery preview with remove capability before upload
        const galleryInput = document.getElementById('gallery_images');
        const galleryPreview = document.getElementById('gallery_preview');
        if (!galleryInput || !galleryPreview) return;

        let galleryFiles = [];

        function refreshGalleryInput() {
            if (!window.DataTransfer) return;
            const dt = new DataTransfer();
            galleryFiles.forEach(file => dt.items.add(file));
            galleryInput.files = dt.files;
        }

        function renderGalleryPreview() {
            galleryPreview.innerHTML = '';
            galleryFiles.forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = function (e) {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'position-relative';
                    wrapper.style.width = '72px';
                    wrapper.style.height = '72px';

                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.alt = 'Preview';
                    img.className = 'rounded border';
                    img.style.width = '72px';
                    img.style.height = '72px';
                    img.style.objectFit = 'cover';

                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'btn btn-sm btn-danger py-0 px-1 position-absolute';
                    btn.style.top = '2px';
                    btn.style.right = '2px';
                    btn.innerHTML = '<i class="fa-solid fa-xmark"></i>';
                    btn.addEventListener('click', function () {
                        galleryFiles.splice(index, 1);
                        refreshGalleryInput();
                        renderGalleryPreview();
                    });

                    wrapper.appendChild(img);
                    wrapper.appendChild(btn);
                    galleryPreview.appendChild(wrapper);
                };
                reader.readAsDataURL(file);
            });
        }

        galleryInput.addEventListener('change', function (event) {
            galleryFiles = Array.from(event.target.files || []);
            renderGalleryPreview();
        });
    });
</script>
@endsection
