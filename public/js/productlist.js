// PRODUCT LIST JAVASCRIPT - EXTERNAL FILE
console.log('🎯 EXTERNAL JavaScript file loading...');

// IMMEDIATE TEST - SHOW WE'RE LOADING
alert('🎯 EXTERNAL JavaScript IS LOADING! Click OK to continue.');

// Wait for DOM
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM ready - initializing Product List...');
    
    // Get all boxes
    const boxes = document.querySelectorAll('.category-box');
    console.log('Found', boxes.length, 'category boxes');
    
    if (boxes.length === 0) {
        console.error('ERROR: No .category-box elements found!');
        return;
    }
    
    // Log each box
    boxes.forEach((box, index) => {
        console.log('Box', index+1, ':', box.id || 'no-id', 
                   '- Category:', box.getAttribute('data-category') || 'no-category');
    });
    
    // Add interactivity to each box
    boxes.forEach(box => {
        // Make sure box is clickable
        box.style.cursor = 'pointer';
        
        // CLICK - Select box
        box.addEventListener('click', function() {
            console.log('Clicked:', this.id || 'unknown');
            
            // Remove selection from all boxes
            boxes.forEach(b => {
                b.classList.remove('selected');
                b.style.borderColor = '';
                b.style.backgroundColor = '';
                b.style.boxShadow = '';
            });
            
            // Add selection to clicked box
            this.classList.add('selected');
            this.style.borderColor = '#0d6efd';
            this.style.backgroundColor = 'rgba(13, 110, 253, 0.05)';
            this.style.boxShadow = '0 5px 20px rgba(13, 110, 253, 0.2)';
            this.style.transform = 'scale(1.02)';
        });
        
        // HOVER IN - Float effect
        box.addEventListener('mouseenter', function() {
            if (!this.classList.contains('selected')) {
                this.style.transform = 'translateY(-5px) scale(1.02)';
                this.style.boxShadow = '0 10px 25px rgba(0,0,0,0.15)';
                this.style.borderColor = '#0d6efd';
                this.style.transition = 'all 0.3s ease';
            }
        });
        
        // HOVER OUT - Return to normal
        box.addEventListener('mouseleave', function() {
            if (!this.classList.contains('selected')) {
                this.style.transform = '';
                this.style.boxShadow = '';
                this.style.borderColor = '';
            }
        });
    });
    
    // AUTO-SELECT FIRST BOX (Shirt Products)
    const shirtBox = document.getElementById('box-shirt');
    if (shirtBox) {
        console.log('Auto-selecting Shirt Products box...');
        shirtBox.classList.add('selected');
        shirtBox.style.borderColor = '#0d6efd';
        shirtBox.style.backgroundColor = 'rgba(13, 110, 253, 0.05)';
        shirtBox.style.boxShadow = '0 5px 20px rgba(13, 110, 253, 0.2)';
        shirtBox.style.transform = 'scale(1.02)';
    } else {
        console.error('ERROR: box-shirt element not found!');
    }
    
    console.log('✅ Product List JavaScript initialized successfully!');
    console.log('Instructions:');
    console.log('1. Hover over boxes to see floating effect');
    console.log('2. Click boxes to select (blue border + glow)');
    console.log('3. Only one box selected at a time');
});

// Fallback if DOMContentLoaded doesn't fire
window.addEventListener('load', function() {
    console.log('Window loaded - checking initialization...');
    if (!window.productListInitialized) {
        console.log('Initializing via window.load...');
        // Re-run initialization
        document.dispatchEvent(new Event('DOMContentLoaded'));
        window.productListInitialized = true;
    }
});
