import { computed, reactive, ref } from 'vue';
import type { EventFilters, PresentedEvent } from '@/types';

interface EventFeedOptions {
    initialFilters: EventFilters;
    perPage: number;
}

interface EventFeedResponse {
    data: PresentedEvent[];
    current_page: number;
    last_page: number;
    total: number;
    stats: {
        ms: number;
        bytes: number;
    };
}

interface EventFeedErrorResponse {
    message?: string;
    errors?: Record<string, string[]>;
}

export function useEventFeed(options: EventFeedOptions) {
    const filters = reactive<EventFilters>({
        status: options.initialFilters.status ?? null,
        location: options.initialFilters.location ?? null,
        from: options.initialFilters.from ?? null,
        to: options.initialFilters.to ?? null,
        sort: options.initialFilters.sort ?? 'upcoming',
    });

    const events = ref<PresentedEvent[]>([]);
    const page = ref(0);
    const lastPage = ref<number | null>(null);
    const total = ref<number | null>(null);
    const loading = ref(false);
    const hasLoadedOnce = ref(false);
    const error = ref<string | null>(null);
    const loadedBytes = ref(0);
    const loadedMs = ref(0);

    const hasMore = computed(
        () => lastPage.value === null || page.value < lastPage.value,
    );
    const loadedSize = computed(() => {
        const kb = loadedBytes.value / 1024;

        return kb < 1024
            ? `${kb.toFixed(1)} KB`
            : `${(kb / 1024).toFixed(2)} MB`;
    });
    const hasInvalidDateRange = computed(
        () =>
            Boolean(
                filters.from &&
                    filters.to &&
                    filters.to.localeCompare(filters.from) < 0,
            ),
    );

    async function loadMore() {
        if (loading.value || !hasMore.value) {
            return;
        }

        if (hasInvalidDateRange.value) {
            error.value = 'The "To" date must be the same as or later than the "From" date.';
            hasLoadedOnce.value = true;

            return;
        }

        loading.value = true;
        error.value = null;

        const params = new URLSearchParams({
            page: String(page.value + 1),
            per_page: String(options.perPage),
            sort: filters.sort,
        });

        for (const [key, value] of Object.entries({
            status: filters.status,
            location: filters.location,
            from: filters.from,
            to: filters.to,
        })) {
            if (value) {
                params.set(key, value);
            }
        }

        try {
            const response = await fetch(`/events/data?${params.toString()}`, {
                headers: { Accept: 'application/json' },
            });

            if (!response.ok) {
                const payload = (await response
                    .json()
                    .catch(() => null)) as EventFeedErrorResponse | null;

                throw new Error(
                    payload?.errors?.to?.[0] ??
                        payload?.message ??
                        'Unable to load events right now.',
                );
            }

            const payload = (await response.json()) as EventFeedResponse;

            events.value.push(...payload.data);
            page.value = payload.current_page;
            lastPage.value = payload.last_page;
            total.value = payload.total;
            loadedBytes.value += payload.stats.bytes;
            loadedMs.value += payload.stats.ms;
            hasLoadedOnce.value = true;
        } catch (err) {
            error.value =
                err instanceof Error
                    ? err.message
                    : 'Unable to load events right now.';
        } finally {
            loading.value = false;
        }
    }

    function reset() {
        events.value = [];
        page.value = 0;
        lastPage.value = null;
        total.value = null;
        loading.value = false;
        hasLoadedOnce.value = false;
        error.value = null;
        loadedBytes.value = 0;
        loadedMs.value = 0;
    }

    async function applyFilters() {
        reset();
        await loadMore();
    }

    return {
        filters,
        events,
        page,
        total,
        loading,
        error,
        hasMore,
        hasLoadedOnce,
        loadedSize,
        loadedMs,
        loadMore,
        applyFilters,
        reset,
    };
}
