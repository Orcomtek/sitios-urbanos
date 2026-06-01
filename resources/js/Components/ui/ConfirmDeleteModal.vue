<script setup lang="ts">
import Modal from '@/Components/Modal.vue';
import { ExclamationTriangleIcon } from '@heroicons/vue/24/outline';

defineProps<{
    show: boolean;
    title?: string;
    message?: string;
    processing?: boolean;
}>();

const emit = defineEmits(['close', 'confirm']);
</script>

<template>
    <Modal :show="show" @close="$emit('close')" maxWidth="md">
        <div class="px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
            <div class="sm:flex sm:items-start">
                <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                    <ExclamationTriangleIcon class="h-6 w-6 text-red-600" aria-hidden="true" />
                </div>
                <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                    <h3 class="text-base font-semibold leading-6 text-gray-900" id="modal-title">
                        {{ title || '¿Eliminar registro?' }}
                    </h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-500">
                            {{ message || 'Esta acción no se puede deshacer.' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
            <button
                type="button"
                class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600 sm:ml-3 sm:w-auto"
                :disabled="processing"
                @click="$emit('confirm')"
            >
                Eliminar
            </button>
            <button
                type="button"
                class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto"
                :disabled="processing"
                @click="$emit('close')"
            >
                Cancelar
            </button>
        </div>
    </Modal>
</template>
