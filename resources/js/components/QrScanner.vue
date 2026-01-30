<script setup lang="ts">
import { Button } from '@/components/ui/button';
import jsQR from 'jsqr';
import { AlertCircle, Camera, ImagePlus, RefreshCw, Video, X } from 'lucide-vue-next';
import { onMounted, onUnmounted, ref } from 'vue';

const emit = defineEmits(['scan', 'error']);

// State
const videoRef = ref<HTMLVideoElement | null>(null);
const canvasRef = ref<HTMLCanvasElement | null>(null);
const fileInputRef = ref<HTMLInputElement | null>(null);
const isScanning = ref(false);
const isLoading = ref(false);
const errorMessage = ref<string | null>(null);
const lastScannedCode = ref<string | null>(null);
const isProcessing = ref(false);
const scanCooldownTimer = ref<ReturnType<typeof setTimeout> | null>(null);
const scanMode = ref<'camera' | 'upload'>('camera');
const availableCameras = ref<MediaDeviceInfo[]>([]);
const selectedCameraId = ref<string>('');
const stream = ref<MediaStream | null>(null);
const animationFrameId = ref<number | null>(null);
const uploadedImageUrl = ref<string | null>(null);

// Get available cameras
const fetchCameras = async () => {
    try {
        // First request permission to get labeled devices
        const tempStream = await navigator.mediaDevices.getUserMedia({ video: true });
        tempStream.getTracks().forEach(track => track.stop());
        
        const devices = await navigator.mediaDevices.enumerateDevices();
        availableCameras.value = devices.filter(d => d.kind === 'videoinput');
        
        if (availableCameras.value.length > 0 && !selectedCameraId.value) {
            selectedCameraId.value = availableCameras.value[0].deviceId;
        }
    } catch (err) {
        console.error('Error fetching cameras:', err);
    }
};

// Start camera scanning
const startCamera = async () => {
    errorMessage.value = null;
    isLoading.value = true;
    lastScannedCode.value = null;

    try {
        // Stop any existing stream
        stopCamera();

        const constraints: MediaStreamConstraints = {
            video: {
                deviceId: selectedCameraId.value ? { exact: selectedCameraId.value } : undefined,
                width: { ideal: 1280 },
                height: { ideal: 720 },
                facingMode: selectedCameraId.value ? undefined : 'environment'
            }
        };

        stream.value = await navigator.mediaDevices.getUserMedia(constraints);
        
        if (videoRef.value) {
            videoRef.value.srcObject = stream.value;
            await videoRef.value.play();
            isScanning.value = true;
            
            // Start scanning loop
            requestAnimationFrame(scanFrame);
        }
    } catch (err: any) {
        console.error('Error starting camera:', err);
        
        // Try fallback without specific device
        try {
            stream.value = await navigator.mediaDevices.getUserMedia({ 
                video: { facingMode: 'user' } 
            });
            
            if (videoRef.value) {
                videoRef.value.srcObject = stream.value;
                await videoRef.value.play();
                isScanning.value = true;
                requestAnimationFrame(scanFrame);
            }
        } catch (fallbackErr: any) {
            errorMessage.value = fallbackErr.message || 'Could not access camera. Please grant permission.';
            emit('error', fallbackErr);
        }
    } finally {
        isLoading.value = false;
    }
};

// Stop camera
const stopCamera = () => {
    if (animationFrameId.value) {
        cancelAnimationFrame(animationFrameId.value);
        animationFrameId.value = null;
    }
    
    if (stream.value) {
        stream.value.getTracks().forEach(track => track.stop());
        stream.value = null;
    }
    
    if (videoRef.value) {
        videoRef.value.srcObject = null;
    }
    
    isScanning.value = false;
};

