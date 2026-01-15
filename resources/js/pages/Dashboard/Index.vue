<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import {
    AlertCircle,
    Calendar,
    CheckCircle2,
    Clock,
    TrendingUp,
    Users,
} from 'lucide-vue-next';
import { onMounted, onUnmounted, ref } from 'vue';

const page = usePage();
const user = page.props.auth.user;
const roles = page.props.auth.roles;

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

const stats = ref<any[]>([]);
const recentRecords = ref<any[]>([]);
const topGrades = ref<any[]>([]);
const gradeDistribution = ref<Record<string, number>>({});
const latestDate = ref<string | null>(null);
const isLoading = ref(true);

const weeklyAttendance = ref<any[]>([]);
const subjectPerformance = ref<any[]>([]);

let pollingInterval: ReturnType<typeof setInterval> | null = null;

const loadDashboardData = async () => {
    try {
        const [statsRes, attendanceRes, gradeRes, weeklyRes, subjectRes] =
            await Promise.all([
                fetch('/api/dashboard/stats'),
                fetch('/api/dashboard/attendance-summary'),
                fetch('/api/dashboard/grade-summary'),
                fetch('/api/dashboard/weekly-attendance'),
                fetch('/api/dashboard/subject-performance'),
            ]);

        const statsData = await statsRes.json();
        const attendanceData = await attendanceRes.json();
        const gradeData = await gradeRes.json();
        const weeklyData = await weeklyRes.json();
        const subjectData = await subjectRes.json();

        stats.value = statsData.stats;
        latestDate.value = statsData.latestDate;
        recentRecords.value = attendanceData.recentRecords;
        topGrades.value = gradeData.topGrades;
        gradeDistribution.value = gradeData.gradeDistribution;
        weeklyAttendance.value = weeklyData.weeklyAttendance;
        subjectPerformance.value = subjectData.subjectPerformance;
    } catch (error) {
        console.error('Error fetching dashboard data:', error);
    } finally {
        isLoading.value = false;
    }
};

onMounted(async () => {
    // Load data immediately
    await loadDashboardData();

    // Set up polling to refresh every 30 seconds
    pollingInterval = setInterval(() => {
        loadDashboardData();
    }, 30000); // 30 seconds
});

onUnmounted(() => {
    // Clean up polling interval when component is destroyed
    if (pollingInterval) {
        clearInterval(pollingInterval);
    }
});

const getIconComponent = (iconName: string) => {
    const icons: Record<string, any> = {
        Users,
        CheckCircle2,
        TrendingUp,
        AlertCircle,
        Clock,
    };
    return icons[iconName] || Users;
};

