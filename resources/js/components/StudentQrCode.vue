<script setup lang="ts">
import { Button } from '@/components/ui/button';
import axios from 'axios';
import { Loader2, RefreshCw } from 'lucide-vue-next';
import QrcodeVue from 'qrcode.vue';
import { onMounted, ref } from 'vue';

const props = defineProps<{
    studentId: number | string;
}>();

const qrData = ref<string | null>(null);
const loading = ref(true);
const regenerating = ref(false);
const error = ref<string | null>(null);

const fetchQrCode = async () => {
    loading.value = true;
    error.value = null;
    try {
        const response = await axios.get(
            route('api.students.qr-code', props.studentId),
        );
        qrData.value = response.data.qr_code;
    } catch (e) {
        error.value = 'Failed to load QR code';
        console.error(e);
    } finally {
        loading.value = false;
    }
};

const regenerateQrCode = async () => {
    if (
        !confirm(
            'Are you sure you want to regenerate your QR code? The old one will stop working.',
        )
    )
        return;

    regenerating.value = true;
    try {
        const response = await axios.post(
            route('api.students.regenerate-qr', props.studentId),
        );
        qrData.value = response.data.qr_code;
    } catch (e) {
        alert('Failed to regenerate QR code');
    } finally {
        regenerating.value = false;
    }
};

onMounted(() => {
    fetchQrCode();
});
</script>

<template>
    <div
        class="flex flex-col items-center justify-center rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800"
    >
        <h3 class="mb-4 text-lg font-semibold">My Attendance QR Code</h3>

        <div
            v-if="loading"
            class="flex h-48 w-48 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-700"
        >
            <Loader2 class="h-8 w-8 animate-spin text-gray-400" />
        </div>

        <div v-else-if="error" class="text-center text-sm text-red-500">
            {{ error }}
            <Button variant="ghost" size="sm" @click="fetchQrCode" class="mt-2"
                >Retry</Button
            >
        </div>

        <div v-else class="flex flex-col items-center gap-4">
            <div class="rounded-xl bg-white p-4 shadow-md">
                <QrcodeVue :value="qrData" :size="200" level="H" />
            </div>

            <p class="max-w-[250px] text-center text-sm text-muted-foreground">
                Show this QR code to your teacher to mark your attendance.
            </p>

            <Button
                variant="outline"
                size="sm"
                @click="regenerateQrCode"
                :disabled="regenerating"
            >
                <RefreshCw
                    class="mr-2 h-4 w-4"
                    :class="{ 'animate-spin': regenerating }"
                />
                Regenerate
            </Button>
        </div>
    </div>
</template>
