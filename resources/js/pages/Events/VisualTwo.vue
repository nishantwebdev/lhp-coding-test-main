<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref, reactive, computed, onMounted, onBeforeUnmount, watch } from 'vue';
import { useDebounceFn } from '@vueuse/core';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { MapPin, Calendar, Crosshair, Search, Building } from '@lucide/vue';
import EventDetailsModal from '@/components/EventDetailsModal.vue';

const LOCATIONS = [
    {
        country: 'United States',
        cities: [
            { name: 'New York', lat: 40.7128, lng: -74.0060 }, { name: 'Los Angeles', lat: 34.0522, lng: -118.2437 },
            { name: 'Chicago', lat: 41.8781, lng: -87.6298 }, { name: 'Houston', lat: 29.7604, lng: -95.3698 },
            { name: 'Phoenix', lat: 33.4484, lng: -112.0740 }, { name: 'Philadelphia', lat: 39.9526, lng: -75.1652 },
            { name: 'San Antonio', lat: 29.4241, lng: -98.4936 }, { name: 'San Diego', lat: 32.7157, lng: -117.1611 },
            { name: 'Dallas', lat: 32.7767, lng: -96.7970 }, { name: 'San Jose', lat: 37.3382, lng: -121.8863 },
            { name: 'Austin', lat: 30.2672, lng: -97.7431 }, { name: 'San Francisco', lat: 37.7749, lng: -122.4194 },
            { name: 'Seattle', lat: 47.6062, lng: -122.3321 }, { name: 'Denver', lat: 39.7392, lng: -104.9903 },
            { name: 'Boston', lat: 42.3601, lng: -71.0589 }, { name: 'Las Vegas', lat: 36.1699, lng: -115.1398 },
            { name: 'Miami', lat: 25.7617, lng: -80.1918 }, { name: 'Atlanta', lat: 33.7490, lng: -84.3880 },
            { name: 'Washington D.C.', lat: 38.9072, lng: -77.0369 }, { name: 'Nashville', lat: 36.1627, lng: -86.7816 },
            { name: 'Portland', lat: 45.5152, lng: -122.6784 }, { name: 'New Orleans', lat: 29.9511, lng: -90.0715 },
        ]
    },
    {
        country: 'Canada',
        cities: [
            { name: 'Toronto', lat: 43.6532, lng: -79.3832 }, { name: 'Montreal', lat: 45.5019, lng: -73.5674 },
            { name: 'Vancouver', lat: 49.2827, lng: -123.1207 }, { name: 'Calgary', lat: 51.0447, lng: -114.0719 },
            { name: 'Ottawa', lat: 45.4215, lng: -75.6972 }, { name: 'Edmonton', lat: 53.5461, lng: -113.4938 },
            { name: 'Quebec City', lat: 46.8139, lng: -71.2080 }, { name: 'Winnipeg', lat: 49.8951, lng: -97.1384 },
        ]
    },
    {
        country: 'Mexico',
        cities: [
            { name: 'Mexico City', lat: 19.4326, lng: -99.1332 }, { name: 'Guadalajara', lat: 20.6597, lng: -103.3496 },
            { name: 'Monterrey', lat: 25.6866, lng: -100.3161 }, { name: 'Puebla', lat: 19.0414, lng: -98.2063 },
            { name: 'Tijuana', lat: 32.5149, lng: -117.0382 }, { name: 'Cancun', lat: 21.1619, lng: -86.8515 },
            { name: 'Merida', lat: 20.9674, lng: -89.5926 },
        ]
    },
    {
        country: 'Europe',
        cities: [
            { name: 'London, UK', lat: 51.5074, lng: -0.1278 }, { name: 'Paris, FR', lat: 48.8566, lng: 2.3522 },
            { name: 'Berlin, DE', lat: 52.5200, lng: 13.4050 }, { name: 'Madrid, ES', lat: 40.4168, lng: -3.7038 },
            { name: 'Rome, IT', lat: 41.9028, lng: 12.4964 }, { name: 'Amsterdam, NL', lat: 52.3676, lng: 4.9041 },
            { name: 'Barcelona, ES', lat: 41.3851, lng: 2.1734 }, { name: 'Munich, DE', lat: 48.1351, lng: 11.5820 },
            { name: 'Milan, IT', lat: 45.4642, lng: 9.1900 }, { name: 'Vienna, AT', lat: 48.2082, lng: 16.3738 },
            { name: 'Prague, CZ', lat: 50.0755, lng: 14.4378 }, { name: 'Lisbon, PT', lat: 38.7223, lng: -9.1393 },
            { name: 'Dublin, IE', lat: 53.3498, lng: -6.2603 }, { name: 'Copenhagen, DK', lat: 55.6761, lng: 12.5683 },
            { name: 'Stockholm, SE', lat: 59.3293, lng: 18.0686 }, { name: 'Oslo, NO', lat: 59.9139, lng: 10.7522 },
            { name: 'Helsinki, FI', lat: 60.1699, lng: 24.9384 }, { name: 'Brussels, BE', lat: 50.8503, lng: 4.3517 },
            { name: 'Zurich, CH', lat: 47.3769, lng: 8.5417 }, { name: 'Warsaw, PL', lat: 52.2297, lng: 21.0122 },
            { name: 'Budapest, HU', lat: 47.4979, lng: 19.0402 }, { name: 'Athens, GR', lat: 37.9838, lng: 23.7275 },
            { name: 'Lyon, FR', lat: 45.7640, lng: 4.8357 }, { name: 'Hamburg, DE', lat: 53.5511, lng: 9.9937 },
            { name: 'Manchester, UK', lat: 53.4808, lng: -2.2426 }, { name: 'Edinburgh, UK', lat: 55.9533, lng: -3.1883 },
            { name: 'Frankfurt, DE', lat: 50.1109, lng: 8.6821 }, { name: 'Krakow, PL', lat: 50.0647, lng: 19.9450 },
            { name: 'Porto, PT', lat: 41.1579, lng: -8.6291 }, { name: 'Naples, IT', lat: 40.8518, lng: 14.2681 },
        ]
    },
    {
        country: 'Global',
        cities: [
            { name: 'Tokyo, JP', lat: 35.6762, lng: 139.6503 }, { name: 'Seoul, KR', lat: 37.5665, lng: 126.9780 },
            { name: 'Singapore', lat: 1.3521, lng: 103.8198 }, { name: 'Sydney, AU', lat: -33.8688, lng: 151.2093 },
            { name: 'Melbourne, AU', lat: -37.8136, lng: 144.9631 }, { name: 'Dubai, AE', lat: 25.2048, lng: 55.2708 },
            { name: 'Sao Paulo, BR', lat: -23.5505, lng: -46.6333 }, { name: 'Buenos Aires, AR', lat: -34.6037, lng: -58.3816 },
        ]
    }
];

