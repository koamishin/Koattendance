<script setup lang="ts">
import { Toaster } from 'vue-sonner';
import { usePage } from '@inertiajs/vue3';
import { watch } from 'vue';
import { toast } from '@/lib/toast';

const page = usePage();

// Watch for Inertia flash messages
watch(
    () => page.props.flash,
    (flash: any) => {
        if (flash?.success) {
            toast.success(flash.success);
        }
        if (flash?.error) {
            toast.error(flash.error);
        }
    },
    { deep: true, immediate: true }
);
</script>

<template>
    <Toaster 
        position="top-right" 
        rich-colors 
        close-button
        :toast-options="{
            style: {
                borderRadius: '0.75rem',
            },
        }"
    />
</template>
