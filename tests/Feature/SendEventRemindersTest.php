<?php

use App\Mail\EventReminder;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

it('sends reminders for events starting in 72 hours and 24 hours', function () {
    Mail::fake();

    $now = Carbon::now();

    // Event starting in exactly 72 hours
    $event72 = Event::factory()->create([
        'payload' => [
            'name' => 'Event 72h',
            'schedule' => [
                'starts_at' => $now->copy()->addHours(72)->addMinutes(30)->timestamp,
            ],
            'venue' => ['capacity' => '10'],
        ],
    ]);
    $event72->attendees()->create(['email' => 'user72@example.com']);

    // Event starting in exactly 24 hours
    $event24 = Event::factory()->create([
        'payload' => [
            'name' => 'Event 24h',
            'schedule' => [
                'starts_at' => $now->copy()->addHours(24)->addMinutes(30)->timestamp,
            ],
            'venue' => ['capacity' => '10'],
        ],
    ]);
    $event24->attendees()->create(['email' => 'user24@example.com']);

    // Event starting in 10 hours (should not get reminder)
    $event10 = Event::factory()->create([
        'payload' => [
            'name' => 'Event 10h',
            'schedule' => [
                'starts_at' => $now->copy()->addHours(10)->timestamp,
            ],
            'venue' => ['capacity' => '10'],
        ],
    ]);
    $event10->attendees()->create(['email' => 'user10@example.com']);

    $this->artisan('app:send-event-reminders')
        ->assertSuccessful()
        ->expectsOutput('Reminders sent successfully.');

    Mail::assertQueued(EventReminder::class, 2);

    Mail::assertQueued(EventReminder::class, function ($mail) {
        return $mail->hasTo('user72@example.com') && $mail->timeframe === '3 days';
    });

    Mail::assertQueued(EventReminder::class, function ($mail) {
        return $mail->hasTo('user24@example.com') && $mail->timeframe === '24 hours';
    });
});
