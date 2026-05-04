@extends('layouts.landlord-app')

@section('title', 'Edit Bulk Units')

@push('styles')
{{-- Critical layout if Vite CSS does not load: collapsed floors must stay hidden (picker-first UX). --}}
<style id="bulk-edit-units-critical-stack">
#floorsContainer.floors-detail-stack:not(.floors-detail-stack--expand-all) > .floor-section--collapsed{display:none!important;}
</style>
@vite(['resources/css/landlord/bulk-edit-units.css'])
@endpush

@section('content')
@php
    $structureStories = isset($apartment)
        ? max(1, (int) ($apartment->building_floors ?? $apartment->floors ?? 1))
        : 1;
@endphp
<!-- Loading Modal -->
<div id="loadingModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: white; padding: 2rem; border-radius: 12px; text-align: center; min-width: 300px;">
        <div class="spinner" style="margin: 0 auto 1rem;"></div>
        <h3 id="loadingMessage" style="margin: 0; color: #1e293b;">Loading...</h3>
        <p id="loadingProgress" style="margin: 0.5rem 0 0; color: #64748b;"></p>
    </div>
</div>

<div class="bulk-edit-container">
    <!-- Header -->
    <div class="content-header">
        <div>
            <h1>Edit Bulk Units</h1>
            <p style="color: #64748b; margin-top: 0.5rem;">Customize your generated units before finalizing</p>
        </div>
    </div>

    @if(isset($apartment))
    <!-- Property Info -->
    <div class="property-info" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.5rem; padding: 1rem; margin-bottom: 1.5rem;">
        <h4 style="color: #1e293b; margin-bottom: 0.5rem; font-size: 1.125rem;">{{ $apartment->name }}</h4>
        <p style="color: #64748b; margin: 0;"><strong>Type:</strong> {{ ucfirst($apartment->property_type) }} | 
           <strong>Building stories:</strong> {{ $structureStories }} | 
           <strong>Address:</strong> {{ $apartment->address }}</p>
    </div>
    @endif

    @if(isset($existingUnitsCount) && $existingUnitsCount > 0)
    <!-- Existing Units Warning -->
    <div style="background: #fef3c7; border: 1px solid #f59e0b; border-radius: 0.5rem; padding: 1rem; margin-bottom: 1.5rem;">
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <i class="fas fa-exclamation-triangle" style="color: #d97706; font-size: 1.25rem;"></i>
            <div>
                <h4 style="color: #92400e; margin: 0 0 0.25rem 0; font-size: 1rem; font-weight: 600;">This property already has {{ $existingUnitsCount }} units</h4>
                <p style="color: #92400e; margin: 0; font-size: 0.875rem;">Units with duplicate numbers will be skipped. Only new unit numbers will be created.</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Summary Stats -->
    <div class="summary-stats">
        <div class="stat-card">
            <div class="stat-number" id="totalUnits">0</div>
            <div class="stat-label">Total Units</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" id="totalFloors">{{ $structureStories }}</div>
            <div class="stat-label">Floors</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" id="avgUnitsPerFloor">0</div>
            <div class="stat-label">Avg Units/Floor</div>
        </div>
    </div>

    <!-- Bulk Actions -->
    <div class="bulk-actions">
        <h3 style="margin: 0 0 1rem 0; color: #1e293b;">Bulk Actions</h3>
        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
            <button type="button" class="btn btn-outline" onclick="applyToAllFloors()">
                <i class="fas fa-copy"></i> Apply to All Floors
            </button>
            <button type="button" class="btn btn-outline" onclick="duplicateFloor()">
                <i class="fas fa-clone"></i> Duplicate Floor
            </button>
            <button type="button" class="btn btn-outline" onclick="addNewFloor()">
                <i class="fas fa-plus"></i> Add Floor
            </button>
        </div>
    </div>

    <form method="POST" action="{{ route('landlord.finalize-bulk-units', $apartment->id) }}" id="bulkEditForm">
        @csrf

        <p class="floors-grid-intro">
            <i class="fas fa-layer-group" aria-hidden="true"></i>
            <span><strong>Floors:</strong> choose a floor to open its unit editor.</span>
        </p>

        <div id="floorPickerRail" class="floor-picker-rail">
            <div id="floorPickerGrid" class="floors-grid floors-grid--picker">
                @for($floor = 1; $floor <= $structureStories; $floor++)
                <button type="button"
                    id="floor-picker-tile-{{ $floor }}"
                    class="floor-picker-tile"
                    data-floor="{{ $floor }}"
                    onclick="selectFloorTile({{ $floor }})"
                    aria-label="Floor {{ $floor }}, edit units"
                    aria-pressed="false">
                    <span class="floor-picker-tile-inner">
                        <span class="floor-picker-heading">Floor</span>
                        <span class="floor-picker-digit">{{ $floor }}</span>
                        <span class="floor-picker-count" id="floor-picker-{{ $floor }}-count">0 units</span>
                    </span>
                </button>
                @endfor
            </div>
        </div>

        <!-- Floors Container -->
        <div id="floorsContainer" class="floors-detail-stack">
            @for($floor = 1; $floor <= $structureStories; $floor++)
            <div class="floor-section floor-section--collapsed" data-floor="{{ $floor }}">
                <div class="floor-header">
                    <button type="button"
                        class="floor-collapse-trigger"
                        id="floor-trigger-{{ $floor }}"
                        onclick="toggleFloorPanel({{ $floor }})"
                        aria-expanded="false"
                        aria-controls="floor-panel-{{ $floor }}">
                        <span class="floor-chevron" aria-hidden="true"><i class="fas fa-chevron-down"></i></span>
                        <span class="floor-title-group">
                            <span class="floor-title">
                                <span class="floor-card-heading">Floor </span><span class="floor-card-digit">{{ $floor }}</span>
                            </span>
                            <span class="floor-unit-count" id="floor-{{ $floor }}-count">0 units</span>
                        </span>
                    </button>
                    <div class="floor-controls">
                        @if(($bulkNewUnitsRemaining ?? null) === null || ($bulkNewUnitsRemaining ?? 0) > 0)
                        <button type="button" class="btn btn-sm btn-outline" onclick="addUnitToFloor({{ $floor }})" title="Add unit">
                            <i class="fas fa-plus" aria-hidden="true"></i><span class="floor-btn-label"> Add Unit</span>
                        </button>
                        @endif
                        <button type="button" class="btn btn-sm btn-outline" onclick="removeFloor({{ $floor }})" style="color: #ef4444;" title="Remove floor">
                            <i class="fas fa-trash" aria-hidden="true"></i><span class="floor-btn-label"> Remove Floor</span>
                        </button>
                    </div>
                </div>

                <div class="floor-section-body" id="floor-panel-{{ $floor }}" role="region" aria-labelledby="floor-trigger-{{ $floor }}">
                    <div class="floor-section-inner">
                        <div class="table-responsive">
                            <table class="table table-hover" id="floor-{{ $floor }}-units-table">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 12%;">Unit Number</th>
                                        <th style="width: 15%;">Unit Type</th>
                                        <th style="width: 12%;" class="text-center">Beds / Baths</th>
                                        <th style="width: 12%;" class="text-end">Rent (₱)</th>
                                        <th style="width: 10%;" class="text-center">Status</th>
                                        <th style="width: 10%;" class="text-center">Max Occupants</th>
                                        <th style="width: 12%;" class="text-center">Leasing Type</th>
                                        <th style="width: 17%;" class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="floor-{{ $floor }}-units">
                                    <!-- Units will be populated by JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            @endfor
        </div>

        <!-- Form Actions -->
        <div style="display: flex; gap: 1rem; justify-content: flex-end; padding-top: 2rem; border-top: 1px solid #e5e7eb; margin-top: 2rem;">
            <a href="{{ route('landlord.units', $apartment->id) }}" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Cancel
            </a>
            <button type="button" class="btn btn-primary" onclick="finalizeUnits()">
                <i class="fas fa-save"></i> Finalize Units
            </button>
        </div>
    </form>
</div>

@endsection

@push('scripts')
@php
    $bulkEditPageConfig = [
        'totalFloors' => $structureStories,
        'propertyType' => $apartment->property_type,
        'bulkParams' => $bulkParams ?? [],
        'apartmentBedrooms' => $apartment->bedrooms ?? 1,
        'existingUnitNumbers' => $apartment->units
            ? $apartment->units->pluck('unit_number')->values()->all()
            : [],
        'existingUnitsCount' => $existingUnitsCount ?? 0,
    ];
@endphp
<script type="application/json" id="bulk-edit-config">@json($bulkEditPageConfig)</script>
@vite(['resources/js/landlord/bulk-edit-units.js'])
@endpush
