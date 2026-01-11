<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Check, X, Calendar, ChevronLeft, ChevronRight } from 'lucide-vue-next';
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
const groupedRecords = ref<Record<string, any[]>>({});
const stats = ref({
    present: 0,
    absent: 0,
    late: 0,
    total: 0,
});
const selectedDate = ref<string | null>(null);
const selectedDateValue = ref<string | null>(null);
const latestDate = ref<string | null>(null);
const availableDates = ref<string[]>([]);
const subjects = ref<any[]>([]);
const selectedSubjectId = ref<number | null>(null);
const editingId = ref<number | null>(null);
const editingStatus = ref<string>('');

onMounted(async () => {
    await loadAttendance();
});

const loadAttendance = async (date?: string, subjectId?: number | null) => {
    try {
        const url = new URL('/api/attendance', window.location.origin);
        if (date) {
            url.searchParams.append('date', date);
        }
        if (subjectId) {
            url.searchParams.append('subjectId', subjectId.toString());
        }
        const response = await fetch(url.toString());
        const data = await response.json();
        attendanceRecords.value = data.attendanceRecords;
        groupedRecords.value = data.groupedRecords;
        stats.value = data.stats;
        selectedDate.value = data.selectedDate;
        latestDate.value = data.latestDate;
        availableDates.value = data.availableDates;
        subjects.value = data.subjects;
        selectedSubjectId.value = data.selectedSubjectId;
        
        // Extract date value from formatted string (e.g., "January 10, 2026" -> "2026-01-10")
        if (data.selectedDate) {
            const dateObj = new Date(data.selectedDate);
            const year = dateObj.getFullYear();
            const month = String(dateObj.getMonth() + 1).padStart(2, '0');
            const day = String(dateObj.getDate()).padStart(2, '0');
            selectedDateValue.value = `${year}-${month}-${day}`;
        }
    } catch (error) {
        console.error('Error fetching attendance:', error);
    }
};

const onDateChange = (date: string) => {
    loadAttendance(date, selectedSubjectId.value);
};

const onSubjectChange = (subjectId: number | null) => {
    selectedSubjectId.value = subjectId;
    loadAttendance(selectedDateValue.value || undefined, subjectId);
};

const goToPreviousDate = () => {
    if (!selectedDateValue.value) return;
    const currentIndex = availableDates.value.indexOf(selectedDateValue.value);
    if (currentIndex < availableDates.value.length - 1) {
        loadAttendance(availableDates.value[currentIndex + 1]);
    }
};

const goToNextDate = () => {
    if (!selectedDateValue.value) return;
    const currentIndex = availableDates.value.indexOf(selectedDateValue.value);
    if (currentIndex > 0) {
        loadAttendance(availableDates.value[currentIndex - 1]);
    }
};

const startEditing = (record: any, index: number) => {
    editingId.value = index;
    editingStatus.value = record.status;
};