const form = reactive({
    search: '',
    from: '',
    to: '',
    lat: '',
    lng: '',
    radius: '50',
    locationName: '',
});

const rows = ref<any[]>([]);
const page = ref(0);
const lastPage = ref<number | null>(null);
const total = ref<number | null>(null);
const loading = ref(false);
const hasLoadedOnce = ref(false);
const imagePrefix = ref('');

const sentinel = ref<HTMLElement | null>(null);
let observer: IntersectionObserver | null = null;

const isModalOpen = ref(false);
const selectedEvent = ref<any>(null);

function openModal(event: any) {
    selectedEvent.value = event;
    isModalOpen.value = true;
}

const hasMore = computed(() => lastPage.value === null || page.value < lastPage.value);

async function loadMore(isNewFilter = false) {
    if (loading.value || (!hasMore.value && !isNewFilter)) return;
    
    loading.value = true;

    const params = new URLSearchParams({ page: String(isNewFilter ? 1 : page.value + 1) });
    if (form.search) params.set('search', form.search);
    if (form.from) params.set('from', form.from);
    if (form.to) params.set('to', form.to);
    if (form.lat && form.lng) {
        params.set('lat', String(form.lat));
        params.set('lng', String(form.lng));
        params.set('radius', form.radius);
    }

    try {
        const response = await fetch(`/events/data?${params.toString()}`, {
            headers: { Accept: 'application/json' },
        });
        const payload = await response.json();
        
        if (payload.image_prefix) {
            imagePrefix.value = payload.image_prefix;
        }

        if (isNewFilter) {
            rows.value = payload.data;
        } else {
            rows.value.push(...payload.data);
        }
        
        page.value = payload.current_page;
        lastPage.value = payload.last_page;
        total.value = payload.total;
        hasLoadedOnce.value = true;
    } finally {
        loading.value = false;
    }
}

