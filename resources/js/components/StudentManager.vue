<script setup lang="ts">
import AddStudentModal from '@/components/AddStudentModal.vue';
import BulkPrintQrCodes from '@/components/BulkPrintQrCodes.vue';
import StudentQrCode from '@/components/StudentQrCode.vue';
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
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { router } from '@inertiajs/vue3';
import {
    AlertCircle,
    CheckCircle2,
    Loader2,
    Plus,
    QrCode,
    UserPlus,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

const props = defineProps<{
    subjectId: number;
    initialStudents?: any[];
}>();

const students = computed(() => props.initialStudents || []);
const isCreating = ref(false);
const showCreateModal = ref(false);
const showQrModal = ref(false);
const selectedStudent = ref<any>(null);
const errorMessage = ref<string | null>(null);
const successMessage = ref<string | null>(null);

const createForm = ref({
    first_name: '',
    last_name: '',
    middle_name: '',
    student_id: '',
    email: '',
    phone: '',
    gender: '',
    // Guardian fields
    guardian_name: '',
    guardian_email: '',
    guardian_phone: '',
    guardian_relationship: 'Parent',
});

const resetForm = () => {
    createForm.value = {
        first_name: '',
        last_name: '',
        middle_name: '',
        student_id: '',
        email: '',
        phone: '',
        gender: '',
        guardian_name: '',
        guardian_email: '',
        guardian_phone: '',
        guardian_relationship: 'Parent',
    };
    errorMessage.value = null;
    successMessage.value = null;
};

const createStudent = () => {
    if (
        !createForm.value.first_name ||
        !createForm.value.last_name ||
        !createForm.value.student_id
    ) {
        errorMessage.value =
            'Please fill in all required fields (First Name, Last Name, Student ID).';
        return;
    }

    isCreating.value = true;
    errorMessage.value = null;
    successMessage.value = null;

    router.post(
        '/api/students',
        {
            ...createForm.value,
            subject_id: props.subjectId,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                successMessage.value =
                    'Student created and enrolled successfully!';
                setTimeout(() => {
                    showCreateModal.value = false;
                    resetForm();
                }, 1500);
            },
            onError: (errors: any) => {
                console.error('Failed to create student', errors);
                errorMessage.value =
                    Object.values(errors).flat().join(', ') ||
                    'Failed to create student.';
            },
            onFinish: () => {
                isCreating.value = false;
            },
        },
    );
};

const openQrModal = (student: any) => {
    selectedStudent.value = student;
    showQrModal.value = true;
};

watch(showCreateModal, (newVal) => {
    if (!newVal) {
        resetForm();
    }
});
</script>

