<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { CalendarDays, MapPin, Ticket, Users } from '@lucide/vue';
import { computed, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    eventStatusTone,
    eventTypeLabel,
    formatEventTime,
    formatViewerTime,
} from '@/lib/eventFormatting';
import type { PresentedEvent } from '@/types';

const props = defineProps<{
    event: PresentedEvent;
    attendanceStatuses: { value: string; label: string }[];
}>();

const page = usePage<{ errors: Record<string, string> }>();
const activeImage = ref(props.event.gallery[0] ?? props.event.hero_image);
const eventError = computed(() => page.props.errors?.event ?? '');

const form = useForm({
    name: '',
    email: '',
    attendance_status: props.attendanceStatuses[0]?.value ?? 'interested',
});

function submit() {
    form.post(`/events/${props.event.id}/attendees`, {
        preserveScroll: true,
        onSuccess: () => form.reset('name', 'email'),
    });
}
</script>

<template>
    <Head :title="event.title" />

    <div
        class="bg-[linear-gradient(180deg,_#f8fafc_0%,_#fff7ed_55%,_#f8fafc_100%)] p-4 md:p-6"
    >
        <div class="mx-auto flex max-w-6xl flex-col gap-6">
            <Link
                href="/events-visual-1"
                class="text-sm font-medium text-slate-600 hover:text-slate-900"
            >
                ← Back to visual explorer
            </Link>

            <section class="grid gap-6 xl:grid-cols-[1.25fr_0.75fr]">
                <div class="space-y-5">
                    <article
                        class="overflow-hidden rounded-[2.5rem] border border-slate-200/70 bg-white shadow-[0_30px_90px_-60px_rgba(15,23,42,0.55)]"
                    >
                        <img
                            :src="activeImage"
                            :alt="event.title"
                            class="h-[28rem] w-full object-cover"
                        />
                        <div class="grid grid-cols-3 gap-3 p-4">
                            <button
                                v-for="image in event.gallery"
                                :key="image"
                                type="button"
                                class="overflow-hidden rounded-[1.25rem] border transition"
                                :class="
                                    image === activeImage
                                        ? 'border-slate-900'
                                        : 'border-slate-200 hover:border-slate-300'
                                "
                                @click="activeImage = image"
                            >
                                <img
                                    :src="image"
                                    :alt="`${event.title} gallery image`"
                                    class="h-24 w-full object-cover"
                                />
                            </button>
                        </div>
                    </article>

                    <article
                        class="rounded-[2.25rem] border border-slate-200/70 bg-white p-6 shadow-[0_28px_80px_-55px_rgba(15,23,42,0.45)]"
                    >
                        <div class="flex flex-wrap items-center gap-3">
                            <Badge
                                :class="eventStatusTone(event.status)"
                                variant="outline"
                                >{{ event.status }}</Badge
                            >
                            <span
                                class="rounded-full bg-slate-100 px-3 py-1 text-xs tracking-[0.25em] text-slate-500 uppercase"
                            >
                                {{ eventTypeLabel(event.type) }}
                            </span>
                            <span
                                v-if="event.organizer.verified"
                                class="rounded-full bg-emerald-50 px-3 py-1 text-xs tracking-[0.25em] text-emerald-700 uppercase"
                            >
                                Verified organizer
                            </span>
                        </div>

                        <h1
                            class="mt-4 text-4xl font-semibold tracking-tight text-slate-950"
                        >
                            {{ event.title }}
                        </h1>
                        <p
                            class="mt-4 max-w-3xl text-base leading-7 text-slate-600"
                        >
                            {{ event.description }}
                        </p>

                        <div class="mt-6 grid gap-4 md:grid-cols-2">
                            <div class="rounded-[1.5rem] bg-slate-50 p-4">
                                <div class="flex items-start gap-3">
                                    <CalendarDays
                                        class="mt-1 size-5 shrink-0 text-slate-400"
                                    />
                                    <div>
                                        <p class="font-semibold text-slate-900">
                                            {{
                                                formatEventTime(
                                                    event.schedule.starts_at,
                                                    event.location.timezone,
                                                )
                                            }}
                                        </p>
                                        <p class="mt-1 text-sm text-slate-600">
                                            Your time:
                                            {{
                                                formatViewerTime(
                                                    event.schedule.starts_at,
                                                )
                                            }}
                                        </p>
                                        <p
                                            class="mt-1 text-xs tracking-[0.24em] text-slate-400 uppercase"
                                        >
                                            {{ event.location.timezone }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-[1.5rem] bg-slate-50 p-4">
                                <div class="flex items-start gap-3">
                                    <MapPin
                                        class="mt-1 size-5 shrink-0 text-slate-400"
                                    />
                                    <div>
                                        <p class="font-semibold text-slate-900">
                                            {{ event.location.label }}
                                        </p>
                                        <p class="mt-1 text-sm text-slate-600">
                                            {{ event.venue_name }}
                                        </p>
                                        <p
                                            class="mt-1 text-xs tracking-[0.24em] text-slate-400 uppercase"
                                        >
                                            {{ event.location.country_code }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex flex-wrap gap-3">
                            <span
                                class="inline-flex items-center gap-2 rounded-full bg-orange-50 px-4 py-2 text-sm text-orange-700"
                            >
                                <Ticket class="size-4" />
                                {{ event.pricing.label }}
                            </span>
                            <span
                                class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-4 py-2 text-sm text-slate-700"
                            >
                                <Users class="size-4" />
                                {{ event.attendee_count }} on the attendee list
                            </span>
                        </div>
                    </article>
                </div>

                <div class="space-y-5">
                    <section
                        id="attendance"
                        class="rounded-[2.25rem] border border-slate-200/70 bg-white p-6 shadow-[0_28px_80px_-55px_rgba(15,23,42,0.45)]"
                    >
                        <div class="flex items-end justify-between gap-4">
                            <div>
                                <p
                                    class="text-sm tracking-[0.3em] text-slate-400 uppercase"
                                >
                                    Attendance
                                </p>
                                <h2
                                    class="mt-2 text-2xl font-semibold text-slate-950"
                                >
                                    Join the list
                                </h2>
                            </div>
                            <span
                                class="rounded-full bg-slate-100 px-4 py-2 text-sm text-slate-600"
                            >
                                {{ event.attendee_count }} registered
                            </span>
                        </div>

                        <form class="mt-6 space-y-4" @submit.prevent="submit">
                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700"
                                    for="name"
                                    >Name</label
                                >
                                <input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    class="h-11 w-full rounded-2xl border border-slate-200 bg-white px-4 text-slate-900 transition outline-none focus:border-slate-400"
                                    placeholder="Your name"
                                />
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.name"
                                />
                            </div>

                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700"
                                    for="email"
                                    >Email</label
                                >
                                <input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    class="h-11 w-full rounded-2xl border border-slate-200 bg-white px-4 text-slate-900 transition outline-none focus:border-slate-400"
                                    placeholder="name@example.com"
                                />
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.email"
                                />
                            </div>

                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700"
                                    for="attendance_status"
                                    >Response</label
                                >
                                <select
                                    id="attendance_status"
                                    v-model="form.attendance_status"
                                    class="h-11 w-full rounded-2xl border border-slate-200 bg-white px-4 text-slate-900 transition outline-none focus:border-slate-400"
                                >
                                    <option
                                        v-for="status in attendanceStatuses"
                                        :key="status.value"
                                        :value="status.value"
                                    >
                                        {{ status.label }}
                                    </option>
                                </select>
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.attendance_status"
                                />
                            </div>

                            <InputError class="mt-2" :message="eventError" />

                            <Button
                                type="submit"
                                class="w-full"
                                :disabled="form.processing"
                            >
                                {{
                                    form.processing
                                        ? 'Submitting...'
                                        : 'Register attendance'
                                }}
                            </Button>
                        </form>
                    </section>

                    <section
                        class="rounded-[2.25rem] border border-slate-200/70 bg-white p-6 shadow-[0_28px_80px_-55px_rgba(15,23,42,0.45)]"
                    >
                        <div class="flex items-end justify-between gap-4">
                            <div>
                                <p
                                    class="text-sm tracking-[0.3em] text-slate-400 uppercase"
                                >
                                    Attendee list
                                </p>
                                <h2
                                    class="mt-2 text-2xl font-semibold text-slate-950"
                                >
                                    Who is in
                                </h2>
                            </div>
                            <span class="text-sm text-slate-500"
                                >{{ event.attendees?.length ?? 0 }} shown</span
                            >
                        </div>

                        <div
                            v-if="event.attendees?.length"
                            class="mt-5 space-y-3"
                        >
                            <div
                                v-for="attendee in event.attendees"
                                :key="attendee.id"
                                class="flex items-center justify-between rounded-[1.25rem] border border-slate-200/70 px-4 py-3"
                            >
                                <div>
                                    <p class="font-medium text-slate-900">
                                        {{ attendee.name }}
                                    </p>
                                    <p class="text-sm text-slate-500">
                                        {{ attendee.email }}
                                    </p>
                                </div>
                                <span
                                    class="rounded-full bg-slate-100 px-3 py-1 text-xs tracking-[0.24em] text-slate-600 uppercase"
                                >
                                    {{ attendee.attendance_status }}
                                </span>
                            </div>
                        </div>
                        <p v-else class="mt-5 text-sm text-slate-500">
                            No attendees yet. Be the first to register.
                        </p>
                    </section>
                </div>
            </section>
        </div>
    </div>
</template>
