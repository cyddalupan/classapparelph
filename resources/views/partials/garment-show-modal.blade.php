{{--
    GARMENT PRINTING MODAL (sale details page: Add Product / Reprocess)
    ---------------------------------------------------------------
    Ito ay 1:1 sa garment printing modal ng Create Sale:
    parehong markup (partials.garment-print-modal-form) at
    parehong JS (partials.garment-print-modal-script).
    Ang tanging pagkakaiba: sa halip na "Add to Cart" sa create page,
    dito ay nagsu-submit ng change request sa sale (garment_submitOverride).
--}}
<style>
    .product-modal .modal-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    .product-modal .modal-title i { margin-right: 10px; }
    #garmentModalBody .print-size-checkbox.selected {
        background-color: #e7f3ff;
        border-color: #0d6efd !important;
    }
    #garmentModalBody .product-row .product-select { font-size: 0.85rem; }
    #garmentModalBody .card { border: 1px solid #dee2e6; border-radius: 8px; }
    #garmentModalBody .card-title { font-size: 0.95rem; font-weight: 600; }
    #referenceDropZone:hover { border-color: #0d6efd !important; background-color: #f0f7ff; }
</style>

<div class="modal fade product-modal" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productModalLabel">
                    <i class="fas fa-tshirt"></i> Add Garment Product
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="garmentModalBody" style="display:block;">
                    @include('partials.garment-print-modal-form')
                </div>
            </div>
        </div>
    </div>
</div>

<script>
@include('partials.garment-print-modal-script')
</script>

{{-- Order Slip template — kailangan ito para may laman ang "Print Order Slip" --}}
@include('partials.garment-order-slip')

<script>
/* ============================================================
   ADAPTER — ginagamit ang parehong garment modal ng Create Sale.
   gadd_open({mode, saleId, department, customerName, saleNumber, submitUrl})
   ============================================================ */
(function () {
    'use strict';

    var CTX = {
        mode: 'add',
        saleId: '',
        department: 'iprint',
        submitUrl: '',
        customerName: '',
        saleNumber: ''
    };
    window.gadd_ctx = CTX;
    window.currentProductType = 'garment';

    function el(id) { return document.getElementById(id); }

    // ---------- OPEN ----------
    window.gadd_open = function (opts) {
        opts = opts || {};
        CTX.mode = opts.mode === 'reprocess' ? 'reprocess' : 'add';
        CTX.saleId = opts.saleId || '';
        CTX.department = (String(opts.department || 'iprint').toLowerCase() === 'consol') ? 'consol' : 'iprint';
        CTX.submitUrl = opts.submitUrl || '';
        CTX.customerName = opts.customerName || '';
        CTX.saleNumber = opts.saleNumber || '';

        // Buong garment form: reset + load products/filters/print types
        // (kaparehong entry point ng Create Sale)
        if (typeof window.garment_openModal === 'function') {
            window.garment_openModal('garment');
        }

        var title = el('productModalLabel');
        if (title) {
            title.innerHTML = '<i class="fas ' + (CTX.mode === 'reprocess' ? 'fa-sync-alt' : 'fa-tshirt') + '"></i> '
                + (CTX.mode === 'reprocess' ? 'Reprocess Order' : 'Add Garment Product')
                + (CTX.saleNumber ? ' <span class="badge bg-light text-dark ms-2">Sale #' + CTX.saleNumber + '</span>' : '');
        }

        // Sale context: fixed department + prefilled project name
        var dept = el('garment_department');
        if (dept) {
            dept.value = CTX.department;
            dept.disabled = true;
        }
        var pn = el('garment_projectName');
        if (pn) {
            pn.value = (CTX.mode === 'reprocess' ? 'Reprocess Order - ' : 'Additional Order - ') + (CTX.customerName || '');
        }

        if (typeof window.garment_refreshCartGate === 'function') window.garment_refreshCartGate();

        var m = bootstrap.Modal.getOrCreateInstance(el('productModal'));
        m.show();
    };

    // ---------- SUBMIT (kapalit ng "Add to Cart") ----------
    window.garment_submitOverride = function (item) {
        if (!CTX.submitUrl) { alert('Walang submit URL. I-refresh ang page.'); return; }

        // Normalize sa server contract: mockup = data-URL string, unitPrice = per-piece
        var payload = {};
        for (var k in item) { if (Object.prototype.hasOwnProperty.call(item, k)) payload[k] = item[k]; }
        if (item && item.mockup && item.mockup.dataUrl) {
            payload.mockup_name = item.mockup.name || '';
            payload.mockup = item.mockup.dataUrl;
        } else if (!item || !item.mockup) {
            payload.mockup = '';
        }
        if (payload.quantity > 0) {
            payload.unitPrice = (Number(item.totalProductPrice) || 0) / payload.quantity;
        }

        var btn = el('garment_addItemBtn');
        var old = btn ? btn.innerHTML : '';
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Submitting...';
        }

        fetch(CTX.submitUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') || {}).content || '',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload)
        })
        .then(function (r) {
            var ct = (r.headers.get('content-type') || '').toLowerCase();
            if (ct.indexOf('application/json') === -1) {
                throw new Error('Server returned HTTP ' + r.status + ' (hindi JSON). I-refresh ang page at subukan muli.');
            }
            return r.json();
        })
        .then(function (data) {
            if (data && data.success) {
                var m = bootstrap.Modal.getInstance(el('productModal'));
                if (m) m.hide();
                if (typeof window.showToast === 'function') {
                    window.showToast(data.message || 'Submitted! Waiting for approval.', 'success');
                } else {
                    alert(data.message || 'Submitted! Waiting for approval.');
                }
                setTimeout(function () { location.reload(); }, 700);
            } else {
                alert('Error: ' + ((data && data.message) || 'Unknown error'));
            }
        })
        .catch(function (err) { alert('Request failed: ' + err.message); })
        .finally(function () {
            if (btn) { btn.disabled = false; btn.innerHTML = old; }
        });
    };
})();
</script>
