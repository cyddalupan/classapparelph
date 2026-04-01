<x-app-layout>
    @section('page-title', 'Inventory Style Test')
    
    <x-slot name="header">
        <div class="page-header-content">
            <h2 class="page-title">Inventory Style Test</h2>
        </div>
    </x-slot>

    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h1>🎯 INVENTORY STYLE JAVASCRIPT TEST</h1>
                        
                        <div id="test-box" style="border: 3px solid blue; padding: 20px; margin: 20px; text-align: center;">
                            <h3>Test Box</h3>
                            <p id="test-status">Waiting for JavaScript...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- USING INVENTORY ACTION STYLE --}}
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🎯 INVENTORY STYLE: JavaScript starting...');
            
            // Simple alert
            alert('🎯 INVENTORY STYLE: Alert works!');
            
            // Update page
            document.getElementById('test-status').textContent = '✅ JavaScript IS WORKING!';
            document.getElementById('test-status').style.color = 'green';
            
            // Get test box
            var testBox = document.getElementById('test-box');
            
            // Change border color
            testBox.style.border = '5px solid red';
            testBox.style.backgroundColor = '#ff000020';
            
            // Add hover effect
            testBox.onmouseover = function() {
                this.style.border = '5px solid green';
                this.style.backgroundColor = '#00ff0020';
                console.log('🎯 HOVER: Box turned green');
            };
            
            testBox.onmouseout = function() {
                this.style.border = '5px solid red';
                this.style.backgroundColor = '#ff000020';
                console.log('🎯 MOUSEOUT: Box turned red');
            };
            
            console.log('✅ INVENTORY STYLE: JavaScript completed!');
        });
    </script>
    @endpush
</x-app-layout>