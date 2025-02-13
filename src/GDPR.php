<?php
declare(strict_types=1);

namespace Gdbots\Bundle\AppBundle;

final class GDPR
{
    // https://gist.github.com/henrik/1688572
    // https://stackoverflow.com/questions/50465813/european-union-specific-iso-1366-1-country-codes-for-geoip
    private const array COUNTRIES = [
        'AT' => true,
        'BE' => true,
        'BG' => true,
        'CY' => true,
        'CZ' => true,
        'DE' => true,
        'DK' => true,
        'EE' => true,
        'EL' => true,
        'ES' => true,
        'FI' => true,
        'FR' => true,
        'GB' => true,
        'GR' => true,
        'HR' => true,
        'HU' => true,
        'IE' => true,
        'IT' => true,
        'LT' => true,
        'LU' => true,
        'LV' => true,
        'MT' => true,
        'NL' => true,
        'PL' => true,
        'PT' => true,
        'RO' => true,
        'SE' => true,
        'SI' => true,
        'SK' => true,
    ];

    public static function applies(string $country): bool
    {
        return isset(self::COUNTRIES[strtoupper(trim($country))]);
    }
}
