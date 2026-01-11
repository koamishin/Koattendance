<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { TrendingUp, BookOpen } from 'lucide-vue-next';
import { ref, computed, onMounted } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Grades',
        href: '/dashboard/grades',
    },
];

const gradeRecords = ref<any[]>([]);
const subjects = ref<string[]>([]);

onMounted(async () => {
    try {
        const response = await fetch('/api/grades');
        const data = await response.json();
        gradeRecords.value = data.gradeRecords;
        subjects.value = data.subjects;
    } catch (error) {
        console.error('Error fetching grades:', error);
    }
});

const getGradeColor = (grade: number | null) => {
    if (grade === null) return 'text-gray-400';
    if (grade >= 90) return 'text-green-600 font-bold';
    if (grade >= 80) return 'text-blue-600 font-bold';
    if (grade >= 70) return 'text-yellow-600 font-bold';
    return 'text-red-600 font-bold';
};

const classStats = computed(() => {
    if (gradeRecords.value.length === 0) {
        return {
            averageGrade: '0',
            highestGrade: 0,
            lowestGrade: 0,
            passRate: '0',
        };
    }

    return {
        averageGrade: (
            gradeRecords.value.reduce((sum, r) => sum + r.average, 0) / gradeRecords.value.length
        ).toFixed(1),
        highestGrade: Math.max(...gradeRecords.value.map((r) => r.average)),
        lowestGrade: Math.min(...gradeRecords.value.map((r) => r.average)),
        passRate: (
            (gradeRecords.value.filter((r) => r.average >= 70).length /
                gradeRecords.value.length) *
            100
        ).toFixed(0),
    };
});
</script>

<template>
    <Head title="Grades" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <div>
                <h1 class="text-3xl font-bold mb-2">Grade Records</h1>
                <div class="flex items-center gap-2 text-muted-foreground">
                    <BookOpen class="w-4 h-4" />
                    <span>All Subjects - Current Term</span>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div
                    class="rounded-lg border border-sidebar-border/70 p-4 dark:border-sidebar-border bg-card"
                >
                    <p class="text-sm text-muted-foreground mb-2">Class Average</p>
                    <p class="text-2xl font-bold text-blue-600">
                        {{ classStats.averageGrade }}
                    </p>
                </div>
                <div
                    class="rounded-lg border border-sidebar-border/70 p-4 dark:border-sidebar-border bg-card"
                >
                    <p class="text-sm text-muted-foreground mb-2">
                        Highest Grade
                    </p>
                    <p class="text-2xl font-bold text-green-600">
                        {{ classStats.highestGrade }}
                    </p>
                </div>
                <div
                    class="rounded-lg border border-sidebar-border/70 p-4 dark:border-sidebar-border bg-card"
                >
                    <p class="text-sm text-muted-foreground mb-2">Lowest Grade</p>
                    <p class="text-2xl font-bold text-red-600">
                        {{ classStats.lowestGrade }}
                    </p>
                </div>
                <div
                    class="rounded-lg border border-sidebar-border/70 p-4 dark:border-sidebar-border bg-card"
                >
                    <p class="text-sm text-muted-foreground mb-2">Pass Rate</p>
                    <p class="text-2xl font-bold text-green-600">
                        {{ classStats.passRate }}%
                    </p>
                </div>
            </div>

            <!-- Grades Table -->
            <div v-if="gradeRecords.length === 0" class="rounded-lg border border-sidebar-border/70 dark:border-sidebar-border p-12">
                <div class="flex flex-col items-center justify-center text-center">
                    <BookOpen class="w-16 h-16 text-muted-foreground/30 mb-4" />
                    <h3 class="text-lg font-semibold text-muted-foreground mb-2">
                        No Grades Yet
                    </h3>
                    <p class="text-sm text-muted-foreground max-w-md">
                        Grades will appear here once the admin adds subjects and grades for you.
                    </p>
                </div>
            </div>
            <div
                v-else
                class="rounded-lg border border-sidebar-border/70 dark:border-sidebar-border overflow-hidden"
            >
                <table class="w-full">
                    <thead class="bg-muted/50 dark:bg-muted/20 border-b border-sidebar-border/70 dark:border-sidebar-border">
                        <tr>
                            <th class="text-left px-6 py-3 font-semibold text-sm">
                                Student Name
                            </th>
                            <th
                                v-for="subject in subjects"
                                :key="subject"
                                class="text-center px-4 py-3 font-semibold text-sm capitalize"
                            >
                                {{ subject.replace(/_/g, ' ') }}
                            </th>
                            <th class="text-center px-6 py-3 font-semibold text-sm">
                                Average
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(record, index) in gradeRecords"
                            :key="index"
                            class="border-b border-sidebar-border/70 dark:border-sidebar-border hover:bg-muted/50 dark:hover:bg-muted/20 transition-colors"
                        >
                            <td class="px-6 py-4">
                                <span class="font-medium">{{ record.name }}</span>
                            </td>
                            <td
                                v-for="subject in subjects"
                                :key="subject"
                                class="text-center px-4 py-4"
                            >
                                <span
                                    v-if="record[subject] !== null"
                                    :class="getGradeColor(record[subject])"
                                >
                                    {{ record[subject] }}
                                </span>
                                <span v-else class="text-gray-400">—</span>
                            </td>
                            <td class="text-center px-6 py-4">
                                <div
                                    class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-primary/10"
                                >
                                    <TrendingUp class="w-4 h-4 text-primary" />
                                    <span :class="getGradeColor(record.average)">
                                        {{ record.average }}
                                    </span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