const updateStatus = (record: any, index: number, newStatus: string) => {
    const payload: any = { status: newStatus };
    
    // For unmarked students (id is null), pass the student name, date, and subject
    if (!record.id) {
        payload.studentName = record.name;
        payload.date = selectedDateValue.value;
        payload.subjectId = selectedSubjectId.value;
    }

    router.post(
        `/attendance/${record.id || 'null'}/update-status`,
        payload,
        {
            preserveState: true,
            onFinish: () => {
                editingId.value = null;
                loadAttendance(selectedDateValue.value, selectedSubjectId.value);
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
                <h1 class="text-3xl font-bold mb-4">Attendance Records</h1>
                
                <!-- Subject Selection (First) -->
                <div class="flex items-center gap-4 mb-6 flex-wrap">
                    <div class="flex items-center gap-2">
                        <select
                            :value="selectedSubjectId || ''"
                            @change="(e) => onSubjectChange(e.target.value ? parseInt((e.target as HTMLSelectElement).value) : null)"
                            class="px-3 py-2 border border-sidebar-border/70 rounded-lg dark:border-sidebar-border dark:bg-card dark:text-white bg-white text-black font-medium"
                        >
                            <option value="">Select a Subject</option>
                            <option v-for="subject in subjects" :key="subject.id" :value="subject.id">
                                {{ subject.name }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Date Selection Controls (Appears after subject selection) -->
                <div v-if="selectedSubjectId" class="flex items-center gap-4 mb-6 flex-wrap">
                    <button
                        @click="goToPreviousDate"
                        :disabled="availableDates.length === 0 || selectedDateValue === availableDates[availableDates.length - 1]"
                        class="p-2 hover:bg-muted rounded-lg disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        <ChevronLeft class="w-5 h-5" />
                    </button>
                    
                    <div class="flex items-center gap-2">
                        <Calendar class="w-4 h-4 text-muted-foreground" />
                        <select
                            :value="selectedDateValue || ''"
                            @change="(e) => onDateChange((e.target as HTMLSelectElement).value)"
                            class="px-3 py-2 border border-sidebar-border/70 rounded-lg dark:border-sidebar-border dark:bg-card dark:text-white bg-white text-black"
                        >
                            <option v-if="!selectedDateValue" value="">Select a date</option>
                            <option v-for="date in availableDates" :key="date" :value="date">
                                {{ new Date(date).toLocaleDateString('en-US', { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' }) }}
                            </option>
                        </select>
                    </div>
                    
                    <button
                        @click="goToNextDate"
                        :disabled="availableDates.length === 0 || selectedDateValue === availableDates[0]"
                        class="p-2 hover:bg-muted rounded-lg disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        <ChevronRight class="w-5 h-5" />
                    </button>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="!selectedSubjectId" class="rounded-lg border border-sidebar-border/70 dark:border-sidebar-border p-12">
                <div class="flex flex-col items-center justify-center text-center">
                    <Calendar class="w-16 h-16 text-muted-foreground/30 mb-4" />
                    <h3 class="text-lg font-semibold text-muted-foreground mb-2">
                        Select a Subject
                    </h3>
                    <p class="text-sm text-muted-foreground max-w-md">
                        Choose a subject above to view and mark attendance for enrolled students.
                    </p>
                </div>
            </div>

            <!-- Stats (Appears after subject selection) -->
            <div v-else class="grid grid-cols-2 md:grid-cols-4 gap-4">
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

            <!-- Attendance Table by Day (Appears after subject and date selection) -->
            <div v-if="selectedSubjectId && !selectedDateValue" class="rounded-lg border border-sidebar-border/70 dark:border-sidebar-border p-12">
                <div class="flex flex-col items-center justify-center text-center">
                    <Calendar class="w-16 h-16 text-muted-foreground/30 mb-4" />
                    <h3 class="text-lg font-semibold text-muted-foreground mb-2">
                        Select a Date
                    </h3>
                    <p class="text-sm text-muted-foreground max-w-md">
                        Choose a date to view and mark attendance.
                    </p>
                </div>
            </div>

            <div v-else-if="selectedSubjectId && attendanceRecords.length === 0" class="rounded-lg border border-sidebar-border/70 dark:border-sidebar-border p-12">
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
            <div v-else-if="selectedSubjectId" class="flex flex-col gap-6">
                <div v-for="(records, day) in groupedRecords" :key="day" class="rounded-lg border border-sidebar-border/70 dark:border-sidebar-border overflow-hidden">
                    <div class="bg-muted/50 dark:bg-muted/20 px-6 py-4 border-b border-sidebar-border/70 dark:border-sidebar-border">
                        <h2 class="text-lg font-semibold">{{ day }}</h2>
                    </div>
                    <table class="w-full">
                        <thead class="bg-muted/30 dark:bg-muted/10 border-b border-sidebar-border/70 dark:border-sidebar-border">
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
                                v-for="(record, index) in records"
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
        </div>
    </AppLayout>
</template>
