<?php

namespace App\Support;

class LocationCode
{
    /**
     * Build a short 4-character code from a place name.
     *
     * @param  list<string>  $taken
     */
    public static function fromName(string $name, array $taken = []): string
    {
        $letters = preg_replace('/[^A-Za-z0-9]/', '', $name) ?: 'CODE';
        $base = self::normalize(substr(str_pad($letters, 4, 'X'), 0, 4));
        $candidate = $base;
        $n = 2;

        $takenLookup = array_map(static fn (string $code) => strtolower($code), $taken);

        while (in_array(strtolower($candidate), $takenLookup, true)) {
            $suffix = (string) $n;
            $candidate = self::normalize(substr($base, 0, max(1, 4 - strlen($suffix))).$suffix);
            $n++;
        }

        return $candidate;
    }

    public static function normalize(string $code): string
    {
        $code = preg_replace('/[^A-Za-z0-9]/', '', $code) ?: 'XXXX';
        $code = str_pad(substr($code, 0, 4), 4, 'X');

        return strtoupper($code);
    }
}
