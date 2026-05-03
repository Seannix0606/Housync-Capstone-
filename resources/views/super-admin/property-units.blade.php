@extends('layouts.super-admin-app')

@section('title', 'Property Units')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Units - {{ $property->name }}</h1>
            <p class="text-muted mb-0">{{ $property->address ?? 'No address provided' }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('super-admin.properties.show', $property->id) }}" class="btn btn-outline-primary">
                <i class="fas fa-eye me-1"></i> View Property
            </a>
            <a href="{{ route('super-admin.apartments') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <p class="text-muted small mb-1">Owner</p>
                    <h6 class="mb-0">{{ $property->landlord->name ?? 'Unknown Landlord' }}</h6>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <p class="text-muted small mb-1">Total Units</p>
                    <h4 class="mb-0">{{ $property->units->count() }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <p class="text-muted small mb-1">Occupied</p>
                    <h4 class="mb-0">{{ $property->occupied_units_count }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <p class="text-muted small mb-1">Available</p>
                    <h4 class="mb-0">{{ $property->available_units_count }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Unit</th>
                            <th>Status</th>
                            <th>Tenant</th>
                            <th class="text-end">Rent</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($property->units as $unit)
                            <tr>
                                <td>{{ $unit->unit_number }}</td>
                                <td class="text-capitalize">{{ $unit->status }}</td>
                                <td>{{ $unit->tenantAssignment?->tenant?->name ?? 'Unassigned' }}</td>
                                <td class="text-end">PHP {{ number_format((float) $unit->rent_amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No units found for this property.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
