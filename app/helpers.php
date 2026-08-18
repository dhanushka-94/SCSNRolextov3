<?php

use App\Support\SriLankanDate;
use Carbon\CarbonInterface;

if (! function_exists('sl_datetime')) {
    function sl_datetime(?CarbonInterface $date, string $fallback = '—'): string
    {
        return SriLankanDate::datetime($date, $fallback);
    }
}

if (! function_exists('sl_date')) {
    function sl_date(?CarbonInterface $date, string $fallback = '—'): string
    {
        return SriLankanDate::date($date, $fallback);
    }
}
