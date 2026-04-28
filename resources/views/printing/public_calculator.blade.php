<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Printing Calculator - CLASS Apparel PH</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .calculator-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 30px;
            margin-top: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #2563eb;
            font-weight: 700;
        }
        .header p {
            color: #64748b;
            font-size: 1.1rem;
        }
        .print-option {
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            transition: all 0.3s;
            cursor: pointer;
        }
        .print-option:hover {
            border-color: #2563eb;
            background: #f0f9ff;
        }
        .print-option.selected {
            border-color: #2563eb;
            background: #dbeafe;
        }
        .price-tag {
            font-size: 1.2rem;
            font-weight: 600;
            color: #059669;
        }
        .discount-badge {
            background: #dcfce7;
            color: #059669;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        .total-box {
            background: #1e40af;
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-top: 30px;
        }
        .login-prompt {
            background: #fef3c7;
            border: 2px dashed #f59e0b;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="calculator-card">
                    <!-- Header -->
                    <div class="header">
                        <h1><i class="fas fa-calculator me-2"></i> Printing Price Calculator</h1>
                        <p>Calculate garment printing costs instantly. No login required for basic calculations.</p>
                    </div>

                    <!-- Print Size Selection -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h4 class="mb-3">Select Print Sizes:</h4>
                            <div class="row" id="print-options">
                                <!-- Options will be loaded by JavaScript -->
                            </div>
                        </div>
                    </div>

                    <!-- Calculation Results -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Order Summary</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tr>
                                            <td>Selected Prints:</td>
                                            <td class="text-end" id="selected-prints">None</td>
                                        </tr>
                                        <tr>
                                            <td>Base Price:</td>
                                            <td class="text-end" id="base-price">₱0.00</td>
                                        </tr>
                                        <tr>
                                            <td>Combo Discount:</td>
                                            <td class="text-end text-success" id="combo-discount">-₱0.00</td>
                                        </tr>
                                        <tr class="table-active">
                                            <td><strong>TOTAL:</strong></td>
                                            <td class="text-end"><strong id="total-price">₱0.00</strong></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0">Applied Discounts</h5>
                                </div>
                                <div class="card-body">
                                    <div id="applied-discounts">
                                        <p class="text-muted mb-0">No discounts applied yet</p>
                                    </div>
                                    <div class="mt-3">
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Discounts are applied automatically when eligible combinations are selected.
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Login Prompt -->
                    <div class="login-prompt">
                        <h5><i class="fas fa-lock me-2"></i> Want More Features?</h5>
                        <p class="mb-3">Login to access the full printing calculator with bulk discounts, size upgrades, and save quotes.</p>
                        <a href="https://app.classapparelph.com/login" class="btn btn-primary me-2">
                            <i class="fas fa-sign-in-alt me-1"></i> Login to Full Calculator
                        </a>
                        <a href="https://app.classapparelph.com/productpricing/printing" class="btn btn-outline-primary">
                            <i class="fas fa-external-link-alt me-1"></i> Go to Full Calculator
                        </a>
                        <div class="mt-2">
                            <small class="text-muted">Username: <code>Admin</code> | Password: <code>admin123</code></small>
                        </div>
                    </div>

                    <!-- Pricing Rules -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Current Pricing Rules</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6>Print Sizes:</h6>
                                            <ul class="list-group">
                                                <li class="list-group-item d-flex justify-content-between">
                                                    <span>Logo</span>
                                                    <span class="fw-bold">₱45.00</span>
                                                </li>
                                                <li class="list-group-item d-flex justify-content-between">
                                                    <span>Half A4</span>
                                                    <span class="fw-bold">₱60.00</span>
                                                </li>
                                                <li class="list-group-item d-flex justify-content-between">
                                                    <span>A4</span>
                                                    <span class="fw-bold">₱90.00</span>
                                                </li>
                                                <li class="list-group-item d-flex justify-content-between">
                                                    <span>A3</span>
                                                    <span class="fw-bold">₱130.00</span>
                                                </li>
                                                <li class="list-group-item d-flex justify-content-between">
                                                    <span>A2</span>
                                                    <span class="fw-bold">₱200.00</span>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <h6>Combo Discounts:</h6>
                                            <ul class="list-group">
                                                <li class="list-group-item">
                                                    <span class="text-success fw-bold">Logo + A3</span>
                                                    <span class="float-end text-success">-₱30.00</span>
                                                </li>
                                                <li class="list-group-item">
                                                    <span class="text-success fw-bold">Logo + A4</span>
                                                    <span class="float-end text-success">-₱20.00</span>
                                                </li>
                                                <li class="list-group-item">
                                                    <span class="text-success fw-bold">Half A4 + A3</span>
                                                    <span class="float-end text-success">-₱25.00</span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Print sizes with prices
        const printSizes = [
            { id: 1, name: 'Logo', price: 45.00 },
            { id: 2, name: 'Half A4', price: 60.00 },
            { id: 3, name: 'A4', price: 90.00 },
            { id: 4, name: 'A3', price: 130.00 },
            { id: 5, name: 'A2', price: 200.00 }
        ];

        // Combo discounts
        const comboDiscounts = [
            { sizes: ['Logo', 'A3'], discount: 30.00 },
            { sizes: ['Logo', 'A4'], discount: 20.00 },
            { sizes: ['Half A4', 'A3'], discount: 25.00 }
        ];

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            // Render print options
            const container = document.getElementById('print-options');
            printSizes.forEach(size => {
                const col = document.createElement('div');
                col.className = 'col-md-4 mb-3';
                col.innerHTML = `
                    <div class="print-option" data-id="${size.id}" data-name="${size.name}" data-price="${size.price}">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="size_${size.id}">
                            <label class="form-check-label w-100" for="size_${size.id}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold">${size.name}</span>
                                    <span class="price-tag">₱${size.price.toFixed(2)}</span>
                                </div>
                            </label>
                        </div>
                    </div>
                `;
                container.appendChild(col);
            });

            // Add event listeners to checkboxes
            document.querySelectorAll('.print-option input[type="checkbox"]').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const option = this.closest('.print-option');
                    if (this.checked) {
                        option.classList.add('selected');
                    } else {
                        option.classList.remove('selected');
                    }
                    calculateTotal();
                });
            });
        });

        // Calculate total
        function calculateTotal() {
            let basePrice = 0;
            let selectedSizes = [];
            let selectedNames = [];
            
            // Get selected sizes
            document.querySelectorAll('.print-option input[type="checkbox"]:checked').forEach(checkbox => {
                const option = checkbox.closest('.print-option');
                const price = parseFloat(option.dataset.price);
                const name = option.dataset.name;
                basePrice += price;
                selectedSizes.push(option.dataset.id);
                selectedNames.push(name);
            });
            
            // Update selected prints display
            const selectedPrintsEl = document.getElementById('selected-prints');
            if (selectedNames.length > 0) {
                selectedPrintsEl.textContent = selectedNames.join(', ');
            } else {
                selectedPrintsEl.textContent = 'None';
            }
            
            // Calculate combo discount
            let comboDiscount = 0;
            let appliedDiscounts = [];
            
            comboDiscounts.forEach(combo => {
                const hasSize1 = selectedNames.includes(combo.sizes[0]);
                const hasSize2 = selectedNames.includes(combo.sizes[1]);
                
                if (hasSize1 && hasSize2) {
                    comboDiscount += combo.discount;
                    appliedDiscounts.push(`${combo.sizes[0]} + ${combo.sizes[1]}: ₱${combo.discount.toFixed(2)} discount`);
                }
            });
            
            // Calculate total
            const totalPrice = basePrice - comboDiscount;
            
            // Update display
            document.getElementById('base-price').textContent = '₱' + basePrice.toFixed(2);
            document.getElementById('combo-discount').textContent = '-₱' + comboDiscount.toFixed(2);
            document.getElementById('total-price').textContent = '₱' + totalPrice.toFixed(2);
            
            // Update applied discounts
            const discountsEl = document.getElementById('applied-discounts');
            if (appliedDiscounts.length > 0) {
                discountsEl.innerHTML = appliedDiscounts.map(d => 
                    `<div class="mb-1"><i class="fas fa-check-circle text-success me-1"></i> ${d}</div>`
                ).join('');
            } else {
                discountsEl.innerHTML = '<p class="text-muted mb-0">No discounts applied yet</p>';
            }
        }
    </script>
</body>
</html>