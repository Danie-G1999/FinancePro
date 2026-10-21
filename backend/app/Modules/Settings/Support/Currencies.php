<?php

namespace App\Modules\Settings\Support;

/** Monedas que el usuario puede elegir como preferida (mismo listado que el frontend). */
final class Currencies
{
    public const DEFAULT = 'COP';

    public const SUPPORTED = [
        'COP' => 'Peso colombiano',
        'USD' => 'Dólar estadounidense',
        'EUR' => 'Euro',
        'MXN' => 'Peso mexicano',
    ];

    public static function codes(): array
    {
        return array_keys(self::SUPPORTED);
    }
}
