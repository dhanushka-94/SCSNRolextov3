<?php

namespace App\Support;

class FarmCoordinates
{
    public const DEFAULT_LATITUDE = 7.8731;

    public const DEFAULT_LONGITUDE = 80.7718;

    public const DEFAULT_ZOOM = 7;

    public const PINNED_ZOOM = 14;

    /**
     * Approximate Sri Lanka bounds used for validation.
     */
    public const MIN_LATITUDE = 5.8;

    public const MAX_LATITUDE = 10.0;

    public const MIN_LONGITUDE = 79.4;

    public const MAX_LONGITUDE = 82.1;

    /**
     * @return array<string, mixed>
     */
    public static function rules(bool $required = true): array
    {
        $presence = $required ? 'required' : 'nullable';

        return [
            'latitude' => [
                $presence,
                'numeric',
                'between:'.self::MIN_LATITUDE.','.self::MAX_LATITUDE,
                'required_with:longitude',
            ],
            'longitude' => [
                $presence,
                'numeric',
                'between:'.self::MIN_LONGITUDE.','.self::MAX_LONGITUDE,
                'required_with:latitude',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function attributes(): array
    {
        return [
            'latitude' => 'map latitude',
            'longitude' => 'map longitude',
        ];
    }

    public static function prepare(?string $latitude, ?string $longitude): array
    {
        return [
            'latitude' => filled($latitude) ? $latitude : null,
            'longitude' => filled($longitude) ? $longitude : null,
        ];
    }
}
