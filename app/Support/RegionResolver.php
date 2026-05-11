<?php

namespace App\Support;

use App\Models\Store;

class RegionResolver
{
    /**
     * Mapeia o código postal português para a zona Norte / Centro / Sul.
     * Convenção: Lisboa (1xxx) e Setúbal/Santarém/Leiria (2xxx) contam como Sul.
     */
    public static function fromPostalCode(?string $postalCode): ?string
    {
        if (!$postalCode) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $postalCode);
        if ($digits === '' || $digits === null) {
            return null;
        }

        $first = $digits[0];

        return match ($first) {
            '4', '5' => 'norte',
            '3', '6' => 'centro',
            '1', '2', '7', '8', '9' => 'sul',
            default => null,
        };
    }

    public static function fromStore(?Store $store): ?string
    {
        if (!$store) {
            return null;
        }

        return self::fromPostalCode($store->codigo_postal);
    }

    public static function fromStoreId(?int $storeId): ?string
    {
        if (!$storeId) {
            return null;
        }

        $store = Store::find($storeId);
        return self::fromStore($store);
    }
}
