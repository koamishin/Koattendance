<script setup lang="ts">
import { dashboard, login, register } from '@/routes';
import { Head, Link } from '@inertiajs/vue3';
import { Users, Calendar, BarChart3, MapPin } from 'lucide-vue-next';

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: true,
    },
);
</script>

<template>
    <Head title="KoAttendance - Student Attendance Management System">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>
    <div class="min-h-screen bg-black">
        <!-- Navigation -->
        <nav class="sticky top-0 z-50 border-b border-slate-800 bg-black/80 backdrop-blur">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-2">
                    <MapPin class="h-8 w-8 text-white" />
                    <span class="text-2xl font-bold text-white">KoAttendance</span>
                </div>
                <div class="flex items-center gap-4">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="dashboard()"
                        class="rounded-lg bg-white px-6 py-2 font-medium text-black hover:bg-slate-200"
                    >
                        Dashboard
                    </Link>
                    <template v-else>
                        <Link
                            :href="login()"
                            class="text-slate-400 hover:text-white"
                        >
                            Login
                        </Link>
                        <Link
                            v-if="canRegister"
                            :href="register()"
                            class="rounded-lg bg-white px-6 py-2 font-medium text-black hover:bg-slate-200"
                        >
                            Register
                        </Link>
                    </template>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
            <div class="grid gap-12 md:grid-cols-2">
                <!-- Left Content -->
                <div class="flex flex-col justify-center">
                    <h1 class="mb-4 text-5xl font-bold leading-tight text-white">
                        Student Attendance Management
                    </h1>
                    <p class="mb-6 text-xl text-slate-300">
                        Streamline your classroom attendance with our intuitive seatplan-based system. Track student presence, manage seating arrangements, and generate insightful reports—all in one place.
                    </p>
                    <div class="flex gap-4">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="dashboard()"
                            class="rounded-lg bg-white px-8 py-3 font-medium text-black hover:bg-slate-200"
                        >
                            Go to Dashboard
                        </Link>
                        <template v-else>
                            <Link
                                :href="login()"
                                class="rounded-lg bg-white px-8 py-3 font-medium text-black hover:bg-slate-200"
                            >
                                Sign In
                            </Link>
                            <Link
                                v-if="canRegister"
                                :href="register()"
                                class="rounded-lg border-2 border-white px-8 py-3 font-medium text-white hover:bg-slate-900"
                            >
                                Get Started
                            </Link>
                        </template>
                    </div>
                </div>

                <!-- Right Illustration -->
                <div class="flex items-center justify-center">
                    <div class="relative">
                        <div class="absolute -inset-4 rounded-3xl bg-gradient-to-r from-slate-700 to-slate-500 opacity-20 blur-2xl"></div>
                        <svg class="h-96 w-96" viewBox="0 0 400 400" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <!-- Seatplan Grid Background -->
                            <rect x="50" y="50" width="300" height="300" rx="20" fill="white" class="dark:fill-slate-800" stroke="url(#grad)" stroke-width="2" />
                            
                            <!-- Whiteboard -->
                            <rect x="80" y="70" width="240" height="30" rx="8" fill="#4F46E5" opacity="0.1" />
                            <text x="200" y="92" text-anchor="middle" font-size="12" fill="#6366F1" font-weight="bold">Classroom</text>
                            
                            <!-- Student Seats with Status Indicators -->
                            <!-- Row 1 -->
                            <g>
                                <!-- Seat 1: Present -->
                                <rect x="90" y="120" width="35" height="35" rx="8" fill="#DBEAFE" stroke="#3B82F6" stroke-width="2" />
                                <circle cx="107" cy="137" r="4" fill="#10B981" />
                                
                                <!-- Seat 2: Late -->
                                <rect x="145" y="120" width="35" height="35" rx="8" fill="#FEF3C7" stroke="#F59E0B" stroke-width="2" />
                                <circle cx="162" cy="137" r="4" fill="#F59E0B" />
                                
                                <!-- Seat 3: Present -->
                                <rect x="200" y="120" width="35" height="35" rx="8" fill="#DBEAFE" stroke="#3B82F6" stroke-width="2" />
                                <circle cx="217" cy="137" r="4" fill="#10B981" />
                                
                                <!-- Seat 4: Absent -->
                                <rect x="255" y="120" width="35" height="35" rx="8" fill="#FEE2E2" stroke="#EF4444" stroke-width="2" />
                                <circle cx="272" cy="137" r="4" fill="#EF4444" />
                            </g>
                            
                            <!-- Row 2 -->
                            <g>
                                <!-- Seat 5: Present -->
                                <rect x="90" y="175" width="35" height="35" rx="8" fill="#DBEAFE" stroke="#3B82F6" stroke-width="2" />
                                <circle cx="107" cy="192" r="4" fill="#10B981" />
                                
                                <!-- Seat 6: Empty -->
                                <rect x="145" y="175" width="35" height="35" rx="8" fill="#F3F4F6" stroke="#D1D5DB" stroke-width="2" stroke-dasharray="5,5" />
                                
                                <!-- Seat 7: Present -->
                                <rect x="200" y="175" width="35" height="35" rx="8" fill="#DBEAFE" stroke="#3B82F6" stroke-width="2" />
                                <circle cx="217" cy="192" r="4" fill="#10B981" />
                                
                                <!-- Seat 8: Late -->
                                <rect x="255" y="175" width="35" height="35" rx="8" fill="#FEF3C7" stroke="#F59E0B" stroke-width="2" />
                                <circle cx="272" cy="192" r="4" fill="#F59E0B" />
                            </g>
                            
                            <!-- Row 3 -->
                            <g>
                                <!-- Seat 9: Present -->
                                <rect x="90" y="230" width="35" height="35" rx="8" fill="#DBEAFE" stroke="#3B82F6" stroke-width="2" />
                                <circle cx="107" cy="247" r="4" fill="#10B981" />
                                
                                <!-- Seat 10: Present -->
                                <rect x="145" y="230" width="35" height="35" rx="8" fill="#DBEAFE" stroke="#3B82F6" stroke-width="2" />
                                <circle cx="162" cy="247" r="4" fill="#10B981" />
                                
                                <!-- Seat 11: Absent -->
                                <rect x="200" y="230" width="35" height="35" rx="8" fill="#FEE2E2" stroke="#EF4444" stroke-width="2" />
                                <circle cx="217" cy="247" r="4" fill="#EF4444" />
                                
                                <!-- Seat 12: Present -->
                                <rect x="255" y="230" width="35" height="35" rx="8" fill="#DBEAFE" stroke="#3B82F6" stroke-width="2" />
                                <circle cx="272" cy="247" r="4" fill="#10B981" />
                            </g>
                            
                            <!-- Row 4 -->
                            <g>
                                <!-- Seat 13: Present -->
                                <rect x="90" y="285" width="35" height="35" rx="8" fill="#DBEAFE" stroke="#3B82F6" stroke-width="2" />
                                <circle cx="107" cy="302" r="4" fill="#10B981" />
                                
                                <!-- Seat 14: Empty -->
                                <rect x="145" y="285" width="35" height="35" rx="8" fill="#F3F4F6" stroke="#D1D5DB" stroke-width="2" stroke-dasharray="5,5" />
                                
                                <!-- Seat 15: Late -->
                                <rect x="200" y="285" width="35" height="35" rx="8" fill="#FEF3C7" stroke="#F59E0B" stroke-width="2" />
                                <circle cx="217" cy="302" r="4" fill="#F59E0B" />
                                
                                <!-- Seat 16: Present -->
                                <rect x="255" y="285" width="35" height="35" rx="8" fill="#DBEAFE" stroke="#3B82F6" stroke-width="2" />
                                <circle cx="272" cy="302" r="4" fill="#10B981" />
                            </g>
                            
                            <!-- Legend -->
                            <g>
                                <!-- Present legend -->
                                <circle cx="70" cy="370" r="3" fill="#10B981" />
                                <text x="80" y="374" font-size="10" fill="#666" class="dark:fill-slate-400">Present</text>
                                
                                <!-- Late legend -->
                                <circle cx="150" cy="370" r="3" fill="#F59E0B" />
                                <text x="160" y="374" font-size="10" fill="#666" class="dark:fill-slate-400">Late</text>
                                
                                <!-- Absent legend -->
                                <circle cx="210" cy="370" r="3" fill="#EF4444" />
                                <text x="220" y="374" font-size="10" fill="#666" class="dark:fill-slate-400">Absent</text>
                            </g>
                            
                            <!-- Gradient -->
                            <defs>
                                <linearGradient id="grad" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" style="stop-color:#4F46E5;stop-opacity:1" />
                                    <stop offset="100%" style="stop-color:#3B82F6;stop-opacity:1" />
                                </linearGradient>
                            </defs>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <div class="border-t border-slate-800 bg-slate-950 py-20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-12 text-center">
                    <h2 class="mb-4 text-4xl font-bold text-white">Key Features</h2>
                    <p class="text-xl text-slate-400">Everything you need to manage classroom attendance effectively</p>
                </div>

                <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-4">
                    <!-- Feature 1 -->
                    <div class="rounded-lg border border-slate-800 bg-slate-900 p-6 hover:border-slate-700">
                        <div class="mb-4 inline-flex rounded-lg bg-slate-800 p-3">
                            <MapPin class="h-6 w-6 text-white" />
                        </div>
                        <h3 class="mb-2 text-lg font-bold text-white">Smart Seatplan</h3>
                        <p class="text-slate-400">Visually arrange students with drag-and-drop seatplan management.</p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="rounded-lg border border-slate-800 bg-slate-900 p-6 hover:border-slate-700">
                        <div class="mb-4 inline-flex rounded-lg bg-slate-800 p-3">
                            <Calendar class="h-6 w-6 text-white" />
                        </div>
                        <h3 class="mb-2 text-lg font-bold text-white">Daily Tracking</h3>
                        <p class="text-slate-400">Record attendance with status indicators: Present, Late, or Absent.</p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="rounded-lg border border-slate-800 bg-slate-900 p-6 hover:border-slate-700">
                        <div class="mb-4 inline-flex rounded-lg bg-slate-800 p-3">
                            <BarChart3 class="h-6 w-6 text-white" />
                        </div>
                        <h3 class="mb-2 text-lg font-bold text-white">Insights & Reports</h3>
                        <p class="text-slate-400">Generate comprehensive attendance reports and analytics.</p>
                    </div>

                    <!-- Feature 4 -->
                    <div class="rounded-lg border border-slate-800 bg-slate-900 p-6 hover:border-slate-700">
                        <div class="mb-4 inline-flex rounded-lg bg-slate-800 p-3">
                            <Users class="h-6 w-6 text-white" />
                        </div>
                        <h3 class="mb-2 text-lg font-bold text-white">Student Management</h3>
                        <p class="text-slate-400">Manage student profiles and attendance records easily.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- How It Works -->
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <h2 class="mb-4 text-4xl font-bold text-white">How It Works</h2>
            </div>

            <div class="grid gap-8 md:grid-cols-3">
                <div class="relative">
                    <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-white text-black text-lg font-bold">1</div>
                    <h3 class="mb-2 text-xl font-bold text-white">Arrange Seating</h3>
                    <p class="text-slate-300">Organize your classroom by creating a seatplan. Drag and drop students to assign seats.</p>
                </div>

                <div class="relative">
                    <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-white text-black text-lg font-bold">2</div>
                    <h3 class="mb-2 text-xl font-bold text-white">Mark Attendance</h3>
                    <p class="text-slate-300">Use the interactive seatplan to quickly mark student attendance status.</p>
                </div>

                <div class="relative">
                    <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-white text-black text-lg font-bold">3</div>
                    <h3 class="mb-2 text-xl font-bold text-white">View Analytics</h3>
                    <p class="text-slate-300">Track attendance patterns and generate detailed reports for insights.</p>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="border-t border-slate-800 bg-gradient-to-r from-slate-900 to-black py-16">
            <div class="mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">
                <h2 class="mb-4 text-3xl font-bold text-white">Ready to Transform Your Classroom?</h2>
                <p class="mb-8 text-lg text-slate-300">Start managing student attendance efficiently today.</p>
                <Link
                    v-if="$page.props.auth.user"
                    :href="dashboard()"
                    class="inline-block rounded-lg bg-white px-8 py-3 font-medium text-black hover:bg-slate-200"
                >
                    Go to Dashboard
                </Link>
                <template v-else>
                    <Link
                        v-if="canRegister"
                        :href="register()"
                        class="inline-block rounded-lg bg-white px-8 py-3 font-medium text-black hover:bg-slate-200"
                    >
                        Get Started Free
                    </Link>
                    <Link
                        v-else
                        :href="login()"
                        class="inline-block rounded-lg bg-white px-8 py-3 font-medium text-black hover:bg-slate-200"
                    >
                        Sign In
                    </Link>
                </template>
            </div>
        </div>

        <!-- Footer -->
        <footer class="border-t border-slate-800 bg-black">
            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <div class="flex flex-col items-center justify-between md:flex-row">
                    <div class="flex items-center gap-2 mb-4 md:mb-0">
                        <MapPin class="h-6 w-6 text-white" />
                        <span class="text-lg font-bold text-white">KoAttendance</span>
                    </div>
                    <p class="text-slate-400">© 2026 KoAttendance. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>
</template>
