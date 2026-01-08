<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Check, X } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Seatplan',
        href: '/dashboard/seatplan',
    },
];

const seats = [
    { id: 1, name: 'John Smith', present: true },
    { id: 2, name: 'Sarah Johnson', present: true },
    { id: 3, name: 'Mike Davis', present: false },
    { id: 4, name: 'Emily Brown', present: true },
    { id: 5, name: 'Alex Wilson', present: true },
    { id: 6, name: 'Jessica Lee', present: true },
    { id: 7, name: 'Chris Martin', present: false },
    { id: 8, name: 'Lisa Anderson', present: true },
    { id: 9, name: 'Tom Taylor', present: true },
    { id: 10, name: 'Amy Thomas', present: true },
    { id: 11, name: 'David Jackson', present: false },
    { id: 12, name: 'Sophie White', present: true },
];
</script>

<template>
    <Head title="Seatplan" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6"
        >
            <div>
                <h1 class="text-3xl font-bold mb-2">Classroom Seatplan</h1>
                <p class="text-muted-foreground">Room 301 - Section A</p>
            </div>

            <!-- Legend -->
            <div class="flex gap-6">
                <div class="flex items-center gap-2">
                    <div
                        class="w-6 h-6 rounded-lg bg-green-100 dark:bg-green-900 border-2 border-green-500 flex items-center justify-center"
                    >
                        <Check class="w-4 h-4 text-green-600" />
                    </div>
                    <span class="text-sm font-medium">Present</span>
                </div>
                <div class="flex items-center gap-2">
                    <div
                        class="w-6 h-6 rounded-lg bg-red-100 dark:bg-red-900 border-2 border-red-500 flex items-center justify-center"
                    >
                        <X class="w-4 h-4 text-red-600" />
                    </div>
                    <span class="text-sm font-medium">Absent</span>
                </div>
            </div>

            <!-- Seatplan Grid -->
            <div
                class="grid grid-cols-4 gap-4 rounded-lg border border-sidebar-border/70 p-8 dark:border-sidebar-border bg-muted/50 dark:bg-muted/20"
            >
                <!-- Whiteboard indicator -->
                <div class="col-span-4 mb-4">
                    <div
                        class="h-12 bg-blue-100 dark:bg-blue-900/30 border-2 border-blue-400 dark:border-blue-700 rounded flex items-center justify-center font-bold text-blue-900 dark:text-blue-200"
                    >
                        Whiteboard
                    </div>
                </div>

                <!-- Seats -->
                <div
                    v-for="seat in seats"
                    :key="seat.id"
                    :class="[
                        'h-28 rounded-lg border-2 p-3 flex flex-col items-center justify-center cursor-pointer transition-all hover:shadow-lg',
                        seat.present
                            ? 'bg-green-50 dark:bg-green-900/20 border-green-500'
                            : 'bg-red-50 dark:bg-red-900/20 border-red-500',
                    ]"
                >
                    <div
                        :class="[
                            'w-8 h-8 rounded-lg flex items-center justify-center mb-2',
                            seat.present
                                ? 'bg-green-100 dark:bg-green-800'
                                : 'bg-red-100 dark:bg-red-800',
                        ]"
                    >
                        <Check
                            v-if="seat.present"
                            class="w-5 h-5 text-green-600"
                        />
                        <X v-else class="w-5 h-5 text-red-600" />
                    </div>
                    <p class="text-xs font-semibold text-center line-clamp-2">
                        {{ seat.name }}
                    </p>
                </div>
            </div>

            <!-- Summary -->
            <div class="grid grid-cols-3 gap-4">
                <div
                    class="rounded-lg border border-sidebar-border/70 p-4 dark:border-sidebar-border bg-card"
                >
                    <p class="text-sm text-muted-foreground mb-2">Present</p>
                    <p class="text-2xl font-bold text-green-600">
                        {{ seats.filter((s) => s.present).length }}
                    </p>
                </div>
                <div
                    class="rounded-lg border border-sidebar-border/70 p-4 dark:border-sidebar-border bg-card"
                >
                    <p class="text-sm text-muted-foreground mb-2">Absent</p>
                    <p class="text-2xl font-bold text-red-600">
                        {{ seats.filter((s) => !s.present).length }}
                    </p>
                </div>
                <div
                    class="rounded-lg border border-sidebar-border/70 p-4 dark:border-sidebar-border bg-card"
                >
                    <p class="text-sm text-muted-foreground mb-2">Total</p>
                    <p class="text-2xl font-bold">{{ seats.length }}</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
