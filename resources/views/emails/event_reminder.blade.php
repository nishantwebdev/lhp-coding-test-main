<x-mail::message>
# Event Reminder

This is a reminder that **{{ $event->payload['name'] }}** is happening in **{{ $timeframe }}**!

**Location:** {{ $event->payload['venue']['name'] ?? 'TBA' }}  
**Date:** {{ \Carbon\Carbon::createFromTimestamp($event->payload['schedule']['starts_at'])->toDayDateTimeString() }}

We look forward to seeing you there!

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
