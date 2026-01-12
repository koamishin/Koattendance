<script setup lang="ts">
import AttendanceManager from '@/components/AttendanceManager.vue';
import SeatPlanManager from '@/components/SeatPlanManager.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { BookOpen, Calendar, LayoutDashboard, Users } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';

const props = defineProps<{
    subjectId: string;
}>();

const subject = ref<any>(null);
const activeTab = ref('overview');

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'My Classes',
        href: '/dashboard/subjects',
    },
    {
        title: subject.value?.name || 'Class Details',
        href: `/dashboard/subjects/${props.subjectId}`,
    },
]);

const fetchSubject = async () => {
    try {
        const response = await axios.get(`/api/subjects/${props.subjectId}`);
        subject.value = response.data;
    } catch (error) {
        console.error('Failed to load subject', error);
    }
};

onMounted(() => {
    fetchSubject();
});

const tabs = [
    { id: 'overview', label: 'Overview', icon: BookOpen },
    { id: 'attendance', label: 'Attendance', icon: Calendar },
    { id: 'seatplan', label: 'Seat Plan', icon: LayoutDashboard },
    { id: 'students', label: 'Students', icon: Users },
];
</script>

<template>
    <Head :title="subject?.name || 'Class Details'" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div v-if="subject" class="flex h-full flex-col">
            <!-- Class Header -->
            <div class="border-b bg-card px-6 py-4">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold">{{ subject.name }}</h1>
                        <p class="text-muted-foreground">
                            {{ subject.description }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <!-- Actions like Edit Class -->
                    </div>
                </div>

                <!-- Tabs Navigation -->
                <div class="flex items-center gap-1">
                    <button
                        v-for="tab in tabs"
                        :key="tab.id"
                        @click="activeTab = tab.id"
                        :class="[
                            'flex items-center gap-2 rounded-t-lg border-b-2 px-4 py-2 text-sm font-medium transition-colors',
                            activeTab === tab.id
                                ? 'border-primary bg-primary/5 text-primary'
                                : 'border-transparent text-muted-foreground hover:bg-muted/50 hover:text-foreground',
                        ]"
                    >
                        <component :is="tab.icon" class="h-4 w-4" />
                        {{ tab.label }}
                    </button>
                </div>
            </div>

            <!-- Tab Content -->
            <div class="flex-1 overflow-auto bg-muted/10 p-6">
                <!-- Overview Tab -->
                <div v-if="activeTab === 'overview'" class="space-y-6">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                        <div class="rounded-xl border bg-card p-6 shadow-sm">
                            <h3 class="mb-2 text-lg font-semibold">
                                Total Students
                            </h3>
                            <p class="text-3xl font-bold">
                                {{ subject.students_count || 0 }}
                            </p>
                        </div>
                        <!-- Add more stats here -->
                    </div>
                </div>

                <!-- Attendance Tab -->
                <div v-if="activeTab === 'attendance'">
                    <div class="rounded-xl border bg-card p-6 shadow-sm">
                        <AttendanceManager :subject-id="parseInt(subjectId)" />
                    </div>
                </div>

                <!-- Seatplan Tab -->
                <div v-if="activeTab === 'seatplan'">
                    <div class="rounded-xl border bg-card p-6 shadow-sm">
                        <SeatPlanManager :subject-id="parseInt(subjectId)" />
                    </div>
                </div>

                <!-- Students Tab -->
                <div v-if="activeTab === 'students'">
                    <div class="rounded-xl border bg-card p-6 shadow-sm">
                        <h3 class="mb-4 text-lg font-semibold">
                            Enrolled Students
                        </h3>
                        <!-- List students here -->
                        <p class="text-muted-foreground">
                            Student list management coming soon.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="flex h-full items-center justify-center">
            <div
                class="h-8 w-8 animate-spin rounded-full border-b-2 border-primary"
            ></div>
        </div>
    </AppLayout>
</template>
