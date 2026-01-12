<script setup lang="ts">
import QrScanner from '@/components/QrScanner.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import {
    AlertCircle,
    Calendar,
    Check,
    ChevronLeft,
    ChevronRight,
    QrCode,
    X,
} from 'lucide-vue-next';
import { onMounted, ref } from 'vue';

const page = usePage();
const roles = page.props.auth.roles;

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
const isScanningOpen = ref(false);
const currentSessionId = ref<number | null>(null);
const scanStatus = ref<string | null>(null);
const scanMessage = ref<string | null>(null);

onMounted(async () => {
    // Load saved subject and date from localStorage
    const savedSubjectId = localStorage.getItem('attendance_selectedSubjectId');
    const savedDateValue = localStorage.getItem('attendance_selectedDateValue');

    // Set the values
    if (savedSubjectId) {
        selectedSubjectId.value = parseInt(savedSubjectId);
    }
    if (savedDateValue) {
        selectedDateValue.value = savedDateValue;
    }

    // Load attendance with saved values
    await loadAttendance(
        savedDateValue || undefined,
        savedSubjectId ? parseInt(savedSubjectId) : null,
    );

    // If teacher, try to fetch today's session
    if (roles?.isTeacher) {
        fetchTodaySession();
    }
});

const fetchTodaySession = async () => {
    try {
        const response = await axios.get(route('api.sessions.today'));
        if (response.data.sessions && response.data.sessions.length > 0) {
            // For now, pick the first one. In reality, let user pick.
            currentSessionId.value = response.data.sessions[0].id;
        }
    } catch (e) {
        console.error('Failed to fetch sessions', e);
    }
};

const handleScan = async (decodedText: string) => {
    if (!currentSessionId.value) {
        // Try to start/create a session if we have a subject selected
        if (selectedSubjectId.value) {
            try {
                // Mock start session logic
                // const sessionRes = await axios.post(route('api.sessions.today'));
            } catch (e) {
                // ignore
            }
        } else {
            scanMessage.value =
                'No active session. Please select a subject first (Mock logic).';
            scanStatus.value = 'error';
            return;
        }
    }

    scanStatus.value = 'processing';
    scanMessage.value = 'Verifying...';

    try {
        const response = await axios.post(route('api.attendance.scan'), {
            qr_code: decodedText,
            session_id: currentSessionId.value,
            device_info: { user_agent: navigator.userAgent },
        });

        scanStatus.value = 'success';
        scanMessage.value = `Marked present: ${response.data.student.name}`;

        // Refresh attendance list
        loadAttendance(
            selectedDateValue.value || undefined,
            selectedSubjectId.value,
        );

        // Clear message after delay
        setTimeout(() => {
            if (scanStatus.value === 'success') {
                scanStatus.value = null;
                scanMessage.value = null;
            }
        }, 3000);
    } catch (e: any) {
        scanStatus.value = 'error';
        scanMessage.value = e.response?.data?.message || 'Scan failed';
    }
};

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
    selectedDateValue.value = date;

    // Save to localStorage
    localStorage.setItem('attendance_selectedDateValue', date);

    loadAttendance(date, selectedSubjectId.value);
};

const onSubjectChange = (subjectId: number | null) => {
    selectedSubjectId.value = subjectId;

    // Save to localStorage
    if (subjectId) {
        localStorage.setItem(
            'attendance_selectedSubjectId',
            subjectId.toString(),
        );
    } else {
        localStorage.removeItem('attendance_selectedSubjectId');
    }

    loadAttendance(selectedDateValue.value || undefined, subjectId);
};

const goToPreviousDate = () => {
    if (!selectedDateValue.value) return;
    const currentIndex = availableDates.value.indexOf(selectedDateValue.value);
    if (currentIndex < availableDates.value.length - 1) {
        const newDate = availableDates.value[currentIndex + 1];
        selectedDateValue.value = newDate;
        localStorage.setItem('attendance_selectedDateValue', newDate);
        loadAttendance(newDate, selectedSubjectId.value);
    }
};

