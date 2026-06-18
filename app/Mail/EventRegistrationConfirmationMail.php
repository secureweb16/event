<?php

namespace App\Mail;

use App\Models\Event;
use App\Models\EventAttendee;
use App\Support\EventPresenter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EventRegistrationConfirmationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public array $eventData;

    public function __construct(
        public Event $event,
        public EventAttendee $attendee,
    ) {
        $this->eventData = (new EventPresenter)->summary($event);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "You're on the list for {$this->eventData['title']}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.events.registration-confirmation',
        );
    }
}
