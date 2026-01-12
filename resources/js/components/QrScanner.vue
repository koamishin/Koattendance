<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Html5Qrcode, Html5QrcodeSupportedFormats } from 'html5-qrcode';
import { AlertCircle } from 'lucide-vue-next';
import { onMounted, onUnmounted, ref } from 'vue';

const emit = defineEmits(['scan', 'error']);

const scannerId = 'qr-reader';
const html5QrCode = ref<Html5Qrcode | null>(null);
const isScanning = ref(false);
const hasCameraPermission = ref(false);
const errorMessage = ref<string | null>(null);

const startScanning = async () => {
    errorMessage.value = null;

    if (!html5QrCode.value) {
        html5QrCode.value = new Html5Qrcode(scannerId);
    }

    try {
        await html5QrCode.value.start(
            { facingMode: 'environment' },
            {
                fps: 10,
                qrbox: { width: 250, height: 250 },
                formatsToSupport: [Html5QrcodeSupportedFormats.QR_CODE],
            },
            (decodedText) => {
                // Success callback
                emit('scan', decodedText);
                // Optional: Play a beep sound
            },
            (error) => {
                // Error callback (called frequently when no QR is found)
                // console.warn(error);
            },
        );
        isScanning.value = true;
        hasCameraPermission.value = true;
    } catch (err) {
        console.error('Error starting scanner', err);
        errorMessage.value =
            'Could not access camera. Please ensure you have granted permission.';
        isScanning.value = false;
        emit('error', err);
    }
};

const stopScanning = async () => {
    if (html5QrCode.value && isScanning.value) {
        try {
            await html5QrCode.value.stop();
            isScanning.value = false;
        } catch (err) {
            console.error('Failed to stop scanner', err);
        }
    }
};

onMounted(() => {
    // Optionally auto-start
    // startScanning();
});

onUnmounted(() => {
    stopScanning();
});

defineExpose({ startScanning, stopScanning });
</script>

<template>
    <div class="flex w-full flex-col items-center gap-4">
        <div
            :id="scannerId"
            class="w-full max-w-md overflow-hidden rounded-lg border border-gray-700 bg-black"
            style="min-height: 300px"
        ></div>

        <div
            v-if="errorMessage"
            class="flex items-center gap-2 text-sm text-red-500"
        >
            <AlertCircle class="h-4 w-4" />
            {{ errorMessage }}
        </div>

        <div class="flex gap-2">
            <Button v-if="!isScanning" @click="startScanning"
                >Start Camera</Button
            >
            <Button v-else variant="destructive" @click="stopScanning"
                >Stop Camera</Button
            >
        </div>
    </div>
</template>

<style>
/* Custom styles for the scanner overlay if needed */
#qr-reader video {
    object-fit: cover;
    border-radius: 0.5rem;
}
</style>
