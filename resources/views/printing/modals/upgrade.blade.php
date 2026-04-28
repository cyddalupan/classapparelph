<!-- Add Size Upgrade Modal -->
<div class="modal fade" id="addUpgradeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Size Upgrade Rule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="upgradeForm">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="upgrade_from_size" class="form-label">Upgrade From</label>
                            <select class="form-control" id="upgrade_from_size" name="from_size_id" required>
                                <option value="">Select size</option>
                                @foreach($prices as $price)
                                <option value="{{ $price->id }}">{{ $price->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="upgrade_from_qty" class="form-label">Quantity Required</label>
                            <input type="number" class="form-control" id="upgrade_from_qty" name="from_quantity" 
                                   min="2" value="2" required placeholder="e.g., 2">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="upgrade_to_size" class="form-label">Upgrade To</label>
                            <select class="form-control" id="upgrade_to_size" name="to_size_id" required>
                                <option value="">Select size</option>
                                @foreach($prices as $price)
                                <option value="{{ $price->id }}">{{ $price->name }}</option>
                                @endforeach
                            </select>
                            <div class="form-text">Example: 2x Logo upgrades to 1x Half A4</div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="upgrade_auto" name="auto_apply" value="1" checked>
                            <label class="form-check-label" for="upgrade_auto">
                                Apply upgrade automatically
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveUpgrade">Save Upgrade Rule</button>
            </div>
        </div>
    </div>
</div>