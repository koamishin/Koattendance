<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import axios from 'axios';
import { Loader2, Printer } from 'lucide-vue-next';
import QrcodeVue from 'qrcode.vue';
import { ref, watch } from 'vue';

const props = defineProps<{
    subjectId: number;
}>();

const isOpen = ref(false);
const loading = ref(false);
const students = ref<any[]>([]);
const error = ref<string | null>(null);

const fetchStudentQrCodes = async () => {
    loading.value = true;
    error.value = null;
    try {
        const response = await axios.get(
            `/api/subjects/${props.subjectId}/students/qr-codes`,
        );
        students.value = response.data.students || [];
    } catch (e: any) {
        console.error('Failed to fetch QR codes', e);
        error.value = e.response?.data?.message || 'Failed to load QR codes';
    } finally {
        loading.value = false;
    }
};

watch(isOpen, (open) => {
    if (open) {
        fetchStudentQrCodes();
    }
});

const printAllQrCodes = () => {
    const printWindow = window.open('', '_blank');
    if (!printWindow) {
        alert('Please allow popups to print');
        return;
    }

    // Get all QR code canvases and convert to data URLs
    const qrCards = students.value
        .map((student, index) => {
            const canvas = document.querySelector(
                `#qr-canvas-${index} canvas`,
            ) as HTMLCanvasElement;
            const dataUrl = canvas?.toDataURL('image/png') || '';
            return `
                <div class="qr-card">
                    <h3>${student.name}</h3>
                    <p class="student-id">ID: ${student.student_id}</p>
                    <div class="qr-container">
                        <img src="${dataUrl}" alt="QR Code" width="150" height="150" />
                    </div>
                </div>
            `;
        })
        .join('');

    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Student QR Codes</title>
            <style>
                * {
                    box-sizing: border-box;
                }
                body {
                    font-family: Arial, sans-serif;
                    margin: 0;
                    padding: 20px;
                    background: white;
                }
                .header {
                    text-align: center;
                    margin-bottom: 30px;
                    page-break-after: avoid;
                }
                .header h1 {
                    margin: 0;
                    font-size: 24px;
                }
                .header p {
                    margin: 5px 0 0 0;
                    color: #666;
                }
                .grid {
                    display: grid;
                    grid-template-columns: repeat(3, 1fr);
                    gap: 20px;
                    max-width: 900px;
                    margin: 0 auto;
                }
                .qr-card {
                    text-align: center;
                    padding: 15px;
                    border: 2px solid #333;
                    border-radius: 8px;
                    background: white;
                    page-break-inside: avoid;
                }
                .qr-card h3 {
                    margin: 0 0 3px 0;
                    font-size: 14px;
                    color: #333;
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                }
                .qr-card .student-id {
                    margin: 0 0 10px 0;
                    font-size: 11px;
                    color: #666;
                }
                .qr-card .qr-container {
                    display: inline-block;
                    padding: 8px;
                    background: white;
                    border: 1px solid #ddd;
                    border-radius: 6px;
                }
                @media print {
                    body { 
                        background: white; 
                        padding: 10px;
                    }
                    .grid {
                        gap: 15px;
                    }
                    .qr-card { 
                        border: 2px solid #000;
                        padding: 10px;
                    }
                    @page {
                        margin: 0.5in;
                    }
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>Student QR Codes</h1>
                <p>${students.value.length} students</p>
            </div>
            <div class="grid">
                ${qrCards}
            </div>
            <script>
                window.onload = function() {
                    setTimeout(function() {
                        window.print();
                        window.onafterprint = function() {
                            window.close();
                        };
                    }, 500);
                };
            <\/script>
        </body>
        </html>
    `);
    printWindow.document.close();
};
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogTrigger as-child>
            <Button variant="outline" class="gap-2">
                <Printer class="h-4 w-4" />
                <span class="hidden sm:inline">Print All QR</span>
                <span class="sm:hidden">Print</span>
            </Button>
        </DialogTrigger>
        <DialogContent class="max-h-[90vh] overflow-y-auto sm:max-w-4xl">
            <DialogHeader>
                <DialogTitle>Print All QR Codes</DialogTitle>
                <DialogDescription>
                    Preview and print QR codes for all students in this class.
                </DialogDescription>
            </DialogHeader>

            <div v-if="loading" class="flex h-48 items-center justify-center">
                <Loader2 class="h-8 w-8 animate-spin text-muted-foreground" />
            </div>

            <div
                v-else-if="error"
                class="flex h-48 flex-col items-center justify-center gap-2 text-center"
            >
                <p class="text-destructive">{{ error }}</p>
                <Button
                    variant="outline"
                    size="sm"
                    @click="fetchStudentQrCodes"
                >
                    Retry
                </Button>
            </div>

            <div
                v-else-if="students.length === 0"
                class="flex h-48 items-center justify-center text-center text-muted-foreground"
            >
                No students enrolled in this class.
            </div>

            <div v-else class="space-y-4">
                <p class="text-sm text-muted-foreground">
                    {{ students.length }} student(s) will be printed (3 per row)
                </p>

                <!-- Preview Grid -->
                <div
                    class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4"
                >
                    <div
                        v-for="(student, index) in students"
                        :key="student.id"
                        class="flex flex-col items-center rounded-lg border bg-card p-3 text-center"
                    >
                        <p
                            class="mb-1 w-full truncate text-sm font-medium"
                            :title="student.name"
                        >
                            {{ student.name }}
                        </p>
                        <p class="mb-2 text-xs text-muted-foreground">
                            {{ student.student_id }}
                        </p>
                        <div
                            :id="`qr-canvas-${index}`"
                            class="rounded bg-white p-2"
                        >
                            <QrcodeVue
                                :value="student.qr_code || ''"
                                :size="80"
                                level="H"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <DialogFooter>
                <Button variant="outline" @click="isOpen = false">
                    Cancel
                </Button>
                <Button
                    @click="printAllQrCodes"
                    :disabled="loading || students.length === 0"
                    class="gap-2"
                >
                    <Printer class="h-4 w-4" />
                    Print All ({{ students.length }})
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
