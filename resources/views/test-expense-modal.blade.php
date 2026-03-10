<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Modal Test</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f8fafc;
            margin: 0;
            padding: 2rem;
        }
        
        .test-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
        }
        
        h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 1.5rem;
        }
        
        .test-section {
            margin-bottom: 2rem;
            padding: 1.5rem;
            border-radius: 12px;
            background: #f8fafc;
        }
        
        .test-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .test-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .test-item {
            background: white;
            padding: 1rem;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }
        
        .test-label {
            font-size: 0.875rem;
            color: #64748b;
            margin-bottom: 0.5rem;
        }
        
        .test-value {
            font-weight: 600;
            color: #1e293b;
        }
        
        .status-badges {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .btn-test {
            background: linear-gradient(135deg, #2563eb, #7c3aed);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-test:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(37, 99, 235, 0.25);
        }
        
        .notes {
            background: #f0f9ff;
            border-left: 4px solid #0ea5e9;
            padding: 1rem;
            border-radius: 8px;
            font-size: 0.875rem;
            color: #0369a1;
            margin-top: 1.5rem;
        }
        
        .notes ul {
            margin: 0.5rem 0;
            padding-left: 1.5rem;
        }
        
        .notes li {
            margin-bottom: 0.25rem;
        }
    </style>
</head>
<body>
    <div class="test-container">
        <h1>Expense Modal Functionality Test</h1>
        
        <div class="test-section">
            <div class="test-title">
                <i class="fas fa-check-circle" style="color: #10b981;"></i>
                ✅ What's Now Working:
            </div>
            
            <div class="test-grid">
                <div class="test-item">
                    <div class="test-label">Modal Opening</div>
                    <div class="test-value">Click "Add Expense" opens modal</div>
                </div>
                
                <div class="test-item">
                    <div class="test-label">Form Validation</div>
                    <div class="test-value">Required fields validated</div>
                </div>
                
                <div class="test-item">
                    <div class="test-label">Form Submission</div>
                    <div class="test-value">Adds expense to table</div>
                </div>
                
                <div class="test-item">
                    <div class="test-label">Notifications</div>
                    <div class="test-value">Success/error messages</div>
                </div>
                
                <div class="test-item">
                    <div class="test-label">Table Updates</div>
                    <div class="test-value">New row added to top</div>
                </div>
                
                <div class="test-item">
                    <div class="test-label">Summary Updates</div>
                    <div class="test-value">Total expenses updated</div>
                </div>
            </div>
            
            <button class="btn-test" onclick="window.open('https://app.classapparelph.com/finance', '_blank')">
                <i class="fas fa-external-link-alt"></i>
                Test Live on Finance Page
            </button>
        </div>
        
        <div class="test-section">
            <div class="test-title">
                <i class="fas fa-tag"></i>
                Status Badges (Now Fixed):
            </div>
            
            <div class="status-badges">
                <span class="status pending">Pending</span>
                <span class="status paid">Paid</span>
                <span class="status overdue">Overdue</span>
            </div>
        </div>
        
        <div class="test-section">
            <div class="test-title">
                <i class="fas fa-money-bill-wave"></i>
                Amount Styling:
            </div>
            
            <div style="display: flex; gap: 2rem; align-items: center;">
                <div>
                    <div class="test-label">Negative Amount (Expense)</div>
                    <div class="amount negative" style="font-size: 1.25rem;">₱ 8,750</div>
                </div>
                
                <div>
                    <div class="test-label">Positive Amount (Income)</div>
                    <div class="amount positive" style="font-size: 1.25rem;">₱ 12,500</div>
                </div>
            </div>
        </div>
        
        <div class="notes">
            <strong>Fixed Issues:</strong>
            <ul>
                <li><strong>Missing Status CSS:</strong> Added `.status.pending`, `.status.paid`, `.status.overdue` classes</li>
                <li><strong>Amount Styling:</strong> Added `.amount.negative` and `.amount.positive` classes</li>
                <li><strong>Form Validation:</strong> All required fields now properly validated</li>
                <li><strong>Currency Format:</strong> PHP currency formatting with proper symbol (₱)</li>
                <li><strong>Modal Functionality:</strong> Open/close, overlay click, Escape key support</li>
                <li><strong>Notification System:</strong> Toast notifications for user feedback</li>
            </ul>
            <p><strong>Test Instructions:</strong> 
                1. Go to <a href="https://app.classapparelph.com/finance" target="_blank">Finance Page</a><br>
                2. Click "Add Expense" button<br>
                3. Fill out the form and submit<br>
                4. Verify new expense appears in table<br>
                5. Check notifications appear<br>
                6. Test row actions (View, Edit, Mark as Paid)
            </p>
        </div>
        
        <div style="text-align: center; margin-top: 2rem; padding-top: 1rem; border-top: 1px solid #e2e8f0;">
            <a href="https://app.classapparelph.com/login" style="display: inline-block; background: linear-gradient(135deg, #2563eb, #7c3aed); color: white; padding: 0.75rem 1.5rem; border-radius: 10px; text-decoration: none; font-weight: 600; transition: all 0.2s;">
                <i class="fas fa-sign-in-alt"></i> Login to Test →
            </a>
        </div>
    </div>
    
    <script>
        // Add Font Awesome if not already loaded
        if (!document.querySelector('link[href*="font-awesome"]')) {
            const faLink = document.createElement('link');
            faLink.rel = 'stylesheet';
            faLink.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css';
            document.head.appendChild(faLink);
        }
        
        // Add status CSS if not already in page
        if (!document.querySelector('style[data-status-css]')) {
            const statusCSS = document.createElement('style');
            statusCSS.setAttribute('data-status-css', 'true');
            statusCSS.textContent = `
                .status {
                    padding: 0.25rem 0.75rem;
                    border-radius: 6px;
                    font-size: 0.75rem;
                    font-weight: 600;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                }
                .status.pending {
                    background: rgba(245, 158, 11, 0.1);
                    color: #f59e0b;
                    border: 1px solid rgba(245, 158, 11, 0.2);
                }
                .status.paid {
                    background: rgba(16, 185, 129, 0.1);
                    color: #10b981;
                    border: 1px solid rgba(16, 185, 129, 0.2);
                }
                .status.overdue {
                    background: rgba(239, 68, 68, 0.1);
                    color: #ef4444;
                    border: 1px solid rgba(239, 68, 68, 0.2);
                }
                .amount {
                    font-weight: 600;
                    font-family: 'Inter', sans-serif;
                }
                .amount.negative {
                    color: #ef4444;
                }
                .amount.positive {
                    color: #10b981;
                }
            `;
            document.head.appendChild(statusCSS);
        }
    </script>
</body>
</html>