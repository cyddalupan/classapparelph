<!DOCTYPE html>
<html>
<head>
    <title>Product List Test</title>
    <style>
        .category-box {
            border: 3px solid #dee2e6;
            border-radius: 12px;
            padding: 20px;
            margin: 10px;
            text-align: center;
            transition: transform 0.2s;
        }
        .category-box:hover {
            transform: translateY(-5px);
            border-color: blue;
        }
    </style>
</head>
<body>
    <h1>Product List Test (Simplified)</h1>
    
    <div class="category-box" id="box-shirt">
        <h3>Shirt Products</h3>
        <p>Test Box 1</p>
    </div>
    
    <div class="category-box">
        <h3>Other Items</h3>
        <p>Test Box 2</p>
    </div>
    
    <script>
        console.log('🎯 PRODUCTLIST TEST: JavaScript starting...');
        
        // Simple alert
        alert('🎯 PRODUCTLIST TEST: If you see this, JavaScript works on productlist!');
        
        // Add border to first box
        var firstBox = document.getElementById('box-shirt');
        if (firstBox) {
            firstBox.style.border = '3px solid red';
            firstBox.style.backgroundColor = '#ff000020';
        }
        
        // Add hover to all boxes
        var boxes = document.querySelectorAll('.category-box');
        boxes.forEach(function(box) {
            box.onmouseover = function() {
                this.style.border = '3px solid blue';
            };
            box.onmouseout = function() {
                this.style.border = '3px solid #dee2e6';
            };
        });
        
        console.log('✅ PRODUCTLIST TEST: JavaScript completed!');
    </script>
</body>
</html>