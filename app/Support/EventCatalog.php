<?php

namespace App\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class EventCatalog
{
    /**
     * @return list<array{
     *     slug: string,
     *     city: string,
     *     region: string,
     *     country: string,
     *     country_code: string,
     *     timezone: string,
     *     latitude: float,
     *     longitude: float
     * }>
     */
    public static function locations(): array
    {
        return config('event-catalog.locations', []);
    }

    /**
     * @return list<array{value: string, label: string, timezone: string}>
     */
    public static function locationOptions(): array
    {
        return array_map(
            fn (array $location) => [
                'value' => $location['slug'],
                'label' => "{$location['city']}, {$location['country']}",
                'timezone' => $location['timezone'],
            ],
            self::locations(),
        );
    }

    /**
     * @return array{
     *     slug: string,
     *     city: string,
     *     region: string,
     *     country: string,
     *     country_code: string,
     *     timezone: string,
     *     latitude: float,
     *     longitude: float,
     *     label: string
     * }
     */
    public static function resolveLocation(?float $latitude, ?float $longitude): array
    {
        if ($latitude === null || $longitude === null) {
            return self::defaultLocation();
        }

        $locations = self::locations();
        $closest = null;
        $closestDistance = PHP_FLOAT_MAX;

        foreach ($locations as $location) {
            $distance = (($latitude - $location['latitude']) ** 2) + (($longitude - $location['longitude']) ** 2);

            if ($distance < $closestDistance) {
                $closestDistance = $distance;
                $closest = $location;
            }
        }

        $location = $closest ?? $locations[0] ?? self::defaultLocation();

        return [
            ...$location,
            'label' => "{$location['city']}, {$location['country']}",
        ];
    }

    /**
     * @return array{min_lat: float, max_lat: float, min_lng: float, max_lng: float}|null
     */
    public static function boundsForLocation(?string $slug, float $radius = 0.85): ?array
    {
        if (! $slug) {
            return null;
        }

        $location = Arr::first(self::locations(), fn (array $entry) => $entry['slug'] === $slug);

        if (! $location) {
            return null;
        }

        return [
            'min_lat' => $location['latitude'] - $radius,
            'max_lat' => $location['latitude'] + $radius,
            'min_lng' => $location['longitude'] - $radius,
            'max_lng' => $location['longitude'] + $radius,
        ];
    }

    /**
     * @return list<string>
     */
    public static function imagesFor(string $eventId, string $type): array
    {
        $seed = Str::lower($eventId.'-'.$type);

        return [
            self::picsumUrl($seed.'-hero', 1200, 900),
            self::picsumUrl($seed.'-gallery-1', 900, 900),
            self::picsumUrl($seed.'-gallery-2', 900, 900),
        ];
    }

    private static function picsumUrl(string $seed, int $width, int $height): string
    {
        return "https://picsum.photos/seed/{$seed}/{$width}/{$height}";
    }

    /**
     * @return array{
     *     slug: string,
     *     city: string,
     *     region: string,
     *     country: string,
     *     country_code: string,
     *     timezone: string,
     *     latitude: float,
     *     longitude: float,
     *     label: string
     * }
     */
    private static function defaultLocation(): array
    {
        return [
            'slug' => 'global',
            'city' => 'Global',
            'region' => 'Worldwide',
            'country' => 'Online',
            'country_code' => 'GL',
            'timezone' => 'UTC',
            'latitude' => 0.0,
            'longitude' => 0.0,
            'label' => 'Global / Online',
        ];
    }
}
