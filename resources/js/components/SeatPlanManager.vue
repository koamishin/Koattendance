<script setup lang="ts">
import {
    Check,
    ChevronDown,
    ChevronRight,
    ChevronUp,
    Clock,
    Plus,
    Trash2,
    X,
} from 'lucide-vue-next';
import { computed, onMounted, ref, watch } from 'vue';

interface Student {
    id: number;
    name: string;
    email?: string;
    student_id?: string;
    present?: boolean;
    status?: 'present' | 'late' | 'absent';
}

const props = defineProps<{
    subjectId: number;
}>();

// All available students from database
const allStudents = ref<Student[]>([]);

// Seating arrangement
const seatingArrangement = ref<
    Array<{ seatId: number; student: Student | null }>
>([]);

const isLoading = ref(true);
const gridRows = ref(4);
const gridColumns = ref(4);

const draggedStudent = ref<Student | null>(null);

// Get status styling based on attendance
const getStatusStyle = (student: Student | null) => {
    if (!student) return {};

    if (student.status === 'present') {
        return {
            bg: 'bg-green-100 dark:bg-green-800',
            border: 'border-green-500',
            icon: 'text-green-600',
        };
    } else if (student.status === 'late') {
        return {
            bg: 'bg-yellow-100 dark:bg-yellow-800',
            border: 'border-yellow-500',
            icon: 'text-yellow-600',
        };
    } else {
        return {
            bg: 'bg-red-100 dark:bg-red-800',
            border: 'border-red-500',
            icon: 'text-red-600',
        };
    }
};

const getStatusIcon = (status: string | undefined) => {
    if (status === 'present') return 'check';
    if (status === 'late') return 'clock';
    return 'x';
};

// Fetch students from database
const fetchStudents = async () => {
    try {
        // We need to filter by subject ID.
        // Assuming api/attendance endpoint returns students for a subject if we filter,
        // OR we use a dedicated subject students endpoint.
        // Let's use the Subject API if possible or filter the generic one.
        // For now, let's reuse api/students but we might need to filter manually or update endpoint.

        // Actually, let's use the Attendance API to get enrolled students + status
        const attendanceUrl = new URL(
            '/api/attendance',
            window.location.origin,
        );
        attendanceUrl.searchParams.append(
            'subjectId',
            props.subjectId.toString(),
        );
        attendanceUrl.searchParams.append(
            'date',
            new Date().toISOString().split('T')[0],
        ); // Get today's status

        const [studentsRes, seatingRes] = await Promise.all([
            fetch(attendanceUrl.toString()),
            fetch(`/api/seating?subject_id=${props.subjectId}`), // We need to update backend to filter by subject
        ]);

        const studentsData = await studentsRes.json();
        const seatingData = await seatingRes.json();

        // Map attendance records to Student interface
        allStudents.value = studentsData.attendanceRecords.map(
            (record: any) => ({
                id: record.student_id,
                name: record.name,
                status:
                    record.status === 'unmarked' ? undefined : record.status,
            }),
        );

        // Set grid dimensions if available, else default
        if (seatingData.arrangement) {
            gridRows.value = seatingData.arrangement.rows;
            gridColumns.value = seatingData.arrangement.columns;
            const totalSeats = seatingData.arrangement.totalSeats;

            // Initialize seating from database
            const seats: Array<{ seatId: number; student: Student | null }> =
                [];
            for (let i = 1; i <= totalSeats; i++) {
                const seating = seatingData.seatings.find(
                    (s: any) => s.seat_number === i,
                );
                seats.push({
                    seatId: i,
                    student: seating?.student
                        ? allStudents.value.find(
                              (s) => s.id === seating.student.id,
                          ) || null
                        : null,
                });
            }
            seatingArrangement.value = seats;
        } else {
            // Default empty grid
            initEmptyGrid(gridRows.value, gridColumns.value);
        }
    } catch (error) {
        console.error('Error fetching data:', error);
    } finally {
        isLoading.value = false;
    }
};

const initEmptyGrid = (rows: number, cols: number) => {
    const totalSeats = rows * cols;
    const seats = [];
    for (let i = 1; i <= totalSeats; i++) {
        seats.push({ seatId: i, student: null });
    }
    seatingArrangement.value = seats;
};

