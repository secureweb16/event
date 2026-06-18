export interface EventLocationOption {
    value: string;
    label: string;
    timezone: string;
}

export interface EventFilters {
    status: string | null;
    location: string | null;
    from: string | null;
    to: string | null;
    sort: 'newest' | 'upcoming';
}

export interface PresentedEvent {
    id: string;
    type: string;
    status: string;
    title: string;
    description: string;
    excerpt: string;
    venue_name: string;
    location: {
        slug: string;
        city: string;
        region: string;
        country: string;
        country_code: string;
        label: string;
        timezone: string;
    };
    coordinates: {
        latitude: number | null;
        longitude: number | null;
    };
    schedule: {
        starts_at: string;
        ends_at: string;
        starts_at_unix: number;
        ends_at_unix: number;
        timezone: string;
        local_date: string;
    };
    images: string[];
    hero_image: string;
    gallery: string[];
    organizer: {
        name: string;
        verified: boolean;
    };
    pricing: {
        amount: number | null;
        currency: string;
        label: string;
    };
    capacity: number | string | null;
    tags: string[];
    attendee_count: number;
    payload?: Record<string, unknown>;
    attendees?: {
        id: number;
        name: string;
        email: string;
        attendance_status: string;
        created_at: string | null;
    }[];
}
