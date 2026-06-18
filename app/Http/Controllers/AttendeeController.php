<?php

namespace App\Http\Controllers;

use App\Mail\AttendanceConfirmed;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class AttendeeController extends Controller
{
    public function store(Request $request, Event $event)
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
        ]);

        // Check if the user is already registered
        if ($event->attendees()->where('email', $validated['email'])->exists()) {
            throw ValidationException::withMessages([
                'email' => 'This email is already registered for this event.',
            ]);
        }

        // Check event capacity
        if ($event->remaining_seats <= 0) {
            throw ValidationException::withMessages([
                'capacity' => 'This event is currently full.',
            ]);
        }

        $event->attendees()->create([
            'email' => $validated['email'],
        ]);

        Mail::to($validated['email'])->send(new AttendanceConfirmed($event));

        return back()->with('success', 'You have successfully registered for this event!');
    }

    public function data(Event $event): JsonResponse
    {
        $attendees = $event->attendees()->latest()->paginate(20);

        return response()->json([
            'data' => $attendees->items(),
            'current_page' => $attendees->currentPage(),
            'last_page' => $attendees->lastPage(),
            'total' => $attendees->total(),
        ]);
    }
}