const applyFilters = () => loadMore(true);

const debouncedSearch = useDebounceFn(() => {
    applyFilters();
}, 500);

watch(() => form.search, () => debouncedSearch());
watch([() => form.from, () => form.to, () => form.radius], () => applyFilters());
watch(() => form.locationName, (name) => {
    if (!name) {
        form.lat = '';
        form.lng = '';
        applyFilters();
        return;
    }
    if (name === 'current') return;

    let city = null;
    for (const group of LOCATIONS) {
        city = group.cities.find(c => c.name === name);
        if (city) break;
    }

    if (city) {
        form.lat = city.lat.toString();
        form.lng = city.lng.toString();
        applyFilters();
    }
});

function useCurrentLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                form.locationName = 'current';
                form.lat = position.coords.latitude.toString();
                form.lng = position.coords.longitude.toString();
                applyFilters();
            },
            (error) => {
                console.error("Geolocation error:", error);
                alert(`Location access failed: ${error.message}\n\nBrowsers block location requests if the site isn't secure (HTTPS or 'localhost'). Try using http://localhost:5173 instead of an IP address, or check your browser's site settings.`);
            },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
        );
    } else {
        alert("Geolocation is not supported by your browser.");
    }
}

function formatTime(timestamp: number) {
    if (!timestamp) return 'TBA';
    return new Intl.DateTimeFormat(navigator.language, {
        month: 'short', day: 'numeric', year: 'numeric',
        hour: 'numeric', minute: 'numeric', timeZoneName: 'short'
    }).format(new Date(timestamp * 1000));
}

function getImageUrl(event: any) {
    const prefix = imagePrefix.value || '/storage/images/events/';
    if (event.payload?.images && event.payload.images.length > 0) {
        return `${prefix}${event.payload.images[0]}`;
    }
    return `${prefix}placeholder.jpg`;
}

// Simple IntersectionObserver for revealing cards
const animateObserver = ref<IntersectionObserver | null>(null);

onMounted(() => {
    observer = new IntersectionObserver(
        (entries) => {
            if (entries[0]?.isIntersecting) {
                loadMore();
            }
        },
        { rootMargin: '400px' },
    );
    if (sentinel.value) {
        observer.observe(sentinel.value);
    }
    loadMore(true);
});

onBeforeUnmount(() => {
    observer?.disconnect();
    animateObserver.value?.disconnect();
});
</script>

