<?php

namespace App\Http\Controllers;

use App\Http\Requests\EventFilterRequest;
use App\Models\Event;
use App\Support\EventCatalog;
use App\Support\EventPresenter;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    public function __construct(
        private readonly EventPresenter $presenter,
    ) {}

    public function index(EventFilterRequest $request): Response
    {
        return $this->renderPage('Events/Index', $request, '2023-01-01');
    }

    public function visualOne(EventFilterRequest $request): Response
    {
        return $this->renderPage('Events/VisualOne', $request, now()->toDateString());
    }

    public function visualTwo(EventFilterRequest $request): Response
    {
        return $this->renderPage('Events/VisualTwo', $request, now()->toDateString());
    }

    public function data(EventFilterRequest $request): JsonResponse
    {
        [$events, $presented, $stats] = $this->loadListing($request);

        return response()->json([
            'data' => $presented,
            'current_page' => $events->currentPage(),
            'last_page' => $events->lastPage(),
            'total' => $events->total(),
            'stats' => $stats,
        ]);
    }

    public function show(Event $event): Response
    {
        $event->load(['user', 'attendees'])->loadCount('attendees');

        return Inertia::render('Events/Show', [
            'event' => $this->presenter->detail($event),
            'attendanceStatuses' => [
                ['value' => 'interested', 'label' => 'Interested'],
                ['value' => 'attending', 'label' => 'Attending'],
            ],
        ]);
    }

    /**
     * @return array{0: LengthAwarePaginator, 1: list<array<string, mixed>>, 2: array{ms: int, bytes: int}}
     */
    private function loadListing(EventFilterRequest $request): array
    {
        $start = microtime(true);
        $filters = $request->validated();
        $sort = $request->sort();

        $query = Event::query()
            ->with('user')
            ->withCount('attendees')
            ->when(
                $filters['status'] ?? null,
                fn ($events, $status) => $events->where('status', $status),
            )
            ->when(
                $filters['from'] ?? null,
                fn ($events, $from) => $events->where(
                    'created_time',
                    '>=',
                    CarbonImmutable::parse($from, 'UTC')->startOfDay()->timestamp,
                ),
            )
            ->when(
                $filters['to'] ?? null,
                fn ($events, $to) => $events->where(
                    'created_time',
                    '<=',
                    CarbonImmutable::parse($to, 'UTC')->endOfDay()->timestamp,
                ),
            );

        if ($bounds = EventCatalog::boundsForLocation($filters['location'] ?? null)) {
            $query
                ->whereBetween('latitude', [$bounds['min_lat'], $bounds['max_lat']])
                ->whereBetween('longitude', [$bounds['min_lng'], $bounds['max_lng']]);
        }

        if ($sort === 'upcoming') {
            $query
                ->where('created_time', '>=', now()->timestamp - 86400)
                ->orderBy('created_time');
        } else {
            $query->orderByDesc('created_time');
        }

        $events = $query
            ->paginate($request->perPage($sort === 'upcoming' ? 18 : 50))
            ->withQueryString();

        $presented = $this->presenter->collection($events->items());
        $stats = [
            'ms' => (int) round((microtime(true) - $start) * 1000),
            'bytes' => strlen((string) json_encode($presented)),
        ];

        return [$events, $presented, $stats];
    }

    private function renderPage(
        string $component,
        EventFilterRequest $request,
        string $defaultFrom,
    ): Response
    {
        return Inertia::render($component, [
            'filters' => $request->filters($defaultFrom),
            'statuses' => Event::STATUSES,
            'locations' => EventCatalog::locationOptions(),
        ]);
    }
}