// Scan a single frame
const scanFrame = () => {
    if (!isScanning.value || !videoRef.value || !canvasRef.value) return;

    const video = videoRef.value;
    const canvas = canvasRef.value;
    const ctx = canvas.getContext('2d', { willReadFrequently: true });
    
    if (!ctx || video.readyState !== video.HAVE_ENOUGH_DATA) {
        animationFrameId.value = requestAnimationFrame(scanFrame);
        return;
    }

    // Set canvas size to match video
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    
    // Draw video frame to canvas
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
    
    // Get image data and scan for QR code
    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
    const code = jsQR(imageData.data, imageData.width, imageData.height, {
        inversionAttempts: 'dontInvert',
    });

    if (code && code.data) {
        // Only emit if it's a new code and not currently processing
        if (code.data !== lastScannedCode.value && !isProcessing.value) {
            lastScannedCode.value = code.data;
            isProcessing.value = true;
            emit('scan', code.data);
            
            // Draw detection box
            drawDetectionBox(ctx, code.location);
            
            // Auto-reset after 3 seconds to allow scanning next student
            if (scanCooldownTimer.value) {
                clearTimeout(scanCooldownTimer.value);
            }
            scanCooldownTimer.value = setTimeout(() => {
                lastScannedCode.value = null;
                isProcessing.value = false;
            }, 3000);
        }
    }

    // Continue scanning
    animationFrameId.value = requestAnimationFrame(scanFrame);
};

// Draw a box around detected QR code
const drawDetectionBox = (ctx: CanvasRenderingContext2D, location: any) => {
    ctx.strokeStyle = '#00ff00';
    ctx.lineWidth = 4;
    ctx.beginPath();
    ctx.moveTo(location.topLeftCorner.x, location.topLeftCorner.y);
    ctx.lineTo(location.topRightCorner.x, location.topRightCorner.y);
    ctx.lineTo(location.bottomRightCorner.x, location.bottomRightCorner.y);
    ctx.lineTo(location.bottomLeftCorner.x, location.bottomLeftCorner.y);
    ctx.closePath();
    ctx.stroke();
};

// Handle file upload
const handleFileUpload = async (event: Event) => {
    const input = event.target as HTMLInputElement;
    if (!input.files || input.files.length === 0) return;

    const file = input.files[0];
    errorMessage.value = null;
    lastScannedCode.value = null;

    try {
        // Create image from file
        const imageUrl = URL.createObjectURL(file);
        uploadedImageUrl.value = imageUrl;
        
        const img = new Image();
        img.onload = () => {
            // Create canvas and draw image
            const canvas = document.createElement('canvas');
            canvas.width = img.width;
            canvas.height = img.height;
            
            const ctx = canvas.getContext('2d');
            if (!ctx) {
                errorMessage.value = 'Could not process image';
                return;
            }
            
            ctx.drawImage(img, 0, 0);
            
            // Get image data and scan
            const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
            const code = jsQR(imageData.data, imageData.width, imageData.height, {
                inversionAttempts: 'attemptBoth',
            });
            
            if (code && code.data) {
                lastScannedCode.value = code.data;
                emit('scan', code.data);
            } else {
                errorMessage.value = 'No QR code found in image. Please try a clearer image.';
            }
        };
        
        img.onerror = () => {
            errorMessage.value = 'Could not load image';
        };
        
        img.src = imageUrl;
    } catch (err: any) {
        errorMessage.value = err.message || 'Error processing image';
    }
    
    // Reset file input
    input.value = '';
};

// Clear uploaded image
const clearUploadedImage = () => {
    if (uploadedImageUrl.value) {
        URL.revokeObjectURL(uploadedImageUrl.value);
        uploadedImageUrl.value = null;
    }
    lastScannedCode.value = null;
    errorMessage.value = null;
};

// Take snapshot from camera
const takeSnapshot = () => {
    if (!videoRef.value || !canvasRef.value) return;
    
    const video = videoRef.value;
    const canvas = canvasRef.value;
    const ctx = canvas.getContext('2d');
    
    if (!ctx) return;
    
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    ctx.drawImage(video, 0, 0);
    
    // Get image data and scan with more attempts
    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
    const code = jsQR(imageData.data, imageData.width, imageData.height, {
        inversionAttempts: 'attemptBoth',
    });
    
    if (code && code.data) {
        lastScannedCode.value = code.data;
        emit('scan', code.data);
        drawDetectionBox(ctx, code.location);
    } else {
        errorMessage.value = 'No QR code detected. Try moving closer or adjusting angle.';
        setTimeout(() => {
            errorMessage.value = null;
        }, 3000);
    }
};

