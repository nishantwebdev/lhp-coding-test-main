<?php

use App\Jobs\GeocodeEventsJob;
use App\Models\Event;
use App\Models\User;
use App\Services\Geocoding\GeocoderInterface;
use Illuminate\Support\Facades\Queue;
use Mockery\MockInterface;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('dispatches a geocoding job when an event is created with coordinates', function () {
    Queue::fake();

    $user = User::factory()->create();

    $event = Event::factory()->create([
        'user_id' => $user->id,
        'latitude' => 40.7128,
        'longitude' => -74.0060,
    ]);

    Queue::assertPushed(GeocodeEventsJob::class, function ($job) use ($event) {
        // We can inspect the array via reflection if needed, but simple class assertion is fine
        return true;
    });
});

it('dispatches a geocoding job when coordinates are updated', function () {
    Queue::fake();

    $user = User::factory()->create();

    $event = Event::factory()->create([
        'user_id' => $user->id,
        'latitude' => 40.7128,
        'longitude' => -74.0060,
    ]);

    // Clear the queue from the initial creation
    Queue::fake();

    $event->update([
        'latitude' => 34.0522,
        'longitude' => -118.2437,
    ]);

    Queue::assertPushed(GeocodeEventsJob::class);
});

it('does not dispatch a job if coordinates are not changed', function () {
    Queue::fake();

    $user = User::factory()->create();

    // Create quietly to avoid the initial creation job
    $event = Event::factory()->createQuietly([
        'user_id' => $user->id,
        'latitude' => 40.7128,
        'longitude' => -74.0060,
    ]);

    // Refresh model to reset wasRecentlyCreated flag
    $event = Event::find($event->id);

    // Update something else
    $event->update([
        'payload' => array_merge($event->payload ?? [], ['title' => 'Updated Title'])
    ]);

    Queue::assertNotPushed(GeocodeEventsJob::class);
});

it('updates the event payload with the resolved address', function () {
    $this->mock(GeocoderInterface::class, function (MockInterface $mock) {
        $mock->shouldReceive('reverseGeocode')
            ->once()
            ->with(40.7128, -74.0060)
            ->andReturn('123 Mock Street, Fake City, NY');
    });

    $user = User::factory()->create();

    // Create quietly to bypass the observer which would dispatch to the sync queue
    $event = Event::factory()->createQuietly([
        'user_id' => $user->id,
        'latitude' => 40.7128,
        'longitude' => -74.0060,
    ]);

    // Dispatch the job manually to ensure it runs synchronously in the test
    (new GeocodeEventsJob([$event->id]))->handle(app(GeocoderInterface::class));

    $event->refresh();

    expect($event->payload)->toHaveKey('address', '123 Mock Street, Fake City, NY');
});
