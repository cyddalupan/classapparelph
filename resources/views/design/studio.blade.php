<x-app-layout>
    <x-slot name="header">
        <div class="dashboard-header-content">
            <h1 class="dashboard-title">
                <i class="fas fa-paint-brush"></i>
                Design Studio
            </h1>
            <p class="dashboard-subtitle">Create and customize t-shirt designs</p>
        </div>
    </x-slot>

    <div class="dashboard-content">
        <div class="placeholder-container">
            <div class="placeholder-icon">
                <i class="fas fa-paint-brush fa-4x"></i>
            </div>
            <h2 class="placeholder-title">Design Studio</h2>
            <p class="placeholder-text">
                Create custom t-shirt designs with our online design tool. Upload images, add text, 
                choose colors, and preview your designs in real-time.
            </p>
            <div class="placeholder-features">
                <div class="feature-card">
                    <i class="fas fa-upload"></i>
                    <h3>Image Upload</h3>
                    <p>Upload your own images and logos</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-font"></i>
                    <h3>Text Editor</h3>
                    <p>Add and customize text with various fonts</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-palette"></i>
                    <h3>Color Picker</h3>
                    <p>Choose from thousands of colors</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-eye"></i>
                    <h3>Live Preview</h3>
                    <p>See your design on different t-shirt models</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>