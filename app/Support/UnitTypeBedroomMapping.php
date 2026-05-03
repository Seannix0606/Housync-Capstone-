<?php

namespace App\Support;

final class UnitTypeBedroomMapping
{
    /**
     * @return array<string, int>
     */
    public static function defaultBedroomsByType(): array
    {
        return config('unit_types.default_bedrooms_by_type', []);
    }

    /**
     * @return list<string>
     */
    public static function allowedUnitTypeKeys(): array
    {
        return array_keys(self::defaultBedroomsByType());
    }

    public static function defaultBedroomsFor(string $unitType): ?int
    {
        $map = self::defaultBedroomsByType();

        if (! array_key_exists($unitType, $map)) {
            return null;
        }

        return (int) $map[$unitType];
    }

    /**
     * Soft consistency check: unknown types always pass.
     */
    public static function bedroomsMatchConfiguredDefaults(string $unitType, int $bedrooms): bool
    {
        $expected = self::defaultBedroomsFor($unitType);

        return $expected === null || $bedrooms === $expected;
    }
}
