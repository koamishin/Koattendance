<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { LayoutGrid, LayoutDashboard, Users, BarChart3 } from 'lucide-vue-next';
import { computed } from 'vue';

const page = usePage();

const navItems = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Seatplan',
        href: '/dashboard/seatplan',
        icon: LayoutDashboard,
    },
    {
        title: 'Attendance',
        href: '/dashboard/attendance',
        icon: Users,
    },
    {
        title: 'Grades',
        href: '/dashboard/grades',
        icon: BarChart3,
    },
];

const isActive = (href: string): boolean => {
    const currentPath = page.url;
    return currentPath === href || currentPath.startsWith(href + '/');
};
</script>

<template>
    <nav class="fixed bottom-0 left-0 right-0 md:hidden bg-card border-t border-sidebar-border/70 dark:border-sidebar-border z-50">
        <div class="flex items-center justify-around">
            <Link
                v-for="item in navItems"
                :key="item.href"
                :href="item.href"
                class="flex-1 flex flex-col items-center justify-center py-3 px-2 text-xs font-medium transition-colors"
                :class="isActive(item.href)
                    ? 'text-primary'
                    : 'text-muted-foreground hover:text-foreground'
                "
            >
                <component :is="item.icon" class="w-6 h-6 mb-1" />
                <span class="truncate">{{ item.title }}</span>
            </Link>
        </div>
    </nav>
</template>
