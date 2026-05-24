@extends('layouts.app')

@section('page-title', 'Design Studio')

@section('content')
<div class="dashboard-header-content">
    <h1 class="dashboard-title">
        <i class="fas fa-paint-brush"></i>
        Design Studio
    </h1>
    <p class="dashboard-subtitle">Create and customize t-shirt designs</p>
</div>

    <div class="dashboard-content">
        <div class="container-fluid px-4">
            <div class="row">
                <!-- Canvas Area -->
                <div class="col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <button class="btn btn-sm btn-outline-secondary" onclick="switchView('front')" id="viewFrontBtn">
                                    <i class="fas fa-tshirt me-1"></i>Front
                                </button>
                                <button class="btn btn-sm btn-outline-secondary" onclick="switchView('back')" id="viewBackBtn">
                                    <i class="fas fa-tshirt me-1"></i>Back
                                </button>
                                <span class="mx-2 text-muted">|</span>
                                <label class="btn btn-sm btn-primary">
                                    <i class="fas fa-upload me-1"></i>Upload Logo
                                    <input type="file" id="logoUpload" accept="image/*" style="display:none" onchange="addLogo(event)">
                                </label>
                                <button class="btn btn-sm btn-success" onclick="addText()">
                                    <i class="fas fa-font me-1"></i>Add Text
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteSelected()">
                                    <i class="fas fa-trash me-1"></i>Delete
                                </button>
                                <span class="mx-2 text-muted">|</span>
                                <button class="btn btn-sm btn-info text-white" onclick="exportDesign()">
                                    <i class="fas fa-download me-1"></i>Export
                                </button>
                            </div>
                            <div class="canvas-wrapper" style="background:#e0e0e0;border-radius:8px;padding:10px;min-height:550px;">
                                <canvas id="tshirtCanvas" width="550" height="550"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Properties Panel -->
                <div class="col-lg-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h6 class="fw-bold"><i class="fas fa-palette me-2"></i>Shirt Color</h6>
                            <div class="d-flex flex-wrap gap-1 mb-3" id="colorPalette">
                                <button class="color-swatch" style="background:#ffffff;border:2px solid #ddd;" onclick="changeShirtColor('#ffffff')" title="White"></button>
                                <button class="color-swatch" style="background:#000000;" onclick="changeShirtColor('#000000')" title="Black"></button>
                                <button class="color-swatch" style="background:#2d3436;" onclick="changeShirtColor('#2d3436')" title="Dark Gray"></button>
                                <button class="color-swatch" style="background:#0984e3;" onclick="changeShirtColor('#0984e3')" title="Blue"></button>
                                <button class="color-swatch" style="background:#e17055;" onclick="changeShirtColor('#e17055')" title="Red"></button>
                                <button class="color-swatch" style="background:#00b894;" onclick="changeShirtColor('#00b894')" title="Green"></button>
                                <button class="color-swatch" style="background:#6c5ce7;" onclick="changeShirtColor('#6c5ce7')" title="Purple"></button>
                                <button class="color-swatch" style="background:#fdcb6e;" onclick="changeShirtColor('#fdcb6e')" title="Yellow"></button>
                                <button class="color-swatch" style="background:#e84393;" onclick="changeShirtColor('#e84393')" title="Pink"></button>
                                <button class="color-swatch" style="background:#f97316;" onclick="changeShirtColor('#f97316')" title="Orange"></button>
                            </div>

                            <h6 class="fw-bold mt-3"><i class="fas fa-cog me-2"></i>Selected Item</h6>
                            <div id="selectedProps">
                                <p class="text-muted small mb-0">Click an item on the shirt to edit properties</p>
                            </div>

                            <hr>
                            <h6 class="fw-bold"><i class="fas fa-info-circle me-2"></i>Tips</h6>
                            <ul class="small text-muted ps-3 mb-0">
                                <li>Click & drag to move logos/text</li>
                                <li>Drag corners to resize</li>
                                <li>Click item then Delete to remove</li>
                                <li>Export to download your design</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('styles')
