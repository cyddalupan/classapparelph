@extends('layouts.app')

@section('content')
<div class="page-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h1 class="page-title">Suppliers</h1>
                <p class="page-subtitle">Manage your procurement suppliers</p>
            </div>
            <a href="{{ route('procurement.suppliers.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Add Supplier
            </a>
        </div>
    </div>
</div>

<div class="container">
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            @if($suppliers->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Contact Person</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Payment Terms</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($suppliers as $supplier)
                        <tr>
                            <td><strong>{{ $supplier->name }}</strong></td>
                            <td>{{ $supplier->contact_person ?? '—' }}</td>
                            <td>{{ $supplier->phone ?? '—' }}</td>
                            <td>{{ $supplier->email ?? '—' }}</td>
                            <td><span class="badge bg-info">{{ str_replace('_', ' ', strtoupper($supplier->payment_terms)) }}</span></td>
                            <td>
                                <span class="badge bg-{{ $supplier->status === 'active' ? 'success' : ($supplier->status === 'inactive' ? 'secondary' : 'danger') }}">
                                    {{ ucfirst($supplier->status) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('procurement.suppliers.edit', $supplier) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('procurement.suppliers.destroy', $supplier) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Delete this supplier?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3">
                {{ $suppliers->links() }}
            </div>
            @else
            <div class="text-center py-5">
                <i class="fas fa-truck fa-3x text-muted mb-3"></i>
                <h5>No suppliers yet</h5>
                <p class="text-muted">Add your first supplier to start ordering.</p>
                <a href="{{ route('procurement.suppliers.create') }}" class="btn btn-primary">Add Supplier</a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
