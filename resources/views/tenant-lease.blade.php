@extends('layouts.app')

@section('title', 'Lease Information')

@section('content')

            <div class="dashboard-content">
                @if($assignment)
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
                    <!-- Lease Details -->
                    <div>
                        <div class="profile-card">
                            <h3 style="margin: 0 0 20px 0;">Lease Agreement Details</h3>
                            
                            <div class="lease-info-grid">
                                <div class="lease-info-item">
                                    <div class="lease-info-value">₱{{ number_format($assignment->rent_amount, 2) }}</div>
                                    <div class="lease-info-label">Monthly Rent</div>
                                </div>
                                <div class="lease-info-item">
                                    <div class="lease-info-value">₱{{ number_format($assignment->security_deposit, 2) }}</div>
                                    <div class="lease-info-label">Security Deposit</div>
                                </div>
                            </div>

                            <div class="info-item">
                                <span class="info-label">Lease Start Date:</span>
                                <span class="info-value">{{ $assignment->lease_start_date->format('F d, Y') }}</span>
                            </div>

                            <div class="info-item">
                                <span class="info-label">Lease End Date:</span>
                                <span class="info-value">{{ $assignment->lease_end_date->format('F d, Y') }}</span>
                            </div>

                            <div class="info-item">
                                <span class="info-label">Lease Duration:</span>
                                <span class="info-value">{{ $assignment->lease_start_date->diffInMonths($assignment->lease_end_date) }} months</span>
                            </div>

                            <div class="info-item">
                                <span class="info-label">Assignment Status:</span>
                                <span class="info-value">
                                    <span class="status-badge status-{{ $assignment->status === 'active' ? 'active' : 'inactive' }}">
                                        {{ ucfirst($assignment->status) }}
                                    </span>
                                </span>
                            </div>

                            <div class="info-item">
                                <span class="info-label">Assigned Date:</span>
                                <span class="info-value">{{ $assignment->assigned_at->format('F d, Y') }}</span>
                            </div>

                            @if($assignment->notes)
                            <div class="info-item">
                                <span class="info-label">Notes:</span>
                                <span class="info-value">{{ $assignment->notes }}</span>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Property & Landlord Information -->
                    <div>
                        <div class="profile-card">
                            <h3 style="margin: 0 0 20px 0;">Property Information</h3>
                            
                            <div class="info-item">
                                <span class="info-label">Unit Number:</span>
                                <span class="info-value">{{ $assignment->unit->unit_number }}</span>
                            </div>

                            <div class="info-item">
                                <span class="info-label">Unit Type:</span>
                                <span class="info-value">{{ ucfirst($assignment->unit->unit_type) }}</span>
                            </div>

                            <div class="info-item">
                                <span class="info-label">Apartment:</span>
                                <span class="info-value">{{ $assignment->unit->apartment->name }}</span>
                            </div>

                            <div class="info-item">
                                <span class="info-label">Address:</span>
                                <span class="info-value">{{ $assignment->unit->apartment->address }}</span>
                            </div>

                            @if($assignment->unit->bedrooms || $assignment->unit->bathrooms)
                            <div class="info-item">
                                <span class="info-label">Unit Details:</span>
                                <span class="info-value">
                                    @if($assignment->unit->bedrooms){{ $assignment->unit->bedrooms }} BR @endif
                                    @if($assignment->unit->bathrooms){{ $assignment->unit->bathrooms }} BA @endif
                                    @if($assignment->unit->floor_area){{ $assignment->unit->floor_area }}m² @endif
                                </span>
                            </div>
                            @endif

                            @if($assignment->unit->is_furnished)
                            <div class="info-item">
                                <span class="info-label">Furnished:</span>
                                <span class="info-value">Yes</span>
                            </div>
                            @endif

                            @if($assignment->unit->floor_number)
                            <div class="info-item">
                                <span class="info-label">Floor:</span>
                                <span class="info-value">{{ $assignment->unit->floor_number }}</span>
                            </div>
                            @endif
                        </div>

                        <div class="profile-card">
                            <h3 style="margin: 0 0 20px 0;">Landlord Information</h3>
                            
                            <div class="info-item">
                                <span class="info-label">Landlord Name:</span>
                                <span class="info-value">{{ $assignment->landlord->name }}</span>
                            </div>

                            <div class="info-item">
                                <span class="info-label">Contact Email:</span>
                                <span class="info-value">{{ $assignment->landlord->email }}</span>
                            </div>

                            @if($assignment->unit->apartment->contact_phone)
                            <div class="info-item">
                                <span class="info-label">Contact Phone:</span>
                                <span class="info-value">{{ $assignment->unit->apartment->contact_phone }}</span>
                            </div>
                            @endif

                            @if($assignment->unit->apartment->contact_person && $assignment->unit->apartment->contact_person !== $assignment->landlord->name)
                            <div class="info-item">
                                <span class="info-label">Property Manager:</span>
                                <span class="info-value">{{ $assignment->unit->apartment->contact_person }}</span>
                            </div>
                            @endif
                        </div>

                        @if($assignment->documents && $assignment->documents->count() > 0)
                        <div class="profile-card">
                            <h3 style="margin: 0 0 20px 0;">Lease Documents</h3>
                            
                            @foreach($assignment->documents as $document)
                            <div style="background: #f9fafb; padding: 16px; border-radius: 8px; margin-bottom: 12px;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div>
                                        <div style="font-weight: 600; color: #1f2937;">{{ ucfirst(str_replace('_', ' ', $document->document_type)) }}</div>
                                        <div style="color: #6b7280; font-size: 14px;">{{ $document->file_name }}</div>
                                    </div>
                                    <div style="text-align: right;">
                                        <div style="color: #4b5563; font-size: 12px;">
                                            <span class="status-badge status-{{ $document->verification_status === 'verified' ? 'active' : ($document->verification_status === 'rejected' ? 'inactive' : 'pending') }}">
                                                {{ ucfirst($document->verification_status) }}
                                            </span>
                                        </div>
                                        <div style="color: #4b5563; font-size: 12px;">{{ $document->created_at->format('M d, Y') }}</div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>

                @else
                <div class="profile-card">
                    <h3 style="margin: 0 0 20px 0;">No Active Lease</h3>
                    <p style="color: #6b7280; margin: 0;">You currently don't have an active lease assignment. Please contact your landlord or property manager for assistance.</p>
                </div>
                @endif
            </div>

    <style>
        .tenant-nav-item.active { background: #10b981; color: white; }
        .tenant-nav-item:hover { background: rgba(16, 185, 129, 0.1); color: #10b981; }
        .tenant-header { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; }
        .tenant-btn-primary { background: #10b981; color: white; border: none; padding: 12px 24px; border-radius: 6px; cursor: pointer; transition: all 0.2s ease; }
        .tenant-btn-primary:hover { background: #059669; }
        .profile-card { background: white; border-radius: 12px; padding: 24px; margin-bottom: 24px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05); }
        .info-item { display: flex; justify-content: space-between; margin-bottom: 12px; padding: 12px 0; border-bottom: 1px solid #f3f4f6; }
        .info-item:last-child { border-bottom: none; }
        .info-label { color: #6b7280; font-weight: 500; }
        .info-value { color: #1f2937; font-weight: 600; }
        .status-badge { padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase; }
        .status-active { background: #d1fae5; color: #065f46; }
        .status-inactive { background: #fee2e2; color: #dc2626; }
        .lease-info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; }
        .lease-info-item { background: #f9fafb; padding: 16px; border-radius: 8px; text-align: center; }
        .lease-info-value { font-size: 24px; font-weight: 700; color: #10b981; margin-bottom: 4px; }
        .lease-info-label { color: #6b7280; font-size: 14px; }
    </style>
@endsection