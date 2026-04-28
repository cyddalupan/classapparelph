<!-- Add Bulk Discount Modal -->
<div class="modal fade" id="addBulkModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Bulk Discount Rule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="bulkForm">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="bulk_min" class="form-label">Minimum Garments</label>
                            <input type="number" class="form-control" id="bulk_min" name="min_garments" 
                                   min="1" required placeholder="e.g., 10">
                        </div>
                        <div class="col-md-6">
                            <label for="bulk_max" class="form-label">Maximum Garments</label>
                            <input type="number" class="form-control" id="bulk_max" name="max_garments" 
                                   min="1" required placeholder="e.g., 24">
                            <div class="form-text">Use 9999 for unlimited</div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="bulk_percent" class="form-label">Discount Percentage</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="bulk_percent" name="discount_percent" 
                                   step="0.01" min="0" max="100" required placeholder="e.g., 10.00">
                            <span class="input-group-text">%</span>
                        </div>
                        <div class="form-text">Discount applied to total order</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveBulk">Save Bulk Rule</button>
            </div>
        </div>
    </div>
</div>