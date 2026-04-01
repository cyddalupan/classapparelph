<x-app-layout>
    @section('page-title', 'Product List - FRESH START')
    
    <x-slot name="header">
        <div class="page-header-content">
            <h2 class="page-title">Product List</h2>
            <div class="page-header-actions">
                <!-- Empty for now -->
            </div>
        </div>
    </x-slot>

    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">🎯 FRESH START - Product List</h5>
                    </div>
                    <div class="card-body">
                        <h1>✅ STEP 1: BASIC PAGE LOADED</h1>
                        <p>If you can see this text, the page is loading correctly.</p>
                        
                        <div id="test-box" style="border: 3px solid blue; padding: 20px; margin: 20px; text-align: center;">
                            <h3>🎯 TEST BOX</h3>
                            <p>This box should have a blue border.</p>
                            <p id="test-status">Waiting for JavaScript...</p>
                        </div>
                        
                        <div class="alert alert-info">
                            <h4>🎯 TEST INSTRUCTIONS:</h4>
                            <ol>
                                <li>You should see an alert popup immediately</li>
                                <li>The box above should turn RED after alert</li>
                                <li>Check browser console (F12) for logs</li>
                                <li>Hover over the box - should turn GREEN</li>
                                <li>Click the box - should turn ORANGE</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- SIMPLEST POSSIBLE JAVASCRIPT --}}
    <script>
        console.log('🎯 FRESH START: JavaScript starting...');
        
        // STEP 1: SIMPLE ALERT
        alert('🎯 FRESH START: If you see this, JavaScript WORKS!');
        
        // STEP 2: UPDATE PAGE
        document.getElementById('test-status').textContent = '✅ JavaScript IS WORKING!';
        document.getElementById('test-status').style.color = 'green';
        document.getElementById('test-status').style.fontWeight = 'bold';
        
        // STEP 3: GET TEST BOX
        var testBox = document.getElementById('test-box');
        
        // STEP 4: CHANGE BORDER COLOR
        testBox.style.border = '5px solid red';
        testBox.style.backgroundColor = '#ff000020';
        
        // STEP 5: ADD HOVER EFFECT
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
        
        // STEP 6: ADD CLICK EFFECT
        testBox.onclick = function() {
            this.style.border = '5px solid orange';
            this.style.backgroundColor = '#ffa50020';
            console.log('🎯 CLICK: Box turned orange');
        };
        
        console.log('✅ FRESH START: JavaScript completed successfully!');
    </script>
</x-app-layout>