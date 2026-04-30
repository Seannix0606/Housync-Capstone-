/**
 * DOM builders for dynamically inserted rows / floors — avoids unsafe innerHTML.
 */

const UNIT_TYPES = [
    ['studio', 'Studio'],
    ['one_bedroom', 'One Bedroom'],
    ['two_bedroom', 'Two Bedroom'],
    ['three_bedroom', 'Three Bedroom'],
    ['penthouse', 'Penthouse'],
];

const STATUS_OPTS = [
    ['available', 'Available'],
    ['maintenance', 'Maintenance'],
];

const LEASING_OPTS = [
    ['separate', 'Separate'],
    ['inclusive', 'Inclusive'],
];

export function buildUnitRowCellsFragment(unitId, _floor, d) {
    const frag = document.createDocumentFragment();

    const tdNum = document.createElement('td');
    const inpNum = document.createElement('input');
    inpNum.type = 'text';
    inpNum.setAttribute('name', `units[${unitId}][unit_number]`);
    inpNum.className = 'form-control form-control-sm';
    inpNum.value = d.unit_number != null ? String(d.unit_number) : '';
    inpNum.required = true;
    tdNum.appendChild(inpNum);
    frag.appendChild(tdNum);

    const tdType = document.createElement('td');
    const selType = document.createElement('select');
    selType.setAttribute('name', `units[${unitId}][unit_type]`);
    selType.className = 'form-control form-control-sm';
    selType.required = true;
    for (const [val, label] of UNIT_TYPES) {
        const o = document.createElement('option');
        o.value = val;
        o.textContent = label;
        if (d.unit_type === val) o.selected = true;
        selType.appendChild(o);
    }
    tdType.appendChild(selType);
    frag.appendChild(tdType);

    const tdBeds = document.createElement('td');
    tdBeds.className = 'text-center';
    const wrapBeds = document.createElement('div');
    wrapBeds.className = 'd-flex justify-content-center align-items-center gap-2';
    const inpBed = document.createElement('input');
    inpBed.type = 'number';
    inpBed.setAttribute('name', `units[${unitId}][bedrooms]`);
    inpBed.className = 'form-control form-control-sm';
    inpBed.value = String(d.bedrooms ?? '');
    inpBed.min = '0';
    inpBed.max = '10';
    inpBed.required = true;
    inpBed.style.width = '60px';
    const sep = document.createElement('span');
    sep.className = 'text-muted';
    sep.textContent = '/';
    const inpBath = document.createElement('input');
    inpBath.type = 'number';
    inpBath.setAttribute('name', `units[${unitId}][bathrooms]`);
    inpBath.className = 'form-control form-control-sm';
    inpBath.value = String(d.bathrooms ?? '');
    inpBath.min = '1';
    inpBath.max = '10';
    inpBath.required = true;
    inpBath.style.width = '60px';
    wrapBeds.appendChild(inpBed);
    wrapBeds.appendChild(sep);
    wrapBeds.appendChild(inpBath);
    tdBeds.appendChild(wrapBeds);
    frag.appendChild(tdBeds);

    const tdRent = document.createElement('td');
    tdRent.className = 'text-end';
    const inpRent = document.createElement('input');
    inpRent.type = 'number';
    inpRent.setAttribute('name', `units[${unitId}][rent_amount]`);
    inpRent.className = 'form-control form-control-sm';
    inpRent.value = String(d.rent_amount ?? '');
    inpRent.min = '0';
    inpRent.step = '100';
    inpRent.required = true;
    tdRent.appendChild(inpRent);
    frag.appendChild(tdRent);

    const tdStat = document.createElement('td');
    tdStat.className = 'text-center';
    const selStat = document.createElement('select');
    selStat.setAttribute('name', `units[${unitId}][status]`);
    selStat.className = 'form-control form-control-sm';
    selStat.required = true;
    for (const [val, label] of STATUS_OPTS) {
        const o = document.createElement('option');
        o.value = val;
        o.textContent = label;
        if (d.status === val) o.selected = true;
        selStat.appendChild(o);
    }
    tdStat.appendChild(selStat);
    frag.appendChild(tdStat);

    const tdOcc = document.createElement('td');
    tdOcc.className = 'text-center';
    const inpOcc = document.createElement('input');
    inpOcc.type = 'number';
    inpOcc.setAttribute('name', `units[${unitId}][max_occupants]`);
    inpOcc.className = 'form-control form-control-sm';
    inpOcc.value = String(d.max_occupants ?? '');
    inpOcc.min = '1';
    inpOcc.max = '20';
    inpOcc.required = true;
    tdOcc.appendChild(inpOcc);
    frag.appendChild(tdOcc);

    const tdLease = document.createElement('td');
    tdLease.className = 'text-center';
    const selLease = document.createElement('select');
    selLease.setAttribute('name', `units[${unitId}][leasing_type]`);
    selLease.className = 'form-control form-control-sm';
    selLease.required = true;
    for (const [val, label] of LEASING_OPTS) {
        const o = document.createElement('option');
        o.value = val;
        o.textContent = label;
        if (d.leasing_type === val) o.selected = true;
        selLease.appendChild(o);
    }
    tdLease.appendChild(selLease);
    frag.appendChild(tdLease);

    const tdAct = document.createElement('td');
    tdAct.className = 'text-center';
    const group = document.createElement('div');
    group.className = 'btn-group';
    group.setAttribute('role', 'group');

    const mkBtn = (className, iconClass, title, handler) => {
        const b = document.createElement('button');
        b.type = 'button';
        b.className = `btn btn-sm ${className}`;
        b.title = title;
        b.setAttribute('aria-label', title);
        const i = document.createElement('i');
        i.className = iconClass;
        i.setAttribute('aria-hidden', 'true');
        b.appendChild(i);
        b.addEventListener('click', handler);
        return b;
    };

    group.appendChild(
        mkBtn('btn-outline-primary', 'fas fa-edit', 'Edit Unit', () =>
            window.editUnit(unitId),
        ),
    );
    group.appendChild(
        mkBtn('btn-outline-success', 'fas fa-copy', 'Duplicate Unit', () =>
            window.duplicateUnit(unitId),
        ),
    );
    group.appendChild(
        mkBtn('btn-outline-danger', 'fas fa-trash', 'Remove Unit', () =>
            window.removeUnit(unitId),
        ),
    );
    tdAct.appendChild(group);
    frag.appendChild(tdAct);

    return frag;
}

export function appendHiddenInputs(container, unitId, floor, defaultData) {
    const hFloor = document.createElement('input');
    hFloor.type = 'hidden';
    hFloor.setAttribute('name', `units[${unitId}][floor_number]`);
    hFloor.value = String(floor);

    const hFurn = document.createElement('input');
    hFurn.type = 'hidden';
    hFurn.setAttribute('name', `units[${unitId}][is_furnished]`);
    hFurn.value = defaultData.is_furnished ? '1' : '0';

    container.appendChild(hFloor);
    container.appendChild(hFurn);
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
