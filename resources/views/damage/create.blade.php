@extends('layouts.app')

@section('title', 'Report Damage')

@push('styles')
<style>
    .upload-area { border: 2px dashed #ccc; border-radius: 8px; padding: 20px; text-align: center; cursor: pointer; transition: all 0.2s; }
    .upload-area:hover, .upload-area.has-file { border-color: #dc3545; background: #fff5f5; }
    .sev-option.selected { outline: 3px solid #dc3545; }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="fas fa-exclamation-triangle me-2 text-danger"></i>Report Damage</h4>
            <p class="text-muted mb-0 small">File a damage incident — it will be routed to the designated shop for review</p>
        </div>
        <a href="{{ route('damage.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Back to Reports
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('damage.store') }}" enctype="multipart/form-data" id="damageForm">
        @csrf
        <div class="row">
            <div class="col-lg-8">
                <!-- Shop -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white fw-bold">Designated Shop <span class="text-danger">*</span></div>
                    <div class="card-body">
                        @if($managedShop)
                            <input type="hidden" name="shop_id" value="{{ $managedShop->id }}">
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-store me-1"></i>
                                Auto-assigned to <strong>{{ $managedShop->name }}</strong> — you manage this shop.
                            </div>
                        @else
                            <select name="shop_id" class="form-select" required>
                                <option value="">Select shop...</option>
                                @foreach($shops as $shop)
                                    <option value="{{ $shop->id }}" {{ old('shop_id', $presetSaleId ? '' : '') == $shop->id ? 'selected' : '' }}>{{ $shop->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Sales team: piliin kung saang shop nangyari ang damage (iPrint, Class, Consol, atbp).</small>
                        @endif
                    </div>
                </div>

                <!-- Incident details -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white fw-bold">Incident Details</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Severity <span class="text-danger">*</span></label>
                            <div class="d-flex gap-2">
                                @foreach(\App\Models\DamageReport::SEVERITIES as $val => $label)
                                    <div class="flex-fill">
                                        <input type="radio" class="btn-check" name="severity" id="sev_{{ $val }}" value="{{ $val }}" {{ old('severity', 'minor') === $val ? 'checked' : '' }} required>
                                        <label class="btn btn-outline-secondary w-100 py-2" for="sev_{{ $val }}">
                                            <i class="fas fa-circle me-1 text-{{ $val === 'minor' ? 'secondary' : ($val === 'major' ? 'warning' : 'danger') }}"></i>
                                            {{ $label }}
                                            <small class="d-block text-muted">{{ \App\Models\DamageReport::SEVERITY_POINTS[$val] }} pt</small>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <select name="category" class="form-select" required>
                                @foreach(\App\Models\DamageReport::CATEGORIES as $val => $label)
                                    <option value="{{ $val }}" {{ old('category') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Quantity Damaged <span class="text-danger">*</span></label>
                            <input type="number" name="quantity" class="form-control" min="1" placeholder="Ilang piraso ang nadamage? e.g. 3" value="{{ old('quantity') }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description <span class="text-danger">*</span></label>
                            <textarea name="description" class="form-control" rows="4" placeholder="Ano ang nangyari? Saan? Sino ang involved (kung alam)?" required>{{ old('description') }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Related Sale (optional)</label>
                            <input type="text" class="form-control" id="sale_search" placeholder="Search sales number... e.g. SALE-20260821-..." autocomplete="off">
                            <input type="hidden" name="sale_id" id="sale_id" value="{{ $presetSaleId }}">
                            <div id="sale_result" class="mt-2"></div>
                            <small class="text-muted">Pwede ring i-tag later ng shop manager bago i-review.</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Evidence -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white fw-bold">Evidence <span class="text-danger">*</span></div>
                    <div class="card-body">
                        <div class="upload-area" id="uploadArea">
                            <i class="fas fa-cloud-upload-alt fa-2x text-danger mb-2"></i>
                            <p class="mb-1">Drag & drop photo here</p>
                            <p class="text-muted small mb-2">o click para mag-browse (JPG, PNG, GIF)</p>
                            <input type="file" id="evidence" name="evidence" accept="image/*" class="d-none">
                            <div id="fileName" class="mt-2 text-success"></div>
                        </div>
                        <div id="previewWrap" class="mt-3" style="display:none;">
                            <img id="evidencePreview" src="" class="img-thumbnail w-100" style="max-height:200px;object-fit:cover;">
                        </div>
                        <small class="text-muted d-block mt-2">Screenshot ng damage o ng kumpletong dokumento.</small>
                    </div>
                </div>

                <button type="submit" class="btn btn-danger btn-lg w-100">
                    <i class="fas fa-paper-plane me-2"></i>Submit Report
                </button>
                <p class="text-muted small text-center mt-2">Ire-route sa shop manager para sa review bago i-issue.</p>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Upload area
    var uploadArea = document.getElementById('uploadArea');
    var fileInput = document.getElementById('evidence');
    if (uploadArea && fileInput) {
        uploadArea.addEventListener('click', function() { fileInput.click(); });
        fileInput.addEventListener('change', function() {
            var nameEl = document.getElementById('fileName');
            if (nameEl && this.files.length > 0) nameEl.textContent = '✅ ' + this.files[0].name;
            var wrap = document.getElementById('previewWrap');
            var img = document.getElementById('evidencePreview');
            if (this.files && this.files[0]) {
                img.src = URL.createObjectURL(this.files[0]);
                wrap.style.display = 'block';
            }
            uploadArea.classList.add('has-file');
        });
    }

    // Sale search (lightweight: GET suggestions from existing route if present, else manual entry fallback)
    var saleSearch = document.getElementById('sale_search');
    var saleId = document.getElementById('sale_id');
    var saleResult = document.getElementById('sale_result');
    if (saleSearch) {
        saleSearch.addEventListener('input', function() {
            saleId.value = '';
            var q = this.value.trim();
            if (q.length < 4) { saleResult.innerHTML = ''; return; }
            fetch('/sales/prototype/search?q=' + encodeURIComponent(q))
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    saleResult.innerHTML = '';
                    if (data && data.length) {
                        data.slice(0, 5).forEach(function(sale) {
                            var item = document.createElement('div');
                            item.className = 'border rounded p-2 mb-1 small';
                            item.style.cursor = 'pointer';
                            item.innerHTML = '<strong>' + sale.sales_number + '</strong> — ' + (sale.customer_name || '');
                            item.addEventListener('click', function() {
                                saleId.value = sale.id;
                                saleSearch.value = sale.sales_number;
                                saleResult.innerHTML = '<span class="text-success"><i class="fas fa-check me-1"></i>Tagged to ' + sale.sales_number + '</span>';
                            });
                            saleResult.appendChild(item);
                        });
                    } else {
                        saleResult.innerHTML = '<span class="text-muted small">No matching sale found.</span>';
                    }
                })
                .catch(function() { saleResult.innerHTML = ''; });
        });
    }

    // Severity radio visual
    document.querySelectorAll('input[name="severity"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            document.querySelectorAll('.btn-check[name="severity"] + label').forEach(function(l) { l.classList.remove('active'); });
        });
    });
});
</script>
@endsection
