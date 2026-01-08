<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import {
    Users,
    CheckCircle2,
    TrendingUp,
    Calendar,
    BookOpen,
    Clock,
} from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

const stats = [
    {
        title: 'Total Students',
        value: '120',
        icon: Users,
        color: 'text-blue-600',
        bgColor: 'bg-blue-100 dark:bg-blue-900/30',
    },
    {
        title: 'Present Today',
        value: '115',
        icon: CheckCircle2,
        color: 'text-green-600',
        bgColor: 'bg-green-100 dark:bg-green-900/30',
    },
    {
        title: 'Average Grade',
        value: '86.5',
        icon: TrendingUp,
        color: 'text-purple-600',
        bgColor: 'bg-purple-100 dark:bg-purple-900/30',
    },
    {
        title: 'Classes Today',
        value: '6',
        icon: BookOpen,
        color: 'text-orange-600',
        bgColor: 'bg-orange-100 dark:bg-orange-900/30',
    },
];

const recentClasses = [
    {
        id: 1,
        name: 'Mathematics 101',
        time: '09:00 AM',
        room: 'Room 301',
        attendance: 35,
        total: 36,
    },
    {
        id: 2,
        name: 'English Literature',
        time: '10:30 AM',
        room: 'Room 302',
        attendance: 34,
        total: 35,
    },
    {
        id: 3,
        name: 'Physics Lab',
        time: '01:00 PM',
        room: 'Lab A',
        attendance: 32,
        total: 32,
    },
    {
        id: 4,
        name: 'History Discussion',
        time: '02:30 PM',
        room: 'Room 304',
        attendance: 28,
        total: 30,
    },
];

const upcomingEvents = [
    {
        id: 1,
        title: 'Midterm Exams',
        date: 'January 15 - 20, 2024',
        type: 'Exam',
    },
    {
        id: 2,
        title: 'Parent-Teacher Conference',
        date: 'January 25, 2024',
        type: 'Meeting',
    },
    {
        id: 3,
        title: 'Science Fair',
        date: 'February 5, 2024',
        type: 'Event',
    },
    {
        id: 4,
        title: 'Field Trip to Museum',
        date: 'February 10, 2024',
        type: 'Activity',
    },
];

const weeklyAttendance = [
    { day: 'Mon', present: 115, absent: 5 },
    { day: 'Tue', present: 118, absent: 2 },
    { day: 'Wed', present: 112, absent: 8 },
    { day: 'Thu', present: 120, absent: 0 },
    { day: 'Fri', present: 110, absent: 10 },
];

const gradeDistribution = [
    { range: 'A (90-100)', count: 35, percentage: 29 },
    { range: 'B (80-89)', count: 48, percentage: 40 },
    { range: 'C (70-79)', count: 28, percentage: 23 },
    { range: 'D (60-69)', count: 7, percentage: 6 },
    { range: 'F (<60)', count: 2, percentage: 2 },
];

