<?php

namespace App\Enums;

enum BillPaymentMethod: string
{
    case Cash = 'cash';
    case Instapay = 'instapay';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Cash',
            self::Instapay => 'InstaPay',
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(
            static fn (self $method): string => $method->value,
            self::cases(),
        );
    }

    public static function validationInRule(): string
    {
        return 'in:'.implode(',', self::values());
    }
}
