<script setup lang="ts">
import { Button } from '@/components/ui/button';
import type { EventFilters, EventLocationOption } from '@/types';

defineProps<{
    statuses: string[];
    locations: EventLocationOption[];
    loading?: boolean;
    title?: string;
    subtitle?: string;
}>();

const filters = defineModel<EventFilters>('filters', { required: true });

const emit = defineEmits<{
    apply: [];
    clear: [];
}>();
</script>

<template>
    <section
        class="rounded-[2rem] border border-slate-200/70 bg-white/85 p-5 shadow-[0_20px_60px_-40px_rgba(15,23,42,0.45)] backdrop-blur"
    >
        <div
            class="mb-4 flex flex-col gap-2 md:flex-row md:items-center md:justify-between"
        >
            <div>
                <p
                    v-if="title"
                    class="text-sm font-semibold tracking-[0.12em] text-slate-500"
                >
                    {{ title }}
                </p>
                <p v-if="subtitle" class="text-sm text-slate-600">
                    {{ subtitle }}
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <Button
                    type="button"
                    variant="outline"
                    :disabled="loading"
                    @click="emit('clear')"
                    >Reset</Button
                >
                <Button type="button" :disabled="loading" @click="emit('apply')"
                    >Apply filters</Button
                >
            </div>
        </div>

        <div class="grid gap-3 md:grid-cols-4">
            <label class="flex flex-col gap-2 text-sm text-slate-600">
                <span class="font-medium">Location</span>
                <select
                    v-model="filters.location"
                    class="h-11 rounded-2xl border border-slate-200 bg-white px-4 text-slate-900 transition outline-none focus:border-slate-400"
                >
                    <option :value="null">All locations</option>
                    <option
                        v-for="location in locations"
                        :key="location.value"
                        :value="location.value"
                    >
                        {{ location.label }}
                    </option>
                </select>
            </label>

            <label class="flex flex-col gap-2 text-sm text-slate-600">
                <span class="font-medium">Status</span>
                <select
                    v-model="filters.status"
                    class="h-11 rounded-2xl border border-slate-200 bg-white px-4 text-slate-900 transition outline-none focus:border-slate-400"
                >
                    <option :value="null">All statuses</option>
                    <option
                        v-for="status in statuses"
                        :key="status"
                        :value="status"
                    >
                        {{ status }}
                    </option>
                </select>
            </label>

            <label class="flex flex-col gap-2 text-sm text-slate-600">
                <span class="font-medium">From</span>
                <input
                    v-model="filters.from"
                    type="date"
                    :max="filters.to ?? undefined"
                    class="h-11 rounded-2xl border border-slate-200 bg-white px-4 text-slate-900 transition outline-none focus:border-slate-400"
                />
            </label>

            <label class="flex flex-col gap-2 text-sm text-slate-600">
                <span class="font-medium">To</span>
                <input
                    v-model="filters.to"
                    type="date"
                    :min="filters.from ?? undefined"
                    class="h-11 rounded-2xl border border-slate-200 bg-white px-4 text-slate-900 transition outline-none focus:border-slate-400"
                />
            </label>
        </div>
    </section>
</template>
