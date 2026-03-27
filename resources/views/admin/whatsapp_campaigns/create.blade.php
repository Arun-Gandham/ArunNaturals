@extends('layouts.app')

@section('content')
<div class="container-fluid mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Create WhatsApp Offer Campaign</h4>
        <a href="{{ route('admin.whatsapp.campaigns.index') }}" class="btn btn-outline-secondary">Back to Campaigns</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.whatsapp.campaigns.store') }}">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Campaign Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="Festival Offer, New Launch, etc.">
                        @error('name')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Offer Link (optional)</label>
                        <input type="text" name="offer_url" class="form-control" value="{{ old('offer_url') }}" placeholder="https://yourstore.com/offers">
                        @error('offer_url')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label">WhatsApp Message</label>
                        <textarea name="message" rows="5" class="form-control" placeholder="Write the offer message you want to send to customers...">{{ old('message') }}</textarea>
                        @error('message')<small class="text-danger">{{ $message }}</small>@enderror
                        <small class="text-muted">
                            This text will be copied or opened in WhatsApp when you share the campaign.
                        </small>
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-brands fa-whatsapp me-1"></i> Save Campaign
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

