<?php

namespace App\Support;

use Carbon\CarbonInterface;

class SriLankanDate
{
    public const TIMEZONE = 'Asia/Colombo';

    public const DATE_FORMAT = 'd/m/Y';

    public const DATETIME_FORMAT = 'd/m/Y, H:i';

    public const OFFSET = '+05:30';

    public static function timezone(): string
    {
        return config('app.timezone', self::TIMEZONE);
    }

    public static function datetime(?CarbonInterface $date, string $fallback = '—'): string
    {
        if (! $date) {
            return $fallback;
        }

        return $date->copy()
            ->timezone(self::timezone())
            ->format(self::DATETIME_FORMAT).' SLST';
    }

    public static function date(?CarbonInterface $date, string $fallback = '—'): string
    {
        if (! $date) {
            return $fallback;
        }

        return $date->copy()
            ->timezone(self::timezone())
            ->format(self::DATE_FORMAT);
    }

    public static function year(?CarbonInterface $date = null): int
    {
        return ($date ?? now())->copy()->timezone(self::timezone())->year;
    }
}
