<?php
// Debug endpoint to test expense validation
require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Only handle our debug endpoint
if ($_SERVER['REQUEST_URI'] === '/test-debug-endpoint') {
    header('Content-Type: application/json');
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = $_POST;
        
        echo json_encode([
            'received_data' => $data,
            'validation_test' => [
                'date_is_string' => is_string($data['date'] ?? null),
                'date_value' => $data['date'] ?? null,
                'date_regex_match' => preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['date'] ?? ''),
                'amount_is_numeric' => is_numeric($data['amount'] ?? null),
                'amount_value' => $data['amount'] ?? null,
                'csrf_token_present' => isset($data['_token']),
                'all_fields' => array_keys($data)
            ],
            'server_info' => [
                'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'not set',
                'request_method' => $_SERVER['REQUEST_METHOD'],
                'php_version' => PHP_VERSION
            ]
        ], JSON_PRETTY_PRINT);
        exit;
    }
    
    // GET request - show form
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Debug Expense Validation</title>
        <style>
            body { font-family: sans-serif; padding: 2rem; }
            .form-group { margin: 1rem 0; }
            label { display: block; margin-bottom: 0.5rem; }
            input, select { padding: 0.5rem; width: 300px; }
            button { padding: 0.75rem 1.5rem; background: #007bff; color: white; border: none; cursor: pointer; }
            pre { background: #f8f9fa; padding: 1rem; border-radius: 4px; overflow: auto; }
        </style>
    </head>
    <body>
        <h1>Debug Expense Validation</h1>
        <p>Test what data is being sent to the server:</p>
        
        <form method="POST" id="debugForm">
            <div class="form-group">
                <label>Date (YYYY-MM-DD):</label>
                <input type="date" name="date" value="2026-02-28" required>
            </div>
            
            <div class="form-group">
                <label>Amount:</label>
                <input type="number" step="0.01" name="amount" value="1500.75" required>
            </div>
            
            <div class="form-group">
                <label>Category:</label>
                <select name="category" required>
                    <option value="software">Software</option>
                    <option value="hardware">Hardware</option>
                    <option value="marketing">Marketing</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Description:</label>
                <input type="text" name="description" value="Test expense" required>
            </div>
            
            <div class="form-group">
                <label>Status:</label>
                <select name="status" required>
                    <option value="pending">Pending</option>
                    <option value="paid">Paid</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>CSRF Token (get from /finance page):</label>
                <input type="text" name="_token" placeholder="Paste CSRF token here" style="width: 500px;">
                <small>Open https://app.classapparelph.com/finance, view page source, find meta[name="csrf-token"]</small>
            </div>
            
            <button type="submit">Test Submit</button>
        </form>
        
        <div id="result" style="margin-top: 2rem;"></div>
        
        <script>
            document.getElementById('debugForm').addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const resultDiv = document.getElementById('result');
                
                resultDiv.innerHTML = '<p>Submitting...</p>';
                
                try {
                    const response = await fetch('/test-debug-endpoint', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const data = await response.json();
                    resultDiv.innerHTML = '<h3>Response:</h3><pre>' + JSON.stringify(data, null, 2) + '</pre>';
                } catch (error) {
                    resultDiv.innerHTML = '<p style="color: red;">Error: ' + error.message + '</p>';
                }
            });
        </script>
    </body>
    </html>
    <?php
    exit;
}

$kernel->terminate($request, $response);