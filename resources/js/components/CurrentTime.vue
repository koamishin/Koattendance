<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';

const currentTime = ref<string>('');

function updateTime() {
    const now = new Date();
    let hours = now.getHours();
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');
    const ampm = hours >= 12 ? 'PM' : 'AM';
    hours = hours % 12;
    hours = hours ? hours : 12; // 0 should be 12
    const hoursStr = String(hours).padStart(2, '0');
    currentTime.value = `${hoursStr}:${minutes}:${seconds} ${ampm}`;
}

let intervalId: ReturnType<typeof setInterval> | null = null;

onMounted(() => {
    updateTime();
    intervalId = setInterval(updateTime, 1000);
});

onUnmounted(() => {
    if (intervalId) {
        clearInterval(intervalId);
    }
});
</script>

<template>
    <div class="flex items-center text-sm text-muted-foreground font-mono">
        {{ currentTime }}
    </div>
</template>
