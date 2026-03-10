@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">DTF Order Form</div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('dtf.store') }}">
                        @csrf
                        
                        <!-- Hidden fields to track row counts -->
                        <input type="hidden" name="print_rows_count" value="{{ $printRows }}">
                        <input type="hidden" name="shirt_rows_count" value="{{ $shirtRows }}">

                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">Print Sizes</h5>
                                <small class="text-muted">Leave empty rows blank</small>
                            </div>
                            
                            <div id="print-sizes-container">
                                @for($i = 0; $i < $printRows; $i++)
                                    <div class="row mb-2 print-size-row">
                                        <div class="col-md-6">
                                            <label class="form-label">Size {{ $i + 1 }}</label>
                                            <input type="text" name="print_sizes[{{ $i }}][size]" 
                                                   class="form-control" 
                                                   placeholder="e.g., 10x10 inches" 
                                                   value="{{ old("print_sizes.$i.size", '') }}">
                                            @error("print_sizes.$i.size")
                                                <div class="text-danger small">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Quantity</label>
                                            <input type="number" name="print_sizes[{{ $i }}][quantity]" 
                                                   class="form-control" 
                                                   min="0" 
                                                   value="{{ old("print_sizes.$i.quantity", 1) }}">
                                            @error("print_sizes.$i.quantity")
                                                <div class="text-danger small">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-2 d-flex align-items-end">
                                            @if($i >= 3)
                                                <button type="button" class="btn btn-outline-danger btn-sm remove-row-btn" 
                                                        data-target=".print-size-row:nth-child({{ $i + 1 }})">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                @endfor
                            </div>
                            
                            <div class="mt-3">
                                <button type="submit" name="add_more_rows" value="1" 
                                        class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-plus me-1"></i> Add 2 More Print Size Rows
                                </button>
                                <small class="text-muted ms-2">(Page will reload with additional rows)</small>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">Shirt Sizes</h5>
                                <small class="text-muted">Leave empty rows blank</small>
                            </div>
                            
                            <div id="shirt-sizes-container">
                                @for($i = 0; $i < $shirtRows; $i++)
                                    <div class="row mb-2 shirt-size-row">
                                        <div class="col-md-6">
                                            <label class="form-label">Size {{ $i + 1 }}</label>
                                            <select name="shirt_sizes[{{ $i }}][size]" class="form-control">
                                                <option value="">Select Size (leave blank if not needed)</option>
                                                <option value="XS" {{ old("shirt_sizes.$i.size") == 'XS' ? 'selected' : '' }}>XS</option>
                                                <option value="S" {{ old("shirt_sizes.$i.size") == 'S' ? 'selected' : '' }}>S</option>
                                                <option value="M" {{ old("shirt_sizes.$i.size") == 'M' ? 'selected' : '' }}>M</option>
                                                <option value="L" {{ old("shirt_sizes.$i.size") == 'L' ? 'selected' : '' }}>L</option>
                                                <option value="XL" {{ old("shirt_sizes.$i.size") == 'XL' ? 'selected' : '' }}>XL</option>
                                                <option value="2XL" {{ old("shirt_sizes.$i.size") == '2XL' ? 'selected' : '' }}>2XL</option>
                                                <option value="3XL" {{ old("shirt_sizes.$i.size") == '3XL' ? 'selected' : '' }}>3XL</option>
                                            </select>
                                            @error("shirt_sizes.$i.size")
                                                <div class="text-danger small">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Quantity</label>
                                            <input type="number" name="shirt_sizes[{{ $i }}][quantity]" 
                                                   class="form-control" 
                                                   min="0" 
                                                   value="{{ old("shirt_sizes.$i.quantity", 1) }}">
                                            @error("shirt_sizes.$i.quantity")
                                                <div class="text-danger small">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-2 d-flex align-items-end">
                                            @if($i >= 3)
                                                <button type="button" class="btn btn-outline-danger btn-sm remove-row-btn" 
                                                        data-target=".shirt-size-row:nth-child({{ $i + 1 }})">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                @endfor
                            </div>
                            
                            <div class="mt-3">
                                <button type="submit" name="add_more_rows" value="1" 
                                        class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-plus me-1"></i> Add 2 More Shirt Size Rows
                                </button>
                                <small class="text-muted ms-2">(Page will reload with additional rows)</small>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>How it works:</strong> Fill only the rows you need. Empty rows will be ignored. 
                                Use "Add More Rows" buttons if you need more than {{ $printRows }} print sizes or {{ $shirtRows }} shirt sizes.
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-check me-1"></i> Submit DTF Order
                                </button>
                                
                                <a href="{{ route('dtf.create') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-redo me-1"></i> Reset Form
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Optional: Simple JavaScript for UX effects only (not required for functionality)
document.addEventListener('DOMContentLoaded', function() {
    // Remove row buttons (visual effect only - rows will be ignored if empty)
    document.querySelectorAll('.remove-row-btn').forEach(button => {
        button.addEventListener('click', function() {
            const targetSelector = this.getAttribute('data-target');
            const row = document.querySelector(targetSelector);
            if (row) {
                // Just clear the inputs instead of removing (safer for form submission)
                row.querySelectorAll('input, select').forEach(input => {
                    if (input.type !== 'hidden') {
                        input.value = '';
                    }
                });
                // Visual feedback
                this.innerHTML = '<i class="fas fa-check text-success"></i>';
                this.classList.remove('btn-outline-danger');
                this.classList.add('btn-outline-success');
                setTimeout(() => {
                    this.innerHTML = '<i class="fas fa-trash"></i>';
                    this.classList.remove('btn-outline-success');
                    this.classList.add('btn-outline-danger');
                }, 1000);
            }
        });
    });
    
    // Auto-focus first empty print size field for better UX
    const firstEmptyPrintSize = document.querySelector('input[name^="print_sizes"][name$="[size]"]');
    if (firstEmptyPrintSize && !firstEmptyPrintSize.value) {
        firstEmptyPrintSize.focus();
    }
});
</script>
@endpush