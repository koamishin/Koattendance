<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { TrendingUp, BookOpen } from 'lucide-vue-next';

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

const gradeRecords = [
    {
        id: 1,
        name: 'John Smith',
        mathematics: 85,
        english: 88,
        science: 92,
        history: 79,
        average: 86,
    },
    {
        id: 2,
        name: 'Sarah Johnson',
        mathematics: 92,
        english: 95,
        science: 88,
        history: 91,
        average: 91.5,
    },
    {
        id: 3,
        name: 'Mike Davis',
        mathematics: 78,
        english: 81,
        science: 75,
        history: 82,
        average: 79,
    },
    {
        id: 4,
        name: 'Emily Brown',
        mathematics: 88,
        english: 89,
        science: 91,
        history: 87,
        average: 88.75,
    },
    {
        id: 5,
        name: 'Alex Wilson',
        mathematics: 91,
        english: 87,
        science: 89,
        history: 93,
        average: 90,
    },
    {
        id: 6,
        name: 'Jessica Lee',
        mathematics: 87,
        english: 90,
        science: 86,
        history: 88,
        average: 87.75,
    },
];

const getGradeColor = (grade: number) => {
    if (grade >= 90) return 'text-green-600 font-bold';
    if (grade >= 80) return 'text-blue-600 font-bold';
    if (grade >= 70) return 'text-yellow-600 font-bold';
    return 'text-red-600 font-bold';
};

const classStats = {
    averageGrade: (
        gradeRecords.reduce((sum, r) => sum + r.average, 0) / gradeRecords.length
    ).toFixed(1),
    highestGrade: Math.max(...gradeRecords.map((r) => r.average)),
    lowestGrade: Math.min(...gradeRecords.map((r) => r.average)),
    passRate: (
        (gradeRecords.filter((r) => r.average >= 70).length /
            gradeRecords.length) *
        100
    ).toFixed(0),
};
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
            <div
                class="rounded-lg border border-sidebar-border/70 dark:border-sidebar-border overflow-hidden"
            >
                <table class="w-full">
                    <thead class="bg-muted/50 dark:bg-muted/20 border-b border-sidebar-border/70 dark:border-sidebar-border">
                        <tr>
                            <th class="text-left px-6 py-3 font-semibold text-sm">
                                Student Name
                            </th>
                            <th class="text-center px-4 py-3 font-semibold text-sm">
                                Math
                            </th>
                            <th class="text-center px-4 py-3 font-semibold text-sm">
                                English
                            </th>
                            <th class="text-center px-4 py-3 font-semibold text-sm">
                                Science
                            </th>
                            <th class="text-center px-4 py-3 font-semibold text-sm">
                                History
                            </th>
                            <th class="text-center px-6 py-3 font-semibold text-sm">
                                Average
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="record in gradeRecords"
                            :key="record.id"
                            class="border-b border-sidebar-border/70 dark:border-sidebar-border hover:bg-muted/50 dark:hover:bg-muted/20 transition-colors"
                        >
                            <td class="px-6 py-4">
                                <span class="font-medium">{{ record.name }}</span>
                            </td>
                            <td class="text-center px-4 py-4">
                                <span :class="getGradeColor(record.mathematics)">
                                    {{ record.mathematics }}
                                </span>
                            </td>
                            <td class="text-center px-4 py-4">
                                <span :class="getGradeColor(record.english)">
                                    {{ record.english }}
                                </span>
                            </td>
                            <td class="text-center px-4 py-4">
                                <span :class="getGradeColor(record.science)">
                                    {{ record.science }}
                                </span>
                            </td>
                            <td class="text-center px-4 py-4">
                                <span :class="getGradeColor(record.history)">
                                    {{ record.history }}
                                </span>
                            </td>
                            <td class="text-center px-6 py-4">
                                <div
                                    class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-primary/10"
                                >
                                    <TrendingUp class="w-4 h-4 text-primary" />
                                    <span
                                        :class="getGradeColor(record.average)"
                                    >
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
