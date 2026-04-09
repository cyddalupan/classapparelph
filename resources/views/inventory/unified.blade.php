                            <td>${statusBadge}</td>
                            <td>
                                <button class="btn btn-sm btn-warning edit-item-btn me-1" data-id="${item.id}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-info adjust-stock-btn me-1" data-id="${item.id}" data-name="${item.name || 'Item'}" data-stock="${item.current_stock || 0}">
                                    <i class="fas fa-exchange-alt"></i>
                                </button>
                                <button class="btn btn-sm btn-danger delete-item-btn" data-id="${item.id}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        `;
                        
                        document.getElementById('inventory-table-body').appendChild(row);
                    });
                    
                    // Show table
                    document.getElementById('inventory-table-wrapper').style.display = 'block';
                    
                    // Add event listeners to action buttons
                    document.querySelectorAll('.edit-item-btn').forEach(btn => {
                        btn.addEventListener('click', function() {
                            const itemId = this.getAttribute('data-id');
                            openEditModal(itemId);
                        });
                    });
                    
                    document.querySelectorAll('.adjust-stock-btn').forEach(btn => {
                        btn.addEventListener('click', function() {
                            const itemId = this.getAttribute('data-id');
                            const itemName = this.getAttribute('data-name');
                            const currentStock = this.getAttribute('data-stock');
                            openAdjustStockModal(itemId, itemName, currentStock);
                        });
                    });
                    
                    document.querySelectorAll('.delete-item-btn').forEach(btn => {
                        btn.addEventListener('click', function() {
                            const itemId = this.getAttribute('data-id');
                            if (confirm('Are you sure you want to delete this item?')) {
                                deleteInventoryItem(itemId);
                            }
                        });
                    });
                }
            })
            .catch(error => {
                console.error('Error loading inventory items:', error);
                document.getElementById('loading-inventory').style.display = 'none';
                document.getElementById('no-inventory-items').innerHTML = `
                    <i class="fas fa-exclamation-triangle fa-3x mb-3 text-danger"></i>
                    <h5>Error Loading Items</h5>
                    <p class="text-muted">Failed to load inventory items: ${error.message}</p>
                `;
                document.getElementById('no-inventory-items').style.display = 'block';
            });
    }
    
    // Add item from browse button
    document.getElementById('add-item-from-browse').addEventListener('click', function() {
        if (currentCategory) {
            // Switch to create tab and pre-select category
            const createTab = new bootstrap.Tab(document.getElementById('create-tab'));
            createTab.show();
            
            // Find and click the corresponding category in create tab
            const categoryBox = document.querySelector(`.category-select-box[data-category="${currentCategory}"]`);
            if (categoryBox) {
                categoryBox.click();
            }
        } else {
            showError('Please select a category first');
        }
    });
    
    // Add first item button
    document.getElementById('add-first-item').addEventListener('click', function() {
        if (currentCategory) {
            const createTab = new bootstrap.Tab(document.getElementById('create-tab'));
            createTab.show();
            
            const categoryBox = document.querySelector(`.category-select-box[data-category="${currentCategory}"]`);
            if (categoryBox) {
                categoryBox.click();
            }
        }
    });
    
    // ========== CREATE TAB FUNCTIONALITY ==========
    
    // Category selection for create
    document.querySelectorAll('.category-select-box').forEach(box => {
        box.addEventListener('click', function() {
            // Remove selected class from all boxes
            document.querySelectorAll('.category-select-box').forEach(b => {
                b.classList.remove('selected');
                b.style.borderColor = '';
            });
            
            // Add selected class to clicked box
            this.classList.add('selected');
            this.style.borderColor = '#06d6a0';
            
            // Get category
            const category = this.getAttribute('data-category');
            document.getElementById('selected-category-name').textContent = category;
            document.getElementById('generic-category').value = category;
            
            // Show create form area
            document.getElementById('create-form-area').style.display = 'block';
            document.getElementById('form-not-available').style.display = 'none';
            
            // Show appropriate form
            if (category === 'Shirt Products') {
                document.getElementById('shirt-product-form').style.display = 'block';
                document.getElementById('generic-product-form').style.display = 'none';
            } else {
                document.getElementById('shirt-product-form').style.display = 'none';
                document.getElementById('generic-product-form').style.display = 'block';
                document.getElementById('form-not-available').style.display = 'block';
            }
        });
    });
    
    // Shirt product form submission
    document.getElementById('shirt-product-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('/inventory/shirt-products', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccess('Shirt product added successfully!');
                this.reset();
                
                // Reload inventory items if we're in the same category
                if (currentCategory === 'Shirt Products') {
                    loadInventoryItems(currentCategory);
                }
            } else {
                showError(data.message || 'Failed to add shirt product');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Failed to add shirt product');
        });
    });
    
    // Generic product form submission
    document.getElementById('generic-product-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('/inventory', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccess('Product added successfully!');
                this.reset();
                
                // Reload inventory items if we're in the same category
                if (currentCategory === formData.get('category')) {
                    loadInventoryItems(currentCategory);
                }
            } else {
                showError(data.message || 'Failed to add product');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Failed to add product');
        });
    });
    
    // Cancel create buttons
    document.getElementById('cancel-create').addEventListener('click', function() {
        document.getElementById('shirt-product-form').reset();
        document.getElementById('shirt-product-form').style.display = 'none';
        document.getElementById('create-form-area').style.display = 'none';
        
        // Reset category selection
        document.querySelectorAll('.category-select-box').forEach(b => {
            b.classList.remove('selected');
            b.style.borderColor = '';
        });
    });
    
    document.getElementById('cancel-generic-create').addEventListener('click', function() {
        document.getElementById('generic-product-form').reset();
        document.getElementById('generic-product-form').style.display = 'none';
        document.getElementById('create-form-area').style.display = 'none';
        
        // Reset category selection
        document.querySelectorAll('.category-select-box').forEach(b => {
            b.classList.remove('selected');
            b.style.borderColor = '';
        });
    });
    
    // ========== EDIT FUNCTIONALITY ==========
    
    function openEditModal(itemId) {
        // Find the item in inventoryItems
        const item = inventoryItems.find(i => i.id == itemId);
        if (!item) {
            showError('Item not found');
            return;
        }
        
        // Populate form
        document.getElementById('edit-item-id').value = item.id;
        document.getElementById('edit-sku').value = item.sku || '';
        document.getElementById('edit-name').value = item.name || '';
        document.getElementById('edit-category').value = item.category || '';
        document.getElementById('edit-type').value = item.type || 'finished_good';
        document.getElementById('edit-unit-price').value = item.unit_price || 0;
        document.getElementById('edit-current-stock').value = item.current_stock || 0;
        document.getElementById('edit-minimum-stock').value = item.minimum_stock || 0;
        document.getElementById('edit-unit-of-measure').value = item.unit_of_measure || 'pieces';
        document.getElementById('edit-description').value = item.description || '';
        document.getElementById('edit-is-active').checked = item.is_active == 1;
        
        // Switch to edit tab
        const editTab = new bootstrap.Tab(document.getElementById('edit-tab'));
        editTab.show();
        
        // Show modal
        editModal.show();
    }
    
    // Save edit button
    document.getElementById('save-edit-btn').addEventListener('click', function() {
        const form = document.getElementById('edit-item-form');
        const formData = new FormData(form);
        const itemId = document.getElementById('edit-item-id').value;
        
        fetch(`/inventory/${itemId}`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'X-HTTP-Method-Override': 'PUT'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccess('Item updated successfully!');
                editModal.hide();
                
                // Reload inventory items
                if (currentCategory) {
                    loadInventoryItems(currentCategory);
                }
            } else {
                showError(data.message || 'Failed to update item');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Failed to update item');
        });
    });
    
    // Delete item button
    document.getElementById('delete-item-btn').addEventListener('click', function() {
        const itemId = document.getElementById('edit-item-id').value;
        
        if (confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
            fetch(`/inventory/${itemId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'X-HTTP-Method-Override': 'DELETE'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccess('Item deleted successfully!');
                    editModal.hide();
                    
                    // Reload inventory items
                    if (currentCategory) {
                        loadInventoryItems(currentCategory);
                    }
                } else {
                    showError(data.message || 'Failed to delete item');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('Failed to delete item');
            });
        }
    });
    
    // ========== STOCK ADJUSTMENT FUNCTIONALITY ==========
    
    function openAdjustStockModal(itemId, itemName, currentStock) {
        document.getElementById('adjust-item-id').value = itemId;
        document.getElementById('adjust-item-name').value = itemName;
        document.getElementById('adjust-current-stock').value = currentStock;
        document.getElementById('adjust-display-stock').value = currentStock;
        document.getElementById('adjust-quantity').value = '';
        
        adjustModal.show();
    }
    
    // Adjustment type change
    document.getElementById('adjustment-type').addEventListener('change', function() {
        const type = this.value;
        const instruction = document.getElementById('adjust-instruction');
        
        switch(type) {
            case 'add':
                instruction.textContent = 'Enter quantity to add';
                break;
            case 'deduct':
                instruction.textContent = 'Enter quantity to deduct';
                break;
            case 'set':
                instruction.textContent = 'Enter new stock value';
                break;
        }
    });
    
    // Apply adjustment button
    document.getElementById('apply-adjustment-btn').addEventListener('click', function() {
        const form = document.getElementById('adjust-stock-form');
        const formData = new FormData(form);
        const itemId = document.getElementById('adjust-item-id').value;
        const adjustmentType = document.getElementById('adjustment-type').value;
        const quantity = parseFloat(document.getElementById('adjust-quantity').value);
        const currentStock = parseFloat(document.getElementById('adjust-current-stock').value);
        
        if (isNaN(quantity) || quantity < 0) {
            showError('Please enter a valid quantity');
            return;
        }
        
        let newStock = currentStock;
        switch(adjustmentType) {
            case 'add':
                newStock = currentStock + quantity;
                break;
            case 'deduct':
                if (quantity > currentStock) {
                    showError('Cannot deduct more than current stock');
                    return;
                }
                newStock = currentStock - quantity;
                break;
            case 'set':
                newStock = quantity;
                break;
        }
        
        // Update stock via API
        fetch(`/products/${itemId}/update-stock`, {
            method: 'POST',
            body: JSON.stringify({
                current_stock: newStock,
                reason: formData.get('reason')
            }),
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccess(`Stock updated successfully! New stock: ${newStock}`);
                adjustModal.hide();
                
                // Reload inventory items
                if (currentCategory) {
                    loadInventoryItems(currentCategory);
                }
            } else {
                showError(data.message || 'Failed to update stock');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Failed to update stock');
        });
    });
    
    // ========== REPORTS FUNCTIONALITY ==========
    
    // Load report data
    function loadReportData() {
        // Load total items count
        fetch('/api/products-by-category?category=')
            .then(response => response.json())
            .then(items => {
                document.getElementById('total-items-count').textContent = items.length;
                
                // Count low stock items
                const lowStockCount = items.filter(item => {
                    const current = parseFloat(item.current_stock) || 0;
                    const minimum = parseFloat(item.minimum_stock) || 5;
                    return current <= minimum;
                }).length;
                
                document.getElementById('low-stock-count').textContent = lowStockCount;
            })
            .catch(error => {
                console.error('Error loading report data:', error);
            });
    }
    
    // Generate stock report
    document.getElementById('generate-stock-report').addEventListener('click', function() {
        showSuccess('Stock report generation started. This feature is under development.');
    });
    
    // View low stock
    document.getElementById('view-low-stock').addEventListener('click', function() {
        // Switch to browse tab and filter low stock
        const browseTab = new bootstrap.Tab(document.getElementById('browse-tab'));
        browseTab.show();
        showInfo('Low stock filter will be implemented in the next update.');
    });
    
    // Custom report form
    document.getElementById('custom-report-form').addEventListener('submit', function(e) {
        e.preventDefault();
        showSuccess('Custom report generation started. This feature is under development.');
    });
    
    // ========== UTILITY FUNCTIONS ==========
    
    function deleteInventoryItem(itemId) {
        fetch(`/inventory/${itemId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'X-HTTP-Method-Override': 'DELETE'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccess('Item deleted successfully!');
                
                // Reload inventory items
                if (currentCategory) {
                    loadInventoryItems(currentCategory);
                }
            } else {
                showError(data.message || 'Failed to delete item');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Failed to delete item');
        });
    }
    
    function showSuccess(message) {
        document.getElementById('success-message').textContent = message;
        successToast.show();
    }
    
    function showError(message) {
        document.getElementById('error-message').textContent = message;
        errorToast.show();
    }
    
    function showInfo(message) {
        alert(message);
    }
    
    // ========== INITIALIZATION ==========
    
    // Check URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const urlCategory = urlParams.get('category');
    const urlTab = urlParams.get('tab');
    
    // Set initial tab
    if (urlTab) {
        const tabElement = document.getElementById(`${urlTab}-tab`);
        if (tabElement) {
            const tab = new bootstrap.Tab(tabElement);
            tab.show();
            currentTab = urlTab;
        }
    }
    
    // Auto-select category from URL
    if (urlCategory) {
        // Find and click the corresponding category box
        const targetBox = document.querySelector(`.category-box[data-category="${urlCategory}"]`);
        if (targetBox) {
            setTimeout(() => {
                targetBox.click();
            }, 500);
        }
    }
    
    // Load initial report data
    loadReportData();
    
    // Refresh button
    document.getElementById('refresh-inventory-btn').addEventListener('click', function() {
        if (currentCategory) {
            loadInventoryItems(currentCategory);
            showSuccess('Inventory refreshed');
        } else {
            showInfo('Please select a category first');
        }
    });
    
    // Export button
    document.getElementById('export-inventory-btn').addEventListener('click', function() {
        showSuccess('Export feature will be available in the next update');
    });
    
    // Print button
    document.getElementById('print-inventory').addEventListener('click', function() {
        window.print();
    });
    
    // Tab change tracking
    document.getElementById('inventoryTabs').addEventListener('shown.bs.tab', function(event) {
        const activeTab = event.target.getAttribute('id');
        currentTab = activeTab.replace('-tab', '');
        
        // Update URL without reloading
        const url = new URL(window.location);
        url.searchParams.set('tab', currentTab);
        window.history.replaceState({}, '', url);
        
        // Load report data when reports tab is shown
        if (currentTab === 'reports') {
            loadReportData();
        }
    });
    
    console.log('Unified Inventory Management initialized successfully');
});
</script>

<style>
.category-box, .category-select-box {
    cursor: pointer;
    transition: all 0.3s ease;
}

.category-box:hover, .category-select-box:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.category-box.selected {
    border-color: #3a86ff !important;
    background-color: rgba(58, 134, 255, 0.05);
}

.category-select-box.selected {
    border-color: #06d6a0 !important;
    background-color: rgba(6, 214, 160, 0.05);
}

.category-icon {
    transition: transform 0.3s ease;
}

.category-box:hover .category-icon, .category-select-box:hover .category-icon {
    transform: scale(1.1);
}

.table th {
    font-weight: 600;
    background-color: #f8f9fa;
}

.badge {
    font-weight: 500;
    padding: 0.35em 0.65em;
}

.toast {
    min-width: 300px;
}
</style>
@endsection