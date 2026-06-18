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

class EventReminderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public array $eventData;

    public function __construct(
        public Event $event,
        public EventAttendee $attendee,
        public int $hoursUntilStart,
    ) {
        $this->eventData = (new EventPresenter)->summary($event);
    }

    public function envelope(): Envelope
    {
        $subjectPrefix = $this->hoursUntilStart === 72 ? '3-day reminder' : '24-hour reminder';

        return new Envelope(
            subject: "{$subjectPrefix}: {$this->eventData['title']}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.events.reminder',
        );
    }
}
