<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Html5Qrcode,
    Html5QrcodeSupportedFormats,
    type CameraDevice,
} from 'html5-qrcode';
import { AlertCircle, Camera, Focus, RefreshCw, Settings } from 'lucide-vue-next';
import { onMounted, onUnmounted, ref, watch } from 'vue';

const emit = defineEmits(['scan', 'error']);

const scannerId = 'qr-reader';
const html5QrCode = ref<Html5Qrcode | null>(null);
const isScanning = ref(false);
const isLoading = ref(false);
const hasCameraPermission = ref(false);
const errorMessage = ref<string | null>(null);
const availableCameras = ref<CameraDevice[]>([]);
const selectedCameraId = ref<string | null>(null);

// Focus control state
const showFocusControls = ref(false);
const focusMode = ref<string>('continuous');
const focusDistance = ref<number>(0);
const focusCapabilities = ref<{
    supportsFocusMode: boolean;
    supportsFocusDistance: boolean;
    focusModes: string[];
    focusDistanceMin: number;
    focusDistanceMax: number;
    focusDistanceStep: number;
}>({
    supportsFocusMode: false,
    supportsFocusDistance: false,
    focusModes: [],
    focusDistanceMin: 0,
    focusDistanceMax: 100,
    focusDistanceStep: 1,
});
const activeVideoTrack = ref<MediaStreamTrack | null>(null);

// Fetch available cameras on mount
const fetchCameras = async () => {
    try {
        const cameras = await Html5Qrcode.getCameras();
        availableCameras.value = cameras;

        if (cameras.length > 0) {
            // Try to find a back camera first (for mobile), otherwise use the first available
            const backCamera = cameras.find(
                (c) =>
                    c.label.toLowerCase().includes('back') ||
                    c.label.toLowerCase().includes('environment') ||
                    c.label.toLowerCase().includes('rear'),
            );
            selectedCameraId.value = backCamera?.id || cameras[0].id;
        }
    } catch (err) {
        console.error('Error fetching cameras', err);
        errorMessage.value =
            'Could not detect cameras. Please ensure camera access is allowed.';
    }
};

// Check and apply focus capabilities
const checkFocusCapabilities = async () => {
    const videoElement = document.querySelector('#qr-reader video') as HTMLVideoElement;
    if (!videoElement || !videoElement.srcObject) return;

    const stream = videoElement.srcObject as MediaStream;
    const track = stream.getVideoTracks()[0];
    if (!track) return;

    activeVideoTrack.value = track;

    try {
        // Get capabilities - using 'any' because TypeScript doesn't have full types for these
        const capabilities = track.getCapabilities() as any;
        
        // Check for focus mode support
        if (capabilities.focusMode && capabilities.focusMode.length > 0) {
            focusCapabilities.value.supportsFocusMode = true;
            focusCapabilities.value.focusModes = capabilities.focusMode;
        }

        // Check for focus distance support (manual focus)
        if (capabilities.focusDistance) {
            focusCapabilities.value.supportsFocusDistance = true;
            focusCapabilities.value.focusDistanceMin = capabilities.focusDistance.min || 0;
            focusCapabilities.value.focusDistanceMax = capabilities.focusDistance.max || 100;
            focusCapabilities.value.focusDistanceStep = capabilities.focusDistance.step || 1;
            
            // Set initial focus distance to middle of range
            focusDistance.value = (focusCapabilities.value.focusDistanceMin + focusCapabilities.value.focusDistanceMax) / 2;
        }

        // Get current settings
        const settings = track.getSettings() as any;
        if (settings.focusMode) {
            focusMode.value = settings.focusMode;
        }
        if (settings.focusDistance !== undefined) {
            focusDistance.value = settings.focusDistance;
        }

        console.log('Focus capabilities:', focusCapabilities.value);
    } catch (err) {
        console.error('Error checking focus capabilities', err);
    }
};

