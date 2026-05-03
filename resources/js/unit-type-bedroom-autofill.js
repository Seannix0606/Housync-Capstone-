function bedroomMap() {
    return window.__UNIT_TYPE_DEFAULT_BEDROOMS__ || {};
}

/**
 * When unit type changes, set bedrooms from the server-provided map.
 * Manual edits are preserved until the user changes unit type again.
 *
 * @param {HTMLSelectElement} unitTypeSelect
 * @param {HTMLInputElement} bedroomsInput
 */
export function attachUnitTypeBedroomAutofill(unitTypeSelect, bedroomsInput) {
    if (!unitTypeSelect || !bedroomsInput) {
        return;
    }

    const map = bedroomMap();

    unitTypeSelect.addEventListener('change', () => {
        const t = unitTypeSelect.value;
        if (Object.prototype.hasOwnProperty.call(map, t)) {
            bedroomsInput.value = String(map[t]);
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const pairs = [
        ['unit_type', 'bedrooms'],
        ['default_unit_type', 'default_bedrooms'],
        ['addUnitBulkDefaultUnitType', 'addUnitBulkDefaultBedrooms'],
    ];

    for (const [selectId, bedId] of pairs) {
        const sel = document.getElementById(selectId);
        const bed = document.getElementById(bedId);
        attachUnitTypeBedroomAutofill(sel, bed);
    }
});

window.attachUnitTypeBedroomAutofill = attachUnitTypeBedroomAutofill;
