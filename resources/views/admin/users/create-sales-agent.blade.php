@extends('layouts.app')

@section('page-title', 'Add New Sales Agent')

@section('content')
<div class="create-sales-agent-page">
    <!-- Header -->
    <div class="page-header">
        <div class="header-content">
            <h1 class="page-title">
                <i class="fas fa-user-plus"></i>
                Add New Sales Agent
            </h1>
            <p class="page-subtitle">Create a new sales agent or representative account</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('sales-agents.index') }}" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <!-- Create Form -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-user-tie"></i>
                Sales Agent Information
            </h3>
        </div>
        
        <div class="card-body">
            <form method="POST" action="{{ route('sales-agents.store') }}" class="form-grid">
                @csrf
                
                <!-- Basic Information -->
                <div class="form-section">
                    <h4 class="form-section-title">
                        <i class="fas fa-id-card"></i>
                        Basic Information
                    </h4>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="name">
                                <i class="fas fa-user"></i>
                                Full Name *
                            </label>
                            <input type="text" id="name" name="name" class="form-input" 
                                   value="{{ old('name') }}" required autofocus>
                            @error('name')
                            <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="email">
                                <i class="fas fa-envelope"></i>
                                Email Address *
                            </label>
                            <input type="email" id="email" name="email" class="form-input" 
                                   value="{{ old('email') }}" required>
                            @error('email')
                            <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="password">
                                <i class="fas fa-lock"></i>
                                Password *
                            </label>
                            <input type="password" id="password" name="password" class="form-input" required>
                            @error('password')
                            <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="password_confirmation">
                                <i class="fas fa-lock"></i>
                                Confirm Password *
                            </label>
                            <input type="password" id="password_confirmation" name="password_confirmation" 
                                   class="form-input" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="phone">
                                <i class="fas fa-phone"></i>
                                Phone Number
                            </label>
                            <input type="tel" id="phone" name="phone" class="form-input" 
                                   value="{{ old('phone') }}">
                            @error('phone')
                            <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="employee_id">
                                <i class="fas fa-id-badge"></i>
                                Employee ID *
                            </label>
                            <input type="text" id="employee_id" name="employee_id" class="form-input" 
                                   value="{{ old('employee_id') }}" required placeholder="e.g., SA-001">
                            @error('employee_id')
                            <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <!-- Role & Employment -->
                <div class="form-section">
                    <h4 class="form-section-title">
                        <i class="fas fa-briefcase"></i>
                        Role & Employment
                    </h4>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="role">
                                <i class="fas fa-user-tag"></i>
                                Role *
                            </label>
                            <select id="role" name="role" class="form-input" required>
                                <option value="">Select Role</option>
                                <option value="sales_agent" {{ old('role') == 'sales_agent' ? 'selected' : '' }}>
                                    Sales Agent
                                </option>
                                <option value="sales_representative" {{ old('role') == 'sales_representative' ? 'selected' : '' }}>
                                    Sales Representative
                                </option>
                            </select>
                            @error('role')
                            <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="department">
                                <i class="fas fa-building"></i>
                                Department
                            </label>
                            <input type="text" id="department" name="department" class="form-input" 
                                   value="{{ old('department') }}" placeholder="e.g., Sales, Retail">
                            @error('department')
                            <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="hire_date">
                                <i class="fas fa-calendar-alt"></i>
                                Hire Date
                            </label>
                            <input type="date" id="hire_date" name="hire_date" class="form-input" 
                                   value="{{ old('hire_date') }}">
                            @error('hire_date')
                            <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="supervisor">
                                <i class="fas fa-user-shield"></i>
                                Supervisor
                            </label>
                            <input type="text" id="supervisor" name="supervisor" class="form-input" 
                                   value="{{ old('supervisor') }}" placeholder="Supervisor name">
                            @error('supervisor')
                            <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <!-- Sales Targets & Commission -->
                <div class="form-section">
                    <h4 class="form-section-title">
                        <i class="fas fa-chart-line"></i>
                        Sales Targets & Commission
                    </h4>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="sales_target">
                                <i class="fas fa-bullseye"></i>
                                Sales Target (₱)
                            </label>
                            <input type="number" id="sales_target" name="sales_target" class="form-input" 
                                   value="{{ old('sales_target') }}" step="0.01" min="0" 
                                   placeholder="e.g., 100000">
                            @error('sales_target')
                            <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="commission_rate">
                                <i class="fas fa-percentage"></i>
                                Commission Rate (%)
                            </label>
                            <input type="number" id="commission_rate" name="commission_rate" class="form-input" 
                                   value="{{ old('commission_rate') }}" step="0.01" min="0" max="100" 
                                   placeholder="e.g., 5.00">
                            @error('commission_rate')
                            <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <!-- Form Actions -->
                <div class="form-actions">
                    <button type="button" class="btn btn-outline" onclick="window.history.back()">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Create Sales Agent
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.create-sales-agent-page {
    padding: 2rem;
}

.form-grid {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.form-section {
    background: #f8fafc;
    border-radius: 12px;
    padding: 1.5rem;
    border: 1px solid #e2e8f0;
}

.form-section-title {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 1.125rem;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 1.5rem;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid #e2e8f0;
}

.form-section-title i {
    color: #3b82f6;
}

.form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.form-row:last-child {
    margin-bottom: 0;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 500;
    color: #475569;
    font-size: 0.875rem;
}

.form-label i {
    color: #64748b;
    width: 16px;
}

.form-input {
    padding: 0.75rem 1rem;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 0.875rem;
    transition: all 0.2s;
    background: white;
}

.form-input:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-input::placeholder {
    color: #94a3b8;
}

.form-error {
    color: #ef4444;
    font-size: 0.75rem;
    margin-top: 0.25rem;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    padding-top: 2rem;
    border-top: 1px solid #e2e8f0;
    margin-top: 2rem;
}

.btn-outline {
    background: white;
    color: #3b82f6;
    border: 2px solid #3b82f6;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-outline:hover {
    background: #eff6ff;
}

.btn-primary {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(59, 130, 246, 0.3);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .create-sales-agent-page {
        padding: 1rem;
    }
    
    .form-row {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .form-section {
        padding: 1rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-generate employee ID if not provided
    const employeeIdInput = document.getElementById('employee_id');
    const roleSelect = document.getElementById('role');
    
    function generateEmployeeId() {
        if (!employeeIdInput.value) {
            const role = roleSelect.value;
            const prefix = role === 'sales_agent' ? 'SA' : 'SR';
            const randomNum = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
            employeeIdInput.value = `${prefix}-${randomNum}`;
        }
    }
    
    // Generate ID when role changes
    roleSelect.addEventListener('change', generateEmployeeId);
    
    // Generate initial ID if role is selected
    if (roleSelect.value) {
        generateEmployeeId();
    }
    
    // Format phone number
    const phoneInput = document.getElementById('phone');
    phoneInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 0) {
            value = value.match(/.{1,4}/g).join(' ');
        }
        e.target.value = value;
    });
    
    // Set today as default hire date
    const hireDateInput = document.getElementById('hire_date');
    if (!hireDateInput.value) {
        const today = new Date().toISOString().split('T')[0];
        hireDateInput.value = today;
    }
});
</script>
@endsection