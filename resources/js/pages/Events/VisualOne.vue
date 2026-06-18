<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref, reactive, computed, onMounted, onBeforeUnmount, watch } from 'vue';
import { useDebounceFn } from '@vueuse/core';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { MapPin, Calendar, Clock, Crosshair, Building } from '@lucide/vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

const customIcon = L.icon({
    iconUrl: '/images/leaflet/marker-icon-2x.png',
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34],
    shadowUrl: '/images/leaflet/marker-shadow.png',
    shadowSize: [41, 41]
});

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
const mapContainer = ref<HTMLElement | null>(null);
let map: L.Map | null = null;
let markers: L.Marker[] = [];

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

        updateMapMarkers();
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
        map?.setView([city.lat, city.lng], 5);
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
                map?.setView([position.coords.latitude, position.coords.longitude], 5);
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

function updateMapMarkers() {
    if (!map) return;
    
    markers.forEach(marker => marker.remove());
    markers = [];

    rows.value.forEach(event => {
        if (event.latitude && event.longitude) {
            let popupHtml = `<b>${event.payload?.name}</b><br/>${event.payload?.venue?.name || 'Venue'}`;
            if (event.payload?.venue?.address) {
                popupHtml += `<br/>${event.payload.venue.address}`;
            }
            const marker = L.marker([event.latitude, event.longitude], { icon: customIcon }).addTo(map!);
            marker.bindPopup(popupHtml);
            (marker as any).eventId = event.id;
            markers.push(marker);
        }
    });
}