// Save seating to database
const saveSeating = async () => {
    try {
        const seatingsToSave = seatingArrangement.value.map((seat) => ({
            seat_number: seat.seatId,
            student_id: seat.student?.id || null,
        }));

        const response = await fetch('/api/seating', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN':
                    document
                        .querySelector('meta[name="csrf-token"]')
                        ?.getAttribute('content') || '',
            },
            body: JSON.stringify({
                seatings: seatingsToSave,
                subject_id: props.subjectId,
            }),
        });

        if (!response.ok) {
            console.error('Failed to save seating');
        }
    } catch (error) {
        console.error('Error saving seating:', error);
    }
};

// Students not seated
const availableStudents = computed(() => {
    const seatedIds = new Set(
        seatingArrangement.value
            .filter((seat) => seat.student)
            .map((seat) => seat.student!.id),
    );
    return allStudents.value.filter((student) => !seatedIds.has(student.id));
});

// Summary stats
const presentCount = computed(() => {
    return seatingArrangement.value.filter(
        (seat) => seat.student?.status === 'present',
    ).length;
});

const lateCount = computed(() => {
    return seatingArrangement.value.filter(
        (seat) => seat.student?.status === 'late',
    ).length;
});

const absentCount = computed(() => {
    return seatingArrangement.value.filter(
        (seat) => seat.student?.status === 'absent',
    ).length;
});

const seatedCount = computed(() => {
    return seatingArrangement.value.filter((seat) => seat.student).length;
});

function dragStartFromAvailable(student: Student) {
    draggedStudent.value = student;
}

function dragStartFromSeat(seatIndex: number, event: DragEvent) {
    draggedStudent.value = seatingArrangement.value[seatIndex].student;
    if (event.dataTransfer) {
        event.dataTransfer.effectAllowed = 'move';
    }
}

function dragOver(event: DragEvent) {
    event.preventDefault();
    if (event.dataTransfer) {
        event.dataTransfer.dropEffect = 'move';
    }
}

function dropOnSeat(seatIndex: number) {
    if (!draggedStudent.value) return;

    const currentSeat = seatingArrangement.value[seatIndex];

    // Find the seat with the dragged student
    const draggedFromIndex = seatingArrangement.value.findIndex(
        (s) => s.student?.id === draggedStudent.value?.id,
    );

    if (draggedFromIndex !== -1) {
        // Dragged from another seat - swap them
        const temp = seatingArrangement.value[draggedFromIndex].student;
        seatingArrangement.value[draggedFromIndex].student =
            currentSeat.student;
        seatingArrangement.value[seatIndex].student = temp;
    } else {
        // Dragged from available list - place in seat
        seatingArrangement.value[seatIndex].student = draggedStudent.value;
    }

    draggedStudent.value = null;
    saveSeating();
}

function removeSeatStudent(seatIndex: number) {
    seatingArrangement.value[seatIndex].student = null;
    saveSeating();
}

function addStudentToFirstEmpty(student: Student) {
    const emptyIndex = seatingArrangement.value.findIndex((s) => !s.student);
    if (emptyIndex !== -1) {
        seatingArrangement.value[emptyIndex].student = student;
        saveSeating();
    }
}

// Update grid dimensions
const updateGridSize = async (newRows: number, newColumns: number) => {
    try {
        const response = await fetch('/api/seating/grid', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN':
                    document
                        .querySelector('meta[name="csrf-token"]')
                        ?.getAttribute('content') || '',
            },
            body: JSON.stringify({
                rows: newRows,
                columns: newColumns,
                subject_id: props.subjectId,
            }),
        });

        if (response.ok) {
            gridRows.value = newRows;
            gridColumns.value = newColumns;

            // Recreate seats array with new dimensions
            const totalSeats = newRows * newColumns;
            const existingSeats = new Map(
                seatingArrangement.value.map((s) => [s.seatId, s.student]),
            );

            const newSeats: Array<{ seatId: number; student: Student | null }> =
                [];
            for (let i = 1; i <= totalSeats; i++) {
                newSeats.push({
                    seatId: i,
                    student: existingSeats.get(i) || null,
                });
            }
            seatingArrangement.value = newSeats;
            saveSeating();
        }
    } catch (error) {
        console.error('Error updating grid dimensions:', error);
    }
};

const addRow = () => {
    updateGridSize(gridRows.value + 1, gridColumns.value);
};

const removeRow = () => {
    if (gridRows.value > 2) {
        updateGridSize(gridRows.value - 1, gridColumns.value);
    }
};

const addColumn = () => {
    updateGridSize(gridRows.value, gridColumns.value + 1);
};

