/**
 * HTML fragments for dynamically inserted rows / floors — presentation only.
 */

export function buildUnitRowMarkup(unitId, floor, d) {
    return `
        <td>
            <input type="text" name="units[${unitId}][unit_number]" class="form-control form-control-sm" value="${d.unit_number}" required>
        </td>
        <td>
            <select name="units[${unitId}][unit_type]" class="form-control form-control-sm" required>
                <option value="studio" ${d.unit_type === 'studio' ? 'selected' : ''}>Studio</option>
                <option value="one_bedroom" ${d.unit_type === 'one_bedroom' ? 'selected' : ''}>One Bedroom</option>
                <option value="two_bedroom" ${d.unit_type === 'two_bedroom' ? 'selected' : ''}>Two Bedroom</option>
                <option value="three_bedroom" ${d.unit_type === 'three_bedroom' ? 'selected' : ''}>Three Bedroom</option>
                <option value="penthouse" ${d.unit_type === 'penthouse' ? 'selected' : ''}>Penthouse</option>
            </select>
        </td>
        <td class="text-center">
            <div class="d-flex justify-content-center align-items-center gap-2">
                <input type="number" name="units[${unitId}][bedrooms]" class="form-control form-control-sm" value="${d.bedrooms}" min="0" max="10" required style="width: 60px;">
                <span class="text-muted">/</span>
                <input type="number" name="units[${unitId}][bathrooms]" class="form-control form-control-sm" value="${d.bathrooms}" min="1" max="10" required style="width: 60px;">
            </div>
        </td>
        <td class="text-end">
            <input type="number" name="units[${unitId}][rent_amount]" class="form-control form-control-sm" value="${d.rent_amount}" min="0" step="100" required>
        </td>
        <td class="text-center">
            <select name="units[${unitId}][status]" class="form-control form-control-sm" required>
                <option value="available" ${d.status === 'available' ? 'selected' : ''}>Available</option>
                <option value="maintenance" ${d.status === 'maintenance' ? 'selected' : ''}>Maintenance</option>
            </select>
        </td>
        <td class="text-center">
            <input type="number" name="units[${unitId}][max_occupants]" class="form-control form-control-sm" value="${d.max_occupants}" min="1" max="20" required>
        </td>
        <td class="text-center">
            <select name="units[${unitId}][leasing_type]" class="form-control form-control-sm" required>
                <option value="separate" ${d.leasing_type === 'separate' ? 'selected' : ''}>Separate</option>
                <option value="inclusive" ${d.leasing_type === 'inclusive' ? 'selected' : ''}>Inclusive</option>
            </select>
        </td>
        <td class="text-center">
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="editUnit('${unitId}')" title="Edit Unit">
                    <i class="fas fa-edit"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-success" onclick="duplicateUnit('${unitId}')" title="Duplicate Unit">
                    <i class="fas fa-copy"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeUnit('${unitId}')" title="Remove Unit">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </td>
    `;
}

export function buildHiddenInputsMarkup(unitId, floor, defaultData) {
    return `
        <input type="hidden" name="units[${unitId}][floor_number]" value="${floor}">
        <input type="hidden" name="units[${unitId}][is_furnished]" value="${defaultData.is_furnished ? 1 : 0}">
    `;
}

export function buildNewFloorSectionMarkup(newFloor) {
    return `
        <div class="floor-header">
            <button type="button"
                class="floor-collapse-trigger"
                id="floor-trigger-${newFloor}"
                onclick="toggleFloorPanel(${newFloor})"
                aria-expanded="true"
                aria-controls="floor-panel-${newFloor}">
                <span class="floor-chevron" aria-hidden="true"><i class="fas fa-chevron-down"></i></span>
                <span class="floor-title-group">
                    <span class="floor-title">
                        <span class="floor-card-heading">Floor </span><span class="floor-card-digit">${newFloor}</span>
                    </span>
                    <span class="floor-unit-count" id="floor-${newFloor}-count">0 units</span>
                </span>
            </button>
            <div class="floor-controls">
                <button type="button" class="btn btn-sm btn-outline" onclick="addUnitToFloor(${newFloor})" title="Add unit">
                    <i class="fas fa-plus" aria-hidden="true"></i><span class="floor-btn-label"> Add Unit</span>
                </button>
                <button type="button" class="btn btn-sm btn-outline" onclick="removeFloor(${newFloor})" style="color: #ef4444;" title="Remove floor">
                    <i class="fas fa-trash" aria-hidden="true"></i><span class="floor-btn-label"> Remove Floor</span>
                </button>
            </div>
        </div>
        <div class="floor-section-body" id="floor-panel-${newFloor}" role="region" aria-labelledby="floor-trigger-${newFloor}">
            <div class="floor-section-inner">
                <div class="table-responsive">
                    <table class="table table-hover" id="floor-${newFloor}-units-table">
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
                        <tbody id="floor-${newFloor}-units">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    `;
}
