import { onBeforeUnmount, onMounted, ref } from 'vue';
import { useEventFeed } from '@/composables/useEventFeed';
import type { EventFilters } from '@/types';

interface EventVisualFeedOptions {
    filters: EventFilters;
    perPage: number;
}

export function useEventVisualFeed(options: EventVisualFeedOptions) {
    const initialFilters: EventFilters = {
        ...options.filters,
        sort: 'upcoming',
    };

    const feed = useEventFeed({
        initialFilters,
        perPage: options.perPage,
    });

    const sentinel = ref<HTMLElement | null>(null);
    let observer: IntersectionObserver | null = null;

    function resetFilters() {
        Object.assign(feed.filters, initialFilters);
        void feed.applyFilters();
    }

    onMounted(() => {
        observer = new IntersectionObserver(
            (entries) => {
                if (entries[0]?.isIntersecting) {
                    void feed.loadMore();
                }
            },
            { rootMargin: '400px' },
        );

        if (sentinel.value) {
            observer.observe(sentinel.value);
        }

        void feed.applyFilters();
    });

    onBeforeUnmount(() => observer?.disconnect());

    return {
        ...feed,
        activeFilters: feed.filters,
        resetFilters,
        sentinel,
    };
}
