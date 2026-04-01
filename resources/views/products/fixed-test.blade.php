<x-app-layout>
    @section('page-title', 'Fixed JavaScript Test')
    
    <x-slot name="header">
        <div class="page-header-content">
            <h2 class="page-title">Fixed JavaScript Test</h2>
        </div>
    </x-slot>

    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h1>🎯 FIXED JAVASCRIPT TEST</h1>
                        
                        <div id="test-box" style="border: 3px solid blue; padding: 20px; margin: 20px; text-align: center;">
                            <h3>Test Box</h3>
                            <p id="test-status">Waiting for JavaScript...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- FIXED JAVASCRIPT WITH ERROR HANDLING --}}
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🎯 FIXED TEST: JavaScript starting...');
            
            // Simple alert
            alert('🎯 FIXED TEST: Alert works!');
            
            // SAFE UPDATE: Check if element exists
            var testStatus = document.getElementById('test-status');
            if (testStatus) {
                testStatus.textContent = '✅ JavaScript IS WORKING!';
                testStatus.style.color = 'green';
                console.log('✅ Updated test-status element');
            } else {
                console.error('❌ ERROR: test-status element not found!');
                // Create it if missing
                var newStatus = document.createElement('p');
                newStatus.id = 'test-status';
                newStatus.textContent = '✅ JavaScript CREATED THIS!';
                newStatus.style.color = 'green';
                document.getElementById('test-box').appendChild(newStatus);
            }
            
            // Get test box
            var testBox = document.getElementById('test-box');
            if (!testBox) {
                console.error('❌ ERROR: test-box element not found!');
                return; // Stop if main element missing
            }
            
            // Change border color
            testBox.style.border = '5px solid red';
            testBox.style.backgroundColor = '#ff000020';
            console.log('✅ Changed box to red');
            
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
            
            console.log('✅ FIXED TEST: JavaScript completed!');
        });
    </script>
    @endpush
</x-app-layout>