function focusEventOnMap(event: any) {
    if (!map || !event.latitude || !event.longitude) return;
    
    map.panTo([event.latitude, event.longitude]);
    
    const marker = markers.find((m: any) => m.eventId === event.id);
    if (marker) {
        marker.openPopup();
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

onMounted(() => {
    if (mapContainer.value) {
        map = L.map(mapContainer.value).setView([20, 0], 2);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);
    }

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
    map?.remove();
});
</script>

<template>
    <div class="h-full">
        <Head title="Map Explorer" />

        <div class="flex h-[calc(100vh-4rem)] flex-col md:flex-row overflow-hidden bg-background">
            <div class="w-full md:w-1/2 lg:w-2/5 flex flex-col h-full border-r border-border shadow-lg z-10 bg-card/50 backdrop-blur-xl">
                
                <div class="p-5 border-b border-border space-y-4 shrink-0 bg-card">
                    <div v-if="false" class="flex items-center justify-between">
                        <h1 class="text-2xl font-bold tracking-tight">Discover</h1>
                        <Badge variant="secondary">{{ total !== null ? total.toLocaleString() : '...' }} Events</Badge>
                    </div>

                    <div class="space-y-4">
                        <div class="flex gap-3">
                            <div class="flex flex-col gap-1.5 flex-1">
                                <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Search</label>
                                <input
                                    v-model="form.search"
                                    type="text"
                                    placeholder="Search events by name..."
                                    class="w-full h-10 rounded-md border border-input bg-background px-3 text-sm focus:ring-2 focus:ring-ring transition-shadow"
                                />
                            </div>

                            <div class="flex flex-col gap-1.5 flex-1">
                                <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Location</label>
                                <div class="flex gap-2">
                                    <select v-model="form.locationName" class="flex-1 h-10 rounded-md border border-input bg-background px-3 text-sm">
                                        <option value="">Anywhere (Global)</option>
                                        <option value="current" hidden>Current Location</option>
                                        <optgroup v-for="group in LOCATIONS" :key="group.country" :label="group.country">
                                            <option v-for="city in group.cities" :key="city.name" :value="city.name">{{ city.name }}</option>
                                        </optgroup>
                                    </select>
                                    <Button variant="outline" size="icon" @click="useCurrentLocation" title="Use my location">
                                        <Crosshair class="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-3">
                            <div class="flex flex-col gap-1.5 w-24">
                                <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Radius</label>
                                <select v-model="form.radius" class="w-full h-10 rounded-md border border-input bg-background px-3 text-sm" :disabled="!form.lat">
                                    <option value="10">10 km</option>
                                    <option value="50">50 km</option>
                                    <option value="100">100 km</option>
                                    <option value="500">500 km</option>
                                </select>
                            </div>
                            <div class="flex flex-col gap-1.5 flex-1">
                                <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">From</label>
                                <input
                                    v-model="form.from"
                                    type="date"
                                    class="w-full h-10 rounded-md border border-input bg-background px-3 text-sm"
                                    placeholder="From"
                                />
                            </div>
                            <div class="flex flex-col gap-1.5 flex-1">
                                <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">To</label>
                                <input
                                    v-model="form.to"
                                    type="date"
                                    class="w-full h-10 rounded-md border border-input bg-background px-3 text-sm"
                                    placeholder="To"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto p-4 space-y-4">
                    <div 
                        v-for="event in rows" 
                        :key="event.id"
                        class="group flex flex-col rounded-xl border border-border bg-card overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-300 cursor-pointer"
                        @mouseenter="focusEventOnMap(event)"
                    >
                        <div class="relative h-48 w-full bg-muted overflow-hidden">
                            <img :src="getImageUrl(event)" alt="Event Image" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                            <div class="absolute top-3 left-3 flex gap-2">
                                <Badge variant="default" class="bg-black/70 backdrop-blur text-white border-0">{{ event.payload?.category || event.type }}</Badge>
                                <Badge v-if="event.status === 'cancelled'" variant="destructive" class="border-0">Cancelled</Badge>
                                <Badge v-if="event.status === 'sold_out'" variant="secondary" class="bg-red-500/90 text-white hover:bg-red-600/90 border-0">Sold Out</Badge>
                            </div>
                        </div>
                        <div class="p-4 space-y-3">
                            <h3 class="font-semibold text-lg line-clamp-1">{{ event.payload?.name }}</h3>
                            
                            <div class="flex items-start gap-2 text-sm text-muted-foreground">
                                <Calendar class="h-4 w-4 shrink-0 mt-0.5" />
                                <span>{{ formatTime(event.created_time) }}</span>
                            </div>
                            
                            <div class="flex items-start gap-2 text-sm text-muted-foreground">
                                <Building class="h-4 w-4 shrink-0 mt-0.5" />
                                <span class="line-clamp-1">{{ event.payload?.venue?.name || 'Unknown Venue' }}</span>
                            </div>
                            
                            <div v-if="event.payload?.venue?.address" class="flex items-start gap-2 text-sm text-muted-foreground">
                                <MapPin class="h-4 w-4 shrink-0 mt-0.5" />
                                <span class="line-clamp-1">{{ event.payload.venue.address }}</span>
                            </div>

                            <div class="pt-2 flex justify-between items-center border-t border-border/50">
                                <span class="font-medium text-primary">
                                    {{ event.payload?.pricing?.currency || 'USD' }} {{ event.payload?.pricing?.min_price || 'Free' }}
                                </span>
                                <Link :href="`/events/${event.id}`" class="text-sm font-medium hover:underline">Details &rarr;</Link>
                            </div>
                        </div>
                    </div>

                    <div v-if="!loading && hasLoadedOnce && rows.length === 0" class="text-center py-10 text-muted-foreground">
                        No events found matching your criteria.
                    </div>

                    <div ref="sentinel" class="h-10 flex items-center justify-center">
                        <span v-if="loading" class="text-sm text-muted-foreground animate-pulse">Loading more events...</span>
                    </div>
                </div>
            </div>

            <div class="w-full md:w-1/2 lg:w-3/5 h-[40vh] md:h-full relative shrink-0">
                <div ref="mapContainer" class="w-full h-full dark:[&_.leaflet-layer]:invert dark:[&_.leaflet-layer]:hue-rotate-180 dark:[&_.leaflet-layer]:contrast-75 dark:[&_.leaflet-layer]:opacity-90 transition-all duration-700"></div>
                
                <div class="absolute top-4 right-4 z-[400] bg-background/90 backdrop-blur p-2 rounded-lg shadow-sm border border-border text-xs text-muted-foreground pointer-events-none">
                    Interactive Map Explorer
                </div>
            </div>
        </div>
    </div>
</template>
