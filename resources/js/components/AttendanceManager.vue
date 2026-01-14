<script setup lang="ts">
import AddStudentModal from '@/components/AddStudentModal.vue';
import QrScanner from '@/components/QrScanner.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
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
    Loader2,
    Play,
    QrCode,
    Square,
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
const currentSession = ref<any>(null);
const scanStatus = ref<string | null>(null);
const scanMessage = ref<string | null>(null);
const isStartingSession = ref(false);
const isEndingSession = ref(false);
const showEndSessionConfirm = ref(false);
const showStartSessionDialog = ref(false);
const lateThresholdMinutes = ref(15);
const sessionStats = ref<any>(null);

onMounted(async () => {
    // Load attendance with saved values or today
    const today = new Date().toISOString().split('T')[0];
    await loadAttendance(today);

    // If teacher, try to fetch today's session
    if (roles?.isTeacher) {
        await fetchTodaySession();
    }
});

watch(
    () => props.subjectId,
    () => {
        loadAttendance(selectedDateValue.value || undefined);
        if (roles?.isTeacher) {
            fetchTodaySession();
        }
    },
);

const fetchTodaySession = async () => {
    try {
        const response = await axios.get('/api/attendance/sessions/today');
        if (response.data.sessions && response.data.sessions.length > 0) {
            // Find session for this subject
            const session = response.data.sessions.find(
                (s: any) =>
                    s.course_id == props.subjectId &&
                    s.status === 'in_progress',
            );
            if (session) {
                currentSessionId.value = session.id;
                currentSession.value = session;
                await fetchSessionStats();
            }
        }
    } catch (e) {
        console.error('Failed to fetch sessions', e);
    }
};

const fetchSessionStats = async () => {
    if (!currentSessionId.value) return;
    try {
        const response = await axios.get(
            `/api/attendance/sessions/${currentSessionId.value}/status`,
        );
        sessionStats.value = response.data;
    } catch (e) {
        console.error('Failed to fetch session stats', e);
    }
};

const startSession = async () => {
    isStartingSession.value = true;
    try {
        const response = await axios.post('/api/attendance/sessions/start', {
            subject_id: props.subjectId,
            late_threshold_minutes: lateThresholdMinutes.value,
        });
        currentSessionId.value = response.data.session.id;
        currentSession.value = response.data.session;
        await fetchSessionStats();
        showStartSessionDialog.value = false;
        scanMessage.value = response.data.is_new
            ? `Session started! Students scanning after ${lateThresholdMinutes.value} minutes will be marked late.`
            : 'Resumed existing session.';
        scanStatus.value = 'success';
        setTimeout(() => {
            scanMessage.value = null;
            scanStatus.value = null;
        }, 5000);
    } catch (e: any) {
        console.error('Failed to start session', e);
        scanMessage.value =
            e.response?.data?.message || 'Failed to start session';
        scanStatus.value = 'error';
    } finally {
        isStartingSession.value = false;
    }
};

const endSession = async () => {
    if (!currentSessionId.value) return;

    isEndingSession.value = true;
    try {
        const response = await axios.post(
            `/api/attendance/sessions/${currentSessionId.value}/end`,
        );

        scanMessage.value = `Session ended. ${response.data.marked_absent} student(s) marked absent.`;
        scanStatus.value = 'success';

        currentSessionId.value = null;
        currentSession.value = null;
        sessionStats.value = null;
        showEndSessionConfirm.value = false;

        // Reload attendance to show updated records
        loadAttendance(selectedDateValue.value || undefined);

        setTimeout(() => {
            scanMessage.value = null;
            scanStatus.value = null;
        }, 5000);
    } catch (e: any) {
        console.error('Failed to end session', e);
        scanMessage.value =
            e.response?.data?.message || 'Failed to end session';
        scanStatus.value = 'error';
    } finally {
        isEndingSession.value = false;
    }
};

