<script setup lang="ts">
import { Form, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import AlertError from '@/components/AlertError.vue';
import InputError from '@/components/InputError.vue';
import { MapPin, Calendar, Users, Building, AlertCircle, ChevronLeft, ChevronRight, CheckCircle2 } from '@lucide/vue';

type Event = any; // You could type this properly later

const props = defineProps<{
    event: Event | null;
    imagePrefix: string;
}>();

const isOpen = defineModel<boolean>('isOpen');

const form = useForm({
    email: '',
});

const isSuccess = ref(false);

const remainingSeats = computed(() => {
    if (!props.event) return 0;
    return props.event.remaining_seats ?? 0;
});

const isFull = computed(() => {
    return remainingSeats.value <= 0;
});

const images = computed(() => {
    if (!props.event?.payload?.images?.length) {
        return [`${props.imagePrefix}placeholder.jpg`];
    }
    return props.event.payload.images.map((img: string) => `${props.imagePrefix}${img}`);
});

function formatTime(timestamp: number) {
    if (!timestamp) return 'TBA';
    return new Intl.DateTimeFormat(navigator.language, {
        month: 'long', day: 'numeric', year: 'numeric',
        hour: 'numeric', minute: 'numeric', timeZoneName: 'short'
    }).format(new Date(timestamp * 1000));
}

function submit() {
    if (!props.event) return;
    form.post(`/events/${props.event.id}/attendees`, {
        preserveScroll: true,
        onSuccess: () => {
            isSuccess.value = true;
        },
    });
}

const carouselRef = ref<HTMLElement | null>(null);

function scrollCarousel(direction: 'prev' | 'next') {
    if (!carouselRef.value) return;
    const scrollAmount = carouselRef.value.clientWidth;
    if (direction === 'prev') {
        carouselRef.value.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
    } else {
        carouselRef.value.scrollBy({ left: scrollAmount, behavior: 'smooth' });
    }
}

// Reset form when modal closes
watch(() => isOpen.value, (val) => {
    if (!val) {
        form.reset();
        form.clearErrors();
        isSuccess.value = false;
    }
});
</script>

<template>
    <Dialog :open="isOpen" @update:open="isOpen = $event">
        <DialogContent class="sm:max-w-2xl p-0 overflow-hidden bg-card border border-border shadow-2xl">
            <div v-if="event" class="flex flex-col max-h-[90vh]">
                <!-- Header / Images Carousel -->
                <div class="relative h-64 shrink-0 bg-muted overflow-hidden group">
                    <div ref="carouselRef" class="flex overflow-x-auto snap-x snap-mandatory h-full hide-scrollbar scroll-smooth">
                        <img 
                            v-for="(img, idx) in images" 
                            :key="idx" 
                            :src="img" 
                            class="snap-center w-full h-full object-cover shrink-0" 
                            alt="Event Image" 
                        />
                    </div>
                    
                    <button 
                        v-if="images.length > 1" 
                        @click.stop="scrollCarousel('prev')" 
                        class="absolute top-1/2 left-2 -translate-y-1/2 bg-black/40 hover:bg-black/70 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity backdrop-blur"
                    >
                        <ChevronLeft class="w-6 h-6" />
                    </button>
                    
                    <button 
                        v-if="images.length > 1" 
                        @click.stop="scrollCarousel('next')" 
                        class="absolute top-1/2 right-2 -translate-y-1/2 bg-black/40 hover:bg-black/70 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity backdrop-blur"
                    >
                        <ChevronRight class="w-6 h-6" />
                    </button>

                    <div class="absolute top-4 left-4 flex gap-2 z-10">
                        <Badge class="bg-black/70 backdrop-blur text-white border-0">{{ event.payload?.category || event.type }}</Badge>
                        <Badge v-if="isFull" variant="destructive" class="border-0 shadow-sm">Sold Out</Badge>
                    </div>
                </div>

                <!-- Content -->
                <div class="overflow-y-auto p-6 space-y-6">
                    <div>
                        <DialogTitle class="text-3xl font-bold tracking-tight text-foreground">{{ event.payload?.name }}</DialogTitle>
                        <DialogDescription class="text-base mt-2 text-muted-foreground">{{ event.payload?.description }}</DialogDescription>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-4">
                            <div class="flex items-start gap-3 text-sm text-foreground">
                                <Calendar class="h-5 w-5 shrink-0 text-primary mt-0.5" />
                                <div>
                                    <p class="font-medium">Starts: {{ formatTime(event.payload?.schedule?.starts_at) }}</p>
                                    <p class="text-muted-foreground">Ends: {{ formatTime(event.payload?.schedule?.ends_at) }}</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start gap-3 text-sm text-foreground">
                                <Building class="h-5 w-5 shrink-0 text-primary mt-0.5" />
                                <div>
                                    <p class="font-medium">{{ event.payload?.venue?.name || 'Venue TBA' }}</p>
                                    <p class="text-muted-foreground">{{ event.payload?.venue?.address || 'Address TBA' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="flex items-center gap-3 text-sm text-foreground">
                                <Users class="h-5 w-5 shrink-0 text-primary" />
                                <p class="font-medium">Remaining Seats: <span :class="{'text-destructive': isFull, 'text-green-500': !isFull}">{{ remainingSeats }}</span></p>
                            </div>

                            <div v-if="event.payload?.pricing" class="flex items-center gap-3 text-sm text-foreground">
                                <div class="h-5 w-5 shrink-0 flex items-center justify-center text-primary font-bold">$</div>
                                <p class="font-medium">{{ event.payload.pricing.currency }} {{ event.payload.pricing.min_price }}</p>
                            </div>

                            <div v-if="event.payload?.organizer?.name" class="flex items-center gap-3 text-sm text-foreground">
                                <Badge variant="outline">Organizer: {{ event.payload.organizer.name }}</Badge>
                            </div>
                        </div>
                    </div>

                    <div v-if="event.payload?.notes" class="bg-muted/50 rounded-lg p-4 text-sm text-muted-foreground border border-border/50">
                        <p class="line-clamp-3 whitespace-pre-wrap">{{ event.payload.notes }}</p>
                    </div>

                    <div v-if="event.payload?.tags?.length" class="flex flex-wrap gap-2">
                        <Badge v-for="tag in event.payload.tags" :key="tag" variant="secondary">{{ tag }}</Badge>
                    </div>
                </div>

                <!-- Registration Form -->
                <div class="p-6 border-t border-border bg-muted/20 shrink-0">
                    
                    <div v-if="isFull" class="flex items-center gap-2 text-destructive bg-destructive/10 p-3 rounded-md">
                        <AlertCircle class="h-5 w-5" />
                        <p class="text-sm font-medium">Sorry, this event is currently full.</p>
                    </div>
                    
                    <form v-else @submit.prevent="submit" class="space-y-4">
                        <!-- We display global errors from the flash if any, or form errors -->
                        <AlertError v-if="form.errors.capacity" :errors="[form.errors.capacity]" />
                        
                        <div v-if="isSuccess" class="flex items-center gap-2 text-green-600 bg-green-500/10 p-3 rounded-md mb-2">
                            <CheckCircle2 class="h-5 w-5 shrink-0" />
                            <p class="text-sm font-medium">Successfully registered for this event!</p>
                        </div>
                        
                        <div class="flex flex-col md:flex-row gap-4 justify-center items-center">
                            <div class="flex-1 w-full">
                                <input
                                    id="email"
                                    type="email"
                                    v-model="form.email"
                                    required
                                    :readonly="isSuccess"
                                    :disabled="isSuccess"
                                    placeholder="Enter your email"
                                    class="w-full h-10 rounded-md border border-input bg-background px-3 text-sm focus:ring-2 focus:ring-primary focus:border-primary transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                                    :class="{'border-destructive focus:ring-destructive': form.errors.email}"
                                />
                                <InputError :message="form.errors.email" />
                            </div>
                            
                            <div class="flex items-end w-full md:w-auto">
                                <Button type="submit" class="w-full md:w-auto h-10 px-8 cursor-pointer disabled:cursor-not-allowed" :disabled="form.processing || isSuccess">
                                    <span v-if="form.processing">Registering...</span>
                                    <span v-else-if="isSuccess">Registered</span>
                                    <span v-else>Attend Event</span>
                                </Button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>

<style scoped>
.hide-scrollbar::-webkit-scrollbar {
    display: none;
}
.hide-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