// Switch camera
const switchCamera = async () => {
    if (availableCameras.value.length <= 1) return;
    
    const currentIndex = availableCameras.value.findIndex(
        c => c.deviceId === selectedCameraId.value
    );
    const nextIndex = (currentIndex + 1) % availableCameras.value.length;
    selectedCameraId.value = availableCameras.value[nextIndex].deviceId;
    
    if (isScanning.value) {
        await startCamera();
    }
};

// Lifecycle
onMounted(() => {
    fetchCameras();
});

onUnmounted(() => {
    stopCamera();
    if (uploadedImageUrl.value) {
        URL.revokeObjectURL(uploadedImageUrl.value);
    }
});

// Reset scan state (call after successful API response to allow immediate next scan)
const resetScan = () => {
    lastScannedCode.value = null;
    isProcessing.value = false;
    if (scanCooldownTimer.value) {
        clearTimeout(scanCooldownTimer.value);
        scanCooldownTimer.value = null;
    }
};

defineExpose({ startCamera, stopCamera, resetScan });
</script>

<template>
    <div class="flex w-full flex-col items-center gap-4">
        <!-- Mode Toggle -->
        <div class="flex w-full max-w-md rounded-lg border bg-muted/30 p-1">
            <button
                @click="scanMode = 'camera'; clearUploadedImage()"
                :class="[
                    'flex flex-1 items-center justify-center gap-2 rounded-md px-3 py-2 text-sm font-medium transition-colors',
                    scanMode === 'camera' 
                        ? 'bg-background shadow-sm' 
                        : 'hover:bg-muted'
                ]"
            >
                <Video class="h-4 w-4" />
                Camera
            </button>
            <button
                @click="scanMode = 'upload'; stopCamera()"
                :class="[
                    'flex flex-1 items-center justify-center gap-2 rounded-md px-3 py-2 text-sm font-medium transition-colors',
                    scanMode === 'upload' 
                        ? 'bg-background shadow-sm' 
                        : 'hover:bg-muted'
                ]"
            >
                <ImagePlus class="h-4 w-4" />
                Upload Image
            </button>
        </div>

        <!-- Camera Selector -->
        <div
            v-if="scanMode === 'camera' && availableCameras.length > 1"
            class="flex w-full max-w-md items-center gap-2"
        >
            <Camera class="h-4 w-4 text-muted-foreground" />
            <select
                v-model="selectedCameraId"
                @change="isScanning && startCamera()"
                class="flex-1 rounded-md border bg-background px-3 py-2 text-sm"
                :disabled="isLoading"
            >
                <option
                    v-for="camera in availableCameras"
                    :key="camera.deviceId"
                    :value="camera.deviceId"
                >
                    {{ camera.label || `Camera ${camera.deviceId.slice(0, 8)}` }}
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

        <!-- Camera Mode -->
        <div v-if="scanMode === 'camera'" class="w-full max-w-md">
            <!-- Video element -->
            <div class="relative overflow-hidden rounded-lg border border-gray-700 bg-black">
                <video
                    ref="videoRef"
                    class="w-full"
                    style="min-height: 300px; object-fit: cover;"
                    playsinline
                    muted
                ></video>
                
                <!-- Detection overlay (canvas renders on top) -->
                <canvas
                    ref="canvasRef"
                    class="pointer-events-none absolute inset-0 h-full w-full"
                    style="display: none;"
                ></canvas>
                
                <!-- Scanning indicator -->
                <div
                    v-if="isScanning"
                    class="absolute bottom-3 left-3 flex items-center gap-2 rounded-full bg-black/70 px-3 py-1.5 text-xs text-white"
                >
                    <span class="h-2 w-2 animate-pulse rounded-full bg-green-500"></span>
                    Scanning...
                </div>
            </div>

            <!-- Camera Controls -->
            <div class="mt-3 flex flex-wrap justify-center gap-2">
                <Button
                    v-if="!isScanning"
                    @click="startCamera"
                    :disabled="isLoading"
                >
                    <Camera v-if="!isLoading" class="mr-2 h-4 w-4" />
                    <RefreshCw v-else class="mr-2 h-4 w-4 animate-spin" />
                    {{ isLoading ? 'Starting...' : 'Start Camera' }}
                </Button>
                <template v-else>
                    <Button variant="secondary" @click="takeSnapshot">
                        <Camera class="mr-2 h-4 w-4" />
                        Capture & Scan
                    </Button>
                    <Button variant="destructive" @click="stopCamera">
                        Stop Camera
                    </Button>
                </template>
            </div>
        </div>

        <!-- Upload Mode -->
        <div v-else class="w-full max-w-md">
            <!-- Upload area -->
            <div
                v-if="!uploadedImageUrl"
                @click="fileInputRef?.click()"
                @dragover.prevent
                @drop.prevent="(e) => { if (e.dataTransfer?.files[0]) { const dt = new DataTransfer(); dt.items.add(e.dataTransfer.files[0]); fileInputRef!.files = dt.files; handleFileUpload({ target: fileInputRef } as any); } }"
                class="flex min-h-[300px] cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-600 bg-muted/30 transition-colors hover:border-primary hover:bg-muted/50"
            >
                <ImagePlus class="mb-3 h-12 w-12 text-muted-foreground" />
                <p class="text-sm font-medium">Click to upload or drag image</p>
                <p class="mt-1 text-xs text-muted-foreground">PNG, JPG up to 10MB</p>
            </div>

            <!-- Uploaded image preview -->
            <div v-else class="relative">
                <img
                    :src="uploadedImageUrl"
                    class="w-full rounded-lg border"
                    alt="Uploaded QR code"
                />
                <button
                    @click="clearUploadedImage"
                    class="absolute right-2 top-2 rounded-full bg-black/70 p-1.5 text-white hover:bg-black"
                >
                    <X class="h-4 w-4" />
                </button>
            </div>

            <input
                ref="fileInputRef"
                type="file"
                accept="image/*"
                class="hidden"
                @change="handleFileUpload"
            />

            <div class="mt-3 flex justify-center">
                <Button @click="fileInputRef?.click()">
                    <ImagePlus class="mr-2 h-4 w-4" />
                    {{ uploadedImageUrl ? 'Upload Another' : 'Select Image' }}
                </Button>
            </div>
        </div>

        <!-- Success indicator -->
        <div
            v-if="lastScannedCode"
            class="flex w-full max-w-md items-center gap-2 rounded-md bg-green-50 p-3 text-sm text-green-700 dark:bg-green-900/20 dark:text-green-300"
        >
            <Camera class="h-4 w-4 flex-shrink-0" />
            <span class="font-medium">QR Code detected!</span>
        </div>

        <!-- Error message -->
        <div
            v-if="errorMessage"
            class="flex max-w-md items-start gap-2 rounded-md bg-red-50 p-3 text-sm text-red-600 dark:bg-red-900/20 dark:text-red-400"
        >
            <AlertCircle class="mt-0.5 h-4 w-4 flex-shrink-0" />
            <span>{{ errorMessage }}</span>
        </div>

        <!-- Tips -->
        <div class="w-full max-w-md rounded-md bg-blue-50 p-3 text-xs text-blue-700 dark:bg-blue-900/20 dark:text-blue-300">
            <p class="font-medium">Tips for better scanning:</p>
            <ul class="mt-1 list-inside list-disc space-y-0.5">
                <li>Hold QR code 15-30cm from camera</li>
                <li>Ensure good lighting (avoid shadows)</li>
                <li>Keep the QR code flat and steady</li>
                <li>Use "Capture & Scan" button if auto-detect isn't working</li>
                <li>Try uploading a photo if camera scanning fails</li>
            </ul>
        </div>
    </div>
</template>

<style scoped>
video {
    transform: scaleX(-1); /* Mirror the video for natural feel */
}
</style>

