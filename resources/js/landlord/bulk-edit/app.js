import { getNextAvailableUnitNumber, incrementUnitNumber } from './numeric.js';
import {
    buildUnitRowCellsFragment,
    appendHiddenInputs,
    buildNewFloorSectionMarkup,
} from './templates.js';

/**
 * Bulk edit units page controller — orchestrates UI + form behavior.
 * Globals mirror legacy inline script names so Blade onclick stays unchanged.
 */
export function mountBulkEditPage(rawConfig) {
    const config = {
        totalFloors: Number(rawConfig.totalFloors) || 1,
        propertyType: String(rawConfig.propertyType ?? ''),
        bulkParams: rawConfig.bulkParams ?? {},
        apartmentBedrooms: Number(rawConfig.apartmentBedrooms) || 1,
        existingUnitNumbers: Array.isArray(rawConfig.existingUnitNumbers)
            ? [...rawConfig.existingUnitNumbers]
            : [],
        existingUnitsCount: Number(rawConfig.existingUnitsCount) || 0,
    };

    let existingUnits = [...config.existingUnitNumbers];
    let unitIdCounter = 0;

    function showLoadingMessage(message, progress = '') {
        const modal = document.getElementById('loadingModal');
        const messageEl = document.getElementById('loadingMessage');
        const progressEl = document.getElementById('loadingProgress');
        if (modal && messageEl) {
            messageEl.textContent = message;
            if (progressEl) progressEl.textContent = progress;
            modal.style.display = 'flex';
        }
    }

    function hideLoadingMessage() {
        const modal = document.getElementById('loadingModal');
        if (modal) modal.style.display = 'none';
    }

    function updateLoadingProgress(current, total) {
        const progressEl = document.getElementById('loadingProgress');
        if (progressEl) {
            const percentage = Math.round((current / total) * 100);
            progressEl.textContent = `${current} / ${total} units created (${percentage}%)`;
        }
    }

    function syncExpandAllMode(enabled) {
        const stack = document.getElementById('floorsContainer');
        if (!stack) return;
        stack.classList.toggle('floors-detail-stack--expand-all', !!enabled);
    }

    function clearFloorPickerActive() {
        document.querySelectorAll('.floor-picker-tile').forEach((tile) => {
            tile.classList.remove('floor-picker-tile--active');
            tile.setAttribute('aria-pressed', 'false');
        });
    }

    function setActiveFloorPicker(floor) {
        document.querySelectorAll('.floor-picker-tile').forEach((tile) => {
            const active = tile.dataset.floor === String(floor);
            tile.classList.toggle('floor-picker-tile--active', active);
            tile.setAttribute('aria-pressed', active ? 'true' : 'false');
        });
    }

    function selectFloorTile(floor) {
        openFloorAccordion(floor, true);
    }

    function openFloorAccordion(floor, scrollIntoViewFlag = true) {
        syncExpandAllMode(false);
        const section = document.querySelector(`.floor-section[data-floor="${floor}"]`);
        if (!section) return;
        document.querySelectorAll('.floor-section').forEach((el) => {
            el.classList.add('floor-section--collapsed');
            const b = el.querySelector('.floor-collapse-trigger');
            if (b) b.setAttribute('aria-expanded', 'false');
        });
        section.classList.remove('floor-section--collapsed');
        const btn = section.querySelector('.floor-collapse-trigger');
        if (btn) btn.setAttribute('aria-expanded', 'true');
        setActiveFloorPicker(floor);
        if (scrollIntoViewFlag) {
            requestAnimationFrame(() =>
                section.scrollIntoView({ behavior: 'smooth', block: 'start' }),
            );
        }
    }

    function toggleFloorPanel(floor) {
        const section = document.querySelector(`.floor-section[data-floor="${floor}"]`);
        if (!section) return;
        const wasCollapsed = section.classList.contains('floor-section--collapsed');

        if (wasCollapsed) {
            openFloorAccordion(floor, true);
        } else {
            section.classList.add('floor-section--collapsed');
            const btn = section.querySelector('.floor-collapse-trigger');
            if (btn) btn.setAttribute('aria-expanded', 'false');
            syncExpandAllMode(false);
            clearFloorPickerActive();
        }
    }

    function updateFloorBadgeCounts() {
        document.querySelectorAll('.floor-section').forEach((section) => {
            const floor = section.dataset.floor;
            const countEl = document.getElementById(`floor-${floor}-count`);
            const pickerCount = document.getElementById(`floor-picker-${floor}-count`);
            const n = section.querySelectorAll('.unit-row').length;
            const label = n === 1 ? '1 unit' : `${n} units`;
            if (countEl) countEl.textContent = label;
            if (pickerCount) pickerCount.textContent = label;
        });
    }

    function updateStats() {
        const totalUnits = document.querySelectorAll('.unit-row').length;
        const totalFloorsCount = document.querySelectorAll('.floor-section').length;
        const avgUnitsPerFloor =
            totalFloorsCount > 0 ? Math.round(totalUnits / totalFloorsCount) : 0;

        const elUnits = document.getElementById('totalUnits');
        const elFloors = document.getElementById('totalFloors');
        const elAvg = document.getElementById('avgUnitsPerFloor');
        if (elUnits) elUnits.textContent = totalUnits;
        if (elFloors) elFloors.textContent = totalFloorsCount;
        if (elAvg) elAvg.textContent = avgUnitsPerFloor;
        updateFloorBadgeCounts();
    }

    function getMaxFloorNumber() {
        let max = 0;
        document.querySelectorAll('.floor-section').forEach((s) => {
            const n = parseInt(s.dataset.floor, 10);
            if (!Number.isNaN(n) && n > max) max = n;
        });
        return max;
    }

    function getReferenceFloorSection() {
        const expanded = document.querySelector(
            '.floor-section:not(.floor-section--collapsed)',
        );
        if (expanded) return expanded;
        const activeTile = document.querySelector('.floor-picker-tile--active');
        if (activeTile && activeTile.dataset.floor) {
            return document.querySelector(
                `.floor-section[data-floor="${activeTile.dataset.floor}"]`,
            );
        }
        return document.querySelector('.floor-section');
    }

    function readUnitRowSnapshot(unitRow) {
        const data = {};
        unitRow.querySelectorAll('input, select').forEach((el) => {
            const m = el.name && el.name.match(/units\[([^\]]+)\]\[([^\]]+)\]/);
            if (!m) return;
            const field = m[2];
            if (field === 'unit_number' || field === 'floor_number') return;
            if (el.type === 'hidden' && field !== 'is_furnished') return;
            if (field === 'is_furnished') {
                data.is_furnished =
                    el.value === '1' || el.value === 'true' || el.checked === true;
                return;
            }
            if (el.type === 'checkbox') {
                data[field] = el.checked;
            } else {
                data[field] = el.value;
            }
        });
        return data;
    }

    function createFloorPickerTile(floorNum) {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'floor-picker-tile';
        btn.id = `floor-picker-tile-${floorNum}`;
        btn.dataset.floor = String(floorNum);
        btn.setAttribute('aria-label', `Floor ${floorNum}, edit units`);
        btn.setAttribute('aria-pressed', 'false');
        btn.onclick = () => selectFloorTile(floorNum);
        btn.innerHTML = `
        <span class="floor-picker-tile-inner">
            <span class="floor-picker-heading">Floor</span>
            <span class="floor-picker-digit">${floorNum}</span>
            <span class="floor-picker-count" id="floor-picker-${floorNum}-count">0 units</span>
        </span>
    `;
        return btn;
    }

    function addUnitToFloor(floor, unitData = null) {
        const section = document.querySelector(`.floor-section[data-floor="${floor}"]`);
        if (section && section.classList.contains('floor-section--collapsed')) {
            openFloorAccordion(floor, true);
        }

        const floorContainer = document.getElementById(`floor-${floor}-units`);

        if (!floorContainer) {
            console.error(`Floor container for floor ${floor} not found!`);
            return false;
        }

        const unitId = `unit-${floor}-${++unitIdCounter}`;

        const formExistingUnits = Array.from(
            document.querySelectorAll('input[name*="unit_number"]'),
        )
            .map((input) => input.value.trim())
            .filter((value) => value !== '');

        const allExistingUnits = [...existingUnits, ...formExistingUnits];

        const baseDefaults = {
            unit_number: null,
            unit_type: 'two_bedroom',
            rent_amount: 15000,
            bedrooms: 2,
            bathrooms: 1,
            status: 'available',
            leasing_type: 'separate',
            max_occupants: 4,
            is_furnished: false,
        };

        const merged = { ...baseDefaults, ...(unitData || {}) };

        const normalizedExistingUnits = allExistingUnits
            .map((value) => String(value ?? '').trim())
            .filter((value) => value !== '');

        let unitNum = merged.unit_number;
        if (unitNum != null && String(unitNum).trim() !== '') {
            unitNum = String(unitNum).trim();
            if (normalizedExistingUnits.includes(unitNum)) {
                unitNum = getNextAvailableUnitNumber(
                    String(floor),
                    normalizedExistingUnits,
                );
            }
        } else {
            unitNum = getNextAvailableUnitNumber(
                String(floor),
                normalizedExistingUnits,
            );
        }

        if (unitNum == null) {
            alert(
                'No available unit number on this floor (slots 01–99 are full). Remove a unit or pick another floor.',
            );
            return false;
        }

        merged.unit_number = unitNum;
        merged.is_furnished = !!merged.is_furnished;

        const unitRow = document.createElement('tr');
        unitRow.className = 'unit-row';
        unitRow.id = unitId;
        unitRow.appendChild(buildUnitRowCellsFragment(unitId, floor, merged));

        const hiddenCell = document.createElement('td');
        hiddenCell.style.display = 'none';
        appendHiddenInputs(hiddenCell, unitId, floor, merged);
        unitRow.appendChild(hiddenCell);

        floorContainer.appendChild(unitRow);
        updateStats();
        return true;
    }

    function editUnit(unitId) {
        const unitRow = document.getElementById(unitId);
        if (unitRow) unitRow.classList.toggle('table-warning');
    }

    function duplicateUnit(unitId) {
        const unitRow = document.getElementById(unitId);
        if (!unitRow) return;
        const floorEl = unitRow.closest('.floor-section');
        const floor = floorEl ? floorEl.dataset.floor : '1';

        const formData = new FormData();
        unitRow.querySelectorAll('input, select').forEach((input) => {
            formData.append(input.name, input.value);
        });

        const numInput = unitRow.querySelector('input[name*="unit_number"]');
        const currentNumber = numInput ? numInput.value : '';
        const newNumber = incrementUnitNumber(currentNumber);
        if (newNumber == null) {
            alert(
                'Cannot duplicate this unit number. Use a floor + two-digit pattern (e.g. 101) with unit part 01–98.',
            );
            return;
        }

        addUnitToFloor(parseInt(floor, 10), {
            unit_number: newNumber,
            unit_type: formData.get(`units[${unitId}][unit_type]`),
            rent_amount: formData.get(`units[${unitId}][rent_amount]`),
            bedrooms: formData.get(`units[${unitId}][bedrooms]`),
            bathrooms: formData.get(`units[${unitId}][bathrooms]`),
            status: formData.get(`units[${unitId}][status]`),
            leasing_type: formData.get(`units[${unitId}][leasing_type]`),
            max_occupants: formData.get(`units[${unitId}][max_occupants]`),
            is_furnished:
                formData.get(`units[${unitId}][is_furnished]`) === '1' ||
                formData.get(`units[${unitId}][is_furnished]`) === true,
        });
    }

    function removeUnit(unitId) {
        if (confirm('Are you sure you want to remove this unit?')) {
            const row = document.getElementById(unitId);
            if (row) row.remove();
            updateStats();
        }
    }

    function addNewFloor() {
        const floorsContainer = document.getElementById('floorsContainer');
        const pickerGrid = document.getElementById('floorPickerGrid');
        const newFloor = getMaxFloorNumber() + 1;

        if (pickerGrid) {
            pickerGrid.appendChild(createFloorPickerTile(newFloor));
        }

        const floorSection = document.createElement('div');
        floorSection.className = 'floor-section';
        floorSection.dataset.floor = String(newFloor);
        floorSection.innerHTML = buildNewFloorSectionMarkup(newFloor);

        floorsContainer.appendChild(floorSection);

        openFloorAccordion(newFloor, true);
        updateStats();
    }

    function removeFloor(floor) {
        if (confirm(`Are you sure you want to remove Floor ${floor} and all its units?`)) {
            const floorSection = document.querySelector(
                `.floor-section[data-floor="${floor}"]`,
            );
            const pickerTile = document.querySelector(
                `.floor-picker-tile[data-floor="${floor}"]`,
            );
            if (pickerTile) pickerTile.remove();
            if (floorSection) floorSection.remove();
            updateStats();
        }
    }

    function applyToAllFloors() {
        const sourceSection = getReferenceFloorSection();
        if (!sourceSection) {
            alert('No floor is available to copy from.');
            return;
        }
        const sourceFloor = sourceSection.dataset.floor;
        const sourceRows = Array.from(sourceSection.querySelectorAll('.unit-row'));
        if (sourceRows.length === 0) {
            alert('The current floor has no units to copy.');
            return;
        }
        if (
            !confirm(
                "Replace all units on every other floor with copies of this floor's settings? Unit numbers will be reassigned per floor.",
            )
        ) {
            return;
        }
        const templates = sourceRows.map((row) => readUnitRowSnapshot(row));
        document.querySelectorAll('.floor-section').forEach((section) => {
            const f = section.dataset.floor;
            if (f === sourceFloor) return;
            section.querySelectorAll('.unit-row').forEach((r) => r.remove());
            templates.forEach((t) => {
                addUnitToFloor(parseInt(f, 10), { ...t });
            });
        });
        updateStats();
    }

    function duplicateFloor() {
        const sourceSection = getReferenceFloorSection();
        if (!sourceSection) {
            alert('No floor is available to duplicate.');
            return;
        }
        const pickerGrid = document.getElementById('floorPickerGrid');
        const newFloor = getMaxFloorNumber() + 1;

        if (pickerGrid) {
            pickerGrid.appendChild(createFloorPickerTile(newFloor));
        }

        const floorSection = document.createElement('div');
        floorSection.className = 'floor-section';
        floorSection.dataset.floor = String(newFloor);
        floorSection.innerHTML = buildNewFloorSectionMarkup(newFloor);
        sourceSection.insertAdjacentElement('afterend', floorSection);

        const templates = Array.from(sourceSection.querySelectorAll('.unit-row')).map(
            readUnitRowSnapshot,
        );
        templates.forEach((t) => addUnitToFloor(newFloor, { ...t }));

        openFloorAccordion(newFloor, true);
        updateStats();
    }

    function finalizeUnits() {
        const form = document.getElementById('bulkEditForm');
        if (!form) return;

        const allUnitInputs = form.querySelectorAll(
            'input[name*="units["], select[name*="units["]',
        );

        const unitsMap = new Map();

        allUnitInputs.forEach((input) => {
            const name = input.name;
            const match = name.match(/units\[([^\]]+)\]\[([^\]]+)\]/);

            if (match) {
                const unitIdKey = match[1];
                const fieldName = match[2];
                const value =
                    input.type === 'checkbox' ? input.checked : input.value;

                if (!unitsMap.has(unitIdKey)) {
                    unitsMap.set(unitIdKey, {});
                }

                unitsMap.get(unitIdKey)[fieldName] = value;
            }
        });

        const units = [];

        unitsMap.forEach((unitData) => {
            if (unitData.unit_number && String(unitData.unit_number).trim() !== '') {
                if (!unitData.floor_number) {
                    const unitNum = unitData.unit_number.toString();
                    if (unitNum.length >= 3) {
                        unitData.floor_number = parseInt(
                            unitNum.substring(0, unitNum.length - 2),
                            10,
                        );
                    } else {
                        unitData.floor_number = 1;
                    }
                }

                const rentRaw = String(unitData.rent_amount ?? '').trim();
                if (rentRaw !== '') {
                    const parsedRent = parseFloat(rentRaw);
                    unitData.rent_amount = Number.isFinite(parsedRent)
                        ? parsedRent
                        : null;
                } else {
                    unitData.rent_amount = null;
                }

                const bedroomsRaw = String(unitData.bedrooms ?? '').trim();
                if (bedroomsRaw !== '') {
                    const parsedBedrooms = parseInt(bedroomsRaw, 10);
                    unitData.bedrooms = Number.isFinite(parsedBedrooms)
                        ? parsedBedrooms
                        : null;
                } else {
                    unitData.bedrooms = null;
                }

                const bathroomsRaw = String(unitData.bathrooms ?? '').trim();
                if (bathroomsRaw !== '') {
                    const parsedBathrooms = parseInt(bathroomsRaw, 10);
                    unitData.bathrooms = Number.isFinite(parsedBathrooms)
                        ? parsedBathrooms
                        : null;
                } else {
                    unitData.bathrooms = null;
                }

                const maxOccupantsRaw = String(unitData.max_occupants ?? '').trim();
                if (maxOccupantsRaw !== '') {
                    const parsedMaxOccupants = parseInt(maxOccupantsRaw, 10);
                    unitData.max_occupants = Number.isFinite(parsedMaxOccupants)
                        ? parsedMaxOccupants
                        : null;
                } else {
                    unitData.max_occupants = null;
                }

                const furnishedRaw = unitData.is_furnished;
                if (furnishedRaw == null || String(furnishedRaw).trim() === '') {
                    unitData.is_furnished = null;
                } else {
                    unitData.is_furnished =
                        furnishedRaw === 'true' ||
                        furnishedRaw === true ||
                        furnishedRaw === '1';
                }

                units.push(unitData);
            }
        });

        if (units.length === 0) {
            alert('No units to create. Please add at least one unit with a unit number.');
            return;
        }

        const existingCount = config.existingUnitsCount;
        let confirmMessage = `Are you sure you want to create ${units.length} units?`;
        if (existingCount > 0) {
            confirmMessage += `\n\n⚠️ Note: This property already has ${existingCount} units. Units with duplicate numbers will be skipped.`;
        }
        confirmMessage += `\n\nUnits: ${units.map((u) => u.unit_number).join(', ')}`;

        if (!confirm(confirmMessage)) {
            return;
        }

        allUnitInputs.forEach((input) => input.remove());

        units.forEach((unit, index) => {
            Object.keys(unit).forEach((key) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = `units[${index}][${key}]`;
                const v = unit[key];
                input.value = v != null && v !== '' ? String(v) : '';
                form.appendChild(input);
            });
        });

        form.submit();
    }

    function initializeBulkEdit() {
        const totalFloors = config.totalFloors;
        const propertyType = config.propertyType;
        const bulkParams = config.bulkParams;
        const unitsPerFloor = bulkParams.units_per_floor || 4;
        const createAllBedrooms = bulkParams.create_all_bedrooms || false;
        const defaultUnitType = bulkParams.default_unit_type || 'two_bedroom';
        const defaultRent = bulkParams.default_rent || 15000;
        const defaultBedrooms = bulkParams.default_bedrooms || 2;
        const defaultBathrooms = bulkParams.default_bathrooms || 1;

        const totalUnitsToCreate =
            propertyType === 'house' && createAllBedrooms
                ? config.apartmentBedrooms
                : unitsPerFloor * totalFloors;

        if (totalUnitsToCreate > 50) {
            showLoadingMessage(`Creating ${totalUnitsToCreate} units, please wait...`);
        }

        if (propertyType === 'house' && createAllBedrooms) {
            const bedrooms = config.apartmentBedrooms;
            for (let i = 1; i <= bedrooms; i++) {
                addUnitToFloor(1, {
                    unit_number: `Bedroom ${i}`,
                    unit_type: defaultUnitType,
                    rent_amount: defaultRent,
                    bedrooms: defaultBedrooms,
                    bathrooms: defaultBathrooms,
                    status: 'available',
                    leasing_type: 'separate',
                    max_occupants: 4,
                    is_furnished: false,
                });
            }
            hideLoadingMessage();
        } else {
            const totalExpectedUnits = unitsPerFloor * totalFloors;
            let totalUnitsCreated = 0;

            for (let floor = 1; floor <= totalFloors; floor++) {
                const floorContainer = document.getElementById(`floor-${floor}-units`);
                if (!floorContainer) {
                    console.error(`Floor ${floor} container not found! Skipping...`);
                    continue;
                }

                for (let unit = 1; unit <= unitsPerFloor; unit++) {
                    const unitNumber = getNextAvailableUnitNumber(String(floor), existingUnits);
                    if (!unitNumber) {
                        console.error(
                            `No free unit number slot on floor ${floor}; stopping auto-create for this floor.`,
                        );
                        break;
                    }
                    const success = addUnitToFloor(floor, {
                        unit_number: unitNumber,
                        unit_type: defaultUnitType,
                        rent_amount: defaultRent,
                        bedrooms: defaultBedrooms,
                        bathrooms: defaultBathrooms,
                        status: 'available',
                        leasing_type: 'separate',
                        max_occupants: 4,
                        is_furnished: false,
                    });

                    if (success) {
                        totalUnitsCreated++;
                        if (totalExpectedUnits > 50 && totalUnitsCreated % 10 === 0) {
                            updateLoadingProgress(totalUnitsCreated, totalExpectedUnits);
                        }
                    }
                }
            }

            hideLoadingMessage();
        }

        updateStats();

        syncExpandAllMode(false);
        document.querySelectorAll('.floor-section').forEach((section) => {
            section.classList.add('floor-section--collapsed');
            const hdr = section.querySelector('.floor-collapse-trigger');
            if (hdr) hdr.setAttribute('aria-expanded', 'false');
        });
        clearFloorPickerActive();
    }

    window.selectFloorTile = selectFloorTile;
    window.toggleFloorPanel = toggleFloorPanel;
    window.addUnitToFloor = addUnitToFloor;
    window.removeFloor = removeFloor;
    window.editUnit = editUnit;
    window.duplicateUnit = duplicateUnit;
    window.removeUnit = removeUnit;
    window.addNewFloor = addNewFloor;
    window.applyToAllFloors = applyToAllFloors;
    window.duplicateFloor = duplicateFloor;
    window.finalizeUnits = finalizeUnits;

    function boot() {
        initializeBulkEdit();
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
}