// Apply focus settings
const applyFocusSettings = async () => {
    if (!activeVideoTrack.value) return;

    try {
        const constraints: any = {};

        if (focusCapabilities.value.supportsFocusMode) {
            constraints.focusMode = focusMode.value;
        }

        // Only apply focus distance if in manual mode and supported
        if (focusMode.value === 'manual' && focusCapabilities.value.supportsFocusDistance) {
            constraints.focusDistance = focusDistance.value;
        }

        await activeVideoTrack.value.applyConstraints({ advanced: [constraints] });
        console.log('Applied focus settings:', constraints);
    } catch (err) {
        console.error('Error applying focus settings', err);
    }
};

// Trigger autofocus (one-shot)
const triggerAutofocus = async () => {
    if (!activeVideoTrack.value) return;

    try {
        // Briefly switch to single-shot autofocus then back
        if (focusCapabilities.value.focusModes.includes('single-shot')) {
            await activeVideoTrack.value.applyConstraints({
                advanced: [{ focusMode: 'single-shot' } as any]
            });
        } else if (focusCapabilities.value.focusModes.includes('auto')) {
            await activeVideoTrack.value.applyConstraints({
                advanced: [{ focusMode: 'auto' } as any]
            });
        }
    } catch (err) {
        console.error('Error triggering autofocus', err);
    }
};

const startScanning = async () => {
    errorMessage.value = null;
    isLoading.value = true;

    // Reset focus capabilities
    focusCapabilities.value = {
        supportsFocusMode: false,
        supportsFocusDistance: false,
        focusModes: [],
        focusDistanceMin: 0,
        focusDistanceMax: 100,
        focusDistanceStep: 1,
    };

    // If no cameras fetched yet, try to fetch them
    if (availableCameras.value.length === 0) {
        await fetchCameras();
    }

    if (!html5QrCode.value) {
        html5QrCode.value = new Html5Qrcode(scannerId);
    }

    // Determine camera config: use selected camera ID or fallback strategies
    let cameraConfig: { facingMode: string } | string;

    if (selectedCameraId.value) {
        // Use specific camera ID
        cameraConfig = selectedCameraId.value;
    } else if (availableCameras.value.length > 0) {
        // Fallback to first available camera
        cameraConfig = availableCameras.value[0].id;
    } else {
        // No specific camera, try environment then user
        cameraConfig = { facingMode: 'environment' };
    }

    try {
        await html5QrCode.value.start(
            cameraConfig,
            {
                fps: 15, // Increased for faster detection
                qrbox: { width: 250, height: 250 },
                formatsToSupport: [Html5QrcodeSupportedFormats.QR_CODE],
                aspectRatio: 1.0, // Square aspect ratio for consistent scanning
            },
            (decodedText) => {
                // Success callback
                emit('scan', decodedText);
            },
            () => {
                // Error callback (called frequently when no QR is found)
                // Intentionally empty - this fires on every failed scan attempt
            },
        );
        isScanning.value = true;
        hasCameraPermission.value = true;

        // Check focus capabilities after camera starts
        setTimeout(() => {
            checkFocusCapabilities();
        }, 500);
    } catch (err: any) {
        console.error('Error starting scanner with selected camera', err);

        // If using camera ID failed, try with facingMode as fallback
        if (typeof cameraConfig === 'string') {
            try {
                // Try with 'user' facing mode (front camera / default webcam)
                await html5QrCode.value.start(
                    { facingMode: 'user' },
                    {
                        fps: 15,
                        qrbox: { width: 250, height: 250 },
                        formatsToSupport: [Html5QrcodeSupportedFormats.QR_CODE],
                        aspectRatio: 1.0,
                    },
                    (decodedText) => {
                        emit('scan', decodedText);
                    },
                    () => {},
                );
                isScanning.value = true;
                hasCameraPermission.value = true;
                isLoading.value = false;

                // Check focus capabilities
                setTimeout(() => {
                    checkFocusCapabilities();
                }, 500);
                return;
            } catch (fallbackErr) {
                console.error('Fallback camera also failed', fallbackErr);
            }
        }

        errorMessage.value =
            err.message ||
            'Could not access camera. Please ensure you have granted permission and no other app is using the camera.';
        isScanning.value = false;
        emit('error', err);
    }

    isLoading.value = false;
};

