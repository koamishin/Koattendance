<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref } from 'vue';
import {
    Users,
    CheckCircle2,
    TrendingUp,
    Calendar,
    AlertCircle,
} from 'lucide-vue-next';

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
        const [statsRes, attendanceRes, gradeRes, weeklyRes, subjectRes] = await Promise.all([
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
    };
    return icons[iconName] || Users;
};

const getGradeDistributionList = () => {
    return Object.entries(gradeDistribution.value).map(([range, count]) => ({
        range,
        count,
        percentage: Math.round((count / Object.values(gradeDistribution.value).reduce((a, b) => a + b, 0)) * 100) || 0,
    }));
};
</script>

<template>

    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-y-auto rounded-xl p-6">
            <!-- Welcome Section -->
            <div>
                <h1 class="text-4xl font-bold mb-2">Welcome Back</h1>
                <p class="text-muted-foreground flex items-center gap-2">
                    <Calendar class="w-4 h-4" />
                    {{ latestDate || 'Loading...' }}
                </p>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div v-for="stat in stats" :key="stat.title"
                    class="rounded-lg border border-sidebar-border/70 p-6 dark:border-sidebar-border bg-card hover:shadow-lg transition-shadow">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <p class="text-sm text-muted-foreground mb-1">
                                {{ stat.title }}
                            </p>
                            <p class="text-3xl font-bold">{{ stat.value }}</p>
                        </div>
                        <div :class="[stat.bgColor, 'rounded-lg p-3']">
                            <component :is="getIconComponent(stat.icon)" :class="['w-6 h-6', stat.color]" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Recent Attendance -->
                <div class="lg:col-span-2">
                    <div class="rounded-lg border border-sidebar-border/70 p-6 dark:border-sidebar-border">
                        <div class="flex items-center gap-2 mb-6">
                            <CheckCircle2 class="w-5 h-5 text-primary" />
                            <h2 class="text-xl font-bold">Recent Attendance</h2>
                        </div>

                        <div v-if="recentRecords.length === 0" class="text-center py-8 text-muted-foreground">
                            No attendance records found
                        </div>

                        <div v-else class="space-y-4 max-h-96 overflow-y-auto pr-2">
                            <div v-for="record in recentRecords" :key="record.id"
                                class="flex items-center justify-between p-4 rounded-lg bg-muted/50 dark:bg-muted/20 hover:bg-muted dark:hover:bg-muted/30 transition-colors">
                                <div class="flex-1">
                                    <h3 class="font-semibold mb-1">
                                        {{ record.name }}
                                    </h3>
                                    <div class="flex flex-col gap-2 text-sm text-muted-foreground">
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="text-xs bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-200 px-2 py-1 rounded">
                                                {{ record.subject }}
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-4">
                                            <span>{{ record.time }}</span>
                                            <span :class="[
                                                'inline-block px-2 py-1 rounded text-xs font-medium',
                                                record.status === 'present'
                                                    ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-200'
                                                    : record.status === 'late'
                                                        ? 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-200'
                                                        : 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-200',
                                            ]">
                                                {{
                                                    record.status.charAt(0).toUpperCase() +
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
                    <div class="rounded-lg border border-sidebar-border/70 p-6 dark:border-sidebar-border">
                        <h2 class="text-xl font-bold mb-6">Top Performing Students</h2>

                        <div v-if="topGrades.length === 0" class="text-center py-8 text-muted-foreground">
                            No grade records found
                        </div>

                        <div v-else class="space-y-4 max-h-96 overflow-y-auto pr-2">
                            <div v-for="(grade, idx) in topGrades" :key="idx"
                                class="p-4 rounded-lg bg-muted/50 dark:bg-muted/20 hover:bg-muted dark:hover:bg-muted/30 transition-colors border-l-4 border-l-blue-500">
                                <h3 class="font-semibold text-sm mb-1">
                                    {{ grade.name }}
                                </h3>
                                <p class="text-xs text-muted-foreground mb-2">
                                    {{ grade.subject }}
                                </p>
                                <span
                                    class="inline-block px-2 py-1 rounded text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-200">
                                    {{ grade.grade }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <a href="/dashboard/seatplan"
                    class="rounded-lg border border-sidebar-border/70 p-4 dark:border-sidebar-border text-center hover:shadow-lg transition-shadow group">
                    <div
                        class="w-12 h-12 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center mx-auto mb-3 group-hover:bg-blue-200 dark:group-hover:bg-blue-900/50 transition-colors">
                        <Users class="w-6 h-6 text-blue-600" />
                    </div>
                    <p class="font-semibold text-sm">View Seatplan</p>
                </a>

                <a href="/dashboard/attendance"
                    class="rounded-lg border border-sidebar-border/70 p-4 dark:border-sidebar-border text-center hover:shadow-lg transition-shadow group">
                    <div
                        class="w-12 h-12 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center mx-auto mb-3 group-hover:bg-green-200 dark:group-hover:bg-green-900/50 transition-colors">
                        <CheckCircle2 class="w-6 h-6 text-green-600" />
                    </div>
                    <p class="font-semibold text-sm">Check Attendance</p>
                </a>

                <a href="/dashboard/grades"
                    class="rounded-lg border border-sidebar-border/70 p-4 dark:border-sidebar-border text-center hover:shadow-lg transition-shadow group">
                    <div
                        class="w-12 h-12 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center mx-auto mb-3 group-hover:bg-purple-200 dark:group-hover:bg-purple-900/50 transition-colors">
                        <TrendingUp class="w-6 h-6 text-purple-600" />
                    </div>
                    <p class="font-semibold text-sm">View Grades</p>
                </a>

                <a href="/dashboard"
                    class="rounded-lg border border-sidebar-border/70 p-4 dark:border-sidebar-border text-center hover:shadow-lg transition-shadow group">
                    <div
                        class="w-12 h-12 rounded-lg bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center mx-auto mb-3 group-hover:bg-orange-200 dark:group-hover:bg-orange-900/50 transition-colors">
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
                <div class="rounded-lg border border-sidebar-border/70 p-6 dark:border-sidebar-border">
                    <h3 class="text-lg font-bold mb-6">Weekly Attendance Trend</h3>

                    <div v-if="weeklyAttendance.length === 0" class="text-center py-8 text-muted-foreground">
                        No attendance data available
                    </div>

                    <div v-else class="space-y-4">
                        <div v-for="week in weeklyAttendance" :key="week.day" class="flex items-end gap-4">
                            <div class="w-12 font-semibold text-sm">
                                {{ week.day }}
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <div class="flex-1 h-6 bg-green-500 rounded transition-all hover:bg-green-600"
                                        :style="{ width: week.total > 0 ? (week.present / week.total) * 100 + '%' : '0%' }">
                                    </div>
                                    <span class="text-xs font-semibold w-8">
                                        {{ week.present }}
                                    </span>
                                </div>
                                <div v-if="week.absent > 0" class="flex items-center gap-2">
                                    <div class="flex-1 h-2 bg-red-500 rounded"
                                        :style="{ width: week.total > 0 ? (week.absent / week.total) * 100 + '%' : '0%' }">
                                    </div>
                                    <span class="text-xs text-muted-foreground w-8">
                                        {{ week.absent }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="weeklyAttendance.length > 0"
                        class="flex gap-6 mt-6 pt-6 border-t border-sidebar-border/70 dark:border-sidebar-border">
                        <div>
                            <p class="text-xs text-muted-foreground mb-1">
                                Avg Present
                            </p>
                            <p class="text-lg font-bold text-green-600">
                                {{
                                    weeklyAttendance.length > 0
                                        ? (
                                            weeklyAttendance.reduce(
                                                (sum: number, w) => sum + w.present,
                                                0
                                            ) / weeklyAttendance.length
                                        ).toFixed(0)
                                        : 0
                                }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground mb-1">
                                Avg Absent
                            </p>
                            <p class="text-lg font-bold text-red-600">
                                {{
                                    weeklyAttendance.length > 0
                                        ? (
                                            weeklyAttendance.reduce(
                                                (sum: number, w) => sum + w.absent,
                                                0
                                            ) / weeklyAttendance.length
                                        ).toFixed(1)
                                        : 0
                                }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Grade Distribution -->
                <div class="rounded-lg border border-sidebar-border/70 p-6 dark:border-sidebar-border">
                    <h3 class="text-lg font-bold mb-6">Grade Distribution</h3>

                    <div v-if="Object.keys(gradeDistribution).length === 0"
                        class="text-center py-8 text-muted-foreground">
                        No grade data available
                    </div>

                    <div v-else class="space-y-4">
                        <div v-for="grade in getGradeDistributionList()" :key="grade.range"
                            class="flex items-center gap-4">
                            <div class="w-20 text-sm font-semibold">
                                {{ grade.range }}
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-3">
                                    <div class="flex-1 h-8 bg-gradient-to-r from-blue-400 to-blue-600 rounded transition-all hover:shadow-lg"
                                        :style="{ width: grade.percentage + '%' }"></div>
                                    <span class="text-sm font-semibold w-12 text-right">
                                        {{ grade.count }}
                                    </span>
                                    <span class="text-xs text-muted-foreground w-10 text-right">
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
                                Object.values(gradeDistribution).reduce((sum: number, g) => sum + g, 0)
                            }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Subject Performance -->
            <div class="rounded-lg border border-sidebar-border/70 p-6 dark:border-sidebar-border">
                <h3 class="text-lg font-bold mb-6">Subject Performance</h3>

                <div v-if="subjectPerformance.length === 0" class="text-center py-8 text-muted-foreground">
                    No subject data available
                </div>

                <div v-else class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 md:gap-6">
                    <div v-for="subject in subjectPerformance" :key="subject.subject" class="text-center">
                        <div class="relative w-16 md:w-20 lg:w-24 h-16 md:h-20 lg:h-24 rounded-full mx-auto mb-3 md:mb-4 flex items-center justify-center"
                            :style="{
                                background: `conic-gradient(
                                    from 0deg,
                                    rgb(59, 130, 246) 0deg,
                                    rgb(59, 130, 246) ${(subject.avgGrade / 100) * 360
                                    }deg,
                                    rgb(229, 231, 235) ${(subject.avgGrade / 100) * 360
                                    }deg
                                )`,
                            }">
                            <div class="absolute w-14 md:w-16 lg:w-20 h-14 md:h-16 lg:h-20 rounded-full bg-card flex items-center justify-center">
                                <span class="text-xs md:text-sm font-bold">
                                    {{ subject.avgGrade }}
                                </span>
                            </div>
                        </div>
                        <h4 class="font-semibold text-xs md:text-sm mb-1 md:mb-2 line-clamp-2">
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
