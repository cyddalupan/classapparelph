<!-- Add Combo Discount Modal -->
<div class="modal fade" id="addComboModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Combo Discount Rule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="comboForm">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="combo_size1" class="form-label">First Print Size</label>
                            <select class="form-control" id="combo_size1" name="size1_id" required>
                                <option value="">Select size</option>
                                @foreach($prices as $price)
                                <option value="{{ $price->id }}">{{ $price->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="combo_size2" class="form-label">Second Print Size</label>
                            <select class="form-control" id="combo_size2" name="size2_id" required>
                                <option value="">Select size</option>
                                @foreach($prices as $price)
                                <option value="{{ $price->id }}">{{ $price->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="combo_type" class="form-label">Discount Type</label>
                            <select class="form-control" id="combo_type" name="discount_type" required>
                                <option value="fixed">Fixed Amount (₱)</option>
                                <option value="percent">Percentage (%)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="combo_value" class="form-label">Discount Value</label>
                            <input type="number" class="form-control" id="combo_value" name="discount_value" 
                                   step="0.01" min="0" required placeholder="e.g., 30.00 or 10.00">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveCombo">Save Combo Rule</button>
            </div>
        </div>
    </div>
</div>