const stopScanning = async () => {
    if (html5QrCode.value && isScanning.value) {
        try {
            await html5QrCode.value.stop();
            isScanning.value = false;
            activeVideoTrack.value = null;
            showFocusControls.value = false;
        } catch (err) {
            console.error('Failed to stop scanner', err);
        }
    }
};

const switchCamera = async () => {
    if (availableCameras.value.length <= 1) return;

    // Find next camera in the list
    const currentIndex = availableCameras.value.findIndex(
        (c) => c.id === selectedCameraId.value,
    );
    const nextIndex = (currentIndex + 1) % availableCameras.value.length;
    selectedCameraId.value = availableCameras.value[nextIndex].id;

    // Restart scanning with new camera
    if (isScanning.value) {
        await stopScanning();
        await startScanning();
    }
};

// Watch for focus mode changes
watch(focusMode, () => {
    applyFocusSettings();
});

// Watch for focus distance changes (debounced)
let focusDistanceTimeout: ReturnType<typeof setTimeout> | null = null;
watch(focusDistance, () => {
    if (focusDistanceTimeout) clearTimeout(focusDistanceTimeout);
    focusDistanceTimeout = setTimeout(() => {
        applyFocusSettings();
    }, 100);
});

// Watch for camera changes and restart if needed
watch(selectedCameraId, async (newId, oldId) => {
    if (newId && oldId && isScanning.value) {
        await stopScanning();
        await startScanning();
    }
});

onMounted(() => {
    fetchCameras();
});

onUnmounted(() => {
    stopScanning();
    if (focusDistanceTimeout) clearTimeout(focusDistanceTimeout);
});

defineExpose({ startScanning, stopScanning });
</script>

