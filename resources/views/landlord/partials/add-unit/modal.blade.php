{{-- Large modal: step-driven Add New Unit (single / bulk). Requires Bootstrap 5 JS. --}}
@push('styles')
<style>
    .add-unit-step-circle {
        width: 2.25rem;
        height: 2.25rem;
        border-radius: 50%;
        background: #e2e8f0;
        color: #64748b;
        font-weight: 600;
        font-size: 0.95rem;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background .2s, color .2s;
    }
    .add-unit-step.is-active .add-unit-step-circle {
        background: linear-gradient(135deg, #ea580c 0%, #dc2626 100%);
        color: #fff;
    }
    .add-unit-step.is-done .add-unit-step-circle {
        background: #22c55e;
        color: #fff;
    }
    .add-unit-mode-card {
        border: 2px solid #e2e8f0;
        border-radius: 0.75rem;
        padding: 1.25rem;
        cursor: pointer;
        transition: border-color .2s, box-shadow .2s;
        height: 100%;
    }
    .add-unit-mode-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.06);
    }
    .add-unit-mode-card.is-selected {
        border-color: #ea580c;
        box-shadow: 0 0 0 1px #ea580c;
    }
</style>
@endpush

<div class="modal fade" id="addUnitModal" tabindex="-1" aria-labelledby="addUnitModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <div>
                    <h5 class="modal-title mb-0" id="addUnitModalLabel">Add New Unit</h5>
                    <p class="small text-muted mb-0">Units</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @include('landlord.partials.add-unit.step-indicator')

                <div id="addUnitModalError" class="alert alert-danger py-2 small d-none" role="alert"></div>

                {{-- Step 1 --}}
                <div class="add-unit-panel" data-add-unit-panel="1">
                    <p class="text-muted small mb-3">Choose how you want to create units for your portfolio.</p>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="add-unit-mode-card" data-add-unit-mode="single" role="button" tabindex="0">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="rounded-circle bg-light d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                                        <i class="fas fa-door-open text-primary"></i>
                                    </span>
                                    <div>
                                        <div class="fw-semibold">Create Single Unit</div>
                                        <div class="small text-muted">One unit with name, price, and status.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="add-unit-mode-card" data-add-unit-mode="bulk" role="button" tabindex="0">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="rounded-circle bg-light d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                                        <i class="fas fa-layer-group text-primary"></i>
                                    </span>
                                    <div>
                                        <div class="fw-semibold">Create Multiple Units (Bulk)</div>
                                        <div class="small text-muted">Generate several units with a naming pattern.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Step 2 --}}
                <div class="add-unit-panel d-none" data-add-unit-panel="2">
                    <div class="mb-3">
                        <label class="form-label">Property</label>
                        <input type="text" class="form-control mb-2" id="addUnitPropertySearch" placeholder="Search properties by name…" autocomplete="off">
                        <select class="form-select" id="addUnitPropertyId" required>
                            <option value="">Select a property</option>
                            @foreach(($properties ?? $apartments ?? collect()) as $prop)
                                <option value="{{ $prop->id }}" data-search-label="{{ strtolower($prop->name) }}">{{ $prop->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div id="addUnitFieldsSingle">
                        <div class="mb-3">
                            <label class="form-label" for="addUnitNameSingle">Unit name</label>
                            <input type="text" class="form-control" id="addUnitNameSingle" maxlength="50" placeholder="e.g. 101 or Studio A">
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="addUnitPriceSingle">Price (monthly rent)</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" class="form-control" id="addUnitPriceSingle" min="0" step="0.01" placeholder="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="addUnitStatusSingle">Status</label>
                                <select class="form-select" id="addUnitStatusSingle">
                                    <option value="available">Available</option>
                                    <option value="maintenance">Maintenance</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div id="addUnitFieldsBulk" class="d-none">
                        <div class="mb-3">
                            <label class="form-label" for="addUnitCountBulk">Number of units</label>
                            <input type="number" class="form-control" id="addUnitCountBulk" min="1" max="200" value="2">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="addUnitPatternBulk">Naming pattern</label>
                            <input type="text" class="form-control" id="addUnitPatternBulk" value="Unit {n}" maxlength="120" placeholder="Unit {n}">
                            <div class="form-text">Use <code>{n}</code> for the sequence number (e.g. Unit 1, Unit 2). If you omit <code>{n}</code>, a number is appended automatically.</div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="addUnitPriceBulk">Default price</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" class="form-control" id="addUnitPriceBulk" min="0" step="0.01" placeholder="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="addUnitStatusBulk">Default status</label>
                                <select class="form-select" id="addUnitStatusBulk">
                                    <option value="available">Available</option>
                                    <option value="maintenance">Maintenance</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Step 3 --}}
                <div class="add-unit-panel d-none" data-add-unit-panel="3">
                    <p class="fw-semibold mb-2">Review your entries</p>
                    <div id="addUnitReviewBody" class="border rounded p-3 bg-light small"></div>
                    <p class="text-muted small mt-3 mb-0">Submitting will create the unit(s) on the selected property.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" id="addUnitBtnBack" disabled>Back</button>
                <button type="button" class="btn btn-primary" id="addUnitBtnNext">Next</button>
                <button type="button" class="btn btn-primary d-none" id="addUnitBtnSubmit">
                    <span class="add-unit-submit-label"><i class="fas fa-check me-1"></i> Confirm &amp; create</span>
                    <span class="add-unit-submit-spinner d-none"><i class="fas fa-spinner fa-spin me-1"></i> Creating…</span>
                </button>
            </div>
        </div>
    </div>
</div>
