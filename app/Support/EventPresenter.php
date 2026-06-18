<?php

namespace App\Support;

use App\Models\Event;
use Carbon\CarbonImmutable;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class EventPresenter
{
    /**
     * @return list<array<string, mixed>>
     */
    public function collection(iterable $events): array
    {
        $items = [];

        foreach ($events as $event) {
            if ($event instanceof Event) {
                $items[] = $this->summary($event);
            }
        }

        return $items;
    }

    /**
     * @return array<string, mixed>
     */
    public function summary(Event $event): array
    {
        $payload = $event->payload ?? [];
        $location = EventCatalog::resolveLocation($event->latitude, $event->longitude);
        $startTimestamp = (int) (Arr::get($payload, 'schedule.starts_at') ?? $event->created_time ?? now()->timestamp);
        $endTimestamp = (int) (Arr::get($payload, 'schedule.ends_at') ?? ($startTimestamp + 7200));
        $title = trim((string) (Arr::get($payload, 'name') ?? ''));
        $description = trim((string) (Arr::get($payload, 'description') ?? ''));
        $venueName = trim((string) (Arr::get($payload, 'venue.name') ?? ''));

        if ($title === '') {
            $title = Str::headline($event->type).' Session';
        }

        if ($description === '') {
            $description = Str::finish(
                Str::limit(trim((string) Arr::get($payload, 'notes', '')), 180, '...'),
                '',
            );
        }

        if ($description === '') {
            $description = "Join {$title} for a {$event->type} experience built around {$location['city']}.";
        }

        $startsAt = CarbonImmutable::createFromTimestampUTC($startTimestamp);
        $endsAt = CarbonImmutable::createFromTimestampUTC($endTimestamp);
        $images = EventCatalog::imagesFor($event->id, $event->type);
        $price = Arr::get($payload, 'pricing.min_price');
        $currency = Arr::get($payload, 'pricing.currency', 'USD');

        return [
            'id' => $event->id,
            'type' => $event->type,
            'status' => $event->status,
            'title' => $title,
            'description' => $description,
            'excerpt' => Str::limit($description, 150, '...'),
            'venue_name' => $venueName !== '' ? $venueName : 'Signature Venue',
            'location' => [
                'slug' => $location['slug'],
                'city' => $location['city'],
                'region' => $location['region'],
                'country' => $location['country'],
                'country_code' => $location['country_code'],
                'label' => $location['label'],
                'timezone' => $location['timezone'],
            ],
            'coordinates' => [
                'latitude' => $event->latitude,
                'longitude' => $event->longitude,
            ],
            'schedule' => [
                'starts_at' => $startsAt->toIso8601String(),
                'ends_at' => $endsAt->toIso8601String(),
                'starts_at_unix' => $startTimestamp,
                'ends_at_unix' => $endTimestamp,
                'timezone' => $location['timezone'],
                'local_date' => $startsAt->setTimezone($location['timezone'])->toDateString(),
            ],
            'images' => $images,
            'hero_image' => $images[0],
            'gallery' => $images,
            'organizer' => [
                'name' => Arr::get($payload, 'organizer.name') ?: $event->user?->name ?: 'Guest Curator',
                'verified' => (bool) Arr::get($payload, 'organizer.verified', false),
            ],
            'pricing' => [
                'amount' => is_numeric($price) ? (float) $price : null,
                'currency' => is_string($currency) ? $currency : 'USD',
                'label' => is_numeric($price)
                    ? sprintf('%s %0.2f', strtoupper((string) $currency), (float) $price)
                    : 'Free entry',
            ],
            'capacity' => Arr::get($payload, 'venue.capacity'),
            'tags' => array_values(array_filter(Arr::wrap(Arr::get($payload, 'tags', [])))),
            'attendee_count' => (int) ($event->attendees_count ?? $event->attendees?->count() ?? 0),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function detail(Event $event): array
    {
        $summary = $this->summary($event);

        return [
            ...$summary,
            'payload' => $event->payload ?? [],
            'attendees' => $event->relationLoaded('attendees')
                ? $event->attendees
                    ->sortByDesc('created_at')
                    ->values()
                    ->map(fn ($attendee) => [
                        'id' => $attendee->id,
                        'name' => $attendee->name,
                        'email' => $attendee->email,
                        'attendance_status' => $attendee->attendance_status,
                        'created_at' => optional($attendee->created_at)?->toIso8601String(),
                    ])->all()
                : [],
        ];
    }
}
