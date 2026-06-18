<?php

namespace App\Console\Commands;

use App\Jobs\GeocodeEventsJob;
use App\Models\Event;
use Illuminate\Console\Command;

class GeocodeAllEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:geocode-all-events {--limit= : The maximum number of events to process}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatches background jobs to reverse geocode all existing events in the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to dispatch geocoding jobs...');

        $eventsQuery = Event::whereNotNull('latitude')
            ->whereNotNull('longitude');

        $limit = $this->option('limit');

        // Note: Using chunkById is more memory efficient for large tables
        $count = 0;
        $eventsQuery->chunkById(100, function ($events) use (&$count, $limit) {
            if ($limit && ($count + $events->count()) > $limit) {
                $events = $events->take($limit - $count);
            }

            $eventIds = $events->pluck('id')->toArray();
            GeocodeEventsJob::dispatch($eventIds);
            
            foreach ($eventIds as $eventId) {
                $this->line("Dispatched event ID: {$eventId}");
            }
            
            $count += count($eventIds);
            $this->info("Total dispatched so far: $count events...");

            if ($limit && $count >= $limit) {
                return false; // Stop chunking
            }
        });

        $this->info('Finished dispatching all geocoding jobs!');
    }
}