const removeColumn = () => {
    if (gridColumns.value > 2) {
        updateGridSize(gridRows.value, gridColumns.value - 1);
    }
};

// Fetch students on component mount
onMounted(() => {
    fetchStudents();
});

watch(
    () => props.subjectId,
    () => {
        isLoading.value = true;
        fetchStudents();
    },
);
</script>

<template>
    <div class="flex h-full flex-col gap-6">
        <!-- Loading State -->
        <div v-if="isLoading" class="flex h-96 items-center justify-center">
            <div class="text-center">
                <div
                    class="mb-4 inline-block h-12 w-12 animate-spin rounded-full border-b-2 border-primary"
                />
                <p class="text-muted-foreground">Loading seat plan...</p>
            </div>
        </div>

        <!-- Main Content -->
        <template v-else>
            <!-- Grid Controls -->
            <div
                class="flex flex-col gap-4 rounded-lg border border-sidebar-border/70 bg-card p-4 md:flex-row md:items-center dark:border-sidebar-border"
            >
                <div class="flex items-center gap-4">
                    <span class="text-sm font-medium">Grid Size:</span>
                    <span class="text-sm text-muted-foreground"
                        >{{ gridRows }} × {{ gridColumns }}</span
                    >
                </div>

                <!-- Row Controls -->
                <div class="flex items-center gap-2">
                    <span class="text-xs text-muted-foreground md:hidden"
                        >Rows:</span
                    >
                    <button
                        @click="removeRow"
                        :disabled="gridRows <= 2"
                        class="flex items-center gap-1 rounded px-2 py-1 text-sm hover:bg-muted disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <ChevronUp class="h-4 w-4" />
                        <span class="hidden md:inline">Rows</span>
                    </button>
                    <button
                        @click="addRow"
                        class="flex items-center gap-1 rounded px-2 py-1 text-sm hover:bg-muted"
                    >
                        <ChevronDown class="h-4 w-4" />
                    </button>
                </div>

                <!-- Column Controls -->
                <div class="flex items-center gap-2">
                    <span class="text-xs text-muted-foreground md:hidden"
                        >Cols:</span
                    >
                    <button
                        @click="removeColumn"
                        :disabled="gridColumns <= 2"
                        class="flex items-center gap-1 rounded px-2 py-1 text-sm hover:bg-muted disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <ChevronRight class="h-4 w-4 rotate-180" />
                        <span class="hidden md:inline">Cols</span>
                    </button>
                    <button
                        @click="addColumn"
                        class="flex items-center gap-1 rounded px-2 py-1 text-sm hover:bg-muted"
                    >
                        <ChevronRight class="h-4 w-4" />
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-4">
                <!-- Available Students Panel -->
                <div
                    class="col-span-1 rounded-lg border border-sidebar-border/70 bg-card p-2 lg:col-span-1 dark:border-sidebar-border"
                >
                    <h2 class="mb-2 text-sm font-semibold">
                        Available ({{ availableStudents.length }})
                    </h2>

                    <div class="max-h-full space-y-1 overflow-y-auto">
                        <div
                            v-if="availableStudents.length === 0"
                            class="flex items-center justify-center py-4 text-center text-xs text-muted-foreground"
                        >
                            All seated!
                        </div>

                        <div
                            v-for="student in availableStudents"
                            :key="student.id"
                            draggable="true"
                            @dragstart="dragStartFromAvailable(student)"
                            class="flex cursor-move items-center gap-2 rounded border border-sidebar-border/70 bg-muted/50 p-1.5 transition-all hover:bg-muted dark:border-sidebar-border dark:bg-muted/20"
                        >
                            <!-- Status indicator -->
                            <div
                                :class="[
                                    'flex h-4 w-4 flex-shrink-0 items-center justify-center rounded border-2',
                                    student.status === 'present'
                                        ? 'border-green-500 bg-green-100 dark:bg-green-800'
                                        : student.status === 'late'
                                          ? 'border-yellow-500 bg-yellow-100 dark:bg-yellow-800'
                                          : 'border-red-500 bg-red-100 dark:bg-red-800',
                                ]"
                            >
                                <Check
                                    v-if="student.status === 'present'"
                                    class="h-3 w-3 text-green-600"
                                />
                                <Clock
                                    v-else-if="student.status === 'late'"
                                    class="h-3 w-3 text-yellow-600"
                                />
                                <X v-else class="h-3 w-3 text-red-600" />
                            </div>

                            <!-- Student info -->
                            <div class="min-w-0 flex-1">
                                <p
                                    class="truncate text-xs leading-tight font-medium"
                                >
                                    {{ student.name }}
                                </p>
                            </div>

                            <!-- Quick add button -->
                            <button
                                @click="addStudentToFirstEmpty(student)"
                                class="flex-shrink-0 rounded p-0.5 hover:bg-primary/20 dark:hover:bg-primary/30"
                                title="Add to first empty seat"
                            >
                                <Plus class="h-3 w-3 text-primary" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Seating Grid -->
                <div class="col-span-1 lg:col-span-3">
                    <div
                        class="rounded-lg border border-sidebar-border/70 bg-muted/50 p-8 dark:border-sidebar-border dark:bg-muted/20"
                    >
                        <!-- Whiteboard indicator -->
                        <div
                            class="mb-8"
                            :style="{ gridColumn: `1 / span ${gridColumns}` }"
                        >
                            <div
                                class="flex items-center justify-center rounded border-2 border-blue-400 bg-blue-100 py-3 font-bold text-blue-900 dark:border-blue-700 dark:bg-blue-900/30 dark:text-blue-200"
                            >
                                Whiteboard
                            </div>
                        </div>

                        <!-- Seats Grid -->
                        <div
                            class="gap-2 overflow-x-auto md:gap-4"
                            :style="{
                                display: 'grid',
                                gridTemplateColumns: `repeat(${gridColumns}, minmax(100px, 1fr))`,
                                gridAutoRows: 'auto',
                            }"
                        >
                            <div
                                v-for="(seat, index) in seatingArrangement"
                                :key="seat.seatId"
                                @dragover="dragOver"
                                @drop="dropOnSeat(index)"
                                class="relative h-24 cursor-grab rounded-lg border-2 p-2 transition-all hover:shadow-lg md:h-32 md:p-3"
                                :class="[
                                    seat.student
                                        ? seat.student.status === 'present'
                                            ? 'border-green-500 bg-green-50 dark:bg-green-900/20'
                                            : seat.student.status === 'late'
                                              ? 'border-yellow-500 bg-yellow-50 dark:bg-yellow-900/20'
                                              : 'border-red-500 bg-red-50 dark:bg-red-900/20'
                                        : 'border-dashed border-gray-400 bg-gray-50 dark:border-gray-600 dark:bg-gray-800/50',
                                ]"
                            >
                                <!-- Remove Button -->
                                <button
                                    v-if="seat.student"
                                    @click="removeSeatStudent(index)"
                                    class="absolute top-1 right-1 rounded p-1 hover:bg-red-200 dark:hover:bg-red-900"
                                    title="Remove student"
                                >
                                    <Trash2 class="h-3 w-3 text-red-600" />
                                </button>

                                <!-- Student or Empty -->
                                <div
                                    v-if="seat.student"
                                    class="flex h-full flex-col items-center justify-center"
                                    draggable="true"
                                    @dragstart="
                                        dragStartFromSeat(index, $event)
                                    "
                                >
                                    <div
                                        :class="[
                                            'mb-2 flex h-8 w-8 items-center justify-center rounded-lg border-2',
                                            seat.student.status === 'present'
                                                ? 'border-green-500 bg-green-100 dark:bg-green-800'
                                                : seat.student.status === 'late'
                                                  ? 'border-yellow-500 bg-yellow-100 dark:bg-yellow-800'
                                                  : 'border-red-500 bg-red-100 dark:bg-red-800',
                                        ]"
                                    >
                                        <Check
                                            v-if="
                                                seat.student.status ===
                                                'present'
                                            "
                                            class="h-5 w-5 text-green-600"
                                        />
                                        <Clock
                                            v-else-if="
                                                seat.student.status === 'late'
                                            "
                                            class="h-5 w-5 text-yellow-600"
                                        />
                                        <X
                                            v-else
                                            class="h-5 w-5 text-red-600"
                                        />
                                    </div>
                                    <p
                                        class="line-clamp-1 text-center text-xs font-semibold md:line-clamp-2"
                                    >
                                        {{ seat.student.name }}
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        Seat {{ seat.seatId }}
                                    </p>
                                </div>
                                <div
                                    v-else
                                    class="flex h-full items-center justify-center"
                                >
                                    <span class="text-xs text-gray-400"
                                        >Seat {{ seat.seatId }}</span
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>
