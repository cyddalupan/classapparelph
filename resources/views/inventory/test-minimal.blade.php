<x-app-layout>
    @section('page-title', 'TEST MINIMAL')
    
    <div class="container-fluid py-4">
        <h1>MINIMAL TEST PAGE</h1>
        
        <!-- TEST AT TOP -->
        <div style="color: green; font-weight: bold; padding: 20px; border: 3px solid green; margin: 20px; background: lightgreen;">
            ✅ TOP TEST - If you see this GREEN BOX, rendering works!
        </div>
        
        <!-- LOT OF CONTENT (to push down) -->
        @for($i = 1; $i <= 50; $i++)
            <p>Line {{ $i }} - Filler content to push test to bottom...</p>
        @endfor
        
        <!-- TEST AT BOTTOM -->
        <div id="test-bottom" style="color: blue; font-weight: bold; padding: 20px; border: 3px solid blue; margin: 20px; background: lightblue;">
            ✅ BOTTOM TEST - If you see this BLUE BOX, entire page renders!
            <br>Time: {{ date('Y-m-d H:i:s') }}
        </div>
    </div>
</x-app-layout>
