@extends('layouts.app')

@section('content')
<div class="container-fluid mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Site Settings</h4>
    </div>

    @if(session('success'))
        <div class="alert alert-success py-2">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Site Name</label>
                        <input type="text" name="site_name" class="form-control" value="{{ old('site_name', $settings->site_name ?? 'Arun Naturals') }}">
                        @error('site_name')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tagline</label>
                        <input type="text" name="tagline" class="form-control" value="{{ old('tagline', $settings->tagline ?? null) }}">
                        @error('tagline')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Default Meta Title</label>
                        <input type="text" name="meta_title" class="form-control" value="{{ old('meta_title', $settings->meta_title ?? null) }}">
                        @error('meta_title')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Favicon</label>
                        <div class="d-flex align-items-center gap-3">
                            <div>
                                <img
                                    id="faviconPreview"
                                    src="{{ !empty($settings->favicon_url) ? url($settings->favicon_url) : '' }}"
                                    alt="Favicon preview"
                                    style="width:32px;height:32px;border-radius:4px;{{ empty($settings->favicon_url) ? 'display:none;' : '' }}"
                                >
                                @if(empty($settings->favicon_url))
                                    <span id="faviconPlaceholder" class="text-muted small">No favicon uploaded</span>
                                @endif
                            </div>
                            <div class="flex-grow-1">
                                <input type="file" name="favicon_file" id="faviconFileInput" class="form-control form-control-sm" accept="image/x-icon,image/png,image/jpeg">
                                <small class="text-muted">Upload .ico, .png, or .jpg file</small>
                                @error('favicon_file')<small class="text-danger d-block">{{ $message }}</small>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Header Logo</label>
                        <div class="d-flex align-items-center gap-3">
                            <div>
                                <img
                                    id="logoPreview"
                                    src="{{ !empty($settings->logo_url) ? url($settings->logo_url) : '' }}"
                                    alt="Logo preview"
                                    style="width:64px;height:64px;object-fit:contain;border-radius:8px;background:#f3f4f6;{{ empty($settings->logo_url) ? 'display:none;' : '' }}"
                                >
                                @if(empty($settings->logo_url))
                                    <span id="logoPlaceholder" class="text-muted small">No logo uploaded</span>
                                @endif
                            </div>
                            <div class="flex-grow-1">
                                <input type="file" name="logo_file" id="logoFileInput" class="form-control form-control-sm" accept="image/png,image/jpeg,image/svg+xml">
                                <small class="text-muted">Upload .png, .jpg, or .svg file</small>
                                @error('logo_file')<small class="text-danger d-block">{{ $message }}</small>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Meta Keywords (comma separated)</label>
                        <input type="text" name="meta_keywords" class="form-control" value="{{ old('meta_keywords', $settings->meta_keywords ?? null) }}">
                        @error('meta_keywords')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label">Default Meta Description</label>
                        <textarea name="meta_description" rows="3" class="form-control">{{ old('meta_description', $settings->meta_description ?? null) }}</textarea>
                        @error('meta_description')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Facebook URL</label>
                        <input type="text" name="facebook_url" class="form-control" value="{{ old('facebook_url', $settings->facebook_url ?? null) }}">
                        @error('facebook_url')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Instagram URL</label>
                        <input type="text" name="instagram_url" class="form-control" value="{{ old('instagram_url', $settings->instagram_url ?? null) }}">
                        @error('instagram_url')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Twitter URL</label>
                        <input type="text" name="twitter_url" class="form-control" value="{{ old('twitter_url', $settings->twitter_url ?? null) }}">
                        @error('twitter_url')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">Save Settings</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        function attachPreview(fileInputId, imgId, placeholderId) {
            const input = document.getElementById(fileInputId);
            const img = document.getElementById(imgId);
            const placeholder = placeholderId ? document.getElementById(placeholderId) : null;

            if (!input || !img) return;

            input.addEventListener('change', function () {
                const file = this.files && this.files[0];
                if (!file) return;

                const reader = new FileReader();
                reader.onload = function (e) {
                    img.src = e.target.result;
                    img.style.display = 'block';
                    if (placeholder) {
                        placeholder.style.display = 'none';
                    }
                };
                reader.readAsDataURL(file);
            });
        }

        attachPreview('faviconFileInput', 'faviconPreview', 'faviconPlaceholder');
        attachPreview('logoFileInput', 'logoPreview', 'logoPlaceholder');
    });
</script>
@endsection