<template>
    <div class="flex flex-col gap-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold">Students</h2>
                <p class="text-sm text-muted-foreground">
                    Manage students enrolled in this class. Click the QR icon to
                    view/print their attendance QR code.
                </p>
            </div>

            <div class="flex gap-2">
                <!-- Bulk Print QR Codes -->
                <BulkPrintQrCodes :subject-id="subjectId" />

                <!-- Create New Student Dialog -->
                <Dialog v-model:open="showCreateModal">
                    <DialogTrigger as-child>
                        <Button class="gap-2">
                            <Plus class="h-4 w-4" />
                            <span class="hidden sm:inline">Create New</span>
                            <span class="sm:hidden">New</span>
                        </Button>
                    </DialogTrigger>
                    <DialogContent
                        class="max-h-[90vh] overflow-y-auto sm:max-w-lg"
                    >
                        <DialogHeader>
                            <DialogTitle>Create New Student</DialogTitle>
                            <DialogDescription>
                                Add a new student to the system and enroll them
                                in this class.
                            </DialogDescription>
                        </DialogHeader>

                        <div class="grid gap-6 py-4">
                            <!-- Success Message -->
                            <div
                                v-if="successMessage"
                                class="flex items-center gap-2 rounded-md bg-green-100 p-3 text-sm text-green-700 dark:bg-green-900/30 dark:text-green-300"
                            >
                                <CheckCircle2 class="h-4 w-4 shrink-0" />
                                {{ successMessage }}
                            </div>

                            <!-- Error Message -->
                            <div
                                v-if="errorMessage"
                                class="flex items-center gap-2 rounded-md bg-destructive/10 p-3 text-sm text-destructive"
                            >
                                <AlertCircle class="h-4 w-4 shrink-0" />
                                {{ errorMessage }}
                            </div>

                            <!-- Student Information Section -->
                            <div class="space-y-4">
                                <h3
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    Student Information
                                </h3>

                                <div class="grid grid-cols-2 gap-4">
                                    <div class="grid gap-2">
                                        <Label for="first_name"
                                            >First Name
                                            <span class="text-destructive"
                                                >*</span
                                            ></Label
                                        >
                                        <Input
                                            id="first_name"
                                            v-model="createForm.first_name"
                                            placeholder="John"
                                        />
                                    </div>
                                    <div class="grid gap-2">
                                        <Label for="last_name"
                                            >Last Name
                                            <span class="text-destructive"
                                                >*</span
                                            ></Label
                                        >
                                        <Input
                                            id="last_name"
                                            v-model="createForm.last_name"
                                            placeholder="Doe"
                                        />
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div class="grid gap-2">
                                        <Label for="middle_name"
                                            >Middle Name</Label
                                        >
                                        <Input
                                            id="middle_name"
                                            v-model="createForm.middle_name"
                                            placeholder="(Optional)"
                                        />
                                    </div>
                                    <div class="grid gap-2">
                                        <Label for="student_id"
                                            >Student ID
                                            <span class="text-destructive"
                                                >*</span
                                            ></Label
                                        >
                                        <Input
                                            id="student_id"
                                            v-model="createForm.student_id"
                                            placeholder="STD-12345"
                                        />
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div class="grid gap-2">
                                        <Label for="email">Email</Label>
                                        <Input
                                            id="email"
                                            type="email"
                                            v-model="createForm.email"
                                            placeholder="student@example.com"
                                        />
                                    </div>
                                    <div class="grid gap-2">
                                        <Label for="phone">Phone</Label>
                                        <Input
                                            id="phone"
                                            v-model="createForm.phone"
                                            placeholder="+1 234 567 8900"
                                        />
                                    </div>
                                </div>

                                <div class="grid gap-2">
                                    <Label for="gender">Gender</Label>
                                    <select
                                        id="gender"
                                        v-model="createForm.gender"
                                        class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                                    >
                                        <option value="">Select gender</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Guardian Information Section -->
                            <div class="space-y-4 border-t pt-4">
                                <h3
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    Guardian/Parent Contact (Optional)
                                </h3>

                                <div class="grid grid-cols-2 gap-4">
                                    <div class="grid gap-2">
                                        <Label for="guardian_name"
                                            >Guardian Name</Label
                                        >
                                        <Input
                                            id="guardian_name"
                                            v-model="createForm.guardian_name"
                                            placeholder="Jane Doe"
                                        />
                                    </div>
                                    <div class="grid gap-2">
                                        <Label for="guardian_relationship"
                                            >Relationship</Label
                                        >
                                        <select
                                            id="guardian_relationship"
                                            v-model="
                                                createForm.guardian_relationship
                                            "
                                            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                                        >
                                            <option value="Parent">
                                                Parent
                                            </option>
                                            <option value="Mother">
                                                Mother
                                            </option>
                                            <option value="Father">
                                                Father
                                            </option>
                                            <option value="Guardian">
                                                Guardian
                                            </option>
                                            <option value="Grandparent">
                                                Grandparent
                                            </option>
                                            <option value="Sibling">
                                                Sibling
                                            </option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div class="grid gap-2">
                                        <Label for="guardian_email"
                                            >Guardian Email</Label
                                        >
                                        <Input
                                            id="guardian_email"
                                            type="email"
                                            v-model="createForm.guardian_email"
                                            placeholder="parent@example.com"
                                        />
                                    </div>
                                    <div class="grid gap-2">
                                        <Label for="guardian_phone"
                                            >Guardian Phone</Label
                                        >
                                        <Input
                                            id="guardian_phone"
                                            v-model="createForm.guardian_phone"
                                            placeholder="+1 234 567 8900"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <DialogFooter>
                            <Button
                                variant="outline"
                                @click="showCreateModal = false"
                                :disabled="isCreating"
                            >
                                Cancel
                            </Button>
                            <Button
                                @click="createStudent"
                                :disabled="isCreating"
                            >
                                <Loader2
                                    v-if="isCreating"
                                    class="mr-2 h-4 w-4 animate-spin"
                                />
                                Create & Enroll
                            </Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>

                <!-- Add Existing Student -->
                <AddStudentModal
                    :subject-id="subjectId"
                    @student-added="router.reload()"
                />
            </div>
        </div>

        <!-- Student List -->
        <div class="rounded-lg border bg-card">
            <div
                v-if="students.length === 0"
                class="flex h-48 flex-col items-center justify-center gap-2 p-6 text-center"
            >
                <div class="rounded-full bg-muted p-3">
                    <UserPlus class="h-6 w-6 text-muted-foreground" />
                </div>
                <h3 class="font-semibold">No students enrolled</h3>
                <p class="mb-4 text-sm text-muted-foreground">
                    Add existing students or create new ones to get started.
                </p>
                <div class="flex gap-2">
                    <Button variant="outline" @click="showCreateModal = true">
                        <Plus class="mr-2 h-4 w-4" /> Create New Student
                    </Button>
                </div>
            </div>

            <div v-else class="divide-y">
                <div
                    v-for="student in students"
                    :key="student.id"
                    class="flex items-center justify-between p-4 transition-colors hover:bg-muted/50"
                >
                    <div class="flex items-center gap-4">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10 font-semibold text-primary"
                        >
                            {{ student.name?.charAt(0) || '?' }}
                        </div>
                        <div>
                            <p class="font-medium">{{ student.name }}</p>
                            <p class="text-sm text-muted-foreground">
                                ID: {{ student.student_code || student.id }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            @click="openQrModal(student)"
                            class="gap-2"
                        >
                            <QrCode class="h-4 w-4" />
                            <span class="hidden sm:inline">QR Code</span>
                        </Button>
                    </div>
                </div>
            </div>
        </div>

        <!-- QR Code Modal -->
        <Dialog v-model:open="showQrModal">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Student QR Code</DialogTitle>
                    <DialogDescription>
                        Print or download this QR code for attendance tracking.
                    </DialogDescription>
                </DialogHeader>
                <StudentQrCode
                    v-if="selectedStudent"
                    :student-id="selectedStudent.id"
                    :student-name="selectedStudent.name"
                    :student-code="selectedStudent.student_code"
                />
            </DialogContent>
        </Dialog>
    </div>
</template>
