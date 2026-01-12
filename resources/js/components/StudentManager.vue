<script setup lang="ts">
import AddStudentModal from '@/components/AddStudentModal.vue';
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
import { useToast } from '@/components/ui/toast/use-toast';
import axios from 'axios';
import { Loader2, Plus, UserPlus } from 'lucide-vue-next';
import { onMounted, ref, watch } from 'vue';

const props = defineProps<{
    subjectId: number;
}>();

const { toast } = useToast();

const students = ref<any[]>([]);
const isLoading = ref(true);
const isCreating = ref(false);
const showCreateModal = ref(false);

const createForm = ref({
    first_name: '',
    last_name: '',
    student_id: '',
    email: '',
});

const fetchEnrolledStudents = async () => {
    isLoading.value = true;
    try {
        const url = new URL('/api/attendance', window.location.origin);
        url.searchParams.append('subjectId', props.subjectId.toString());

        const response = await axios.get(url.toString());
        students.value = response.data.attendanceRecords.map((record: any) => ({
            id: record.student_id,
            name: record.name,
            student_code: record.student_code || '',
        }));
    } catch (error) {
        console.error('Failed to fetch students', error);
        toast({
            title: 'Error',
            description: 'Failed to load students.',
            variant: 'destructive',
        });
    } finally {
        isLoading.value = false;
    }
};

const createStudent = async () => {
    if (
        !createForm.value.first_name ||
        !createForm.value.last_name ||
        !createForm.value.student_id
    ) {
        toast({
            title: 'Validation Error',
            description: 'Please fill in all required fields.',
            variant: 'destructive',
        });
        return;
    }

    isCreating.value = true;
    try {
        await axios.post(route('api.students.store'), {
            ...createForm.value,
            subject_id: props.subjectId,
        });

        toast({
            title: 'Success',
            description: 'Student created and enrolled successfully.',
        });

        showCreateModal.value = false;
        createForm.value = {
            first_name: '',
            last_name: '',
            student_id: '',
            email: '',
        };

        fetchEnrolledStudents();
    } catch (error: any) {
        console.error('Failed to create student', error);
        toast({
            title: 'Error',
            description:
                error.response?.data?.message || 'Failed to create student.',
            variant: 'destructive',
        });
    } finally {
        isCreating.value = false;
    }
};

onMounted(() => {
    fetchEnrolledStudents();
});

watch(
    () => props.subjectId,
    () => {
        fetchEnrolledStudents();
    },
);
</script>

<template>
    <div class="flex flex-col gap-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold">Students</h2>
                <p class="text-sm text-muted-foreground">
                    Manage students enrolled in this class.
                </p>
            </div>

            <div class="flex gap-2">
                <!-- Create New Student Dialog -->
                <Dialog v-model:open="showCreateModal">
                    <DialogTrigger as-child>
                        <Button class="gap-2">
                            <Plus class="h-4 w-4" />
                            <span class="hidden sm:inline">Create New</span>
                            <span class="sm:hidden">New</span>
                        </Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Create New Student</DialogTitle>
                            <DialogDescription>
                                Add a new student to the system and enroll them
                                in this class.
                            </DialogDescription>
                        </DialogHeader>

                        <div class="grid gap-4 py-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="grid gap-2">
                                    <Label for="first_name">First Name</Label>
                                    <Input
                                        id="first_name"
                                        v-model="createForm.first_name"
                                        placeholder="John"
                                    />
                                </div>
                                <div class="grid gap-2">
                                    <Label for="last_name">Last Name</Label>
                                    <Input
                                        id="last_name"
                                        v-model="createForm.last_name"
                                        placeholder="Doe"
                                    />
                                </div>
                            </div>

                            <div class="grid gap-2">
                                <Label for="student_id">Student ID</Label>
                                <Input
                                    id="student_id"
                                    v-model="createForm.student_id"
                                    placeholder="STD-12345"
                                />
                            </div>

                            <div class="grid gap-2">
                                <Label for="email">Email (Optional)</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    v-model="createForm.email"
                                    placeholder="john@example.com"
                                />
                            </div>
                        </div>

                        <DialogFooter>
                            <Button
                                variant="outline"
                                @click="showCreateModal = false"
                                >Cancel</Button
                            >
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
                    @student-added="fetchEnrolledStudents"
                />
            </div>
        </div>

        <!-- Student List -->
        <div class="rounded-lg border bg-card">
            <div v-if="isLoading" class="flex h-48 items-center justify-center">
                <Loader2 class="h-8 w-8 animate-spin text-muted-foreground" />
            </div>

            <div
                v-else-if="students.length === 0"
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
                            {{ student.name.charAt(0) }}
                        </div>
                        <div>
                            <p class="font-medium">{{ student.name }}</p>
                            <p class="text-sm text-muted-foreground">
                                ID: {{ student.id }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