<style>
.canvas-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
}
#tshirtCanvas {
    cursor: crosshair;
    width: 100%;
    max-width: 550px;
    height: auto !important;
}
.color-swatch {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    border: 2px solid transparent;
    cursor: pointer;
    transition: transform 0.15s, border-color 0.15s;
}
.color-swatch:hover {
    transform: scale(1.15);
}
.color-swatch.active {
    border-color: #667eea;
    box-shadow: 0 0 0 2px rgba(102,126,234,0.3);
}
</style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.1/fabric.min.js"></script>
<script>
(function() {
    'use strict';
    
    var el = document.getElementById('tshirtCanvas');
    if (!el) { console.error('Canvas element not found!'); return; }
    
    var canvas = new fabric.Canvas('tshirtCanvas', {
        width: 550, height: 550,
        backgroundColor: '#e8e8e8',
        selection: true
    });
    
    var shirtBg = '#ffffff';
    var currentView = 'front';
    var shirtTemplate = null; // fabric.Image object
    var shirtOverlay = null;  // colored rect overlay
    
    // Draw t-shirt using actual image template
    function drawShirt(color, view) {
        var old = canvas.getObjects().filter(function(o) { return o._isShirt; });
        old.forEach(function(o) { canvas.remove(o); });
        
        view = view || 'front';
        var imgSrc = view === 'front' ? '/images/tshirt_front.png' : '/images/tshirt_back.png';
        
        fabric.Image.fromURL(imgSrc, function(img) {
            // Scale by width to fit in canvas (leave 20px padding each side)
            var maxW = 510;
            var scale = maxW / img.width;
            var h = img.height * scale;
            img.set({
                left: (550 - img.width * scale) / 2,
                top: (550 - h) / 2,
                scaleX: scale,
                scaleY: scale,
                selectable: false,
                evented: false
            });
            img._isShirt = true;
            shirtTemplate = img;
            
            // Remove old overlay
            if (shirtOverlay) {
                canvas.remove(shirtOverlay);
            }
            
            var c = color || '#ffffff';
            shirtOverlay = new fabric.Rect({
                left: img.left,
                top: img.top,
                width: img.getScaledWidth(),
                height: img.getScaledHeight(),
                fill: c,
                opacity: 0.5,
                selectable: false,
                evented: false
            });
            shirtOverlay._isShirt = true;
            
            canvas.add(img);
            canvas.add(shirtOverlay);
            canvas.renderAll();
        });
    }
    
    // Init
    setTimeout(function() {
        drawShirt('#ffffff', 'front');
    }, 200);
    
    var viewFrontBtn = document.getElementById('viewFrontBtn');
    if (viewFrontBtn) viewFrontBtn.classList.add('btn-secondary');
    
    window.switchView = function(view) {
        currentView = view;
        var f = document.getElementById('viewFrontBtn');
        var b = document.getElementById('viewBackBtn');
        if (f) { f.classList.toggle('btn-outline-secondary', view !== 'front'); f.classList.toggle('btn-secondary', view === 'front'); }
        if (b) { b.classList.toggle('btn-outline-secondary', view !== 'back'); b.classList.toggle('btn-secondary', view === 'back'); }
        drawShirt(shirtBg, view);
    };
    
    window.changeShirtColor = function(color) {
        shirtBg = color;
        if (shirtOverlay) {
            shirtOverlay.set({ fill: color });
            canvas.renderAll();
        }
        document.querySelectorAll('.color-swatch').forEach(function(s) { s.classList.remove('active'); });
        document.querySelectorAll('.color-swatch').forEach(function(s) {
            var bg = s.style.backgroundColor;
            if (!bg) return;
            if (!bg.startsWith('#')) {
                var m = bg.match(/\d+/g);
                if (m) bg = '#' + m.slice(0,3).map(function(x) { return parseInt(x).toString(16).padStart(2,'0'); }).join('');
            }
            if (bg.toLowerCase() === color.toLowerCase()) s.classList.add('active');
        });
    };
    
    window.addLogo = function(event) {
        var file = event.target.files[0];
        if (!file) return;
        var reader = new FileReader();
        reader.onload = function(e) {
            fabric.Image.fromURL(e.target.result, function(img) {
                img.set({
                    left: 180, top: 250,
                    scaleX: 0.3, scaleY: 0.3,
                    cornerColor: '#667eea', cornerStrokeColor: '#667eea',
                    cornerSize: 10, transparentCorners: false,
                    borderColor: '#667eea', padding: 5
                });
                canvas.add(img);
                canvas.setActiveObject(img);
                canvas.renderAll();
            });
        };
        reader.readAsDataURL(file);
        event.target.value = '';
    };
    
    window.addText = function() {
        var t = new fabric.IText('Type here', {
            left: 220, top: 280,
            fontSize: 28, fontFamily: 'Arial', fill: '#000',
            cornerColor: '#667eea', cornerStrokeColor: '#667eea',
            cornerSize: 10, transparentCorners: false,
            borderColor: '#667eea', padding: 5
        });
        canvas.add(t);
        canvas.setActiveObject(t);
        canvas.renderAll();
    };
    
    window.deleteSelected = function() {
        var active = canvas.getActiveObject();
        if (active && !active._isShirt) {
            canvas.remove(active);
            canvas.discardActiveObject();
            canvas.renderAll();
        }
    };
    
    window.exportDesign = function() {
        var link = document.createElement('a');
        link.download = 'tshirt-design.png';
        link.href = canvas.toDataURL({ format: 'png', quality: 1, multiplier: 2 });
        link.click();
    };
    
    document.addEventListener('keydown', function(e) {
        if ((e.key === 'Delete' || e.key === 'Backspace') && e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA') {
            window.deleteSelected();
        }
    });
    
})();
</script>
@endpush
