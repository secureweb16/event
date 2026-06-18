<?php

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders the events listing shell without authentication', function () {
    $this->get(route('events.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Events/Index')
            ->has('statuses', 4)
            ->has('locations')
            ->where('filters.from', '2023-01-01')
        );
});

it('returns a normalized json page of events with load stats for lazy loading', function () {
    $user = User::factory()->create(['name' => 'Ada Lovelace']);
    Event::factory()->for($user)->create([
        'type' => 'concert',
        'status' => 'published',
        'created_time' => 1_700_000_000,
        'latitude' => 40.7128,
        'longitude' => -74.0060,
        'payload' => [
            'name' => 'Global Tech Summit',
            'description' => 'A large-format launch event.',
            'venue' => ['name' => 'Skyline Hall', 'capacity' => 5000],
            'schedule' => ['starts_at' => 1_700_000_000, 'ends_at' => 1_700_007_200],
            'pricing' => ['currency' => 'USD', 'min_price' => 99.0],
        ],
    ]);

    $this->getJson(route('events.data'))
        ->assertOk()
        ->assertJsonStructure([
            'data' => [[
                'id',
                'title',
                'description',
                'location' => ['label', 'city', 'timezone'],
                'schedule' => ['starts_at', 'starts_at_unix'],
                'gallery',
            ]],
            'current_page',
            'last_page',
            'total',
            'stats' => ['ms', 'bytes'],
        ])
        ->assertJsonPath('total', 1)
        ->assertJsonPath('data.0.type', 'concert')
        ->assertJsonPath('data.0.title', 'Global Tech Summit')
        ->assertJsonPath('data.0.location.city', 'New York')
        ->assertJsonPath('data.0.attendee_count', 0)
        ->assertJsonPath('data.0.organizer.name', 'Ada Lovelace');
});

it('filters the data endpoint by status', function () {
    $user = User::factory()->create();
    Event::factory()->for($user)->create(['status' => 'published']);
    Event::factory()->for($user)->create(['status' => 'cancelled']);

    $this->getJson(route('events.data', ['status' => 'cancelled']))
        ->assertOk()
        ->assertJsonPath('total', 1)
        ->assertJsonPath('data.0.status', 'cancelled');
});

it('shows an event detail page with attendee registration options', function () {
    $user = User::factory()->create();
    $event = Event::factory()->for($user)->create([
        'payload' => ['name' => 'Global Tech Summit', 'location' => ['lat' => 1.5, 'lng' => 2.5]],
    ]);

    $this->get(route('events.show', $event))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Events/Show')
            ->where('event.id', $event->id)
            ->where('event.payload.name', 'Global Tech Summit')
            ->has('attendanceStatuses', 2)
        );
});

it('renders the two visualization pages and the dashboard without authentication', function () {
    $this->get(route('events.visual1'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Events/VisualOne'));

    $this->get(route('events.visual2'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Events/VisualTwo'));

    $this->get(route('dashboard'))->assertOk();
});
