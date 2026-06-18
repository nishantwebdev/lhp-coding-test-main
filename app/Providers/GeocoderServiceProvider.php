<?php

namespace App\Providers;

use App\Services\Geocoding\GeocoderInterface;
use App\Services\Geocoding\Providers\GoogleGeocoder;
use App\Services\Geocoding\Providers\NominatimGeocoder;
use Illuminate\Support\ServiceProvider;

class GeocoderServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(GeocoderInterface::class, function ($app) {
            $provider = config('services.geocoder.provider', 'google');

            if ($provider === 'nominatim') {
                return new NominatimGeocoder();
            }

            return new GoogleGeocoder();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
