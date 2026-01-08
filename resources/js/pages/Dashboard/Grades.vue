<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

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
</script>

<template>
    <Head title="Grades" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <h1 class="text-3xl font-bold">Grades</h1>
            <div
                class="relative flex-1 rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
            >
                <div class="flex h-full items-center justify-center">
                    <p class="text-muted-foreground">
                        Grade information will be displayed here
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
