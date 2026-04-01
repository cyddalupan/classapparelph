<!DOCTYPE html>
<html>
<head>
    <title>Debug Test</title>
</head>
<body>
    <h1>Debug Test - Is JavaScript in HTML?</h1>
    
    <div id="debug-output"></div>
    
    <script>
        console.log('🎯 DEBUG: JavaScript starting...');
        
        // Check if JavaScript is in HTML
        var scripts = document.getElementsByTagName('script');
        var debugOutput = document.getElementById('debug-output');
        
        debugOutput.innerHTML = '<h3>Found ' + scripts.length + ' script tags</h3>';
        
        for (var i = 0; i < scripts.length; i++) {
            debugOutput.innerHTML += '<p>Script ' + (i+1) + ': ' + 
                (scripts[i].src ? 'External: ' + scripts[i].src : 'Inline (' + scripts[i].innerHTML.length + ' chars)') + 
                '</p>';
        }
        
        // Simple test
        alert('🎯 DEBUG: JavaScript test alert!');
        
        console.log('✅ DEBUG: JavaScript completed!');
    </script>
</body>
</html>