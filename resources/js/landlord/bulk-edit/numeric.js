/**
 * Unit numbering helpers — pure functions (single responsibility).
 */
export function getNextAvailableUnitNumber(floor, existingUnitsList) {
    let unitNumber = 1;
    while (true) {
        const paddedUnit = String(unitNumber).padStart(2, '0');
        const fullUnitNumber = `${floor}${paddedUnit}`;
        if (!existingUnitsList.includes(fullUnitNumber)) {
            return fullUnitNumber;
        }
        unitNumber++;
        if (unitNumber > 99) {
            return `${floor}99`;
        }
    }
}

export function incrementUnitNumber(currentNumber) {
    const match = String(currentNumber).match(/^(\d+)(\d{2})$/);
    if (match) {
        const floorPart = match[1];
        const unit = parseInt(match[2], 10) + 1;
        return floorPart + String(unit).padStart(2, '0');
    }
    return `${currentNumber}1`;
}
