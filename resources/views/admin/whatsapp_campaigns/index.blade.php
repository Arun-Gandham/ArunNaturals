@extends('layouts.app')

@section('content')
<div class="container-fluid mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-1">WhatsApp Offer Campaigns</h4>
            <p class="text-muted mb-0 small">Create shareable WhatsApp offer messages for your customers.</p>
        </div>
        <a href="{{ route('admin.whatsapp.campaigns.create') }}" class="btn btn-primary">
            <i class="fa-brands fa-whatsapp me-1"></i> New Campaign
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success py-2 mb-3">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-0">
            @if($campaigns->isEmpty())
                <p class="text-muted small mb-0 p-3">No WhatsApp campaigns yet. Click “New Campaign” to create your first offer.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0 align-middle">
                        <thead class="table-light small text-uppercase text-muted">
                        <tr>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($campaigns as $campaign)
                            <tr>
                                <td>
                                    <div class="fw-semibold small">{{ $campaign->name }}</div>
                                    @if($campaign->offer_url)
                                        <div class="text-muted small">{{ $campaign->offer_url }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $campaign->status === 'active' ? 'success' : ($campaign->status === 'archived' ? 'secondary' : 'warning') }} text-uppercase">
                                        {{ $campaign->status }}
                                    </span>
                                </td>
                                <td class="small text-muted">
                                    {{ $campaign->created_at?->format('d M, Y H:i') }}
                                </td>
                                <td class="small">
                                    @php
                                        $text = trim($campaign->message . ($campaign->offer_url ? "\n\n" . $campaign->offer_url : ''));
                                        $waLink = 'https://wa.me/?text=' . urlencode($text);
                                    @endphp
                                    <div class="d-flex flex-wrap gap-2">
                                        <button
                                            type="button"
                                            class="btn btn-outline-secondary btn-sm"
                                            onclick="copyCampaignText('{{ addslashes($text) }}')"
                                        >
                                            Copy Text
                                        </button>
                                        <a href="{{ $waLink }}" target="_blank" class="btn btn-success btn-sm">
                                            <i class="fa-brands fa-whatsapp me-1"></i>Open in WhatsApp
                                        </a>
                                        <button
                                            type="button"
                                            class="btn btn-outline-primary btn-sm"
                                            onclick='openSendModal({{ $campaign->id }}, @json($text))'
                                        >
                                            Send to Customers
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-3">
                    {{ $campaigns->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Send campaign modal -->
<div class="modal fade" id="sendCampaignModal" tabindex="-1" aria-labelledby="sendCampaignModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sendCampaignModalLabel">Send WhatsApp Offer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label small">Filter</label>
                        <select id="recipientFilter" class="form-select form-select-sm">
                            <option value="all">All customers with phone</option>
                            <option value="product">Customers who bought product</option>
                        </select>
                    </div>
                    <div class="col-md-4" id="productFilterContainer" style="display:none;">
                        <label class="form-label small">Product</label>
                        <select id="recipientProduct" class="form-select form-select-sm">
                            <option value="">Select product</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">
                                    {{ $product->name }} @if($product->sku) ({{ $product->sku }}) @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="loadRecipientsBtn">
                            Load Recipients
                        </button>
                    </div>
                </div>

                <div class="mb-2 small text-muted" id="recipientsSummary"></div>

                <div class="table-responsive" style="max-height: 360px; overflow-y: auto;">
                    <table class="table table-sm align-middle">
                        <thead class="table-light small">
                        <tr>
                            <th style="width:36px;">
                                <input type="checkbox" id="selectAllRecipients">
                            </th>
                            <th>Name</th>
                            <th>Phone</th>
                        </tr>
                        </thead>
                        <tbody id="recipientsBody"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <div class="small text-muted" id="sendCampaignHint">
                    Selected contacts will open in WhatsApp one by one; you can paste or edit the message there.
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="copyNumbersBtn">
                        Copy Phone Numbers
                    </button>
                    <button type="button" class="btn btn-success btn-sm" id="openWhatsappBtn">
                        <i class="fa-brands fa-whatsapp me-1"></i>Open WhatsApp for First Selected
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const campaignsRecipientsBase = '{{ url('/admin/whatsapp/campaigns') }}';
    let currentCampaignId = null;
    let currentCampaignText = '';

    function copyCampaignText(text) {
        if (!navigator.clipboard) {
            alert('Clipboard not supported in this browser.');
            return;
        }
        navigator.clipboard.writeText(text)
            .then(() => {
                alert('Campaign text copied. Paste it into WhatsApp to send your offer.');
            })
            .catch(() => {
                alert('Failed to copy text.');
            });
    }

    function openSendModal(campaignId, messageText) {
        currentCampaignId = campaignId;
        currentCampaignText = messageText || '';

        document.getElementById('recipientFilter').value = 'all';
        document.getElementById('productFilterContainer').style.display = 'none';
        document.getElementById('recipientsBody').innerHTML = '';
        document.getElementById('recipientsSummary').textContent = '';
        const selectAll = document.getElementById('selectAllRecipients');
        if (selectAll) {
            selectAll.checked = false;
        }

        const modalEl = document.getElementById('sendCampaignModal');

        // Prefer Bootstrap modal if available, otherwise fall back to simple show/hide
        if (window.bootstrap && bootstrap.Modal) {
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        } else {
            modalEl.classList.add('show');
            modalEl.style.display = 'block';
            modalEl.removeAttribute('aria-hidden');
        }

        loadRecipients(); // load all customers by default
    }

    function normalizePhone(phone) {
        if (!phone) return '';
        return String(phone).replace(/\D/g, '');
    }

    async function loadRecipients() {
        if (!currentCampaignId) return;

        const filter = document.getElementById('recipientFilter').value;
        const productId = document.getElementById('recipientProduct').value;

        const params = new URLSearchParams();
        params.set('filter', filter);
        if (filter === 'product' && productId) {
            params.set('product_id', productId);
        }

        const url = `${campaignsRecipientsBase}/${currentCampaignId}/recipients?` + params.toString();

        const tbody = document.getElementById('recipientsBody');
        const summary = document.getElementById('recipientsSummary');
        tbody.innerHTML = '<tr><td colspan="3" class="text-muted small">Loading recipients...</td></tr>';
        summary.textContent = '';

        try {
            const response = await fetch(url, {
                headers: { 'Accept': 'application/json' },
            });
            const data = await response.json();

            if (!response.ok || !data.success) {
                tbody.innerHTML = '<tr><td colspan="3" class="text-danger small">Failed to load recipients.</td></tr>';
                return;
            }

            const recipients = Array.isArray(data.data) ? data.data : [];

            if (!recipients.length) {
                tbody.innerHTML = '<tr><td colspan="3" class="text-muted small">No matching customers with phone numbers.</td></tr>';
                summary.textContent = 'No recipients found for the selected filter.';
                return;
            }

            tbody.innerHTML = recipients.map((r, index) => {
                const phone = normalizePhone(r.phone);
                if (!phone) return '';
                const name = r.name || 'Customer';
                return `
                    <tr>
                        <td>
                            <input type="checkbox" class="recipient-select" value="${phone}" data-name="${name}" checked>
                        </td>
                        <td>${name}</td>
                        <td>${phone}</td>
                    </tr>
                `;
            }).join('');

            summary.textContent = `Loaded ${recipients.length} recipients. You can uncheck any contacts you do not want to include.`;
        } catch (e) {
            tbody.innerHTML = '<tr><td colspan="3" class="text-danger small">Error loading recipients.</td></tr>';
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const filterSelect = document.getElementById('recipientFilter');
        const productContainer = document.getElementById('productFilterContainer');
        const loadBtn = document.getElementById('loadRecipientsBtn');
        const selectAllCb = document.getElementById('selectAllRecipients');
        const copyNumbersBtn = document.getElementById('copyNumbersBtn');
        const openWhatsappBtn = document.getElementById('openWhatsappBtn');
        const sendModalEl = document.getElementById('sendCampaignModal');

        if (filterSelect) {
            filterSelect.addEventListener('change', () => {
                productContainer.style.display = filterSelect.value === 'product' ? 'block' : 'none';
            });
        }

        if (loadBtn) {
            loadBtn.addEventListener('click', () => loadRecipients());
        }

        if (selectAllCb) {
            selectAllCb.addEventListener('change', () => {
                document.querySelectorAll('.recipient-select').forEach(cb => {
                    cb.checked = selectAllCb.checked;
                });
            });
        }

        if (copyNumbersBtn) {
            copyNumbersBtn.addEventListener('click', async () => {
                const selected = Array.from(document.querySelectorAll('.recipient-select:checked'))
                    .map(cb => cb.value);
                if (!selected.length) {
                    alert('Please select at least one recipient.');
                    return;
                }
                const numbersText = selected.join(', ');
                try {
                    await navigator.clipboard.writeText(numbersText);
                    alert('Phone numbers copied. Paste them into your WhatsApp broadcast list or contacts.');
                } catch (e) {
                    alert('Could not copy phone numbers.');
                }
            });
        }

        if (openWhatsappBtn) {
            openWhatsappBtn.addEventListener('click', () => {
                const selected = Array.from(document.querySelectorAll('.recipient-select:checked'))
                    .map(cb => cb.value);
                if (!selected.length) {
                    alert('Please select at least one recipient.');
                    return;
                }
                const phone = selected[0];
                const url = `https://wa.me/${encodeURIComponent(phone)}?text=${encodeURIComponent(currentCampaignText)}`;
                window.open(url, '_blank');
            });
        }

        // If Bootstrap's JS is not present, wire up manual close for the modal
        if (!(window.bootstrap && bootstrap.Modal) && sendModalEl) {
            const closeButtons = sendModalEl.querySelectorAll('[data-bs-dismiss="modal"]');
            closeButtons.forEach(btn => {
                btn.addEventListener('click', () => {
                    sendModalEl.classList.remove('show');
                    sendModalEl.style.display = 'none';
                    sendModalEl.setAttribute('aria-hidden', 'true');
                });
            });
        }
    });
</script>
@endsection
