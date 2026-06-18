<?php

namespace App\Jobs;

use App\Models\Event;
use App\Services\Geocoding\GeocoderInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GeocodeEventsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 120;

    protected array $eventIds;

    /**
     * Create a new job instance.
     */
    public function __construct(array $eventIds)
    {
        // Enforce max 100 IDs to avoid timeouts
        $this->eventIds = array_slice($eventIds, 0, 100);
    }

    /**
     * Execute the job.
     */
    public function handle(GeocoderInterface $geocoder): void
    {
        $events = Event::whereIn('id', $this->eventIds)->get();

        foreach ($events as $event) {
            try {
                if (empty($event->latitude) || empty($event->longitude)) {
                    continue;
                }

                $address = $geocoder->reverseGeocode($event->latitude, $event->longitude);

                if ($address) {
                    // Use a transaction with 3 retries in case the SQLite database is locked
                    DB::transaction(function () use ($event, $address) {
                        $payload = $event->payload ?? [];
                        if (! isset($payload['venue'])) {
                            $payload['venue'] = [];
                        }
                        $payload['venue']['address'] = $address;
                        $event->payload = $payload;

                        // Use saveQuietly to prevent triggering the Observer and creating an infinite loop
                        $event->saveQuietly();
                    }, 5);
                }

                // Sleep for 0.5 seconds to respect geocoding API rate limits
                usleep(500000);
            } catch (\Exception $e) {
                Log::error("Failed to geocode event {$event->id}: ".$e->getMessage());
            }
        }
    }
}
