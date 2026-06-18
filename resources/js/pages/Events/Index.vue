<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import EventFilterBar from '@/components/events/EventFilterBar.vue';
import { Badge } from '@/components/ui/badge';
import { useEventFeed } from '@/composables/useEventFeed';
import {
    eventStatusTone,
    eventTypeLabel,
    formatEventTime,
} from '@/lib/eventFormatting';
import type { EventFilters, EventLocationOption } from '@/types';

const props = defineProps<{
    filters: EventFilters;
    statuses: string[];
    locations: EventLocationOption[];
}>();

const initialFilters: EventFilters = {
    ...props.filters,
    sort: 'newest',
};

const {
    filters: activeFilters,
    events,
    loading,
    error,
    hasMore,
    hasLoadedOnce,
    loadedMs,
    loadedSize,
    loadMore,
    applyFilters,
} = useEventFeed({
    initialFilters,
    perPage: 50,
});

const sentinel = ref<HTMLElement | null>(null);
let observer: IntersectionObserver | null = null;

const loadedSeconds = computed(() => (loadedMs.value / 1000).toFixed(1));

function resetFilters() {
    Object.assign(activeFilters, initialFilters);
    void applyFilters();
}

onMounted(() => {
    observer = new IntersectionObserver(
        (entries) => {
            if (entries[0]?.isIntersecting) {
                void loadMore();
            }
        },
        { rootMargin: '400px' },
    );

    if (sentinel.value) {
        observer.observe(sentinel.value);
    }

    void applyFilters();
});

onBeforeUnmount(() => observer?.disconnect());
</script>

<template>
    <Head title="Events" />

    <div class="flex flex-col gap-6 p-4 md:p-6">
        <EventFilterBar
            v-model:filters="activeFilters"
            :statuses="statuses"
            :locations="locations"
            :loading="loading"
            title="Data Filters"
            subtitle="Use the same validated date and location filters across every event view."
            @apply="applyFilters"
            @clear="resetFilters"
        />

        <div
            class="overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white shadow-[0_30px_80px_-55px_rgba(15,23,42,0.45)]"
        >
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-slate-50 text-slate-500">
                        <tr>
                            <th class="px-4 py-3 font-medium">Event</th>
                            <th class="px-4 py-3 font-medium">Location</th>
                            <th class="px-4 py-3 font-medium">Start time</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 font-medium">Attendees</th>
                            <th class="px-4 py-3 font-medium"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="event in events"
                            :key="event.id"
                            class="border-t border-slate-100 align-top transition hover:bg-slate-50/70"
                        >
                            <td class="px-4 py-4">
                                <div class="flex items-start gap-3">
                                    <img
                                        :src="event.hero_image"
                                        :alt="event.title"
                                        class="h-14 w-14 rounded-2xl object-cover"
                                    />
                                    <div>
                                        <p class="font-semibold text-slate-900">
                                            {{ event.title }}
                                        </p>
                                        <p
                                            class="mt-1 text-xs tracking-[0.28em] text-slate-400 uppercase"
                                        >
                                            {{ eventTypeLabel(event.type) }}
                                        </p>
                                        <p
                                            class="mt-2 max-w-md text-sm text-slate-600"
                                        >
                                            {{ event.excerpt }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-slate-700">
                                <p class="font-medium">
                                    {{ event.location.label }}
                                </p>
                                <p class="mt-1 text-xs text-slate-500">
                                    {{ event.venue_name }}
                                </p>
                            </td>
                            <td class="px-4 py-4 text-slate-700">
                                <p>
                                    {{
                                        formatEventTime(
                                            event.schedule.starts_at,
                                            event.location.timezone,
                                        )
                                    }}
                                </p>
                                <p class="mt-1 text-xs text-slate-500">
                                    {{ event.location.timezone }}
                                </p>
                            </td>
                            <td class="px-4 py-4">
                                <Badge
                                    :class="eventStatusTone(event.status)"
                                    variant="outline"
                                >
                                    {{ event.status }}
                                </Badge>
                            </td>
                            <td class="px-4 py-4 text-slate-700">
                                {{ event.attendee_count }}
                            </td>
                            <td class="px-4 py-4 text-right">
                                <Link
                                    :href="`/events/${event.id}`"
                                    class="inline-flex rounded-full border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:bg-slate-100"
                                >
                                    View
                                </Link>
                            </td>
                        </tr>
                        <tr
                            v-if="
                                !loading && hasLoadedOnce && events.length === 0
                            "
                        >
                            <td
                                colspan="6"
                                class="px-4 py-10 text-center text-slate-500"
                            >
                                No events found for the current filters.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div
            v-if="error"
            class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"
        >
            {{ error }}
        </div>

        <div ref="sentinel"></div>

        <div class="flex items-center justify-between text-sm text-slate-500">
            <span v-if="loading">Loading more events...</span>
            <span v-else-if="hasLoadedOnce"
                >Loaded {{ loadedSize }} in {{ loadedSeconds }}s</span
            >
            <span v-if="!hasMore && events.length > 0">End of results</span>
        </div>
    </div>
</template>
