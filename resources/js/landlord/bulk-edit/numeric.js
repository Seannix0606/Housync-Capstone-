/**
 * Unit numbering helpers — pure functions (single responsibility).
 */
export function getNextAvailableUnitNumber(floor, existingUnitsList) {
    const floorStr = String(floor);
    for (let unitNumber = 1; unitNumber <= 99; unitNumber++) {
        const paddedUnit = String(unitNumber).padStart(2, '0');
        const fullUnitNumber = `${floorStr}${paddedUnit}`;
        if (!existingUnitsList.includes(fullUnitNumber)) {
            return fullUnitNumber;
        }
    }
    return null;
}

export function incrementUnitNumber(currentNumber) {
    const match = String(currentNumber).match(/^(\d+)(\d{2})$/);
    if (!match) {
        return null;
    }
    const floorPart = match[1];
    const unit = parseInt(match[2], 10) + 1;
    if (unit > 99) {
        return null;
    }
    return floorPart + String(unit).padStart(2, '0');
}
