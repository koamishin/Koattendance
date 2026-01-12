<script setup lang="ts">
import AddStudentModal from '@/components/AddStudentModal.vue';
import QrScanner from '@/components/QrScanner.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { router, usePage } from '@inertiajs/vue3';
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
import { onMounted, ref, watch } from 'vue';

const props = defineProps<{
    subjectId: number;
}>();

const page = usePage();
const roles = page.props.auth.roles;

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
const availableDates = ref<string[]>([]);
const editingId = ref<number | null>(null);
const editingStatus = ref<string>('');
const isScanningOpen = ref(false);
const currentSessionId = ref<number | null>(null);
const scanStatus = ref<string | null>(null);
const scanMessage = ref<string | null>(null);

onMounted(async () => {
    // Load attendance with saved values or today
    const today = new Date().toISOString().split('T')[0];
    await loadAttendance(today);

    // If teacher, try to fetch today's session
    if (roles?.isTeacher) {
        fetchTodaySession();
    }
});

watch(
    () => props.subjectId,
    () => {
        loadAttendance(selectedDateValue.value || undefined);
    },
);

const fetchTodaySession = async () => {
    try {
        const response = await axios.get(route('api.sessions.today'));
        if (response.data.sessions && response.data.sessions.length > 0) {
            // Filter session for this subject if possible, or just pick first active
            // Ideally backend filters by teacher and date. We might need to filter by subject here if the API returns all.
            const session =
                response.data.sessions.find(
                    (s: any) => s.course_id == props.subjectId,
                ) || response.data.sessions[0];
            currentSessionId.value = session.id;
        }
    } catch (e) {
        console.error('Failed to fetch sessions', e);
    }
};

const handleScan = async (decodedText: string) => {
    if (!currentSessionId.value) {
        scanMessage.value =
            'No active session. Please select a subject first (Mock logic).';
        scanStatus.value = 'error';
        return;
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
        loadAttendance(selectedDateValue.value || undefined);

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

const loadAttendance = async (date?: string) => {
    if (!props.subjectId) return;

    try {
        const url = new URL('/api/attendance', window.location.origin);
        if (date) {
            url.searchParams.append('date', date);
        }
        url.searchParams.append('subjectId', props.subjectId.toString());

        const response = await fetch(url.toString());
        const data = await response.json();
        attendanceRecords.value = data.attendanceRecords;
        groupedRecords.value = data.groupedRecords;
        stats.value = data.stats;
        selectedDate.value = data.selectedDate;
        availableDates.value = data.availableDates;

        // Extract date value from formatted string (e.g., "January 10, 2026" -> "2026-01-10")
        if (data.selectedDate) {
            // This relies on the backend sending formatted date, ideally backend sends ISO date too
            // For now, let's trust the 'date' param we sent or default to today if it matches
            if (date) selectedDateValue.value = date;
            else {
                const dateObj = new Date();
                selectedDateValue.value = dateObj.toISOString().split('T')[0];
            }
        }
    } catch (error) {
        console.error('Error fetching attendance:', error);
    }
};

const onDateChange = (date: string) => {
    selectedDateValue.value = date;
    loadAttendance(date);
};

const goToPreviousDate = () => {
    if (!selectedDateValue.value) return;
    const currentIndex = availableDates.value.indexOf(selectedDateValue.value);
    if (currentIndex < availableDates.value.length - 1) {
        const newDate = availableDates.value[currentIndex + 1];
        selectedDateValue.value = newDate;
        loadAttendance(newDate);
    }
};

const goToNextDate = () => {
    if (!selectedDateValue.value) return;
    const currentIndex = availableDates.value.indexOf(selectedDateValue.value);
    if (currentIndex > 0) {
        const newDate = availableDates.value[currentIndex - 1];
        selectedDateValue.value = newDate;
        loadAttendance(newDate);
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
        payload.subjectId = props.subjectId;
    }

    router.post(`/attendance/${record.id || 'null'}/update-status`, payload, {
        preserveState: true,
        onFinish: () => {
            editingId.value = null;
            loadAttendance(selectedDateValue.value || undefined);
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
    <div class="flex flex-col gap-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold">Attendance Records</h2>
                <p class="text-sm text-muted-foreground">
                    {{ selectedDateValue }}
                </p>
            </div>

            <div class="flex items-center gap-2">
                <!-- Scan Button for Teachers -->
                <Dialog v-if="roles?.isTeacher" v-model:open="isScanningOpen">
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

                <!-- Add Student Button -->
                <AddStudentModal
                    v-if="roles?.isTeacher || roles?.isAdmin"
                    :subject-id="subjectId"
                    @student-added="
                        loadAttendance(selectedDateValue || undefined)
                    "
                />
            </div>
        </div>

        <!-- Date Selection -->
        <div class="flex items-center gap-4">
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
                            onDateChange((e.target as HTMLSelectElement).value)
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

        <!-- Stats -->
        <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
            <div class="rounded-lg border bg-card p-4">
                <p class="mb-2 text-sm text-muted-foreground">Present</p>
                <p class="text-2xl font-bold text-green-600">
                    {{ stats.present }}
                </p>
            </div>
            <div class="rounded-lg border bg-card p-4">
                <p class="mb-2 text-sm text-muted-foreground">Absent</p>
                <p class="text-2xl font-bold text-red-600">
                    {{ stats.absent }}
                </p>
            </div>
            <div class="rounded-lg border bg-card p-4">
                <p class="mb-2 text-sm text-muted-foreground">Late</p>
                <p class="text-2xl font-bold text-yellow-600">
                    {{ stats.late }}
                </p>
            </div>
            <div class="rounded-lg border bg-card p-4">
                <p class="mb-2 text-sm text-muted-foreground">Total</p>
                <p class="text-2xl font-bold">{{ stats.total }}</p>
            </div>
        </div>

        <!-- Attendance Table -->
        <div
            v-if="attendanceRecords.length === 0"
            class="rounded-lg border border-dashed p-12 text-center"
        >
            <p class="text-muted-foreground">
                No students found for this class.
            </p>
        </div>

        <div v-else class="overflow-hidden rounded-lg border">
            <table class="w-full">
                <thead class="bg-muted/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold">
                            Student Name
                        </th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">
                            Status
                        </th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">
                            Time
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <tr
                        v-for="(record, index) in attendanceRecords"
                        :key="index"
                        class="hover:bg-muted/50"
                    >
                        <td class="px-6 py-4 font-medium">{{ record.name }}</td>
                        <td class="px-6 py-4">
                            <div v-if="editingId === index" class="flex gap-2">
                                <select
                                    v-model="editingStatus"
                                    class="rounded border bg-background px-2 py-1 text-sm"
                                >
                                    <option value="present">Present</option>
                                    <option value="late">Late</option>
                                    <option value="absent">Absent</option>
                                </select>
                                <button
                                    @click="
                                        updateStatus(
                                            record,
                                            index,
                                            editingStatus,
                                        )
                                    "
                                    class="text-green-600 hover:text-green-700"
                                >
                                    <Check class="h-4 w-4" />
                                </button>
                                <button
                                    @click="cancelEditing"
                                    class="text-gray-500 hover:text-gray-700"
                                >
                                    <X class="h-4 w-4" />
                                </button>
                            </div>
                            <span
                                v-else
                                @click="startEditing(record, index)"
                                :class="[
                                    'inline-flex cursor-pointer items-center gap-2 rounded-full px-3 py-1 text-sm font-medium',
                                    getStatusBadge(record.status).bg,
                                    getStatusBadge(record.status).text,
                                ]"
                            >
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
</template>
