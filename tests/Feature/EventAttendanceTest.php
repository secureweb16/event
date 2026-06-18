<?php

use App\Mail\EventRegistrationConfirmationMail;
use App\Mail\EventReminderMail;
use App\Models\Event;
use App\Models\EventAttendee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

it('registers an attendee and queues a confirmation email', function () {
    Mail::fake();

    $event = Event::factory()->create([
        'status' => 'published',
        'created_time' => now()->addDays(7)->timestamp,
    ]);

    $this->post(route('events.attendees.store', $event), [
        'name' => 'Jamie Rivers',
        'email' => 'jamie@example.com',
        'attendance_status' => 'attending',
    ])->assertRedirect(route('events.show', $event));

    expect(EventAttendee::count())->toBe(1);

    Mail::assertQueued(EventRegistrationConfirmationMail::class, function (EventRegistrationConfirmationMail $mail) {
        return $mail->attendee->email === 'jamie@example.com';
    });
});

it('validates duplicate attendee emails per event', function () {
    Mail::fake();

    $event = Event::factory()->create([
        'status' => 'published',
        'created_time' => now()->addDays(7)->timestamp,
    ]);

    EventAttendee::create([
        'event_id' => $event->id,
        'name' => 'Jamie Rivers',
        'email' => 'jamie@example.com',
        'attendance_status' => 'attending',
    ]);

    $this->from(route('events.show', $event))
        ->post(route('events.attendees.store', $event), [
            'name' => 'Jamie Rivers',
            'email' => 'jamie@example.com',
            'attendance_status' => 'attending',
        ])
        ->assertRedirect(route('events.show', $event))
        ->assertSessionHasErrors(['email']);

    Mail::assertNothingQueued();
});

it('queues reminder emails at 72 and 24 hours and does not duplicate them', function () {
    Mail::fake();

    $this->travelTo(now()->startOfHour());

    $threeDayEvent = Event::factory()->create([
        'status' => 'published',
        'created_time' => now()->addHours(72)->timestamp,
    ]);

    $oneDayEvent = Event::factory()->create([
        'status' => 'published',
        'created_time' => now()->addHours(24)->timestamp,
    ]);

    $threeDayAttendee = EventAttendee::create([
        'event_id' => $threeDayEvent->id,
        'name' => 'Three Day Guest',
        'email' => 'three-day@example.com',
        'attendance_status' => 'interested',
    ]);

    $oneDayAttendee = EventAttendee::create([
        'event_id' => $oneDayEvent->id,
        'name' => 'One Day Guest',
        'email' => 'one-day@example.com',
        'attendance_status' => 'attending',
    ]);

    $this->artisan('events:send-reminders')->assertSuccessful();
    $this->artisan('events:send-reminders')->assertSuccessful();

    Mail::assertQueued(EventReminderMail::class, 2);
    Mail::assertQueued(EventReminderMail::class, fn (EventReminderMail $mail) => $mail->hoursUntilStart === 72);
    Mail::assertQueued(EventReminderMail::class, fn (EventReminderMail $mail) => $mail->hoursUntilStart === 24);

    expect($threeDayAttendee->fresh()->reminder_72h_sent_at)->not->toBeNull();
    expect($oneDayAttendee->fresh()->reminder_24h_sent_at)->not->toBeNull();

    $this->travelBack();
});
