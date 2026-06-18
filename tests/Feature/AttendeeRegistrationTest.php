<?php

use App\Mail\AttendanceConfirmed;
use App\Models\Attendee;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

it('can register attendance for an event and dispatches confirmation email', function () {
    Mail::fake();

    $event = Event::factory()->create([
        'payload' => [
            'name' => 'Test Event',
            'venue' => ['capacity' => '10'],
        ],
    ]);

    $response = $this->post(route('events.attendees.store', $event->id), [
        'email' => 'test@example.com',
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect();

    expect(Attendee::count())->toBe(1);
    expect(Attendee::first()->email)->toBe('test@example.com');

    Mail::assertQueued(AttendanceConfirmed::class, function ($mail) use ($event) {
        return $mail->hasTo('test@example.com') && $mail->event->id === $event->id;
    });
});

it('cannot register if event is at capacity', function () {
    $event = Event::factory()->create([
        'payload' => [
            'venue' => ['capacity' => '1'],
        ],
    ]);

    $event->attendees()->create(['email' => 'first@example.com']);

    $response = $this->post(route('events.attendees.store', $event->id), [
        'email' => 'second@example.com',
    ]);

    $response->assertSessionHasErrors('capacity');
    expect(Attendee::count())->toBe(1);
});

it('cannot register the same email twice for the same event', function () {
    $event = Event::factory()->create([
        'payload' => [
            'venue' => ['capacity' => '10'],
        ],
    ]);

    $event->attendees()->create(['email' => 'test@example.com']);

    $response = $this->post(route('events.attendees.store', $event->id), [
        'email' => 'test@example.com',
    ]);

    $response->assertSessionHasErrors('email');
    expect(Attendee::count())->toBe(1);
});

it('can fetch paginated attendees data for an event', function () {
    $event = Event::factory()->create();
    $event->attendees()->createMany([
        ['email' => 'one@example.com'],
        ['email' => 'two@example.com'],
    ]);

    $response = $this->getJson(route('events.attendees.data', $event->id));

    $response->assertSuccessful()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.email', 'one@example.com')
        ->assertJsonPath('data.1.email', 'two@example.com')
        ->assertJsonPath('total', 2);
});