const goToNextDate = () => {
    if (!selectedDateValue.value) return;
    const currentIndex = availableDates.value.indexOf(selectedDateValue.value);
    if (currentIndex > 0) {
        const newDate = availableDates.value[currentIndex - 1];
        selectedDateValue.value = newDate;
        localStorage.setItem('attendance_selectedDateValue', newDate);
        loadAttendance(newDate, selectedSubjectId.value);
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

    router.post(`/attendance/${record.id || 'null'}/update-status`, payload, {
        preserveState: true,
        onFinish: () => {
            editingId.value = null;
            loadAttendance(selectedDateValue.value, selectedSubjectId.value);
        },
    });
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
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
            <div>
                <div class="mb-4 flex items-center justify-between">
                    <h1 class="text-3xl font-bold">Attendance Records</h1>

                    <!-- Scan Button for Teachers -->
                    <Dialog
                        v-if="roles?.isTeacher"
                        v-model:open="isScanningOpen"
                    >
                        <DialogTrigger as-child>
                            <Button class="gap-2">
                                <QrCode class="h-4 w-4" />
                                Scan Attendance
                            </Button>
                        </DialogTrigger>
                        <DialogContent class="sm:max-w-md">
                            <DialogHeader>
                                <DialogTitle>Scan Student QR Code</DialogTitle>
                            </DialogHeader>

                            <div class="flex flex-col items-center gap-4 py-4">
                                <div
                                    v-if="!currentSessionId"
                                    class="mb-2 w-full rounded-md bg-yellow-50 p-3 text-center text-sm text-yellow-600"
                                >
                                    <AlertCircle class="mr-1 inline h-4 w-4" />
                                    No active class session found for today.
                                    <br />
                                    (Mock: Ensure you have a session scheduled
                                    in DB)
                                </div>

                                <QrScanner
                                    v-if="currentSessionId || true"
                                    @scan="handleScan"
                                />

                                <div
                                    v-if="scanMessage"
                                    :class="[
                                        'w-full rounded-md p-3 text-center text-sm font-medium transition-all',
                                        scanStatus === 'success'
                                            ? 'bg-green-100 text-green-700'
                                            : scanStatus === 'error'
                                              ? 'bg-red-100 text-red-700'
                                              : 'bg-blue-100 text-blue-700',
                                    ]"
                                >
                                    {{ scanMessage }}
                                </div>
                            </div>
                        </DialogContent>
                    </Dialog>
                </div>

                <!-- Subject Selection (First) -->
                <div class="mb-6 flex flex-wrap items-center gap-4">
                    <div class="flex items-center gap-2">
                        <select
                            :value="selectedSubjectId || ''"
                            @change="
                                (e) =>
                                    onSubjectChange(
                                        e.target.value
                                            ? parseInt(
                                                  (
                                                      e.target as HTMLSelectElement
                                                  ).value,
                                              )
                                            : null,
                                    )
                            "
                            class="rounded-lg border border-sidebar-border/70 bg-white px-3 py-2 font-medium text-black dark:border-sidebar-border dark:bg-card dark:text-white"
                        >
                            <option value="">Select a Subject</option>
                            <option
                                v-for="subject in subjects"
                                :key="subject.id"
                                :value="subject.id"
                            >
                                {{ subject.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Add Student Button -->
                    <AddStudentModal
                        v-if="
                            selectedSubjectId &&
                            (roles?.isTeacher || roles?.isAdmin)
                        "
                        :subject-id="selectedSubjectId"
                        @student-added="
                            loadAttendance(
                                selectedDateValue || undefined,
                                selectedSubjectId,
                            )
                        "
                    />
                </div>

                <!-- Date Selection Controls (Appears after subject selection) -->
                <div
                    v-if="selectedSubjectId"
                    class="mb-6 flex flex-wrap items-center gap-4"
                >
                    <button
                        @click="goToPreviousDate"
                        :disabled="
                            availableDates.length === 0 ||
                            selectedDateValue ===
                                availableDates[availableDates.length - 1]
                        "
                        class="rounded-lg p-2 transition-colors hover:bg-muted disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <ChevronLeft class="h-5 w-5" />
                    </button>

                    <div class="flex items-center gap-2">
                        <Calendar class="h-4 w-4 text-muted-foreground" />
                        <select
                            :value="selectedDateValue || ''"
                            @change="
                                (e) =>
                                    onDateChange(
                                        (e.target as HTMLSelectElement).value,
                                    )
                            "
                            class="rounded-lg border border-sidebar-border/70 bg-white px-3 py-2 text-black dark:border-sidebar-border dark:bg-card dark:text-white"
                        >
                            <option v-if="!selectedDateValue" value="">
                                Select a date
                            </option>
                            <option
                                v-for="date in availableDates"
                                :key="date"
                                :value="date"
                            >
                                {{
                                    new Date(date).toLocaleDateString('en-US', {
                                        weekday: 'short',
                                        year: 'numeric',
                                        month: 'short',
                                        day: 'numeric',
                                    })
                                }}
                            </option>
                        </select>
                    </div>

                    <button
                        @click="goToNextDate"
                        :disabled="
                            availableDates.length === 0 ||
                            selectedDateValue === availableDates[0]
                        "
                        class="rounded-lg p-2 transition-colors hover:bg-muted disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <ChevronRight class="h-5 w-5" />
                    </button>
                </div>
            </div>

            <!-- Empty State -->
            <div
                v-if="!selectedSubjectId"
                class="rounded-lg border border-sidebar-border/70 p-12 dark:border-sidebar-border"
            >
                <div
                    class="flex flex-col items-center justify-center text-center"
                >
                    <Calendar class="mb-4 h-16 w-16 text-muted-foreground/30" />
                    <h3
                        class="mb-2 text-lg font-semibold text-muted-foreground"
                    >
                        Select a Subject
                    </h3>
                    <p class="max-w-md text-sm text-muted-foreground">
                        Choose a subject above to view and mark attendance for
                        enrolled students.
                    </p>
                </div>
            </div>

            <!-- Stats (Appears after subject selection) -->
            <div v-else class="grid grid-cols-2 gap-4 md:grid-cols-4">
                <div
                    class="rounded-lg border border-sidebar-border/70 bg-card p-4 dark:border-sidebar-border"
                >
                    <p class="mb-2 text-sm text-muted-foreground">Present</p>
                    <p class="text-2xl font-bold text-green-600">
                        {{ stats.present }}
                    </p>
                    <p class="mt-1 text-xs text-muted-foreground">
                        {{ ((stats.present / stats.total) * 100).toFixed(0) }}%
                    </p>
                </div>
                <div
                    class="rounded-lg border border-sidebar-border/70 bg-card p-4 dark:border-sidebar-border"
                >
                    <p class="mb-2 text-sm text-muted-foreground">Absent</p>
                    <p class="text-2xl font-bold text-red-600">
                        {{ stats.absent }}
                    </p>
                    <p class="mt-1 text-xs text-muted-foreground">
                        {{ ((stats.absent / stats.total) * 100).toFixed(0) }}%
                    </p>
                </div>
                <div
                    class="rounded-lg border border-sidebar-border/70 bg-card p-4 dark:border-sidebar-border"
                >
                    <p class="mb-2 text-sm text-muted-foreground">Late</p>
                    <p class="text-2xl font-bold text-yellow-600">
                        {{ stats.late }}
                    </p>
                    <p class="mt-1 text-xs text-muted-foreground">
                        {{ ((stats.late / stats.total) * 100).toFixed(0) }}%
                    </p>
                </div>
                <div
                    class="rounded-lg border border-sidebar-border/70 bg-card p-4 dark:border-sidebar-border"
                >
                    <p class="mb-2 text-sm text-muted-foreground">Total</p>
                    <p class="text-2xl font-bold">{{ stats.total }}</p>
                </div>
            </div>

            <!-- Attendance Table by Day (Appears after subject and date selection) -->
            <div
                v-if="selectedSubjectId && !selectedDateValue"
                class="rounded-lg border border-sidebar-border/70 p-12 dark:border-sidebar-border"
            >
                <div
                    class="flex flex-col items-center justify-center text-center"
                >
                    <Calendar class="mb-4 h-16 w-16 text-muted-foreground/30" />
                    <h3
                        class="mb-2 text-lg font-semibold text-muted-foreground"
                    >
                        Select a Date
                    </h3>
                    <p class="max-w-md text-sm text-muted-foreground">
                        Choose a date to view and mark attendance.
                    </p>
                </div>
            </div>

            <div
                v-else-if="selectedSubjectId && attendanceRecords.length === 0"
                class="rounded-lg border border-sidebar-border/70 p-12 dark:border-sidebar-border"
            >
                <div
                    class="flex flex-col items-center justify-center text-center"
                >
                    <Calendar class="mb-4 h-16 w-16 text-muted-foreground/30" />
                    <h3
                        class="mb-2 text-lg font-semibold text-muted-foreground"
                    >
                        No Attendance Records Yet
                    </h3>
                    <p class="max-w-md text-sm text-muted-foreground">
                        Attendance records will appear here once the admin adds
                        attendance data.
                    </p>
                </div>
            </div>
            <div v-else-if="selectedSubjectId" class="flex flex-col gap-6">
                <div
                    v-for="(records, day) in groupedRecords"
                    :key="day"
                    class="overflow-hidden overflow-x-auto rounded-lg border border-sidebar-border/70 dark:border-sidebar-border"
                >
                    <div
                        class="border-b border-sidebar-border/70 bg-muted/50 px-6 py-4 dark:border-sidebar-border dark:bg-muted/20"
                    >
                        <h2 class="text-lg font-semibold">{{ day }}</h2>
                    </div>
                    <table class="w-full min-w-max">
                        <thead
                            class="border-b border-sidebar-border/70 bg-muted/30 dark:border-sidebar-border dark:bg-muted/10"
                        >
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-sm font-semibold"
                                >
                                    Student Name
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-sm font-semibold"
                                >
                                    Status
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-sm font-semibold"
                                >
                                    Date
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-sm font-semibold"
                                >
                                    Time
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(record, index) in records"
                                :key="index"
                                class="border-b border-sidebar-border/70 transition-colors hover:bg-muted/50 dark:border-sidebar-border dark:hover:bg-muted/20"
                            >
                                <td class="px-6 py-4">
                                    <span class="font-medium">{{
                                        record.name
                                    }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div
                                        v-if="editingId === index"
                                        class="flex gap-2"
                                    >
                                        <select
                                            v-model="editingStatus"
                                            class="rounded border border-gray-300 px-2 py-1 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                        >
                                            <option value="present">
                                                Present
                                            </option>
                                            <option value="late">Late</option>
                                            <option value="absent">
                                                Absent
                                            </option>
                                        </select>
                                        <button
                                            type="button"
                                            @click.prevent="
                                                updateStatus(
                                                    record,
                                                    index,
                                                    editingStatus,
                                                )
                                            "
                                            class="rounded bg-green-600 px-2 py-1 text-sm text-white hover:bg-green-700"
                                        >
                                            ✓
                                        </button>
                                        <button
                                            @click="cancelEditing"
                                            class="rounded bg-gray-600 px-2 py-1 text-sm text-white hover:bg-gray-700"
                                        >
                                            ✕
                                        </button>
                                    </div>
                                    <span
                                        v-else
                                        @click="startEditing(record, index)"
                                        :class="[
                                            'inline-flex cursor-pointer items-center gap-2 rounded-full px-3 py-1 text-sm font-medium transition-opacity hover:opacity-80',
                                            getStatusBadge(record.status).bg,
                                            getStatusBadge(record.status).text,
                                        ]"
                                    >
                                        <Check
                                            v-if="record.status === 'present'"
                                            class="h-4 w-4"
                                        />
                                        <X
                                            v-else-if="
                                                record.status === 'absent'
                                            "
                                            class="h-4 w-4"
                                        />
                                        {{
                                            getStatusBadge(record.status).label
                                        }}
                                    </span>
                                </td>
                                <td
                                    class="px-6 py-4 text-sm text-muted-foreground"
                                >
                                    {{ record.date }}
                                </td>
                                <td
                                    class="px-6 py-4 text-sm text-muted-foreground"
                                >
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
