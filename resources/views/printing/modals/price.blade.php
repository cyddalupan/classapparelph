<!-- Add/Edit Price Modal -->
<div class="modal fade" id="addPriceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Print Size Price</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="priceForm">
                    <input type="hidden" id="price_id" name="id">
                    <div class="mb-3">
                        <label for="price_name" class="form-label">Print Size Name</label>
                        <input type="text" class="form-control" id="price_name" name="name" required 
                               placeholder="e.g., Logo, Half A4, A4, A3, A2">
                    </div>
                    <div class="mb-3">
                        <label for="price_amount" class="form-label">Price (₱)</label>
                        <input type="number" class="form-control" id="price_amount" name="price" 
                               step="0.01" min="0" required placeholder="e.g., 45.00">
                    </div>
                    <div class="mb-3">
                        <label for="price_order" class="form-label">Display Order</label>
                        <input type="number" class="form-control" id="price_order" name="order" 
                               min="0" value="0" placeholder="Lower numbers show first">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="savePrice">Save Price</button>
            </div>
        </div>
    </div>
</div>