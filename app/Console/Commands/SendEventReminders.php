<?php

namespace App\Console\Commands;

use App\Mail\EventReminder;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendEventReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-event-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send 3-day and 24-hour reminders for upcoming events.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now()->timestamp;

        // 3 Days (72 hours to 73 hours)
        $this->sendReminders($now + (72 * 3600), $now + (73 * 3600), '3 days');

        // 24 Hours (24 hours to 25 hours)
        $this->sendReminders($now + (24 * 3600), $now + (25 * 3600), '24 hours');

        $this->info('Reminders sent successfully.');
    }

    private function sendReminders(int $startRange, int $endRange, string $timeframe)
    {
        Event::with('attendees')->chunk(500, function ($events) use ($startRange, $endRange, $timeframe) {
            foreach ($events as $event) {
                $startsAt = (int) ($event->payload['schedule']['starts_at'] ?? 0);
                if ($startsAt >= $startRange && $startsAt < $endRange) {
                    foreach ($event->attendees as $attendee) {
                        Mail::to($attendee->email)->send(new EventReminder($event, $timeframe));
                    }
                }
            }
        });
    }
}
