<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import axios from 'axios';
import {
    BookOpen,
    Edit,
    MoreVertical,
    Plus,
    Trash2,
    Users,
} from 'lucide-vue-next';
import { onMounted, ref } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'My Classes',
        href: '/dashboard/subjects',
    },
];

const subjects = ref<any[]>([]);
const isLoading = ref(true);
const isCreating = ref(false);
const showCreateModal = ref(false);

const newSubject = ref({
    name: '',
    description: '',
});

const fetchSubjects = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get('/api/subjects');
        subjects.value = response.data;
    } catch (error) {
        console.error('Failed to load subjects', error);
    } finally {
        isLoading.value = false;
    }
};

const createSubject = async () => {
    isCreating.value = true;
    try {
        await axios.post('/api/subjects', newSubject.value);
        showCreateModal.value = false;
        newSubject.value = { name: '', description: '' };
        fetchSubjects();
    } catch (error) {
        console.error('Failed to create subject', error);
    } finally {
        isCreating.value = false;
    }
};

const deleteSubject = async (id: number) => {
    if (
        !confirm(
            'Are you sure? This will delete all attendance records and seat plans for this class.',
        )
    )
        return;

    try {
        await axios.delete(`/api/subjects/${id}`);
        fetchSubjects();
    } catch (error) {
        console.error('Failed to delete subject', error);
    }
};

onMounted(() => {
    fetchSubjects();
});
</script>

<template>
    <Head title="My Classes" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold">My Classes</h1>
                    <p class="text-muted-foreground">
                        Manage your subjects, students, and attendance.
                    </p>
                </div>

                <Dialog v-model:open="showCreateModal">
                    <DialogTrigger as-child>
                        <Button class="gap-2">
                            <Plus class="h-4 w-4" />
                            Create Class
                        </Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Create New Class</DialogTitle>
                        </DialogHeader>
                        <div class="space-y-4 py-4">
                            <div class="space-y-2">
                                <Label>Class Name</Label>
                                <Input
                                    v-model="newSubject.name"
                                    placeholder="e.g. Mathematics 101"
                                />
                            </div>
                            <div class="space-y-2">
                                <Label>Description (Optional)</Label>
                                <Textarea
                                    v-model="newSubject.description"
                                    placeholder="Class description..."
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
                                @click="createSubject"
                                :disabled="isCreating"
                            >
                                {{
                                    isCreating ? 'Creating...' : 'Create Class'
                                }}
                            </Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
            </div>

            <div
                v-if="isLoading"
                class="flex items-center justify-center py-12"
            >
                <div
                    class="h-8 w-8 animate-spin rounded-full border-b-2 border-primary"
                ></div>
            </div>

            <div
                v-else-if="subjects.length === 0"
                class="flex flex-col items-center justify-center rounded-lg border border-dashed border-gray-300 py-12 text-center dark:border-gray-700"
            >
                <div class="mb-4 rounded-full bg-muted/50 p-4">
                    <BookOpen class="h-8 w-8 text-muted-foreground" />
                </div>
                <h3 class="text-lg font-semibold">No Classes Yet</h3>
                <p class="mb-4 max-w-sm text-muted-foreground">
                    Get started by creating your first class subject.
                </p>
                <Button @click="showCreateModal = true">Create Class</Button>
            </div>

            <div
                v-else
                class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3"
            >
                <Link
                    v-for="subject in subjects"
                    :key="subject.id"
                    :href="`/dashboard/subjects/${subject.id}`"
                    class="group relative block rounded-xl border bg-card text-card-foreground shadow transition-all hover:border-primary/50 hover:shadow-lg"
                >
                    <div class="p-6">
                        <div class="mb-4 flex items-start justify-between">
                            <div
                                class="rounded-lg bg-primary/10 p-3 text-primary transition-colors group-hover:bg-primary group-hover:text-primary-foreground"
                            >
                                <BookOpen class="h-6 w-6" />
                            </div>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="h-8 w-8"
                                        @click.prevent
                                    >
                                        <MoreVertical class="h-4 w-4" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end">
                                    <DropdownMenuItem @click.prevent>
                                        <Edit class="mr-2 h-4 w-4" />
                                        Edit
                                    </DropdownMenuItem>
                                    <DropdownMenuItem
                                        class="text-red-600"
                                        @click.prevent="
                                            deleteSubject(subject.id)
                                        "
                                    >
                                        <Trash2 class="mr-2 h-4 w-4" />
                                        Delete
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>

                        <h3
                            class="mb-2 text-xl font-semibold transition-colors group-hover:text-primary"
                        >
                            {{ subject.name }}
                        </h3>
                        <p
                            class="mb-4 line-clamp-2 min-h-[2.5rem] text-sm text-muted-foreground"
                        >
                            {{
                                subject.description ||
                                'No description provided.'
                            }}
                        </p>

                        <div
                            class="flex items-center gap-4 border-t pt-4 text-sm text-muted-foreground"
                        >
                            <div class="flex items-center gap-1">
                                <Users class="h-4 w-4" />
                                {{ subject.students_count || 0 }} Students
                            </div>
                        </div>
                    </div>
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
