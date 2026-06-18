<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { CalendarDays, MapPin } from '@lucide/vue';
import EventFilterBar from '@/components/events/EventFilterBar.vue';
import { useEventVisualFeed } from '@/composables/useEventVisualFeed';
import { eventStatusTone, eventTypeLabel, formatEventTime } from '@/lib/eventFormatting';
import type { EventFilters, EventLocationOption } from '@/types';

const props = defineProps<{
    filters: EventFilters;
    statuses: string[];
    locations: EventLocationOption[];
}>();

const {
    activeFilters,
    events,
    loading,
    error,
    hasMore,
    hasLoadedOnce,
    applyFilters,
    resetFilters,
    sentinel,
} = useEventVisualFeed({
    filters: props.filters,
    perPage: 12,
});
</script>

<template>
    <Head title="Events Visual 1" />

    <div class="bg-background p-4 md:p-6">
        <div class="mx-auto flex max-w-7xl flex-col gap-6">
            <EventFilterBar
                v-model:filters="activeFilters"
                :statuses="statuses"
                :locations="locations"
                :loading="loading"
                subtitle="Filter upcoming events by city, date range, and publishing state."
                @apply="applyFilters"
                @clear="resetFilters"
            />

            <section class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                <article
                    v-for="event in events"
                    :key="event.id"
                    class="group overflow-hidden rounded-[2rem] border border-slate-200/70 bg-white shadow-[0_28px_70px_-55px_rgba(15,23,42,0.55)] transition duration-300 hover:-translate-y-1"
                >
                    <div class="grid grid-cols-[1.4fr_0.8fr] gap-2 p-2">
                        <img
                            :src="event.gallery[0]"
                            :alt="event.title"
                            class="h-56 w-full rounded-[1.5rem] object-cover"
                        />
                        <div class="grid gap-2">
                            <img
                                v-for="image in event.gallery.slice(1)"
                                :key="image"
                                :src="image"
                                :alt="`${event.title} gallery image`"
                                class="h-[6.75rem] w-full rounded-[1.2rem] object-cover"
                            />
                        </div>
                    </div>
                    <div class="p-5 pt-2">
                        <div class="flex items-center justify-between gap-3">
                            <span
                                class="text-xs tracking-[0.26em] text-slate-400 uppercase"
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
                        <h3 class="mt-3 text-xl font-semibold text-slate-900">
                            {{ event.title }}
                        </h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            {{ event.excerpt }}
                        </p>

                        <div class="mt-4 space-y-3 text-sm text-slate-600">
                            <div class="flex items-start gap-3">
                                <MapPin
                                    class="mt-0.5 size-4 shrink-0 text-slate-400"
                                />
                                <span>
                                    {{ event.location.label }} •
                                    {{ event.venue_name }}
                                </span>
                            </div>
                            <div class="flex items-start gap-3">
                                <CalendarDays
                                    class="mt-0.5 size-4 shrink-0 text-slate-400"
                                />
                                <span>{{
                                    formatEventTime(
                                        event.schedule.starts_at,
                                        event.location.timezone,
                                    )
                                }}</span>
                            </div>
                        </div>

                        <div class="mt-5 flex items-center justify-between">
                            <div class="flex gap-2 text-xs text-slate-500">
                                <span class="rounded-full bg-slate-100 px-3 py-1">
                                    {{ event.attendee_count }} attending
                                </span>
                                <span
                                    class="rounded-full bg-orange-50 px-3 py-1 text-orange-700"
                                >
                                    {{ event.pricing.label }}
                                </span>
                            </div>
                            <Link
                                :href="`/events/${event.id}`"
                                class="text-sm font-semibold text-slate-900 hover:text-orange-600"
                            >
                                Details
                            </Link>
                        </div>
                    </div>
                </article>
            </section>

            <div
                v-if="error"
                class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"
            >
                {{ error }}
            </div>

            <div ref="sentinel"></div>

            <div class="flex items-center justify-between text-sm text-slate-500">
                <span v-if="loading">Loading more events...</span>
                <span v-else-if="hasLoadedOnce">
                    {{ events.length.toLocaleString() }} cards loaded
                </span>
                <span v-if="!hasMore && events.length > 0">End of line-up</span>
            </div>
        </div>
    </div>
</template>
