<?php

namespace App\Services\Geocoding;

interface GeocoderInterface
{
    /**
     * Reverse geocode coordinates to a human-readable address.
     *
     * @param float $latitude
     * @param float $longitude
     * @return string|null
     */
    public function reverseGeocode(float $latitude, float $longitude): ?string;
}
