<?php

namespace App\Console\Commands;

use App\Mail\EventReminderMail;
use App\Models\EventAttendee;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Mail;

class SendEventReminders extends Command
{
    protected $signature = 'events:send-reminders';

    protected $description = 'Queue attendee reminder emails for upcoming events.';

    public function handle(): int
    {
        $queued = 0;

        $queued += $this->queueReminderBatch(72, 'reminder_72h_sent_at');
        $queued += $this->queueReminderBatch(24, 'reminder_24h_sent_at');

        $this->info("Queued {$queued} reminder emails.");

        return self::SUCCESS;
    }

    private function queueReminderBatch(int $hoursUntilStart, string $column): int
    {
        $now = CarbonImmutable::now('UTC');
        $windowStart = $now->addHours($hoursUntilStart - 1)->timestamp;
        $windowEnd = $now->addHours($hoursUntilStart + 1)->timestamp;
        $queued = 0;

        EventAttendee::query()
            ->with('event')
            ->whereNull($column)
            ->whereHas('event', function (Builder $query) use ($windowStart, $windowEnd): void {
                $query
                    ->where('status', '!=', 'cancelled')
                    ->whereBetween('created_time', [$windowStart, $windowEnd]);
            })
            ->chunkById(200, function ($attendees) use ($column, $hoursUntilStart, &$queued): void {
                foreach ($attendees as $attendee) {
                    if (! $attendee->event) {
                        continue;
                    }

                    Mail::to($attendee->email)->queue(new EventReminderMail(
                        $attendee->event,
                        $attendee,
                        $hoursUntilStart,
                    ));

                    $attendee->forceFill([$column => now()])->save();
                    $queued++;
                }
            });

        return $queued;
    }
}
