<!DOCTYPE html>
<html>
<head>
    <title>JavaScript Test</title>
</head>
<body>
    <h1>JavaScript Test Page</h1>
    
    <div id="test-result">Waiting for JavaScript...</div>
    
    <script>
        console.log('🎯 TEST: JavaScript starting...');
        
        // Simple alert
        alert('🎯 TEST ALERT: If you see this, JavaScript works!');
        
        // Update page
        document.getElementById('test-result').innerHTML = '✅ JavaScript IS working!';
        document.getElementById('test-result').style.color = 'green';
        document.getElementById('test-result').style.fontWeight = 'bold';
        
        console.log('✅ TEST: JavaScript completed successfully!');
    </script>
</body>
</html>