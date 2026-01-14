<script setup lang="ts">
import { Button } from '@/components/ui/button';
import axios from 'axios';
import { Download, Loader2, Printer, RefreshCw } from 'lucide-vue-next';
import QrcodeVue from 'qrcode.vue';
import { onMounted, ref } from 'vue';

const props = defineProps<{
    studentId: number | string;
    studentName?: string;
    studentCode?: string;
}>();

const qrData = ref<string | null>(null);
const studentInfo = ref<{ name: string; student_id: string } | null>(null);
const loading = ref(true);
const regenerating = ref(false);
const error = ref<string | null>(null);

const fetchQrCode = async () => {
    loading.value = true;
    error.value = null;
    try {
        const response = await axios.get(
            `/api/students/${props.studentId}/qr-code`,
        );
        qrData.value = response.data.qr_code;
        studentInfo.value = {
            name: response.data.name,
            student_id: response.data.student_id,
        };
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
            'Are you sure you want to regenerate the QR code? The old one will stop working.',
        )
    ) {
        return;
    }

    regenerating.value = true;
    try {
        const response = await axios.post(
            `/api/students/${props.studentId}/regenerate-qr`,
        );
        qrData.value = response.data.qr_code;
    } catch (e) {
        alert('Failed to regenerate QR code');
    } finally {
        regenerating.value = false;
    }
};

const printQrCode = () => {
    const printWindow = window.open('', '_blank');
    if (!printWindow) {
        alert('Please allow popups to print');
        return;
    }

    const name = studentInfo.value?.name || props.studentName || 'Student';
    const studentId = studentInfo.value?.student_id || props.studentCode || '';

    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>QR Code - ${name}</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    min-height: 100vh;
                    margin: 0;
                    background: white;
                }
                .qr-card {
                    text-align: center;
                    padding: 30px;
                    border: 2px solid #333;
                    border-radius: 12px;
                    background: white;
                }
                .qr-card h2 {
                    margin: 0 0 5px 0;
                    font-size: 24px;
                    color: #333;
                }
                .qr-card .student-id {
                    margin: 0 0 20px 0;
                    font-size: 14px;
                    color: #666;
                }
                .qr-card .qr-container {
                    display: inline-block;
                    padding: 15px;
                    background: white;
                    border: 1px solid #ddd;
                    border-radius: 8px;
                }
                .qr-card .instructions {
                    margin-top: 15px;
                    font-size: 12px;
                    color: #888;
                }
                @media print {
                    body { background: white; }
                    .qr-card { border: 2px solid #000; }
                }
            </style>
        </head>
        <body>
            <div class="qr-card">
                <h2>${name}</h2>
                <p class="student-id">ID: ${studentId}</p>
                <div class="qr-container">
                    <img src="${getQrCodeDataUrl()}" alt="QR Code" width="200" height="200" />
                </div>
                <p class="instructions">Scan this QR code to mark attendance</p>
            </div>
            <script>
                window.onload = function() {
                    window.print();
                    window.onafterprint = function() {
                        window.close();
                    };
                };
            <\/script>
        </body>
        </html>
    `);
    printWindow.document.close();
};

const getQrCodeDataUrl = (): string => {
    const canvas = document.querySelector(
        '.qr-code-canvas canvas',
    ) as HTMLCanvasElement;
    return canvas?.toDataURL('image/png') || '';
};

const downloadQrCode = () => {
    const canvas = document.querySelector(
        '.qr-code-canvas canvas',
    ) as HTMLCanvasElement;
    if (!canvas) return;

    const name = studentInfo.value?.name || props.studentName || 'Student';
    const link = document.createElement('a');
    link.download = `qr-code-${name.replace(/\s+/g, '-').toLowerCase()}.png`;
    link.href = canvas.toDataURL('image/png');
    link.click();
};

onMounted(() => {
    fetchQrCode();
});
</script>

<template>
    <div
        class="flex flex-col items-center justify-center rounded-lg bg-card p-6"
    >
        <h3 class="mb-4 text-lg font-semibold">Student QR Code</h3>

        <div
            v-if="loading"
            class="flex h-48 w-48 items-center justify-center rounded-lg bg-muted"
        >
            <Loader2 class="h-8 w-8 animate-spin text-muted-foreground" />
        </div>

        <div v-else-if="error" class="text-center text-sm text-destructive">
            {{ error }}
            <Button variant="ghost" size="sm" @click="fetchQrCode" class="mt-2">
                Retry
            </Button>
        </div>

        <div v-else class="flex flex-col items-center gap-4">
            <!-- Student Info -->
            <div class="text-center">
                <p class="font-medium">
                    {{ studentInfo?.name || studentName }}
                </p>
                <p class="text-sm text-muted-foreground">
                    ID: {{ studentInfo?.student_id || studentCode }}
                </p>
            </div>

            <!-- QR Code -->
            <div class="qr-code-canvas rounded-xl bg-white p-4 shadow-md">
                <QrcodeVue :value="qrData || ''" :size="200" level="H" />
            </div>

            <p class="max-w-[250px] text-center text-sm text-muted-foreground">
                Scan this QR code to mark attendance.
            </p>

            <!-- Actions -->
            <div class="flex flex-wrap justify-center gap-2">
                <Button variant="outline" size="sm" @click="printQrCode">
                    <Printer class="mr-2 h-4 w-4" />
                    Print
                </Button>
                <Button variant="outline" size="sm" @click="downloadQrCode">
                    <Download class="mr-2 h-4 w-4" />
                    Download
                </Button>
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
    </div>
</template>
