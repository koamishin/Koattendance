<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { useAppearance } from '@/composables/useAppearance';
import { Moon, Sun } from 'lucide-vue-next';

const { appearance, updateAppearance } = useAppearance();

function toggleTheme() {
    if (appearance.value === 'light') {
        updateAppearance('dark');
    } else if (appearance.value === 'dark') {
        updateAppearance('system');
    } else {
        updateAppearance('light');
    }
}
</script>

<template>
    <Button
        variant="ghost"
        size="icon"
        class="h-9 w-9"
        @click="toggleTheme"
        title="Toggle theme (Light/Dark/System)"
    >
        <Sun class="h-4 w-4 rotate-0 scale-100 transition-all dark:-rotate-90 dark:scale-0" />
        <Moon class="absolute h-4 w-4 rotate-90 scale-0 transition-all dark:rotate-0 dark:scale-100" />
        <span class="sr-only">Toggle theme</span>
    </Button>
</template>
