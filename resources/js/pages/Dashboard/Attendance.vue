<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Check, X, Calendar } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Attendance',
        href: '/dashboard/attendance',
    },
];

const attendanceRecords = [
    {
        id: 1,
        name: 'John Smith',
        status: 'present',
        date: '2024-01-08',
        time: '09:00 AM',
    },
    {
        id: 2,
        name: 'Sarah Johnson',
        status: 'present',
        date: '2024-01-08',
        time: '09:05 AM',
    },
    {
        id: 3,
        name: 'Mike Davis',
        status: 'absent',
        date: '2024-01-08',
        time: '-',
    },
    {
        id: 4,
        name: 'Emily Brown',
        status: 'present',
        date: '2024-01-08',
        time: '09:02 AM',
    },
    {
        id: 5,
        name: 'Alex Wilson',
        status: 'present',
        date: '2024-01-08',
        time: '09:01 AM',
    },
    {
        id: 6,
        name: 'Jessica Lee',
        status: 'present',
        date: '2024-01-08',
        time: '09:03 AM',
    },
    {
        id: 7,
        name: 'Chris Martin',
        status: 'late',
        date: '2024-01-08',
        time: '09:35 AM',
    },
    {
        id: 8,
        name: 'Lisa Anderson',
        status: 'present',
        date: '2024-01-08',
        time: '08:58 AM',
    },
];

const stats = {
    present: 6,
    absent: 1,
    late: 1,
    total: 8,
};

const getStatusBadge = (status: string) => {
    switch (status) {
        case 'present':
            return {
                bg: 'bg-green-100 dark:bg-green-900/30',
                text: 'text-green-800 dark:text-green-200',
                label: 'Present',
            };
        case 'absent':
            return {
                bg: 'bg-red-100 dark:bg-red-900/30',
                text: 'text-red-800 dark:text-red-200',
                label: 'Absent',
            };
        case 'late':
            return {
                bg: 'bg-yellow-100 dark:bg-yellow-900/30',
                text: 'text-yellow-800 dark:text-yellow-200',
                label: 'Late',
            };
        default:
            return {
                bg: 'bg-gray-100 dark:bg-gray-800',
                text: 'text-gray-800 dark:text-gray-200',
                label: status,
            };
    }
};
</script>

<template>
    <Head title="Attendance" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <div>
                <h1 class="text-3xl font-bold mb-2">Attendance Records</h1>
                <div class="flex items-center gap-2 text-muted-foreground">
                    <Calendar class="w-4 h-4" />
                    <span>January 8, 2024</span>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div
                    class="rounded-lg border border-sidebar-border/70 p-4 dark:border-sidebar-border bg-card"
                >
                    <p class="text-sm text-muted-foreground mb-2">Present</p>
                    <p class="text-2xl font-bold text-green-600">
                        {{ stats.present }}
                    </p>
                    <p class="text-xs text-muted-foreground mt-1">
                        {{ ((stats.present / stats.total) * 100).toFixed(0) }}%
                    </p>
                </div>
                <div
                    class="rounded-lg border border-sidebar-border/70 p-4 dark:border-sidebar-border bg-card"
                >
                    <p class="text-sm text-muted-foreground mb-2">Absent</p>
                    <p class="text-2xl font-bold text-red-600">
                        {{ stats.absent }}
                    </p>
                    <p class="text-xs text-muted-foreground mt-1">
                        {{ ((stats.absent / stats.total) * 100).toFixed(0) }}%
                    </p>
                </div>
                <div
                    class="rounded-lg border border-sidebar-border/70 p-4 dark:border-sidebar-border bg-card"
                >
                    <p class="text-sm text-muted-foreground mb-2">Late</p>
                    <p class="text-2xl font-bold text-yellow-600">
                        {{ stats.late }}
                    </p>
                    <p class="text-xs text-muted-foreground mt-1">
                        {{ ((stats.late / stats.total) * 100).toFixed(0) }}%
                    </p>
                </div>
                <div
                    class="rounded-lg border border-sidebar-border/70 p-4 dark:border-sidebar-border bg-card"
                >
                    <p class="text-sm text-muted-foreground mb-2">Total</p>
                    <p class="text-2xl font-bold">{{ stats.total }}</p>
                </div>
            </div>

            <!-- Attendance Table -->
            <div
                class="rounded-lg border border-sidebar-border/70 dark:border-sidebar-border overflow-hidden"
            >
                <table class="w-full">
                    <thead class="bg-muted/50 dark:bg-muted/20 border-b border-sidebar-border/70 dark:border-sidebar-border">
                        <tr>
                            <th class="text-left px-6 py-3 font-semibold text-sm">
                                Student Name
                            </th>
                            <th class="text-left px-6 py-3 font-semibold text-sm">
                                Status
                            </th>
                            <th class="text-left px-6 py-3 font-semibold text-sm">
                                Time
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="record in attendanceRecords"
                            :key="record.id"
                            class="border-b border-sidebar-border/70 dark:border-sidebar-border hover:bg-muted/50 dark:hover:bg-muted/20 transition-colors"
                        >
                            <td class="px-6 py-4">
                                <span class="font-medium">{{ record.name }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    :class="[
                                        'inline-flex items-center gap-2 px-3 py-1 rounded-full text-sm font-medium',
                                        getStatusBadge(record.status).bg,
                                        getStatusBadge(record.status).text,
                                    ]"
                                >
                                    <Check
                                        v-if="record.status === 'present'"
                                        class="w-4 h-4"
                                    />
                                    <X
                                        v-else-if="record.status === 'absent'"
                                        class="w-4 h-4"
                                    />
                                    {{ getStatusBadge(record.status).label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-muted-foreground">
                                {{ record.time }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
