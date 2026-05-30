<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { ref, onUnmounted, computed } from 'vue';
import axios from 'axios';
import { useToast } from '@/Composables/useToast';

const { show: showToast } = useToast();

const page = usePage();
const communitySlug = (page.props.tenant as any)?.community?.slug;

const fileInput = ref<HTMLInputElement | null>(null);
const file = ref<File | null>(null);
const importType = ref<string>('residents');

const isUploading = ref(false);
const importRecord = ref<any>(null);
let pollingInterval: ReturnType<typeof setInterval> | null = null;

const progressPercentage = computed(() => {
    if (!importRecord.value || importRecord.value.total_rows === 0) return 0;
    const total = importRecord.value.total_rows;
    const processed = importRecord.value.processed_rows;
    const failed = importRecord.value.failed_rows;
    return Math.min(Math.round(((processed + failed) / total) * 100), 100);
});

const isProcessing = computed(() => {
    return importRecord.value && (importRecord.value.status === 'pending' || importRecord.value.status === 'processing');
});

const isCompleted = computed(() => {
    return importRecord.value && importRecord.value.status === 'completed';
});

const isFailed = computed(() => {
    return importRecord.value && importRecord.value.status === 'failed';
});

const handleFileDrop = (e: DragEvent) => {
    e.preventDefault();
    const droppedFiles = e.dataTransfer?.files;
    if (droppedFiles && droppedFiles.length > 0) {
        if (droppedFiles[0].type === 'text/csv' || droppedFiles[0].name.endsWith('.csv')) {
            file.value = droppedFiles[0];
        } else {
            showToast('Por favor, selecciona un archivo CSV.', 'error');
        }
    }
};

const handleFileSelect = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        file.value = target.files[0];
    }
};

const triggerFileInput = () => {
    fileInput.value?.click();
};

const startPolling = (importId: number) => {
    if (pollingInterval) clearInterval(pollingInterval);
    
    pollingInterval = setInterval(async () => {
        try {
            // @ts-ignore
            const response = await axios.get(route('tenant.admin.core.imports.show', { community_slug: communitySlug, import: importId }));
            importRecord.value = response.data.import;

            if (importRecord.value.status === 'completed' || importRecord.value.status === 'failed') {
                clearInterval(pollingInterval!);
                pollingInterval = null;
                isUploading.value = false;
                file.value = null; // Reset file selection
            }
        } catch (error) {
            console.error('Error polling import status:', error);
            clearInterval(pollingInterval!);
            pollingInterval = null;
            isUploading.value = false;
        }
    }, 3000);
};

const uploadFile = async () => {
    if (!file.value) return;

    isUploading.value = true;
    importRecord.value = null;

    const formData = new FormData();
    formData.append('file', file.value);
    formData.append('type', importType.value);

    try {
        // @ts-ignore
        const response = await axios.post(route('tenant.admin.core.imports.store', { community_slug: communitySlug }), formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });
        
        importRecord.value = response.data.import;
        startPolling(importRecord.value.id);
    } catch (error: any) {
        console.error('Upload failed:', error);
        showToast(error.response?.data?.message || 'Error al subir el archivo.', 'error');
        isUploading.value = false;
    }
};

onUnmounted(() => {
    if (pollingInterval) {
        clearInterval(pollingInterval);
    }
});

const formatErrorRows = (errors: any[]) => {
    if (!errors) return [];
    // Group general errors if they exist, else just return the array
    return errors;
};
</script>

