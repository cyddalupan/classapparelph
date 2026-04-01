<!DOCTYPE html>
<html>
<head>
    <title>Show HTML Source</title>
    <style>
        body { font-family: monospace; background: #f0f0f0; padding: 20px; }
        pre { background: white; border: 1px solid #ccc; padding: 20px; overflow: auto; }
        .script-found { color: green; font-weight: bold; }
        .script-not-found { color: red; font-weight: bold; }
    </style>
</head>
<body>
    <h1>HTML Source Code Viewer</h1>
    <p>This page shows the ACTUAL HTML that is sent to your browser.</p>
    
    <div id="source-container">
        <h2>Looking for JavaScript...</h2>
        <p id="script-status">Checking...</p>
    </div>
    
    <script>
        console.log('🎯 SOURCE VIEWER: JavaScript starting...');
        
        // Get ALL HTML source
        var fullHTML = document.documentElement.outerHTML;
        
        // Check for script tags
        var scriptTags = document.getElementsByTagName('script');
        var scriptCount = scriptTags.length;
        
        // Check for our specific JavaScript
        var hasAlert = fullHTML.includes('FRESH START: If you see this');
        var hasConsoleLog = fullHTML.includes('console.log');
        
        // Update page
        var status = document.getElementById('script-status');
        var html = '<h3>Script Analysis:</h3>';
        html += '<p>Total script tags found: <strong>' + scriptCount + '</strong></p>';
        html += '<p>Our alert code in HTML: <span class="' + (hasAlert ? 'script-found' : 'script-not-found') + '">' + (hasAlert ? '✅ FOUND' : '❌ NOT FOUND') + '</span></p>';
        html += '<p>console.log in HTML: <span class="' + (hasConsoleLog ? 'script-found' : 'script-not-found') + '">' + (hasConsoleLog ? '✅ FOUND' : '❌ NOT FOUND') + '</span></p>';
        
        if (scriptCount > 0) {
            html += '<h3>Script tags found:</h3>';
            for (var i = 0; i < scriptCount; i++) {
                var script = scriptTags[i];
                html += '<p>Script ' + (i+1) + ': ';
                if (script.src) {
                    html += 'External: ' + script.src;
                } else {
                    var content = script.innerHTML.trim();
                    html += 'Inline (' + content.length + ' chars)';
                    if (content.length < 100) {
                        html += ': <code>' + content.substring(0, 100) + '</code>';
                    }
                }
                html += '</p>';
            }
        }
        
        status.innerHTML = html;
        
        // Simple test
        alert('🎯 SOURCE VIEWER: JavaScript test alert!');
        
        console.log('✅ SOURCE VIEWER: Analysis complete');
    </script>
</body>
</html>