const subjectPerformance = [
    { subject: 'Mathematics', avgGrade: 86.5, maxGrade: 98 },
    { subject: 'English', avgGrade: 84.2, maxGrade: 95 },
    { subject: 'Science', avgGrade: 85.8, maxGrade: 97 },
    { subject: 'History', avgGrade: 83.5, maxGrade: 96 },
    { subject: 'PE', avgGrade: 88.1, maxGrade: 100 },
];
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-y-auto rounded-xl p-6"
        >
            <!-- Welcome Section -->
            <div>
                <h1 class="text-4xl font-bold mb-2">Welcome Back</h1>
                <p class="text-muted-foreground flex items-center gap-2">
                    <Calendar class="w-4 h-4" />
                    Monday, January 8, 2024
                </p>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div
                    v-for="stat in stats"
                    :key="stat.title"
                    class="rounded-lg border border-sidebar-border/70 p-6 dark:border-sidebar-border bg-card hover:shadow-lg transition-shadow"
                >
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <p class="text-sm text-muted-foreground mb-1">
                                {{ stat.title }}
                            </p>
                            <p class="text-3xl font-bold">{{ stat.value }}</p>
                        </div>
                        <div :class="[stat.bgColor, 'rounded-lg p-3']">
                            <component
                                :is="stat.icon"
                                :class="['w-6 h-6', stat.color]"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Today's Classes -->
                <div class="lg:col-span-2">
                    <div
                        class="rounded-lg border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                    >
                        <div class="flex items-center gap-2 mb-6">
                            <Clock class="w-5 h-5 text-primary" />
                            <h2 class="text-xl font-bold">Today's Classes</h2>
                        </div>

                        <div class="space-y-4">
                            <div
                                v-for="classItem in recentClasses"
                                :key="classItem.id"
                                class="flex items-center justify-between p-4 rounded-lg bg-muted/50 dark:bg-muted/20 hover:bg-muted dark:hover:bg-muted/30 transition-colors"
                            >
                                <div class="flex-1">
                                    <h3 class="font-semibold mb-1">
                                        {{ classItem.name }}
                                    </h3>
                                    <div
                                        class="flex items-center gap-4 text-sm text-muted-foreground"
                                    >
                                        <span>{{ classItem.time }}</span>
                                        <span>{{ classItem.room }}</span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-semibold">
                                        {{
                                            classItem.attendance
                                        }}/{{ classItem.total }}
                                    </div>
                                    <div class="text-xs text-muted-foreground">
                                        {{
                                            (
                                                (classItem.attendance /
                                                    classItem.total) *
                                                100
                                            ).toFixed(0)
                                        }}%
                                        Present
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Events -->
                <div>
                    <div
                        class="rounded-lg border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                    >
                        <h2 class="text-xl font-bold mb-6">Upcoming Events</h2>

                        <div class="space-y-4">
                            <div
                                v-for="event in upcomingEvents"
                                :key="event.id"
                                class="p-4 rounded-lg bg-muted/50 dark:bg-muted/20 hover:bg-muted dark:hover:bg-muted/30 transition-colors border-l-4"
                                :class="[
                                    event.type === 'Exam'
                                        ? 'border-l-red-500'
                                        : event.type === 'Meeting'
                                          ? 'border-l-blue-500'
                                          : event.type === 'Event'
                                            ? 'border-l-purple-500'
                                            : 'border-l-green-500',
                                ]"
                            >
                                <h3 class="font-semibold text-sm mb-1">
                                    {{ event.title }}
                                </h3>
                                <p class="text-xs text-muted-foreground mb-2">
                                    {{ event.date }}
                                </p>
                                <span
                                    :class="[
                                        'inline-block px-2 py-1 rounded text-xs font-medium',
                                        event.type === 'Exam'
                                            ? 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-200'
                                            : event.type === 'Meeting'
                                              ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-200'
                                              : event.type === 'Event'
                                                ? 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-200'
                                                : 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-200',
                                    ]"
                                >
                                    {{ event.type }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <a
                    href="/dashboard/seatplan"
                    class="rounded-lg border border-sidebar-border/70 p-4 dark:border-sidebar-border text-center hover:shadow-lg transition-shadow group"
                >
                    <div
                        class="w-12 h-12 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center mx-auto mb-3 group-hover:bg-blue-200 dark:group-hover:bg-blue-900/50 transition-colors"
                    >
                        <Users class="w-6 h-6 text-blue-600" />
                    </div>
                    <p class="font-semibold text-sm">View Seatplan</p>
                </a>

                <a
                    href="/dashboard/attendance"
                    class="rounded-lg border border-sidebar-border/70 p-4 dark:border-sidebar-border text-center hover:shadow-lg transition-shadow group"
                >
                    <div
                        class="w-12 h-12 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center mx-auto mb-3 group-hover:bg-green-200 dark:group-hover:bg-green-900/50 transition-colors"
                    >
                        <CheckCircle2 class="w-6 h-6 text-green-600" />
                    </div>
                    <p class="font-semibold text-sm">Check Attendance</p>
                </a>

                <a
                    href="/dashboard/grades"
                    class="rounded-lg border border-sidebar-border/70 p-4 dark:border-sidebar-border text-center hover:shadow-lg transition-shadow group"
                >
                    <div
                        class="w-12 h-12 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center mx-auto mb-3 group-hover:bg-purple-200 dark:group-hover:bg-purple-900/50 transition-colors"
                    >
                        <TrendingUp class="w-6 h-6 text-purple-600" />
                    </div>
                    <p class="font-semibold text-sm">View Grades</p>
                </a>

                <a
                    href="/dashboard"
                    class="rounded-lg border border-sidebar-border/70 p-4 dark:border-sidebar-border text-center hover:shadow-lg transition-shadow group"
                >
                    <div
                        class="w-12 h-12 rounded-lg bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center mx-auto mb-3 group-hover:bg-orange-200 dark:group-hover:bg-orange-900/50 transition-colors"
                    >
                        <Calendar class="w-6 h-6 text-orange-600" />
                    </div>
                    <p class="font-semibold text-sm">Schedule</p>
                </a>
            </div>

            <!-- Analytics Section -->
            <div>
                <h2 class="text-2xl font-bold mb-4">Analytics</h2>
            </div>

            <!-- Weekly Attendance Chart -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div
                    class="rounded-lg border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                >
                    <h3 class="text-lg font-bold mb-6">Weekly Attendance Trend</h3>

                    <div class="space-y-4">
                        <div
                            v-for="week in weeklyAttendance"
                            :key="week.day"
                            class="flex items-end gap-4"
                        >
                            <div class="w-12 font-semibold text-sm">
                                {{ week.day }}
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <div class="flex-1 h-6 bg-green-500 rounded transition-all hover:bg-green-600"
                                        :style="{ width: (week.present / 120) * 100 + '%' }"
                                    ></div>
                                    <span class="text-xs font-semibold w-8">
                                        {{ week.present }}
                                    </span>
                                </div>
                                <div
                                    v-if="week.absent > 0"
                                    class="flex items-center gap-2"
                                >
                                    <div class="flex-1 h-2 bg-red-500 rounded"
                                        :style="{ width: (week.absent / 120) * 100 + '%' }"
                                    ></div>
                                    <span class="text-xs text-muted-foreground w-8">
                                        {{ week.absent }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-6 mt-6 pt-6 border-t border-sidebar-border/70 dark:border-sidebar-border">
                        <div>
                            <p class="text-xs text-muted-foreground mb-1">
                                Avg Present
                            </p>
                            <p class="text-lg font-bold text-green-600">
                                {{
                                    (
                                        weeklyAttendance.reduce(
                                            (sum, w) => sum + w.present,
                                            0
                                        ) / weeklyAttendance.length
                                    ).toFixed(0)
                                }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground mb-1">
                                Avg Absent
                            </p>
                            <p class="text-lg font-bold text-red-600">
                                {{
                                    (
                                        weeklyAttendance.reduce(
                                            (sum, w) => sum + w.absent,
                                            0
                                        ) / weeklyAttendance.length
                                    ).toFixed(1)
                                }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Grade Distribution -->
                <div
                    class="rounded-lg border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                >
                    <h3 class="text-lg font-bold mb-6">Grade Distribution</h3>

                    <div class="space-y-4">
                        <div
                            v-for="grade in gradeDistribution"
                            :key="grade.range"
                            class="flex items-end gap-4"
                        >
                            <div class="w-20 text-sm font-semibold">
                                {{ grade.range }}
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-3">
                                    <div class="flex-1 h-8 bg-gradient-to-r from-blue-400 to-blue-600 rounded transition-all hover:shadow-lg"
                                        :style="{ width: (grade.percentage * 2.5) + '%' }"
                                    ></div>
                                    <span class="text-sm font-semibold w-12 text-right">
                                        {{ grade.count }}
                                    </span>
                                    <span
                                        class="text-xs text-muted-foreground w-10 text-right"
                                    >
                                        {{ grade.percentage }}%
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 pt-6 border-t border-sidebar-border/70 dark:border-sidebar-border">
                        <p class="text-sm text-muted-foreground mb-2">
                            Total Students
                        </p>
                        <p class="text-2xl font-bold">
                            {{
                                gradeDistribution.reduce((sum, g) => sum + g.count, 0)
                            }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Subject Performance -->
            <div
                class="rounded-lg border border-sidebar-border/70 p-6 dark:border-sidebar-border"
            >
                <h3 class="text-lg font-bold mb-6">Subject Performance</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
                    <div
                        v-for="subject in subjectPerformance"
                        :key="subject.subject"
                        class="text-center"
                    >
                        <div
                            class="relative w-24 h-24 rounded-full mx-auto mb-4 flex items-center justify-center"
                            :style="{
                                background: `conic-gradient(
                                    from 0deg,
                                    rgb(59, 130, 246) 0deg,
                                    rgb(59, 130, 246) ${
                                        (subject.avgGrade / 100) * 360
                                    }deg,
                                    rgb(229, 231, 235) ${
                                        (subject.avgGrade / 100) * 360
                                    }deg
                                )`,
                            }"
                        >
                            <div
                                class="w-20 h-20 rounded-full bg-card flex items-center justify-center"
                            >
                                <span class="text-sm font-bold">
                                    {{ subject.avgGrade }}
                                </span>
                            </div>
                        </div>
                        <h4 class="font-semibold text-sm mb-2">
                            {{ subject.subject }}
                        </h4>
                        <p class="text-xs text-muted-foreground">
                            Max: {{ subject.maxGrade }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
