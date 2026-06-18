<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref, onMounted, onBeforeUnmount } from 'vue';

interface EventDetail {
    id: string;
    type: string;
    status: string;
    created_time: number | null;
    latitude: number | null;
    longitude: number | null;
    payload: Record<string, unknown>;
}

interface Attendee {
    id: number;
    email: string;
    created_at: string;
}

const props = defineProps<{ event: EventDetail }>();

const prettyPayload = computed(() => JSON.stringify(props.event.payload, null, 2));

const attendees = ref<Attendee[]>([]);
const page = ref(0);
const lastPage = ref<number | null>(null);
const total = ref<number | null>(null);
const loading = ref(false);
const hasLoadedOnce = ref(false);

const sentinel = ref<HTMLElement | null>(null);
let observer: IntersectionObserver | null = null;

const hasMore = computed(() => lastPage.value === null || page.value < lastPage.value);

async function loadMoreAttendees() {
    if (loading.value || !hasMore.value) return;
    loading.value = true;

    try {
        const response = await fetch(`/events/${props.event.id}/attendees/data?page=${page.value + 1}`, {
            headers: { Accept: 'application/json' },
        });
        const payload = await response.json();

        attendees.value.push(...payload.data);
        page.value = payload.current_page;
        lastPage.value = payload.last_page;
        total.value = payload.total;
        hasLoadedOnce.value = true;
    } finally {
        loading.value = false;
    }
}

function formatDate(dateStr: string) {
    if (!dateStr) return '—';
    return new Intl.DateTimeFormat(navigator.language, {
        month: 'short', day: 'numeric', year: 'numeric',
        hour: 'numeric', minute: 'numeric'
    }).format(new Date(dateStr));
}

onMounted(() => {
    observer = new IntersectionObserver(
        (entries) => {
            if (entries[0]?.isIntersecting) {
                loadMoreAttendees();
            }
        },
        { rootMargin: '400px' },
    );
    if (sentinel.value) {
        observer.observe(sentinel.value);
    }
    loadMoreAttendees();
});

onBeforeUnmount(() => observer?.disconnect());
</script>

<template>
    <Head :title="`Event ${event.id}`" />

    <div class="flex flex-col gap-4 p-4">
        <Link href="/events" class="text-sm text-primary hover:underline">← Back to events</Link>

        <h1 class="text-lg font-semibold">Event {{ event.id }}</h1>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start mt-4">
            <div>
                <h2 class="text-lg font-semibold mb-4">Payload</h2>
                <pre class="overflow-x-auto rounded-lg border p-4 text-xs bg-muted/30">{{ prettyPayload }}</pre>
            </div>

            <div>
                <div class="mb-4">
                    <h2 class="text-lg font-semibold">Attendees</h2>
                    <p class="text-sm text-muted-foreground">
                        {{ total !== null ? `${total.toLocaleString()} total attendees` : '—' }}
                    </p>
                </div>

            <div class="overflow-x-auto rounded-lg border">
                <table class="w-full text-sm">
                    <thead class="border-b bg-muted/50 text-left">
                        <tr>
                            <th class="px-3 py-2 font-medium">ID</th>
                            <th class="px-3 py-2 font-medium">Email</th>
                            <th class="px-3 py-2 font-medium">Registered At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="attendee in attendees" :key="attendee.id" class="border-b last:border-0">
                            <td class="px-3 py-2 font-mono text-xs">{{ attendee.id }}</td>
                            <td class="px-3 py-2">{{ attendee.email }}</td>
                            <td class="px-3 py-2 text-muted-foreground">{{ formatDate(attendee.created_at) }}</td>
                        </tr>
                        <tr v-if="!loading && hasLoadedOnce && attendees.length === 0">
                            <td colspan="3" class="px-3 py-8 text-center text-muted-foreground">No attendees found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div ref="sentinel"></div>

            <div class="py-2 text-sm text-gray-400">
                <span v-if="loading">loading...</span>
            </div>
            </div>
        </div>
    </div>
</template>
