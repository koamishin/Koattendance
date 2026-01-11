<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Check, X, Calendar } from 'lucide-vue-next';
import { ref, computed, onMounted } from 'vue';

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

const attendanceRecords = ref<any[]>([]);
const stats = ref({
    present: 0,
    absent: 0,
    late: 0,
    total: 0,
});
const latestDate = ref<string | null>(null);
const editingId = ref<number | null>(null);
const editingStatus = ref<string>('');

onMounted(async () => {
    await loadAttendance();
});

const loadAttendance = async () => {
    try {
        const response = await fetch('/api/attendance');
        const data = await response.json();
        attendanceRecords.value = data.attendanceRecords;
        stats.value = data.stats;
        latestDate.value = data.latestDate;
    } catch (error) {
        console.error('Error fetching attendance:', error);
    }
};

const startEditing = (record: any, index: number) => {
    editingId.value = index;
    editingStatus.value = record.status;
};

const updateStatus = (record: any, index: number, newStatus: string) => {
    const payload: any = { status: newStatus };
    
    // For unmarked students (id is null), pass the student name
    if (!record.id) {
        payload.studentName = record.name;
    }

    router.post(
        `/attendance/${record.id || 'null'}/update-status`,
        payload,
        {
            preserveState: true,
            onFinish: () => {
                editingId.value = null;
                loadAttendance();
            },
        }
    );
};

const cancelEditing = () => {
    editingId.value = null;
    editingStatus.value = '';
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
        case 'unmarked':
            return {
                bg: 'bg-gray-100 dark:bg-gray-800',
                text: 'text-gray-600 dark:text-gray-400',
                label: 'Unmarked',
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
                    <span>{{ latestDate || 'No records yet' }}</span>
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
            <div v-if="attendanceRecords.length === 0" class="rounded-lg border border-sidebar-border/70 dark:border-sidebar-border p-12">
                <div class="flex flex-col items-center justify-center text-center">
                    <Calendar class="w-16 h-16 text-muted-foreground/30 mb-4" />
                    <h3 class="text-lg font-semibold text-muted-foreground mb-2">
                        No Attendance Records Yet
                    </h3>
                    <p class="text-sm text-muted-foreground max-w-md">
                        Attendance records will appear here once the admin adds attendance data.
                    </p>
                </div>
            </div>
            <div
                v-else
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
                                Date
                            </th>
                            <th class="text-left px-6 py-3 font-semibold text-sm">
                                Time
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(record, index) in attendanceRecords"
                            :key="index"
                            class="border-b border-sidebar-border/70 dark:border-sidebar-border hover:bg-muted/50 dark:hover:bg-muted/20 transition-colors"
                        >
                            <td class="px-6 py-4">
                                <span class="font-medium">{{ record.name }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div v-if="editingId === index" class="flex gap-2">
                                    <select
                                        v-model="editingStatus"
                                        class="px-2 py-1 border border-gray-300 dark:border-gray-600 rounded text-sm dark:bg-gray-700 dark:text-white"
                                    >
                                        <option value="present">Present</option>
                                        <option value="late">Late</option>
                                        <option value="absent">Absent</option>
                                    </select>
                                    <button
                                        type="button"
                                        @click.prevent="updateStatus(record, index, editingStatus)"
                                        class="px-2 py-1 bg-green-600 hover:bg-green-700 text-white rounded text-sm"
                                    >
                                        ✓
                                    </button>
                                    <button
                                        @click="cancelEditing"
                                        class="px-2 py-1 bg-gray-600 hover:bg-gray-700 text-white rounded text-sm"
                                    >
                                        ✕
                                    </button>
                                </div>
                                <span
                                    v-else
                                    @click="startEditing(record, index)"
                                    :class="[
                                        'inline-flex items-center gap-2 px-3 py-1 rounded-full text-sm font-medium cursor-pointer hover:opacity-80 transition-opacity',
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
                                {{ record.date }}
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
