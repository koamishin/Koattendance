<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { Loader2, Plus, UserPlus } from 'lucide-vue-next';
import { ref, watch } from 'vue';

const props = defineProps<{
    subjectId: number;
}>();

const emit = defineEmits(['student-added']);

const isOpen = ref(false);
const searchQuery = ref('');
const searchResults = ref<any[]>([]);
const isSearching = ref(false);
const isAdding = ref(false);

const searchStudents = async () => {
    if (searchQuery.value.length < 2) {
        searchResults.value = [];
        return;
    }

    isSearching.value = true;
    try {
        const response = await axios.get(
            route('api.attendance.search-students'),
            {
                params: {
                    query: searchQuery.value,
                    subject_id: props.subjectId,
                },
            },
        );
        searchResults.value = response.data;
    } catch (error) {
        console.error('Search failed', error);
    } finally {
        isSearching.value = false;
    }
};

let debounceTimer: ReturnType<typeof setTimeout>;
watch(searchQuery, () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(searchStudents, 300);
});

const addStudent = (studentId: number) => {
    isAdding.value = true;
    router.post(
        route('api.attendance.enroll'),
        {
            subject_id: props.subjectId,
            student_id: studentId,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                isOpen.value = false;
                searchQuery.value = '';
                searchResults.value = [];
                emit('student-added');
            },
            onFinish: () => {
                isAdding.value = false;
            },
        },
    );
};
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogTrigger as-child>
            <Button variant="outline" class="gap-2">
                <UserPlus class="h-4 w-4" />
                Add Student
            </Button>
        </DialogTrigger>
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Add Student to Class</DialogTitle>
            </DialogHeader>

            <div class="flex flex-col gap-4 py-4">
                <div class="relative">
                    <Input
                        v-model="searchQuery"
                        placeholder="Search by name or ID..."
                        class="pr-10"
                    />
                    <div v-if="isSearching" class="absolute top-2.5 right-3">
                        <Loader2
                            class="h-4 w-4 animate-spin text-muted-foreground"
                        />
                    </div>
                </div>

                <div class="max-h-[300px] space-y-2 overflow-y-auto">
                    <div
                        v-if="
                            searchQuery.length >= 2 &&
                            searchResults.length === 0 &&
                            !isSearching
                        "
                        class="py-4 text-center text-sm text-muted-foreground"
                    >
                        No students found.
                    </div>

                    <div
                        v-for="student in searchResults"
                        :key="student.id"
                        class="flex items-center justify-between rounded-lg border p-3 transition-colors hover:bg-muted/50"
                    >
                        <div>
                            <p class="font-medium">{{ student.name }}</p>
                            <p class="text-xs text-muted-foreground">
                                {{ student.student_id }}
                            </p>
                        </div>
                        <Button
                            size="sm"
                            @click="addStudent(student.id)"
                            :disabled="isAdding"
                        >
                            <Plus class="mr-1 h-4 w-4" />
                            Add
                        </Button>
                    </div>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>
