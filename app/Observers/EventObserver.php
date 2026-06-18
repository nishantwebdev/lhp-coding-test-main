<?php

namespace App\Observers;

use App\Jobs\GeocodeEventsJob;
use App\Models\Event;

class EventObserver
{
    /**
     * Handle the Event "saved" event.
     */
    public function saved(Event $event): void
    {
        // Only trigger if coordinates were updated or it's a new record
        if ($event->wasRecentlyCreated || $event->wasChanged(['latitude', 'longitude'])) {
            GeocodeEventsJob::dispatch([$event->id]);
        }
    }
}