<template>
    <div class="h-full bg-background relative overflow-y-auto">
        <Head title="Cinematic Timeline" />

        <!-- Ambient Background -->
        <div class="fixed inset-0 pointer-events-none opacity-40 dark:opacity-20 mix-blend-screen dark:mix-blend-color-dodge transition-colors duration-1000 z-0">
            <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-primary/30 rounded-full blur-[100px]"></div>
            <div class="absolute bottom-1/4 right-1/4 w-[30rem] h-[30rem] bg-blue-500/20 rounded-full blur-[120px]"></div>
        </div>

        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            
            <!-- Sticky Header & Filters -->
            <div class="sticky top-0 z-50 backdrop-blur-xl bg-background/80 border border-border/50 shadow-sm rounded-2xl p-4 mb-12 animate-in slide-in-from-top-4 duration-500">
                <div class="flex flex-col gap-4">
                    <div class="flex items-center justify-between" v-if="false">
                        <h1 class="text-3xl font-extrabold tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-primary to-blue-500">
                            Timeline
                        </h1>
                        <Badge variant="secondary" class="rounded-full px-3 py-1 text-sm">{{ total !== null ? total.toLocaleString() : '...' }} Events</Badge>
                    </div>

                    <div class="flex flex-col md:flex-row gap-4 items-end">
                        <div class="flex flex-col gap-1.5 flex-1 w-full">
                            <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider ml-1">Search</label>
                            <div class="relative">
                                <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                                <input
                                    v-model="form.search"
                                    type="text"
                                    placeholder="Search experiences..."
                                    class="w-full h-10 rounded-full border border-input bg-background/50 pl-10 pr-4 text-sm focus:ring-2 focus:ring-primary transition-shadow"
                                />
                            </div>
                        </div>
                        
                        <div class="flex flex-col gap-1.5 w-full md:w-auto">
                            <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider ml-1">Location</label>
                            <div class="flex gap-2">
                                <select v-model="form.locationName" class="w-full md:w-40 h-10 rounded-full border border-input bg-background/50 px-4 text-sm">
                                    <option value="">Global</option>
                                    <option value="current" hidden>Current Location</option>
                                    <optgroup v-for="group in LOCATIONS" :key="group.country" :label="group.country">
                                        <option v-for="city in group.cities" :key="city.name" :value="city.name">{{ city.name }}</option>
                                    </optgroup>
                                </select>
                                <Button variant="outline" size="icon" class="rounded-full h-10 w-10 shrink-0" @click="useCurrentLocation" title="Use my location">
                                    <Crosshair class="h-4 w-4" />
                                </Button>
                            </div>
                        </div>
                        
                        <div class="flex gap-3 w-full md:w-auto">
                            <div class="flex flex-col gap-1.5">
                                <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider ml-1">Radius</label>
                                <select v-model="form.radius" class="w-full md:w-24 h-10 rounded-full border border-input bg-background/50 px-4 text-sm" :disabled="!form.lat">
                                    <option value="10">10 km</option>
                                    <option value="50">50 km</option>
                                    <option value="100">100 km</option>
                                    <option value="500">500 km</option>
                                </select>
                            </div>
                            <div class="flex flex-col gap-1.5 flex-1">
                                <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider ml-1">From</label>
                                <input v-model="form.from" type="date" class="w-full md:w-32 h-10 rounded-full border border-input bg-background/50 px-4 text-sm" placeholder="From" />
                            </div>
                            <div class="flex flex-col gap-1.5 flex-1">
                                <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider ml-1">To</label>
                                <input v-model="form.to" type="date" class="w-full md:w-32 h-10 rounded-full border border-input bg-background/50 px-4 text-sm" placeholder="To" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Timeline View -->
            <div class="relative max-w-5xl mx-auto pb-20">
                <!-- Central line -->
                <div class="absolute left-6 md:left-1/2 top-4 bottom-0 w-0.5 bg-gradient-to-b from-primary via-border to-transparent transform md:-translate-x-1/2 opacity-30"></div>
                
                <div class="space-y-16">
                    <div 
                        v-for="(event, index) in rows" 
                        :key="event.id"
                        class="relative flex flex-col md:flex-row items-center w-full group animate-in fade-in slide-in-from-bottom-8 duration-700 ease-out fill-mode-both"
                        :style="{ animationDelay: `${(index % 10) * 100}ms` }"
                        :class="index % 2 === 0 ? 'md:flex-row-reverse' : ''"
                    >
                        <!-- Timeline Dot -->
                        <div class="absolute left-6 md:left-1/2 w-4 h-4 rounded-full bg-background border-4 border-primary transform -translate-x-1/2 z-20 group-hover:scale-150 group-hover:bg-primary transition-all duration-300"></div>

                        <!-- Card Container -->
                        <div class="w-full md:w-1/2 pl-16 md:pl-0" :class="index % 2 === 0 ? 'md:pr-16 text-left' : 'md:pl-16 text-left'">
                            <div class="w-full text-left">
                                <div class="rounded-3xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-500 bg-card border border-border/50 hover:border-primary/50 group-hover:-translate-y-2">
                                    <div class="aspect-[16/9] w-full overflow-hidden relative">
                                        <img :src="getImageUrl(event)" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-in-out" />
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/20 to-transparent"></div>
                                        <div class="absolute bottom-5 left-5 right-5 text-left">
                                            <div class="flex flex-wrap gap-2 mb-3">
                                                <Badge variant="secondary" class="bg-white/20 hover:bg-white/30 backdrop-blur-md text-white border-0">{{ event.payload?.category || event.type }}</Badge>
                                                <Badge v-if="event.status === 'cancelled'" variant="destructive" class="border-0">Cancelled</Badge>
                                                <Badge v-if="event.status === 'sold_out'" variant="secondary" class="bg-red-500/90 text-white hover:bg-red-600/90 border-0">Sold Out</Badge>
                                            </div>
                                            <h3 class="text-2xl sm:text-3xl font-bold text-white leading-tight drop-shadow-lg">{{ event.payload?.name }}</h3>
                                        </div>
                                    </div>
                                    <div class="p-6 flex flex-col gap-4 relative bg-card">
                                        <div class="absolute top-0 right-6 transform -translate-y-1/2 bg-primary text-primary-foreground px-4 py-1 rounded-full text-sm font-bold shadow-lg shadow-primary/30">
                                            {{ event.payload?.pricing?.currency || 'USD' }} {{ event.payload?.pricing?.min_price || 'Free' }}
                                        </div>
                                        <div class="flex items-center gap-3 text-sm text-muted-foreground">
                                            <Calendar class="h-4 w-4 shrink-0 text-primary" />
                                            <span>{{ formatTime(event.created_time) }}</span>
                                        </div>
                                        <div class="flex items-center gap-3 text-sm text-muted-foreground">
                                            <Building class="h-4 w-4 shrink-0 text-primary" />
                                            <span class="line-clamp-1">{{ event.payload?.venue?.name || 'Unknown Venue' }}</span>
                                        </div>
                                        <div v-if="event.payload?.venue?.address" class="flex items-center gap-3 text-sm text-muted-foreground">
                                            <MapPin class="h-4 w-4 shrink-0 text-primary" />
                                            <span class="line-clamp-1">{{ event.payload.venue.address }}</span>
                                        </div>
                                        <div class="pt-4 flex">
                                            <Button @click.stop="openModal(event)" variant="default" class="rounded-full px-6 transition-transform hover:scale-105 cursor-pointer">View Details</Button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="!loading && hasLoadedOnce && rows.length === 0" class="text-center py-20 text-muted-foreground text-lg">
                    No events found matching your criteria.
                </div>

                <div ref="sentinel" class="h-20 mt-10 flex items-center justify-center">
                    <div v-if="loading" class="flex gap-2 items-center text-primary">
                        <div class="w-2 h-2 rounded-full bg-primary animate-bounce"></div>
                        <div class="w-2 h-2 rounded-full bg-primary animate-bounce" style="animation-delay: 0.1s"></div>
                        <div class="w-2 h-2 rounded-full bg-primary animate-bounce" style="animation-delay: 0.2s"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <EventDetailsModal
            v-model:isOpen="isModalOpen"
            :event="selectedEvent"
            :imagePrefix="imagePrefix || '/storage/images/events/'"
        />
    </div>
</template>
