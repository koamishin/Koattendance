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
import { Select } from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import { toast } from '@/lib/toast';
import { router } from '@inertiajs/vue3';
import {
    AlertCircle,
    Check,
    ChevronLeft,
    ChevronRight,
    Contact,
    FileText,
    Loader2,
    Plus,
    QrCode,
    User,
    UserPlus,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { z } from 'zod';

const props = defineProps<{
    subjectId: number;
    initialStudents?: any[];
}>();

const students = computed(() => props.initialStudents || []);
const isCreating = ref(false);
const showCreateModal = ref(false);
const showQrModal = ref(false);
const selectedStudent = ref<any>(null);
const currentStep = ref(1);

const createForm = ref({
    first_name: '',
    last_name: '',
    middle_name: '',
    student_id: '',
    email: '',
    phone: '',
    gender: '',
    birth_date: '',
    current_grade_level: '',
    // Guardian fields
    guardian_name: '',
    guardian_email: '',
    guardian_phone: '',
    guardian_relationship: 'Parent',
});

const errors = ref<Record<string, string>>({});

// Validation Schemas
const step1Schema = z.object({
    first_name: z.string().min(2, 'First name is required'),
    last_name: z.string().min(2, 'Last name is required'),
    student_id: z.string().min(3, 'Student ID is required'),
    gender: z.string().min(1, 'Gender is required'),
    current_grade_level: z.string().min(1, 'Grade level is required'),
});

const step2Schema = z.object({
    email: z.string().email('Invalid email').optional().or(z.literal('')),
    phone: z.string().optional(),
    guardian_name: z.string().optional(),
    guardian_email: z.string().email('Invalid email').optional().or(z.literal('')),
});

const validateStep = (step: number) => {
    errors.value = {};
    try {
        if (step === 1) {
            step1Schema.parse(createForm.value);
        } else if (step === 2) {
            step2Schema.parse(createForm.value);
        }
        return true;
    } catch (err) {
        if (err instanceof z.ZodError) {
            const fieldErrors: Record<string, string> = {};
            err.errors.forEach((e) => {
                if (e.path[0]) {
                    fieldErrors[e.path[0].toString()] = e.message;
                }
            });
            errors.value = fieldErrors;
        }
        return false;
    }
};

const nextStep = () => {
    if (validateStep(currentStep.value)) {
        currentStep.value++;
    }
};

const prevStep = () => {
    currentStep.value--;
};

const resetForm = () => {
    createForm.value = {
        first_name: '',
        last_name: '',
        middle_name: '',
        student_id: '',
        email: '',
        phone: '',
        gender: '',
        birth_date: '',
        current_grade_level: '',
        guardian_name: '',
        guardian_email: '',
        guardian_phone: '',
        guardian_relationship: 'Parent',
    };
    errors.value = {};
    currentStep.value = 1;
};

const createStudent = () => {
    if (!validateStep(1) || !validateStep(2)) {
        return;
    }

    isCreating.value = true;

    router.post(
        '/api/students',
        {
            ...createForm.value,
            subject_id: props.subjectId,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                showCreateModal.value = false;
                toast.success('Student Created', 'The student has been successfully enrolled.');
                resetForm();
            },
            onError: (err: any) => {
                console.error('Failed to create student', err);
                toast.error('Creation Failed', Object.values(err).flat().join(', ') || 'Could not create student.');
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
                <BulkPrintQrCodes :subject-id="subjectId" />

                <Dialog v-model:open="showCreateModal">
                    <DialogTrigger as-child>
                        <Button class="gap-2">
                            <Plus class="h-4 w-4" />
                            <span class="hidden sm:inline">Create New</span>
                            <span class="sm:hidden">New</span>
                        </Button>
                    </DialogTrigger>
                    <DialogContent class="max-h-[90vh] overflow-y-auto sm:max-w-2xl p-0 gap-0 border-none shadow-2xl">
                        <DialogHeader class="p-6 bg-primary text-primary-foreground rounded-t-lg">
                            <DialogTitle class="text-xl flex items-center gap-2">
                                <UserPlus class="h-6 w-6" />
                                Create New Student
                            </DialogTitle>
                            <DialogDescription class="text-primary-foreground/80">
                                Complete the steps below to add a new student and enroll them in this class.
                            </DialogDescription>
                        </DialogHeader>

                        <div class="p-6">
                            <!-- Stepper -->
                            <div class="mb-8 flex items-center justify-between relative px-2">
                                <div class="absolute left-0 top-1/2 h-0.5 w-full bg-muted -translate-y-1/2 z-0"></div>
                                <div 
                                    class="absolute left-0 top-1/2 h-0.5 bg-primary -translate-y-1/2 z-0 transition-all duration-300"
                                    :style="{ width: `${((currentStep - 1) / 2) * 100}%` }"
                                ></div>
                                
                                <div 
                                    v-for="step in 3" 
                                    :key="step"
                                    class="relative z-10 flex flex-col items-center gap-2"
                                >
                                    <div 
                                        :class="[
                                            'h-10 w-10 rounded-full flex items-center justify-center border-2 transition-all duration-300',
                                            currentStep >= step ? 'bg-primary border-primary text-primary-foreground shadow-md' : 'bg-background border-muted text-muted-foreground'
                                        ]"
                                    >
                                        <User v-if="step === 1" class="h-5 w-5" />
                                        <Contact v-else-if="step === 2" class="h-5 w-5" />
                                        <Check v-else class="h-5 w-5" />
                                    </div>
                                    <span :class="['text-xs font-medium', currentStep >= step ? 'text-primary' : 'text-muted-foreground']">
                                        {{ step === 1 ? 'Personal' : step === 2 ? 'Contact' : 'Review' }}
                                    </span>
                                </div>
                            </div>

                            <form @submit.prevent>
                                <Transition
                                    mode="out-in"
                                    enter-active-class="transition duration-300 ease-out"
                                    enter-from-class="translate-y-4 opacity-0"
                                    enter-to-class="translate-y-0 opacity-100"
                                    leave-active-class="transition duration-200 ease-in"
                                    leave-from-class="translate-y-0 opacity-100"
                                    leave-to-class="-translate-y-4 opacity-0"
                                >
                                    <!-- Step 1: Personal Info -->
                                    <div v-if="currentStep === 1" class="space-y-6">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <div class="space-y-2">
                                                <Label for="first_name" :class="errors.first_name ? 'text-destructive' : ''">First Name *</Label>
                                                <div class="relative">
                                                    <Input id="first_name" v-model="createForm.first_name" placeholder="John" :class="errors.first_name ? 'border-destructive' : ''" />
                                                    <AlertCircle v-if="errors.first_name" class="absolute right-3 top-2.5 h-4 w-4 text-destructive" />
                                                </div>
                                                <p v-if="errors.first_name" class="text-xs text-destructive mt-1">{{ errors.first_name }}</p>
                                            </div>
                                            <div class="space-y-2">
                                                <Label for="last_name" :class="errors.last_name ? 'text-destructive' : ''">Last Name *</Label>
                                                <div class="relative">
                                                    <Input id="last_name" v-model="createForm.last_name" placeholder="Doe" :class="errors.last_name ? 'border-destructive' : ''" />
                                                    <AlertCircle v-if="errors.last_name" class="absolute right-3 top-2.5 h-4 w-4 text-destructive" />
                                                </div>
                                                <p v-if="errors.last_name" class="text-xs text-destructive mt-1">{{ errors.last_name }}</p>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <div class="space-y-2">
                                                <Label for="student_id" :class="errors.student_id ? 'text-destructive' : ''">Student ID *</Label>
                                                <div class="relative">
                                                    <Input id="student_id" v-model="createForm.student_id" placeholder="STD-12345" :class="errors.student_id ? 'border-destructive' : ''" />
                                                    <AlertCircle v-if="errors.student_id" class="absolute right-3 top-2.5 h-4 w-4 text-destructive" />
                                                </div>
                                                <p v-if="errors.student_id" class="text-xs text-destructive mt-1">{{ errors.student_id }}</p>
                                            </div>
                                            <div class="space-y-2">
                                                <Label for="current_grade_level" :class="errors.current_grade_level ? 'text-destructive' : ''">Grade Level *</Label>
                                                <Select id="current_grade_level" v-model="createForm.current_grade_level" placeholder="Select Grade" :class="errors.current_grade_level ? 'border-destructive' : ''">
                                                    <option value="Grade 1">Grade 1</option>
                                                    <option value="Grade 2">Grade 2</option>
                                                    <option value="Grade 3">Grade 3</option>
                                                    <option value="Grade 4">Grade 4</option>
                                                    <option value="Grade 5">Grade 5</option>
                                                    <option value="Grade 6">Grade 6</option>
                                                    <option value="Grade 7">Grade 7</option>
                                                    <option value="Grade 8">Grade 8</option>
                                                    <option value="Grade 9">Grade 9</option>
                                                    <option value="Grade 10">Grade 10</option>
                                                    <option value="Grade 11">Grade 11</option>
                                                    <option value="Grade 12">Grade 12</option>
                                                </Select>
                                                <p v-if="errors.current_grade_level" class="text-xs text-destructive mt-1">{{ errors.current_grade_level }}</p>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <div class="space-y-2">
                                                <Label for="gender" :class="errors.gender ? 'text-destructive' : ''">Gender *</Label>
                                                <Select id="gender" v-model="createForm.gender" placeholder="Select Gender" :class="errors.gender ? 'border-destructive' : ''">
                                                    <option value="male">Male</option>
                                                    <option value="female">Female</option>
                                                    <option value="other">Other</option>
                                                </Select>
                                                <p v-if="errors.gender" class="text-xs text-destructive mt-1">{{ errors.gender }}</p>
                                            </div>
                                            <div class="space-y-2">
                                                <Label for="birth_date">Date of Birth</Label>
                                                <Input id="birth_date" type="date" v-model="createForm.birth_date" />
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Step 2: Contact & Guardian -->
                                    <div v-else-if="currentStep === 2" class="space-y-6">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <div class="space-y-2">
                                                <Label for="email" :class="errors.email ? 'text-destructive' : ''">Email Address</Label>
                                                <Input id="email" type="email" v-model="createForm.email" placeholder="student@example.com" :class="errors.email ? 'border-destructive' : ''" />
                                                <p v-if="errors.email" class="text-xs text-destructive mt-1">{{ errors.email }}</p>
                                            </div>
                                            <div class="space-y-2">
                                                <Label for="phone">Phone Number</Label>
                                                <Input id="phone" v-model="createForm.phone" placeholder="+1 234 567 890" />
                                            </div>
                                        </div>

                                        <Separator class="my-4" />
                                        <h3 class="text-sm font-semibold flex items-center gap-2">
                                            <Contact class="h-4 w-4" /> Guardian Information
                                        </h3>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <div class="space-y-2">
                                                <Label for="guardian_name">Guardian Name</Label>
                                                <Input id="guardian_name" v-model="createForm.guardian_name" placeholder="Full Name" />
                                            </div>
                                            <div class="space-y-2">
                                                <Label for="guardian_relationship">Relationship</Label>
                                                <Select id="guardian_relationship" v-model="createForm.guardian_relationship">
                                                    <option value="Parent">Parent</option>
                                                    <option value="Mother">Mother</option>
                                                    <option value="Father">Father</option>
                                                    <option value="Guardian">Guardian</option>
                                                    <option value="Other">Other</option>
                                                </Select>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <div class="space-y-2">
                                                <Label for="guardian_email" :class="errors.guardian_email ? 'text-destructive' : ''">Guardian Email</Label>
                                                <Input id="guardian_email" type="email" v-model="createForm.guardian_email" placeholder="parent@example.com" :class="errors.guardian_email ? 'border-destructive' : ''" />
                                                <p v-if="errors.guardian_email" class="text-xs text-destructive mt-1">{{ errors.guardian_email }}</p>
                                            </div>
                                            <div class="space-y-2">
                                                <Label for="guardian_phone">Guardian Phone</Label>
                                                <Input id="guardian_phone" v-model="createForm.guardian_phone" placeholder="+1 234 567 890" />
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Step 3: Review -->
                                    <div v-else class="space-y-6">
                                        <div class="rounded-lg bg-muted/50 p-6 space-y-4">
                                            <div class="flex items-center gap-2 text-primary mb-2">
                                                <FileText class="h-5 w-5" />
                                                <h3 class="font-bold">Summary Confirmation</h3>
                                            </div>
                                            
                                            <div class="grid grid-cols-2 gap-y-3 text-sm">
                                                <span class="text-muted-foreground">Full Name:</span>
                                                <span class="font-medium">{{ createForm.first_name }} {{ createForm.middle_name }} {{ createForm.last_name }}</span>
                                                
                                                <span class="text-muted-foreground">Student ID:</span>
                                                <span class="font-medium">{{ createForm.student_id }}</span>
                                                
                                                <span class="text-muted-foreground">Grade Level:</span>
                                                <span class="font-medium">{{ createForm.current_grade_level }}</span>
                                                
                                                <span class="text-muted-foreground">Gender:</span>
                                                <span class="font-medium capitalize">{{ createForm.gender }}</span>
                                                
                                                <span class="text-muted-foreground">Email:</span>
                                                <span class="font-medium">{{ createForm.email || 'N/A' }}</span>

                                                <Separator class="col-span-2 my-2" />

                                                <span class="text-muted-foreground">Guardian:</span>
                                                <span class="font-medium">{{ createForm.guardian_name || 'N/A' }}</span>
                                                
                                                <span class="text-muted-foreground">Relationship:</span>
                                                <span class="font-medium">{{ createForm.guardian_relationship }}</span>
                                            </div>
                                        </div>
                                        <p class="text-xs text-muted-foreground italic text-center">
                                            Please verify all information before final submission.
                                        </p>
                                    </div>
                                </Transition>
                            </form>
                        </div>

                        <DialogFooter class="p-6 bg-muted/30 rounded-b-lg flex items-center justify-between border-t">
                            <div class="flex gap-2 w-full justify-between">
                                <Button
                                    v-if="currentStep > 1"
                                    variant="outline"
                                    @click="prevStep"
                                    :disabled="isCreating"
                                    class="gap-2"
                                >
                                    <ChevronLeft class="h-4 w-4" /> Previous
                                </Button>
                                <div v-else></div>

                                <div class="flex gap-2">
                                    <Button
                                        variant="ghost"
                                        @click="showCreateModal = false"
                                        :disabled="isCreating"
                                    >
                                        Cancel
                                    </Button>
                                    
                                    <Button
                                        v-if="currentStep < 3"
                                        @click="nextStep"
                                        class="gap-2"
                                    >
                                        Next <ChevronRight class="h-4 w-4" />
                                    </Button>
                                    
                                    <Button
                                        v-else
                                        @click="createStudent"
                                        :disabled="isCreating"
                                        class="gap-2"
                                    >
                                        <Loader2 v-if="isCreating" class="h-4 w-4 animate-spin" />
                                        <Check v-else class="h-4 w-4" />
                                        Confirm & Create
                                    </Button>
                                </div>
                            </div>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>

                <AddStudentModal
                    :subject-id="subjectId"
                    @student-added="router.reload()"
                />
            </div>
        </div>

        <!-- Student List -->
        <div class="rounded-xl border bg-card shadow-sm overflow-hidden">
            <div
                v-if="students.length === 0"
                class="flex h-64 flex-col items-center justify-center gap-4 p-6 text-center"
            >
                <div class="rounded-full bg-muted p-4 shadow-inner">
                    <UserPlus class="h-8 w-8 text-muted-foreground" />
                </div>
                <div>
                    <h3 class="text-lg font-semibold">No students enrolled</h3>
                    <p class="text-sm text-muted-foreground max-w-xs mx-auto">
                        Your student roster is empty. Create a new student or add existing ones to start tracking attendance.
                    </p>
                </div>
                <Button variant="default" @click="showCreateModal = true" class="gap-2 shadow-md">
                    <Plus class="h-4 w-4" /> Create First Student
                </Button>
            </div>

            <div v-else class="divide-y divide-border/50">
                <div
                    v-for="student in students"
                    :key="student.id"
                    class="group flex items-center justify-between p-4 transition-all hover:bg-muted/30"
                >
                    <div class="flex items-center gap-4">
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-full bg-primary/10 font-bold text-primary border-2 border-primary/20 shadow-sm"
                        >
                            {{ student.name?.charAt(0).toUpperCase() || '?' }}
                        </div>
                        <div>
                            <p class="font-semibold text-foreground leading-tight">{{ student.name }}</p>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-xs font-mono bg-muted px-1.5 py-0.5 rounded text-muted-foreground">
                                    {{ student.student_code || student.id }}
                                </span>
                                <span v-if="student.current_grade_level" class="text-[10px] uppercase font-bold text-primary/70 tracking-wider">
                                    {{ student.current_grade_level }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            @click="openQrModal(student)"
                            class="gap-2 bg-background hover:bg-primary hover:text-primary-foreground transition-all duration-300 shadow-sm"
                        >
                            <QrCode class="h-4 w-4" />
                            <span class="hidden sm:inline">QR Code</span>
                        </Button>
                    </div>
                </div>
            </div>
        </div>

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