<template>
    <Head title="Importar Datos" />

    <AppLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 px-4 sm:px-6 lg:px-8 py-4">
                Importación Masiva de Datos
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <!-- Upload Section -->
                <div class="p-6 bg-white shadow-sm sm:rounded-lg border border-gray-100">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Cargar Archivo CSV</h3>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Importación</label>
                        <select v-model="importType" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm rounded-md" :disabled="isUploading || isProcessing">
                            <option value="residents">Residentes</option>
                            <option value="balances">Saldos</option>
                            <option value="units">Unidades</option>
                        </select>
                    </div>

                    <div 
                        class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md transition-colors"
                        :class="{ 'bg-gray-50 border-emerald-400': file, 'hover:border-emerald-500 hover:bg-gray-50': !isUploading && !isProcessing, 'opacity-50 cursor-not-allowed': isUploading || isProcessing }"
                        @dragover.prevent
                        @drop="!isUploading && !isProcessing && handleFileDrop($event)"
                        @click="!isUploading && !isProcessing && triggerFileInput()"
                    >
                        <div class="space-y-1 text-center cursor-pointer">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-gray-600 justify-center">
                                <label class="relative cursor-pointer bg-white rounded-md font-medium text-emerald-600 hover:text-emerald-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-emerald-500" @click.stop>
                                    <span>Sube un archivo</span>
                                    <input ref="fileInput" type="file" class="sr-only" accept=".csv" @change="handleFileSelect" :disabled="isUploading || isProcessing">
                                </label>
                                <p class="pl-1">o arrastra y suelta aquí</p>
                            </div>
                            <p class="text-xs text-gray-500">CSV hasta 10MB</p>
                            <p v-if="file" class="mt-2 text-sm font-semibold text-emerald-600">{{ file.name }} ({{ (file.size / 1024 / 1024).toFixed(2) }} MB)</p>
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end">
                        <button 
                            @click="uploadFile" 
                            :disabled="!file || isUploading || isProcessing"
                            class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 disabled:opacity-50"
                        >
                            {{ isUploading ? 'Subiendo...' : 'Iniciar Importación' }}
                        </button>
                    </div>
                </div>

                <!-- Progress Section -->
                <div v-if="importRecord" class="p-6 bg-white shadow-sm sm:rounded-lg border border-gray-100">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Progreso de la Importación</h3>
                    
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700">Estado: <span class="capitalize font-semibold" :class="{'text-emerald-600': isCompleted, 'text-red-600': isFailed, 'text-amber-600': isProcessing}">{{ importRecord.status }}</span></span>
                        <span class="text-sm font-medium text-gray-700">{{ progressPercentage }}%</span>
                    </div>
                    
                    <div class="w-full bg-gray-200 rounded-full h-2.5 mb-4 overflow-hidden">
                        <div class="bg-emerald-600 h-2.5 rounded-full transition-all duration-500 ease-out" :style="{ width: progressPercentage + '%' }"></div>
                    </div>

                    <div class="grid grid-cols-3 gap-4 text-center mt-4">
                        <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                            <div class="text-xs text-gray-500 uppercase">Total</div>
                            <div class="font-semibold text-lg text-gray-800">{{ importRecord.total_rows }}</div>
                        </div>
                        <div class="bg-emerald-50 p-3 rounded-lg border border-emerald-100">
                            <div class="text-xs text-emerald-600 uppercase">Procesados</div>
                            <div class="font-semibold text-lg text-emerald-700">{{ importRecord.processed_rows }}</div>
                        </div>
                        <div class="bg-red-50 p-3 rounded-lg border border-red-100">
                            <div class="text-xs text-red-600 uppercase">Fallidos</div>
                            <div class="font-semibold text-lg text-red-700">{{ importRecord.failed_rows }}</div>
                        </div>
                    </div>
                </div>

                <!-- Forensic Report Section -->
                <div v-if="(isCompleted || isFailed) && importRecord?.failed_rows > 0" class="p-6 bg-white shadow-sm sm:rounded-lg border border-red-200">
                    <h3 class="text-lg font-medium text-red-700 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        Reporte Forense (Errores)
                    </h3>
                    
                    <div v-if="importRecord.errors?.general" class="mb-4 p-4 bg-red-50 text-red-700 rounded-md border border-red-200">
                        <span class="font-bold">Error fatal:</span> {{ importRecord.errors.general }}
                    </div>

                    <div v-if="importRecord.errors && importRecord.errors.length > 0" class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 border border-gray-200 rounded-lg overflow-hidden">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">Fila</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Motivo del Error</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="(err, idx) in importRecord.errors" :key="idx" class="hover:bg-red-50/50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ err.row || '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        {{ err.error }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>
