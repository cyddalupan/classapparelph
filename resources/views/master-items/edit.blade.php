@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-edit me-2"></i>
                        Edit Master Item
                    </h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Edit Master Item</strong> - This form is under construction. 
                        Full functionality coming soon.
                    </div>
                    
                    <form>
                        <div class="mb-3">
                            <label for="name" class="form-label">Product Name</label>
                            <input type="text" class="form-control" id="name" value="Sample Product" placeholder="Enter product name">
                        </div>
                        
                        <div class="mb-3">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-select" id="category">
                                <option value="">Select category</option>
                                <option value="Shirt Products" selected>Shirt Products</option>
                                <option value="Pants Products">Pants Products</option>
                                <option value="Shoes Products">Shoes Products</option>
                                <option value="Accessories Products">Accessories Products</option>
                                <option value="Others Products">Others Products</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" rows="3" placeholder="Enter product description">This is a sample product description for editing.</textarea>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="button" class="btn btn-secondary me-md-2" onclick="window.history.back()">
                                <i class="fas fa-arrow-left me-1"></i> Back
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Update Master Item
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection