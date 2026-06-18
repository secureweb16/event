<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventAttendeeRequest;
use App\Mail\EventRegistrationConfirmationMail;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class EventAttendeeController extends Controller
{
    public function store(StoreEventAttendeeRequest $request, Event $event): RedirectResponse
    {
        if (in_array($event->status, ['draft', 'cancelled'], true)) {
            throw ValidationException::withMessages([
                'event' => 'Registration is unavailable for this event right now.',
            ]);
        }

        $attendee = $event->attendees()->create($request->validated());

        Mail::to($attendee->email)->queue(new EventRegistrationConfirmationMail($event, $attendee));

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Registration received. A confirmation email is on the way.',
        ]);

        return to_route('events.show', $event);
    }
}
