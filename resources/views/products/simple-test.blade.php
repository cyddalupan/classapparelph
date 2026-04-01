<!DOCTYPE html>
<html>
<head>
    <title>Simple JavaScript Test</title>
</head>
<body>
    <h1>🎯 SIMPLE JAVASCRIPT TEST</h1>
    
    <div id="step1">Step 1: Waiting...</div>
    <div id="step2">Step 2: Waiting...</div>
    <div id="step3">Step 3: Waiting...</div>
    <div id="step4">Step 4: Waiting...</div>
    
    <script>
        console.log('🎯 SIMPLE TEST: Starting...');
        
        // STEP 1: Simple alert
        alert('🎯 STEP 1: Alert works!');
        document.getElementById('step1').textContent = '✅ STEP 1: Alert worked!';
        document.getElementById('step1').style.color = 'green';
        
        // STEP 2: Simple console log
        console.log('🎯 STEP 2: Console log works!');
        document.getElementById('step2').textContent = '✅ STEP 2: Console log worked!';
        document.getElementById('step2').style.color = 'green';
        
        // STEP 3: Simple DOM manipulation
        var testDiv = document.createElement('div');
        testDiv.textContent = '✅ STEP 3: DOM manipulation worked!';
        testDiv.style.color = 'green';
        testDiv.style.margin = '10px';
        testDiv.style.padding = '10px';
        testDiv.style.border = '2px solid green';
        document.body.appendChild(testDiv);
        document.getElementById('step3').textContent = '✅ STEP 3: DOM manipulation worked!';
        document.getElementById('step3').style.color = 'green';
        
        // STEP 4: Simple string operation
        var testString = 'Hello World';
        var hasHello = testString.includes('Hello');
        document.getElementById('step4').textContent = '✅ STEP 4: String includes() worked! Result: ' + hasHello;
        document.getElementById('step4').style.color = 'green';
        
        console.log('✅ SIMPLE TEST: All steps completed!');
    </script>
</body>
</html>