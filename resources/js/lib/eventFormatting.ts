import type { PresentedEvent } from '@/types';

export function formatEventTime(
    iso: string,
    timezone: string,
    options?: Intl.DateTimeFormatOptions,
) {
    return new Intl.DateTimeFormat('en-US', {
        dateStyle: 'medium',
        timeStyle: 'short',
        timeZone: timezone,
        ...options,
    }).format(new Date(iso));
}

export function formatViewerTime(
    iso: string,
    options?: Intl.DateTimeFormatOptions,
) {
    return new Intl.DateTimeFormat('en-US', {
        dateStyle: 'medium',
        timeStyle: 'short',
        ...options,
    }).format(new Date(iso));
}

export function eventStatusTone(status: string) {
    switch (status) {
        case 'published':
            return 'bg-emerald-500/15 text-emerald-700 ring-emerald-500/30';
        case 'sold_out':
            return 'bg-amber-500/15 text-amber-700 ring-amber-500/30';
        case 'cancelled':
            return 'bg-rose-500/15 text-rose-700 ring-rose-500/30';
        default:
            return 'bg-slate-500/10 text-slate-700 ring-slate-500/20';
    }
}

export function eventTypeLabel(type: string) {
    return type.charAt(0).toUpperCase() + type.slice(1);
}

export function formatCountLabel(count: number, singular: string, plural = `${singular}s`) {
    return `${count} ${count === 1 ? singular : plural}`;
}

export function formatGroupDate(date: string) {
    return new Intl.DateTimeFormat('en-US', {
        weekday: 'long',
        month: 'long',
        day: 'numeric',
    }).format(new Date(`${date}T00:00:00`));
}

export function groupEventsByDay(events: PresentedEvent[]) {
    const groups = new Map<string, PresentedEvent[]>();

    for (const event of events) {
        const bucket = groups.get(event.schedule.local_date) ?? [];
        bucket.push(event);
        groups.set(event.schedule.local_date, bucket);
    }

    return Array.from(groups.entries()).map(([date, items]) => ({
        date,
        items,
    }));
}
