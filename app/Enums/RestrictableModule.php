<?php

namespace App\Enums;

/**
 * Maps dunning restriction flag keys (stored in financial_settings.dunning_policies.restrictions)
 * to the module keys defined in config/modules.php.
 */
enum RestrictableModule: string
{
    case Ecosystem = 'restrict_ecosystem';
    case Pqrs = 'restrict_pqrs';
    case MovingPermits = 'restrict_moving_permits';
    case Amenities = 'restrict_amenities';

    /**
     * Returns the module registry key(s) this restriction flag maps to.
     *
     * @return list<string>
     */
    public function moduleKeys(): array
    {
        return match ($this) {
            self::Ecosystem => ['marketplace'],
            self::Pqrs => ['pqrs'],
            self::MovingPermits => ['resident_moves'],
            self::Amenities => [], // No amenity booking module yet — forward-compatible placeholder.
        };
    }

    /**
     * Derive the restriction flag key that covers the given module registry key.
     */
    public static function fromModuleKey(string $moduleKey): ?self
    {
        foreach (self::cases() as $case) {
            if (in_array($moduleKey, $case->moduleKeys(), true)) {
                return $case;
            }
        }

        return null;
    }
}
