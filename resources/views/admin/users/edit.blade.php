@extends('layouts.app')

@section('page-title', 'Edit User - ' . $user->name)

@section('content')
<div class="edit-user-page">
    <!-- Header -->
    <div class="page-header">
        <div class="header-content">
            <h1 class="page-title">
                <i class="fas fa-user-edit"></i>
                Edit User
            </h1>
            <p class="page-subtitle">{{ $user->name }} — {{ ucfirst(str_replace('_', ' ', $user->role)) }}</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Back to Users
            </a>
        </div>
    </div>

    @if($errors->any())
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i>
        <ul style="margin:0;padding-left:1.25rem;">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Edit Form -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-user-cog"></i>
                User Details
            </h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.users.update', $user) }}" method="POST" class="edit-user-form">
                @csrf
                @method('PUT')

                <div class="form-grid">
                    <!-- Left Column -->
                    <div class="form-column">
                        <h4 class="section-title">Account Information</h4>

                        <div class="form-group">
                            <label for="name">Full Name <span class="required">*</span></label>
                            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name', $user->name) }}" required>
                            @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label for="email">Email Address <span class="required">*</span></label>
                            <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                                   value="{{ old('email', $user->email) }}" required>
                            @error('email') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label for="password">New Password <small class="text-muted">(leave blank to keep current)</small></label>
                            <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" 
                                   placeholder="Minimum 8 characters">
                            @error('password') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label for="password_confirmation">Confirm New Password</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" 
                                   class="form-control" placeholder="Confirm new password">
                        </div>

                        <div class="form-group">
                            <label for="role">User Role <span class="required">*</span></label>
                            <select id="role" name="role" class="form-control @error('role') is-invalid @enderror" required onchange="toggleRoleFields()">
                                <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Administrator</option>
                                <option value="sales_agent" {{ $user->role === 'sales_agent' ? 'selected' : '' }}>Sales Agent</option>
                                <option value="sales_representative" {{ $user->role === 'sales_representative' ? 'selected' : '' }}>Sales Representative</option>
                                <option value="staff" {{ $user->role === 'staff' ? 'selected' : '' }}>Staff</option>
                                <option value="procurement" {{ $user->role === 'procurement' ? 'selected' : '' }}>Procurement</option>
                            </select>
                            @error('role') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="is_active" value="1" {{ $user->is_active ? 'checked' : '' }}>
                                Active Account
                            </label>
                            <small class="text-muted d-block">Inactive users cannot log in.</small>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="form-column">
                        <h4 class="section-title">Employment Details</h4>

                        <div class="form-group">
                            <label for="employee_id">Employee ID</label>
                            <input type="text" id="employee_id" name="employee_id" class="form-control @error('employee_id') is-invalid @enderror" 
                                   value="{{ old('employee_id', $user->employee_id) }}">
                            @error('employee_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="text" id="phone" name="phone" class="form-control @error('phone') is-invalid @enderror" 
                                   value="{{ old('phone', $user->phone) }}">
                            @error('phone') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label for="department">Department</label>
                            <input type="text" id="department" name="department" class="form-control @error('department') is-invalid @enderror" 
                                   value="{{ old('department', $user->department) }}">
                            @error('department') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <!-- Sales-specific fields -->
                        <div id="salesFields" style="{{ in_array($user->role, ['sales_agent', 'sales_representative']) ? 'display:block;' : 'display:none;' }}">
                            <h4 class="section-title">Sales Configuration</h4>

                            <div class="form-group">
                                <label for="sales_target">Monthly Sales Target (₱)</label>
                                <input type="number" id="sales_target" name="sales_target" class="form-control @error('sales_target') is-invalid @enderror" 
                                       value="{{ old('sales_target', $user->sales_target) }}" step="0.01" min="0">
                                @error('sales_target') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-group">
                                <label for="commission_rate">Commission Rate (%)</label>
                                <input type="number" id="commission_rate" name="commission_rate" class="form-control @error('commission_rate') is-invalid @enderror" 
                                       value="{{ old('commission_rate', $user->commission_rate) }}" step="0.1" min="0" max="100">
                                @error('commission_rate') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-group">
                                <label for="hire_date">Hire Date</label>
                                <input type="date" id="hire_date" name="hire_date" class="form-control @error('hire_date') is-invalid @enderror" 
                                       value="{{ old('hire_date', $user->hire_date ? \Carbon\Carbon::parse($user->hire_date)->format('Y-m-d') : '') }}">
                                @error('hire_date') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-group">
                                <label for="supervisor">Supervisor</label>
                                <input type="text" id="supervisor" name="supervisor" class="form-control @error('supervisor') is-invalid @enderror" 
                                       value="{{ old('supervisor', $user->supervisor) }}">
                                @error('supervisor') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
.edit-user-page { padding: 2rem; }

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
}

@media (max-width: 768px) {
    .form-grid { grid-template-columns: 1fr; gap: 1rem; }
    .edit-user-page { padding: 1rem; }
}

.section-title {
    font-size: 0.875rem;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 1.25rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #e2e8f0;
}

.form-group { margin-bottom: 1.25rem; }
.form-group label { display: block; font-size: 0.875rem; font-weight: 600; color: #334155; margin-bottom: 0.375rem; }
.form-group .required { color: #ef4444; }
.text-muted { color: #94a3b8; font-size: 0.8rem; }
.d-block { display: block; }

.form-control {
    width: 100%;
    padding: 0.625rem 0.875rem;
    border: 1.5px solid #e2e8f0;
    border-radius: 8px;
    font-size: 0.875rem;
    background: white;
    transition: all 0.2s;
}

.form-control:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
.form-control.is-invalid { border-color: #ef4444; }

select.form-control {
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 0.75rem center;
    padding-right: 2rem;
}

.invalid-feedback { display: block; font-size: 0.75rem; color: #ef4444; margin-top: 0.25rem; }

.alert-danger { background: #fce4ec; color: #c62828; border: 1px solid #f8bbd0; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; }

.form-actions { display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid #e2e8f0; }

.btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.625rem 1.25rem; border-radius: 8px; font-size: 0.875rem; font-weight: 600; cursor: pointer; transition: all 0.2s; border: none; }
.btn-primary { background: #3b82f6; color: white; }
.btn-primary:hover { background: #2563eb; }
.btn-outline { background: white; color: #475569; border: 1.5px solid #e2e8f0; }
.btn-outline:hover { background: #f8fafc; border-color: #cbd5e1; }
</style>
@endpush

@push('scripts')
<script>
function toggleRoleFields() {
    const role = document.getElementById('role').value;
    const salesFields = document.getElementById('salesFields');
    salesFields.style.display = (role === 'sales_agent' || role === 'sales_representative') ? 'block' : 'none';
}
document.addEventListener('DOMContentLoaded', toggleRoleFields);
</script>
@endpush
@endsection
