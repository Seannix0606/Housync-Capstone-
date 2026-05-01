@extends('layouts.super-admin-app')

@section('title', 'Property Details')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">{{ $property->name }}</h1>
            <p class="text-muted mb-0">{{ $property->address ?? 'No address provided' }}</p>
        </div>
        <a href="{{ route('super-admin.apartments') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Properties
        </a>
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
                    <h4 class="mb-0">{{ $property->units_count }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <p class="text-muted small mb-1">Occupied Units</p>
                    <h4 class="mb-0">{{ $property->occupied_units_count }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <p class="text-muted small mb-1">Available Units</p>
                    <h4 class="mb-0">{{ $property->available_units_count }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <h5 class="card-title mb-3">Timestamps</h5>
            <div class="row">
                <div class="col-md-6">
                    <p class="text-muted small mb-1">Created At</p>
                    <p class="mb-3">{{ optional($property->created_at)->format('M d, Y h:i A') ?? 'N/A' }}</p>
                </div>
                <div class="col-md-6">
                    <p class="text-muted small mb-1">Updated At</p>
                    <p class="mb-3">{{ optional($property->updated_at)->format('M d, Y h:i A') ?? 'N/A' }}</p>
                </div>
            </div>
            <a href="{{ route('super-admin.properties.units', $property->id) }}" class="btn btn-primary">
                <i class="fas fa-door-open me-1"></i> View Units
            </a>
        </div>
    </div>
@endsection