const handleScan = async (decodedText: string) => {
    if (!currentSessionId.value) {
        scanMessage.value = 'No active session. Please start a session first.';
        scanStatus.value = 'error';
        return;
    }

    scanStatus.value = 'processing';
    scanMessage.value = 'Verifying...';

    try {
        const response = await axios.post('/api/attendance/scan', {
            qr_code: decodedText,
            session_id: currentSessionId.value,
            device_info: { user_agent: navigator.userAgent },
        });

        scanStatus.value = 'success';
        const statusLabel =
            response.data.status === 'late' ? 'late' : 'present';
        scanMessage.value = `Marked ${statusLabel}: ${response.data.student.name}`;

        // Refresh attendance list and stats
        loadAttendance(selectedDateValue.value || undefined);
        fetchSessionStats();

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

        if (data.selectedDate) {
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
        <!-- Session Status Banner -->
        <div
            v-if="roles?.isTeacher && currentSession"
            class="flex items-center justify-between rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-800 dark:bg-green-900/20"
        >
            <div class="flex items-center gap-3">
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-full bg-green-100 dark:bg-green-800"
                >
                    <Play class="h-5 w-5 text-green-600 dark:text-green-300" />
                </div>
                <div>
                    <p class="font-medium text-green-800 dark:text-green-200">
                        Session Active
                    </p>
                    <p class="text-sm text-green-600 dark:text-green-400">
                        Present: {{ sessionStats?.present_count || 0 }} | Late:
                        {{ sessionStats?.late_count || 0 }} | Unmarked:
                        {{ sessionStats?.unmarked_count || 0 }}
                        <span class="ml-2 inline-flex items-center gap-1">
                            <Clock class="h-3 w-3" />
                            Late after
                            {{
                                currentSession?.late_threshold_minutes || 15
                            }}min
                        </span>
                    </p>
                </div>
            </div>
            <Button
                variant="destructive"
                size="sm"
                @click="showEndSessionConfirm = true"
            >
                <Square class="mr-2 h-4 w-4" />
                End Session
            </Button>
        </div>

        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold">Attendance Records</h2>
                <p class="text-sm text-muted-foreground">
                    {{ selectedDateValue }}
                </p>
            </div>

            <div class="flex items-center gap-2">
                <!-- Session Controls for Teachers -->
                <div v-if="roles?.isTeacher" class="flex gap-2">
                    <!-- Start Session Dialog (when no active session) -->
                    <Dialog
                        v-if="!currentSessionId"
                        v-model:open="showStartSessionDialog"
                    >
                        <DialogTrigger as-child>
                            <Button class="gap-2">
                                <Play class="h-4 w-4" />
                                Start Session
                            </Button>
                        </DialogTrigger>
                        <DialogContent class="sm:max-w-md">
                            <DialogHeader>
                                <DialogTitle
                                    >Start Attendance Session</DialogTitle
                                >
                                <DialogDescription>
                                    Configure the session settings before
                                    starting.
                                </DialogDescription>
                            </DialogHeader>
                            <div class="grid gap-4 py-4">
                                <div class="grid gap-2">
                                    <Label for="lateThreshold">
                                        Late Threshold (minutes)
                                    </Label>
                                    <Input
                                        id="lateThreshold"
                                        v-model.number="lateThresholdMinutes"
                                        type="number"
                                        min="1"
                                        max="120"
                                        placeholder="15"
                                    />
                                    <p class="text-xs text-muted-foreground">
                                        Students scanning after this many
                                        minutes will be marked as "late" instead
                                        of "present".
                                    </p>
                                </div>
                            </div>
                            <DialogFooter>
                                <Button
                                    variant="outline"
                                    @click="showStartSessionDialog = false"
                                >
                                    Cancel
                                </Button>
                                <Button
                                    @click="startSession"
                                    :disabled="isStartingSession"
                                    class="gap-2"
                                >
                                    <Loader2
                                        v-if="isStartingSession"
                                        class="h-4 w-4 animate-spin"
                                    />
                                    <Play v-else class="h-4 w-4" />
                                    Start Session
                                </Button>
                            </DialogFooter>
                        </DialogContent>
                    </Dialog>

                    <!-- Scan Button (when session is active) -->
                    <Dialog
                        v-if="currentSessionId"
                        v-model:open="isScanningOpen"
                    >
                        <DialogTrigger as-child>
                            <Button class="gap-2">
                                <QrCode class="h-4 w-4" />
                                Scan QR
                            </Button>
                        </DialogTrigger>
                        <DialogContent class="sm:max-w-md">
                            <DialogHeader>
                                <DialogTitle>Scan Student QR Code</DialogTitle>
                                <DialogDescription>
                                    Point your camera at a student's QR code to
                                    mark their attendance.
                                </DialogDescription>
                            </DialogHeader>

                            <div class="flex flex-col items-center gap-4 py-4">
                                <QrScanner @scan="handleScan" />

                                <div
                                    v-if="scanMessage"
                                    :class="[
                                        'w-full rounded-md p-3 text-center text-sm font-medium transition-all',
                                        scanStatus === 'success'
                                            ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300'
                                            : scanStatus === 'error'
                                              ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300'
                                              : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
                                    ]"
                                >
                                    {{ scanMessage }}
                                </div>
                            </div>
                        </DialogContent>
                    </Dialog>
                </div>

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

        <!-- Status Message -->
        <div
            v-if="scanMessage && !isScanningOpen"
            :class="[
                'flex items-center gap-2 rounded-md p-3 text-sm',
                scanStatus === 'success'
                    ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300'
                    : scanStatus === 'error'
                      ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300'
                      : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
            ]"
        >
            <AlertCircle class="h-4 w-4" />
            {{ scanMessage }}
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

        <!-- End Session Confirmation Dialog -->
        <Dialog v-model:open="showEndSessionConfirm">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>End Session?</DialogTitle>
                    <DialogDescription>
                        This will end the current attendance session. All
                        students who haven't been marked will automatically be
                        marked as <strong>absent</strong>.
                    </DialogDescription>
                </DialogHeader>
                <div class="py-4">
                    <div class="rounded-lg bg-muted p-4 text-sm">
                        <p><strong>Session Summary:</strong></p>
                        <ul class="mt-2 space-y-1">
                            <li>
                                Present: {{ sessionStats?.present_count || 0 }}
                            </li>
                            <li>Late: {{ sessionStats?.late_count || 0 }}</li>
                            <li class="text-red-600">
                                Will be marked absent:
                                {{ sessionStats?.unmarked_count || 0 }}
                            </li>
                        </ul>
                    </div>
                </div>
                <DialogFooter>
                    <Button
                        variant="outline"
                        @click="showEndSessionConfirm = false"
                    >
                        Cancel
                    </Button>
                    <Button
                        variant="destructive"
                        @click="endSession"
                        :disabled="isEndingSession"
                    >
                        <Loader2
                            v-if="isEndingSession"
                            class="mr-2 h-4 w-4 animate-spin"
                        />
                        End Session & Mark Absent
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