<template>
    <div class="flex w-full flex-col items-center gap-4">
        <!-- Camera selector (if multiple cameras) -->
        <div
            v-if="availableCameras.length > 1"
            class="flex w-full max-w-md items-center gap-2"
        >
            <Camera class="h-4 w-4 text-muted-foreground" />
            <select
                v-model="selectedCameraId"
                class="flex-1 rounded-md border bg-background px-3 py-2 text-sm"
                :disabled="isLoading"
            >
                <option
                    v-for="camera in availableCameras"
                    :key="camera.id"
                    :value="camera.id"
                >
                    {{ camera.label || `Camera ${camera.id.slice(0, 8)}` }}
                </option>
            </select>
            <Button
                variant="outline"
                size="icon"
                @click="switchCamera"
                :disabled="isLoading || !isScanning"
                title="Switch Camera"
            >
                <RefreshCw class="h-4 w-4" />
            </Button>
        </div>

        <div
            :id="scannerId"
            class="w-full max-w-md overflow-hidden rounded-lg border border-gray-700 bg-black"
            style="min-height: 300px"
        ></div>

        <!-- Focus Controls -->
        <div
            v-if="isScanning && (focusCapabilities.supportsFocusMode || focusCapabilities.supportsFocusDistance)"
            class="w-full max-w-md"
        >
            <button
                @click="showFocusControls = !showFocusControls"
                class="flex w-full items-center justify-between rounded-md border bg-muted/50 px-3 py-2 text-sm hover:bg-muted"
            >
                <span class="flex items-center gap-2">
                    <Settings class="h-4 w-4" />
                    Focus Controls
                </span>
                <span class="text-xs text-muted-foreground">
                    {{ showFocusControls ? 'Hide' : 'Show' }}
                </span>
            </button>

            <div
                v-if="showFocusControls"
                class="mt-2 space-y-3 rounded-md border bg-card p-3"
            >
                <!-- Focus Mode -->
                <div v-if="focusCapabilities.supportsFocusMode" class="space-y-2">
                    <label class="flex items-center gap-2 text-sm font-medium">
                        <Focus class="h-4 w-4" />
                        Focus Mode
                    </label>
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="mode in focusCapabilities.focusModes"
                            :key="mode"
                            @click="focusMode = mode"
                            :class="[
                                'rounded-md px-3 py-1.5 text-xs font-medium transition-colors',
                                focusMode === mode
                                    ? 'bg-primary text-primary-foreground'
                                    : 'bg-muted hover:bg-muted/80'
                            ]"
                        >
                            {{ mode === 'continuous' ? 'Auto (Continuous)' : mode === 'single-shot' ? 'Single Shot' : mode.charAt(0).toUpperCase() + mode.slice(1) }}
                        </button>
                    </div>
                </div>

                <!-- Manual Focus Distance -->
                <div v-if="focusCapabilities.supportsFocusDistance && focusMode === 'manual'" class="space-y-2">
                    <label class="flex items-center justify-between text-sm font-medium">
                        <span>Focus Distance</span>
                        <span class="text-xs text-muted-foreground">{{ focusDistance.toFixed(1) }}</span>
                    </label>
                    <input
                        type="range"
                        v-model.number="focusDistance"
                        :min="focusCapabilities.focusDistanceMin"
                        :max="focusCapabilities.focusDistanceMax"
                        :step="focusCapabilities.focusDistanceStep"
                        class="w-full accent-primary"
                    />
                    <div class="flex justify-between text-xs text-muted-foreground">
                        <span>Near</span>
                        <span>Far</span>
                    </div>
                </div>

                <!-- Autofocus Button -->
                <Button
                    v-if="focusCapabilities.focusModes.includes('single-shot') || focusCapabilities.focusModes.includes('auto')"
                    variant="outline"
                    size="sm"
                    class="w-full"
                    @click="triggerAutofocus"
                >
                    <Focus class="mr-2 h-4 w-4" />
                    Trigger Autofocus
                </Button>

                <!-- No Focus Support Message -->
                <p
                    v-if="!focusCapabilities.supportsFocusMode && !focusCapabilities.supportsFocusDistance"
                    class="text-center text-xs text-muted-foreground"
                >
                    Your camera doesn't report focus controls. Try moving the QR code closer or further from the camera.
                </p>
            </div>
        </div>

        <!-- Tip for blurry cameras -->
        <div
            v-if="isScanning && !focusCapabilities.supportsFocusMode && !focusCapabilities.supportsFocusDistance"
            class="flex max-w-md items-start gap-2 rounded-md bg-blue-50 p-3 text-xs text-blue-700 dark:bg-blue-900/20 dark:text-blue-300"
        >
            <Focus class="mt-0.5 h-4 w-4 flex-shrink-0" />
            <span>
                <strong>Tip:</strong> If the image is blurry, try moving the QR code 20-40cm from the camera. 
                You may also check your HIKVision camera's driver software for focus settings.
            </span>
        </div>

        <div
            v-if="errorMessage"
            class="flex max-w-md items-start gap-2 rounded-md bg-red-50 p-3 text-sm text-red-600 dark:bg-red-900/20 dark:text-red-400"
        >
            <AlertCircle class="mt-0.5 h-4 w-4 flex-shrink-0" />
            <span>{{ errorMessage }}</span>
        </div>

        <div
            v-if="availableCameras.length === 0 && !errorMessage && !isLoading"
            class="text-sm text-muted-foreground"
        >
            Click "Start Camera" to begin scanning
        </div>

        <div class="flex gap-2">
            <Button
                v-if="!isScanning"
                @click="startScanning"
                :disabled="isLoading"
            >
                <Camera v-if="!isLoading" class="mr-2 h-4 w-4" />
                <RefreshCw v-else class="mr-2 h-4 w-4 animate-spin" />
                {{ isLoading ? 'Starting...' : 'Start Camera' }}
            </Button>
            <Button v-else variant="destructive" @click="stopScanning">
                Stop Camera
            </Button>
        </div>
    </div>
</template>

<style>
/* Custom styles for the scanner overlay if needed */
#qr-reader video {
    object-fit: cover;
    border-radius: 0.5rem;
}

#qr-reader__scan_region {
    min-height: 280px;
}
</style>
