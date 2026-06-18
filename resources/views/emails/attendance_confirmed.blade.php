<x-mail::message>
# Attendance Confirmed

You have successfully registered for **{{ $event->payload['name'] }}**!

**Location:** {{ $event->payload['venue']['name'] ?? 'TBA' }}  
**Date:** {{ \Carbon\Carbon::createFromTimestamp($event->payload['schedule']['starts_at'])->toDayDateTimeString() }}

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
