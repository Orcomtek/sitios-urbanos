<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';

/**
 * RestrictionModal — Loss Aversion CRO Modal
 *
 * Shown when a resident clicks a restricted module.
 * Uses Loss Aversion psychological framing to motivate payment:
 *  - Shows the total amount overdue and oldest due date
 *  - CTA links to the Financial Statement page for context before checkout
 *  - Module remains visible (grayscale) — not hidden — per legal requirement
 */
const props = defineProps<{
    show: boolean;
    totalOverdue?: number;
    oldestDueDate?: string | null;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const page = usePage();
const communitySlug = computed(() => (page.props.tenant as any)?.community?.slug);

const formattedAmount = computed(() => {
    if (!props.totalOverdue) return '—';
    return new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(props.totalOverdue);
});

const formattedDate = computed(() => {
    if (!props.oldestDueDate) return null;
    return new Date(props.oldestDueDate + 'T00:00:00').toLocaleDateString('es-CO', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
});

const statementUrl = computed(() => {
    if (!communitySlug.value) return '#';
    try {
        // @ts-ignore
        return route('tenant.resident.financial.statement.index', { community_slug: communitySlug.value });
    } catch {
        return '#';
    }
});
</script>

<template>
    <Modal :show="show" max-width="md" @close="emit('close')">
        <div class="p-6">
            <!-- Icon -->
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-red-100 mb-4">
                <svg class="h-7 w-7 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
            </div>

            <!-- Heading (Loss Aversion framing) -->
            <h3 class="text-center text-lg font-semibold text-gray-900 mb-1">
                Acceso restringido por obligaciones pendientes
            </h3>
            <p class="text-center text-sm text-gray-500 mb-5">
                Tu unidad tiene cuotas de administración sin pagar que bloquean el acceso a este módulo.
            </p>

            <!-- Overdue summary card -->
            <div class="rounded-lg bg-red-50 border border-red-200 p-4 mb-5 space-y-2">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600">Total en mora:</span>
                    <span class="font-bold text-red-700 text-base">{{ formattedAmount }}</span>
                </div>
                <div v-if="formattedDate" class="flex items-center justify-between text-sm">
                    <span class="text-gray-600">Vencimiento más antiguo:</span>
                    <span class="font-medium text-red-600">{{ formattedDate }}</span>
                </div>
                <p class="text-xs text-gray-500 pt-1 border-t border-red-200">
                    Cada día sin pagar puede generar intereses por mora y limitar tu acceso a más servicios de la comunidad.
                </p>
            </div>

            <!-- CTA buttons -->
            <div class="space-y-2">
                <Link
                    :href="statementUrl"
                    class="block w-full text-center rounded-md bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600"
                    @click="emit('close')"
                >
                    Ver mis finanzas y pagar
                </Link>
                <button
                    type="button"
                    class="block w-full text-center rounded-md bg-white px-4 py-2.5 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition-colors"
                    @click="emit('close')"
                >
                    Cancelar
                </button>
            </div>
        </div>
    </Modal>
</template>