const getGradeDistributionList = () => {
    return Object.entries(gradeDistribution.value).map(([range, count]) => ({
        range,
        count,
        percentage:
            Math.round(
                (count /
                    Object.values(gradeDistribution.value).reduce(
                        (a, b) => a + b,
                        0,
                    )) *
                    100,
            ) || 0,
    }));
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-y-auto rounded-xl p-6"
        >
            <!-- Welcome Section -->
            <div>
                <h1 class="mb-2 text-4xl font-bold">Welcome Back</h1>
                <p class="flex items-center gap-2 text-muted-foreground">
                    <Calendar class="h-4 w-4" />
                    {{ latestDate || 'Loading...' }}
                </p>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                <div
                    v-for="stat in stats"
                    :key="stat.title"
                    class="rounded-lg border border-sidebar-border/70 bg-card p-6 transition-shadow hover:shadow-lg dark:border-sidebar-border"
                >
                    <div class="mb-4 flex items-start justify-between">
                        <div>
                            <p class="mb-1 text-sm text-muted-foreground">
                                {{ stat.title }}
                            </p>
                            <p class="text-3xl font-bold">{{ stat.value }}</p>
                            <p
                                v-if="stat.subtitle"
                                class="mt-1 text-xs text-muted-foreground"
                            >
                                {{ stat.subtitle }}
                            </p>
                        </div>
                        <div :class="[stat.bgColor, 'rounded-lg p-3']">
                            <component
                                :is="getIconComponent(stat.icon)"
                                :class="['h-6 w-6', stat.color]"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Recent Attendance -->
                <div class="lg:col-span-2">
                    <div
                        class="rounded-lg border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                    >
                        <div class="mb-6 flex items-center gap-2">
                            <CheckCircle2 class="h-5 w-5 text-primary" />
                            <h2 class="text-xl font-bold">Recent Attendance</h2>
                        </div>

                        <div
                            v-if="recentRecords.length === 0"
                            class="py-8 text-center text-muted-foreground"
                        >
                            No attendance records found
                        </div>

                        <div
                            v-else
                            class="max-h-96 space-y-4 overflow-y-auto pr-2"
                        >
                            <div
                                v-for="record in recentRecords"
                                :key="record.id"
                                class="flex items-center justify-between rounded-lg bg-muted/50 p-4 transition-colors hover:bg-muted dark:bg-muted/20 dark:hover:bg-muted/30"
                            >
                                <div class="flex-1">
                                    <h3 class="mb-1 font-semibold">
                                        {{ record.name }}
                                    </h3>
                                    <div
                                        class="flex flex-col gap-2 text-sm text-muted-foreground"
                                    >
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="rounded bg-blue-100 px-2 py-1 text-xs text-blue-700 dark:bg-blue-900/30 dark:text-blue-200"
                                            >
                                                {{ record.subject }}
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-4">
                                            <span>{{ record.time }}</span>
                                            <span
                                                :class="[
                                                    'inline-block rounded px-2 py-1 text-xs font-medium',
                                                    record.status === 'present'
                                                        ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-200'
                                                        : record.status ===
                                                            'late'
                                                          ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-200'
                                                          : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-200',
                                                ]"
                                            >
                                                {{
                                                    record.status
                                                        .charAt(0)
                                                        .toUpperCase() +
                                                    record.status.slice(1)
                                                }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Grades -->
                <div>
                    <div
                        class="rounded-lg border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                    >
                        <h2 class="mb-6 text-xl font-bold">
                            Top Performing Students
                        </h2>

                        <div
                            v-if="topGrades.length === 0"
                            class="py-8 text-center text-muted-foreground"
                        >
                            No grade records found
                        </div>

                        <div
                            v-else
                            class="max-h-96 space-y-4 overflow-y-auto pr-2"
                        >
                            <div
                                v-for="(grade, idx) in topGrades"
                                :key="idx"
                                class="rounded-lg border-l-4 border-l-blue-500 bg-muted/50 p-4 transition-colors hover:bg-muted dark:bg-muted/20 dark:hover:bg-muted/30"
                            >
                                <h3 class="mb-1 text-sm font-semibold">
                                    {{ grade.name }}
                                </h3>
                                <p class="mb-2 text-xs text-muted-foreground">
                                    {{ grade.subject }}
                                </p>
                                <span
                                    class="inline-block rounded bg-blue-100 px-2 py-1 text-xs font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-200"
                                >
                                    {{ grade.grade }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                <!-- Student QR Code Action -->
                <Dialog v-if="roles?.isStudent && user?.student">
                    <DialogTrigger as-child>
                        <button
                            class="group rounded-lg border border-sidebar-border/70 bg-card p-4 text-center transition-shadow hover:shadow-lg dark:border-sidebar-border"
                        >
                            <div
                                class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-100 transition-colors group-hover:bg-indigo-200 dark:bg-indigo-900/30 dark:group-hover:bg-indigo-900/50"
                            >
                                <QrCode class="h-6 w-6 text-indigo-600" />
                            </div>
                            <p class="text-sm font-semibold">My QR Code</p>
                        </button>
                    </DialogTrigger>
                    <DialogContent class="sm:max-w-md">
                        <StudentQrCode :student-id="user.student.id" />
                    </DialogContent>
                </Dialog>

                <a
                    href="/dashboard/seatplan"
                    class="group rounded-lg border border-sidebar-border/70 p-4 text-center transition-shadow hover:shadow-lg dark:border-sidebar-border"
                >
                    <div
                        class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100 transition-colors group-hover:bg-blue-200 dark:bg-blue-900/30 dark:group-hover:bg-blue-900/50"
                    >
                        <Users class="h-6 w-6 text-blue-600" />
                    </div>
                    <p class="text-sm font-semibold">View Seatplan</p>
                </a>

                <a
                    href="/dashboard/attendance"
                    class="group rounded-lg border border-sidebar-border/70 p-4 text-center transition-shadow hover:shadow-lg dark:border-sidebar-border"
                >
                    <div
                        class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-lg bg-green-100 transition-colors group-hover:bg-green-200 dark:bg-green-900/30 dark:group-hover:bg-green-900/50"
                    >
                        <CheckCircle2 class="h-6 w-6 text-green-600" />
                    </div>
                    <p class="text-sm font-semibold">Check Attendance</p>
                </a>

                <a
                    href="/dashboard/grades"
                    class="group rounded-lg border border-sidebar-border/70 p-4 text-center transition-shadow hover:shadow-lg dark:border-sidebar-border"
                >
                    <div
                        class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-lg bg-purple-100 transition-colors group-hover:bg-purple-200 dark:bg-purple-900/30 dark:group-hover:bg-purple-900/50"
                    >
                        <TrendingUp class="h-6 w-6 text-purple-600" />
                    </div>
                    <p class="text-sm font-semibold">View Grades</p>
                </a>

                <a
                    href="/dashboard"
                    class="group rounded-lg border border-sidebar-border/70 p-4 text-center transition-shadow hover:shadow-lg dark:border-sidebar-border"
                >
                    <div
                        class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-lg bg-orange-100 transition-colors group-hover:bg-orange-200 dark:bg-orange-900/30 dark:group-hover:bg-orange-900/50"
                    >
                        <Calendar class="h-6 w-6 text-orange-600" />
                    </div>
                    <p class="text-sm font-semibold">Schedule</p>
                </a>
            </div>

            <!-- Analytics Section -->
            <div>
                <h2 class="mb-4 text-2xl font-bold">Analytics</h2>
            </div>

            <!-- Weekly Attendance Chart -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div
                    class="rounded-lg border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                >
                    <h3 class="mb-6 text-lg font-bold">
                        Weekly Attendance Trend
                    </h3>

                    <div
                        v-if="weeklyAttendance.length === 0"
                        class="py-8 text-center text-muted-foreground"
                    >
                        No attendance data available
                    </div>

                    <div v-else class="space-y-4">
                        <div
                            v-for="week in weeklyAttendance"
                            :key="week.day"
                            class="flex items-end gap-4"
                        >
                            <div class="w-12 text-sm font-semibold">
                                {{ week.day }}
                            </div>
                            <div class="flex-1">
                                <div class="mb-2 flex items-center gap-2">
                                    <div
                                        class="h-6 flex-1 rounded bg-green-500 transition-all hover:bg-green-600"
                                        :style="{
                                            width:
                                                week.total > 0
                                                    ? (week.present /
                                                          week.total) *
                                                          100 +
                                                      '%'
                                                    : '0%',
                                        }"
                                    ></div>
                                    <span class="w-8 text-xs font-semibold">
                                        {{ week.present }}
                                    </span>
                                </div>
                                <div
                                    v-if="week.absent > 0"
                                    class="flex items-center gap-2"
                                >
                                    <div
                                        class="h-2 flex-1 rounded bg-red-500"
                                        :style="{
                                            width:
                                                week.total > 0
                                                    ? (week.absent /
                                                          week.total) *
                                                          100 +
                                                      '%'
                                                    : '0%',
                                        }"
                                    ></div>
                                    <span
                                        class="w-8 text-xs text-muted-foreground"
                                    >
                                        {{ week.absent }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="weeklyAttendance.length > 0"
                        class="mt-6 flex gap-6 border-t border-sidebar-border/70 pt-6 dark:border-sidebar-border"
                    >
                        <div>
                            <p class="mb-1 text-xs text-muted-foreground">
                                Avg Present
                            </p>
                            <p class="text-lg font-bold text-green-600">
                                {{
                                    weeklyAttendance.length > 0
                                        ? (
                                              weeklyAttendance.reduce(
                                                  (sum: number, w) =>
                                                      sum + w.present,
                                                  0,
                                              ) / weeklyAttendance.length
                                          ).toFixed(0)
                                        : 0
                                }}
                            </p>
                        </div>
                        <div>
                            <p class="mb-1 text-xs text-muted-foreground">
                                Avg Absent
                            </p>
                            <p class="text-lg font-bold text-red-600">
                                {{
                                    weeklyAttendance.length > 0
                                        ? (
                                              weeklyAttendance.reduce(
                                                  (sum: number, w) =>
                                                      sum + w.absent,
                                                  0,
                                              ) / weeklyAttendance.length
                                          ).toFixed(1)
                                        : 0
                                }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Grade Distribution -->
                <div
                    class="rounded-lg border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                >
                    <h3 class="mb-6 text-lg font-bold">Grade Distribution</h3>

                    <div
                        v-if="Object.keys(gradeDistribution).length === 0"
                        class="py-8 text-center text-muted-foreground"
                    >
                        No grade data available
                    </div>

                    <div v-else class="space-y-4">
                        <div
                            v-for="grade in getGradeDistributionList()"
                            :key="grade.range"
                            class="flex items-center gap-4"
                        >
                            <div class="w-20 text-sm font-semibold">
                                {{ grade.range }}
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="h-8 flex-1 rounded bg-gradient-to-r from-blue-400 to-blue-600 transition-all hover:shadow-lg"
                                        :style="{
                                            width: grade.percentage + '%',
                                        }"
                                    ></div>
                                    <span
                                        class="w-12 text-right text-sm font-semibold"
                                    >
                                        {{ grade.count }}
                                    </span>
                                    <span
                                        class="w-10 text-right text-xs text-muted-foreground"
                                    >
                                        {{ grade.percentage }}%
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="mt-6 border-t border-sidebar-border/70 pt-6 dark:border-sidebar-border"
                    >
                        <p class="mb-2 text-sm text-muted-foreground">
                            Total Students
                        </p>
                        <p class="text-2xl font-bold">
                            {{
                                Object.values(gradeDistribution).reduce(
                                    (sum: number, g) => sum + g,
                                    0,
                                )
                            }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Subject Performance -->
            <div
                class="rounded-lg border border-sidebar-border/70 p-6 dark:border-sidebar-border"
            >
                <h3 class="mb-6 text-lg font-bold">Subject Performance</h3>

                <div
                    v-if="subjectPerformance.length === 0"
                    class="py-8 text-center text-muted-foreground"
                >
                    No subject data available
                </div>

                <div
                    v-else
                    class="grid grid-cols-2 gap-4 md:grid-cols-3 md:gap-6 lg:grid-cols-5"
                >
                    <div
                        v-for="subject in subjectPerformance"
                        :key="subject.subject"
                        class="text-center"
                    >
                        <div
                            class="relative mx-auto mb-3 flex h-16 w-16 items-center justify-center rounded-full md:mb-4 md:h-20 md:w-20 lg:h-24 lg:w-24"
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
                                class="absolute flex h-14 w-14 items-center justify-center rounded-full bg-card md:h-16 md:w-16 lg:h-20 lg:w-20"
                            >
                                <span class="text-xs font-bold md:text-sm">
                                    {{ subject.avgGrade }}
                                </span>
                            </div>
                        </div>
                        <h4
                            class="mb-1 line-clamp-2 text-xs font-semibold md:mb-2 md:text-sm"
                        >
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
