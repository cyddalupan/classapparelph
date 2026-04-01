<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Navigation Test - Class Apparel PH</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .test-result { background: #f0f0f0; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .info { background: #d1ecf1; color: #0c5460; }
    </style>
</head>
<body>
    <h1>Navigation Test Page</h1>
    
    <div class="test-result info">
        <h3>Page Information:</h3>
        <p><strong>URL:</strong> {{ url()->current() }}</p>
        <p><strong>Route Name:</strong> {{ Route::currentRouteName() ?? 'N/A' }}</p>
        <p><strong>Timestamp:</strong> {{ now() }}</p>
    </div>
    
    <div class="test-result">
        <h3>Test Links:</h3>
        <p><a href="{{ route('inventory.select-category') }}">Link to: {{ route('inventory.select-category') }}</a></p>
        <p><a href="{{ route('inventory.create') }}">Link to: {{ route('inventory.create') }}</a></p>
        <p><a href="{{ route('inventory.index') }}">Link to: {{ route('inventory.index') }}</a></p>
    </div>
    
    <div class="test-result">
        <h3>What to do:</h3>
        <ol>
            <li>Click the first link above (inventory.select-category)</li>
            <li>Should go to 4-category selection page</li>
            <li>If not, check browser console for errors</li>
            <li>Report back what happens</li>
        </ol>
    </div>
    
    <script>
        console.log('Navigation Test Page Loaded');
        console.log('Current URL:', window.location.href);
        console.log('Route should be: test-navigation');
        
        // Check if links are correct
        document.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', function(e) {
                console.log('Clicked link:', this.href);
            });
        });
    </script>
</body>
</html>