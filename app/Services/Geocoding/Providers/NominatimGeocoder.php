<?php

namespace App\Services\Geocoding\Providers;

use App\Services\Geocoding\GeocoderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NominatimGeocoder implements GeocoderInterface
{
    protected ?string $clientId;

    protected ?string $secret;

    public function __construct()
    {
        $this->clientId = config('services.geocoder.nominatim.client_id');
        $this->secret = config('services.geocoder.nominatim.secret');
    }

    public function reverseGeocode(float $latitude, float $longitude): ?string
    {
        try {
            // Build the query
            $query = [
                'lat' => $latitude,
                'lon' => $longitude,
                'format' => 'json',
            ];

            // Some premium Nominatim-based services use client_id and secret,
            // or we just append them if present.
            if ($this->clientId) {
                $query['client_id'] = $this->clientId;
            }
            if ($this->secret) {
                $query['secret'] = $this->secret;
            }

            // A user-agent is strictly required by the public Nominatim API
            $userAgent = config('app.name', 'Laravel').' ReverseGeocoder';

            $response = Http::withoutVerifying()->withHeaders([
                'User-Agent' => $userAgent,
            ])->timeout(5)->get('https://nominatim.openstreetmap.org/reverse', $query);

            if ($response->successful()) {
                $data = $response->json();
                // info('Nominatim Geocoder response:', $data);
                if (! empty($data['display_name'])) {
                    return $data['display_name'];
                }
            } else {
                Log::error('Nominatim Geocoder failed', ['status' => $response->status(), 'response' => $response->body()]);
            }
        } catch (\Exception $e) {
            Log::error('Nominatim Geocoder exception: '.$e->getMessage());
        }

        return null;
    }
}
