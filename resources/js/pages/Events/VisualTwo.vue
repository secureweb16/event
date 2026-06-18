<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { CalendarRange, Clock3, MapPin, Users } from '@lucide/vue';
import { computed } from 'vue';
import EventFilterBar from '@/components/events/EventFilterBar.vue';
import { useEventVisualFeed } from '@/composables/useEventVisualFeed';
import {
    eventStatusTone,
    eventTypeLabel,
    formatCountLabel,
    formatEventTime,
    formatGroupDate,
    formatViewerTime,
    groupEventsByDay,
} from '@/lib/eventFormatting';
import type { EventFilters, EventLocationOption } from '@/types';

const props = defineProps<{
    filters: EventFilters;
    statuses: string[];
    locations: EventLocationOption[];
}>();

const {
    activeFilters,
    events,
    total,
    loading,
    error,
    hasMore,
    hasLoadedOnce,
    applyFilters,
    resetFilters,
    sentinel,
} = useEventVisualFeed({
    filters: props.filters,
    perPage: 16,
});

const groupedEvents = computed(() => groupEventsByDay(events.value));
</script>

<template>
    <Head title="Events Visual 2" />

    <div
        class="p-4 md:p-6"
    >
        <div class="mx-auto max-w-6xl space-y-6">
            <section>
                <EventFilterBar
                    v-model:filters="activeFilters"
                    :statuses="statuses"
                    :locations="locations"
                    :loading="loading"
                    subtitle="Filter by city, date range, and status."
                    @apply="applyFilters"
                    @clear="resetFilters"
                />
            </section>

            <main class="space-y-6">
                <section
                    v-for="group in groupedEvents"
                    :key="group.date"
                    class="rounded-[2.25rem] border border-slate-200/70 bg-white p-5 shadow-[0_28px_80px_-55px_rgba(15,23,42,0.45)]"
                >
                    <div
                            class="mb-5 flex flex-col gap-3 border-b border-slate-100 pb-5 md:flex-row md:items-end md:justify-between"
                    >
                        <div>
                            <p
                                class="text-xs tracking-[0.18em] text-slate-400"
                            >
                                Local event day
                            </p>
                            <h2
                                class="mt-2 text-2xl font-semibold text-slate-950"
                            >
                                {{ formatGroupDate(group.date) }}
                            </h2>
                        </div>
                        <div
                            class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-4 py-2 text-sm text-slate-600"
                        >
                            <CalendarRange class="size-4" />
                            {{ formatCountLabel(group.items.length, 'event') }}
                        </div>
                    </div>

                    <div class="space-y-4">
                        <article
                            v-for="event in group.items"
                            :key="event.id"
                            class="grid gap-4 rounded-[1.5rem] border border-slate-200/70 p-4 transition hover:border-slate-300 hover:bg-slate-50/70 md:grid-cols-[180px_1fr]"
                        >
                            <div class="overflow-hidden rounded-[1.35rem]">
                                <img
                                    :src="event.hero_image"
                                    :alt="event.title"
                                    class="h-full min-h-40 w-full object-cover"
                                />
                            </div>

                            <div class="flex flex-col gap-4">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span
                                        class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-medium tracking-[0.24em] text-indigo-700 uppercase"
                                    >
                                        {{ eventTypeLabel(event.type) }}
                                    </span>
                                    <span
                                        :class="eventStatusTone(event.status)"
                                        class="rounded-full px-3 py-1 text-xs font-medium ring-1"
                                    >
                                        {{ event.status }}
                                    </span>
                                </div>

                                <div>
                                    <h3
                                        class="text-2xl font-semibold text-slate-950"
                                    >
                                        {{ event.title }}
                                    </h3>
                                    <p
                                        class="mt-2 text-sm leading-6 text-slate-600"
                                    >
                                        {{ event.description }}
                                    </p>
                                </div>

                                <div
                                    class="grid gap-3 text-sm text-slate-600 md:grid-cols-2"
                                >
                                    <div class="flex items-start gap-3">
                                        <Clock3
                                            class="mt-0.5 size-4 shrink-0 text-slate-400"
                                        />
                                        <div>
                                            <p
                                                class="font-medium text-slate-900"
                                            >
                                                {{
                                                    formatEventTime(
                                                        event.schedule
                                                            .starts_at,
                                                        event.location.timezone,
                                                    )
                                                }}
                                            </p>
                                            <p>
                                                Your time:
                                                {{
                                                    formatViewerTime(
                                                        event.schedule
                                                            .starts_at,
                                                    )
                                                }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex items-start gap-3">
                                        <MapPin
                                            class="mt-0.5 size-4 shrink-0 text-slate-400"
                                        />
                                        <div>
                                            <p
                                                class="font-medium text-slate-900"
                                            >
                                                {{ event.location.label }}
                                            </p>
                                            <p>{{ event.venue_name }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4"
                                >
                                    <div class="flex flex-wrap gap-2">
                                        <span
                                            class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-sm text-slate-600"
                                        >
                                            <Users class="size-4" />
                                            {{ event.attendee_count }}
                                            registered
                                        </span>
                                        <span
                                            class="rounded-full bg-emerald-50 px-3 py-1 text-sm text-emerald-700"
                                        >
                                            {{ event.pricing.label }}
                                        </span>
                                    </div>

                                    <Link
                                        :href="`/events/${event.id}`"
                                        class="inline-flex items-center rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-800 transition hover:border-slate-300 hover:bg-white"
                                    >
                                        Open event
                                    </Link>
                                </div>
                            </div>
                        </article>
                    </div>
                </section>

                <div
                    v-if="error"
                    class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"
                >
                    {{ error }}
                </div>

                <div
                    v-if="
                        !loading && hasLoadedOnce && groupedEvents.length === 0
                    "
                    class="rounded-[2rem] border border-dashed border-slate-300 bg-white px-6 py-12 text-center text-slate-500"
                >
                    No events matched the selected location and date window.
                </div>

                <div ref="sentinel"></div>

                <div
                    class="flex items-center justify-between text-sm text-slate-500"
                >
                    <span v-if="loading">Loading more schedule blocks...</span>
                    <span v-else-if="hasLoadedOnce"
                        >{{ events.length.toLocaleString() }} events
                        loaded</span
                    >
                    <span v-if="!hasMore && events.length > 0"
                        >All matching events loaded</span
                    >
                </div>
            </main>
        </div>
    </div>
</template>
