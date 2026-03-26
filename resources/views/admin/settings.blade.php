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
            <form method="POST" action="{{ route('admin.settings.update') }}">
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
                        <label class="form-label">Favicon URL (e.g. /favicon.ico)</label>
                        <input type="text" name="favicon_url" class="form-control" value="{{ old('favicon_url', $settings->favicon_url ?? null) }}">
                        @error('favicon_url')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Logo URL (for frontend header)</label>
                        <input type="text" name="logo_url" class="form-control" value="{{ old('logo_url', $settings->logo_url ?? null) }}">
                        @error('logo_url')<small class="text-danger">{{ $message }}</small>@enderror
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
@endsection

