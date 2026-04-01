<!DOCTYPE html>
<html>
<head>
    <title>Test Page</title>
</head>
<body>
    <h1>Testing Modal Existence</h1>
    
    <!-- Test modal -->
    <div class="modal fade" id="addShirtProductModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Test Modal</h5>
                </div>
                <div class="modal-body">
                    This is a test modal.
                </div>
            </div>
        </div>
    </div>
    
    <button id="test-button">Test Button</button>
    
    <script>
        console.log('Test page loaded');
        const modal = document.getElementById('addShirtProductModal');
        console.log('Modal element:', modal);
        
        if (modal) {
            console.log('Modal FOUND in test page!');
        } else {
            console.error('Modal NOT FOUND in test page!');
        }
        
        document.getElementById('test-button').addEventListener('click', function() {
            console.log('Button clicked, modal:', document.getElementById('addShirtProductModal'));
        });
    </script>
</body>
</html>