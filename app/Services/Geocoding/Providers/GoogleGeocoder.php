<?php

namespace App\Services\Geocoding\Providers;

use App\Services\Geocoding\GeocoderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleGeocoder implements GeocoderInterface
{
    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.geocoder.google.api_key');
    }

    public function reverseGeocode(float $latitude, float $longitude): ?string
    {
        if (empty($this->apiKey)) {
            Log::warning('Google Maps API Key is missing for Reverse Geocoding.');
            return null;
        }

        try {
            $response = Http::withoutVerifying()->timeout(5)->get('https://maps.googleapis.com/maps/api/geocode/json', [
                'latlng' => "{$latitude},{$longitude}",
                'key' => $this->apiKey,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (!empty($data['results'])) {
                    return $data['results'][0]['formatted_address'];
                }
            } else {
                Log::error('Google Geocoder failed', ['status' => $response->status(), 'response' => $response->body()]);
            }
        } catch (\Exception $e) {
            Log::error('Google Geocoder exception: ' . $e->getMessage());
        }

        return null;
    }
}
