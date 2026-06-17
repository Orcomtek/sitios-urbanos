<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, computed, watch } from 'vue';
import { WalletIcon, DocumentTextIcon } from '@heroicons/vue/24/outline';

const props = defineProps<{
    units: {
        id: number;
        identifier: string;
        property_type: string;
        net_balance: number;
        invoices?: any[];
    }[];
    billing_concepts: {
        id: number;
        name: string;
        type: string;
    }[];
}>();

const propertyTypesMap: Record<string, string> = {
    'apartment': 'Apartamento',
    'house': 'Casa',
    'commercial': 'Local Comercial',
    'office': 'Oficina',
    'parking': 'Parqueadero',
    'storage': 'Depósito'
};

// Form and Modal State for Payment
const isPaymentModalOpen = ref(false);
const selectedUnitForPayment = ref<number | null>(null);
const selectedUnit = computed(() => props.units.find(u => u.id === selectedUnitForPayment.value));
const paymentForm = useForm({
    amount: '',
    payment_method: '',
    reference_number: '',
    invoice_id: '' as string | null,
    notes: '',
});

// Form and Modal State for Adjustment
const isAdjustmentModalOpen = ref(false);
const selectedUnitForAdjustment = ref<number | null>(null);
const adjustmentForm = useForm({
    amount: '',
    type: '',
    billing_concept_id: '',
    description: '',
});

const filteredBillingConcepts = computed(() => {
    if (adjustmentForm.type === 'credit') {
        return props.billing_concepts.filter(c => c.type === 'discount');
    } else if (adjustmentForm.type === 'debit') {
        return props.billing_concepts.filter(c => c.type !== 'discount');
    }
    return [];
});

watch(() => adjustmentForm.type, () => {
    adjustmentForm.billing_concept_id = '';
});

const searchQuery = ref('');
const statusFilter = ref('all');

const filteredUnits = computed(() => {
    return props.units.filter(unit => {
        if (searchQuery.value && !unit.identifier.toLowerCase().includes(searchQuery.value.toLowerCase())) {
            return false;
        }
        if (statusFilter.value === 'arrears' && unit.net_balance <= 0) return false;
        if (statusFilter.value === 'settled' && unit.net_balance > 0) return false;
        return true;
    });
});

const parseCurrency = (val: string) => {
    let clean = val.replace(/[^\d,]/g, '');
    clean = clean.replace(',', '.');
    return clean;
};

const formatCurrency = (val: string | number) => {
    if (!val) return '';
    const parts = val.toString().split('.');
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    return parts.join(',');
};

const formattedPaymentAmount = computed({
    get: () => formatCurrency(paymentForm.amount),
    set: (val: string) => {
        paymentForm.amount = parseCurrency(val);
    }
});

const formattedAdjustmentAmount = computed({
    get: () => formatCurrency(adjustmentForm.amount),
    set: (val: string) => {
        adjustmentForm.amount = parseCurrency(val);
    }
});

const openPaymentModal = (unitId: number) => {
    selectedUnitForPayment.value = unitId;
    paymentForm.reset();
    isPaymentModalOpen.value = true;
};

const openAdjustmentModal = (unitId: number) => {
    selectedUnitForAdjustment.value = unitId;
    adjustmentForm.reset();
    isAdjustmentModalOpen.value = true;
};

const submitPayment = () => {
    if (!selectedUnitForPayment.value) return;
    paymentForm.post(route('tenant.admin.financial.ledger.payment.store', { 
        // @ts-ignore
        community_slug: route().params.community_slug, 
        unit: selectedUnitForPayment.value 
    }), {
        onSuccess: () => {
            paymentForm.reset();
            isPaymentModalOpen.value = false;
        },
    });
};

const submitAdjustment = () => {
    if (!selectedUnitForAdjustment.value) return;
    adjustmentForm.post(route('tenant.admin.financial.ledger.adjustment.store', { 
        // @ts-ignore
        community_slug: route().params.community_slug, 
        unit: selectedUnitForAdjustment.value 
    }), {
        onSuccess: () => {
            adjustmentForm.reset();
            isAdjustmentModalOpen.value = false;
        },
    });
};
</script>

<template>
    <Head title="Libro Mayor" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Libro Mayor Financiero</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="mb-6 flex flex-col sm:flex-row gap-4 items-center justify-between">
                    <div class="flex gap-4 w-full sm:w-auto">
                        <input v-model="searchQuery" type="search" placeholder="Buscar por unidad..." class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm flex-1 sm:w-64">
                        <select v-model="statusFilter" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="all">Todas</option>
                            <option value="arrears">En Mora</option>
                            <option value="settled">Al Día</option>
                        </select>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unidad</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Balance Neto</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="unit in filteredUnits" :key="unit.id">
                                <td class="px-6 py-4 whitespace-nowrap">{{ unit.identifier }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ propertyTypesMap[unit.property_type] || unit.property_type }}</td>
                                <td class="px-6 py-4 whitespace-nowrap font-bold"
                                    :class="{
                                        'text-red-600': unit.net_balance > 0,
                                        'text-green-600': unit.net_balance <= 0
                                    }">
                                    $ {{ parseFloat(unit.net_balance.toString()).toLocaleString('es-CO') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button class="relative group text-gray-400 hover:text-gray-600 transition mr-4" aria-label="Registrar Pago" @click="openPaymentModal(unit.id)">
                                        <WalletIcon class="w-5 h-5 inline" />
                                        <span class="absolute bottom-full mb-2 hidden group-hover:block whitespace-nowrap bg-gray-800 text-white text-xs rounded py-1 px-2 left-1/2 -translate-x-1/2">Registrar Pago</span>
                                    </button>
                                    <button class="relative group text-gray-400 hover:text-gray-600 transition" aria-label="Nota Contable" @click="openAdjustmentModal(unit.id)">
                                        <DocumentTextIcon class="w-5 h-5 inline" />
                                        <span class="absolute bottom-full mb-2 hidden group-hover:block whitespace-nowrap bg-gray-800 text-white text-xs rounded py-1 px-2 left-1/2 -translate-x-1/2">Nota Contable</span>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Payment Modal -->
        <div v-if="isPaymentModalOpen" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="isPaymentModalOpen = false"></div>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form @submit.prevent="submitPayment">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Registrar Pago Manual</h3>
                            
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Monto</label>
                                <input v-model="formattedPaymentAmount" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                                <div v-if="paymentForm.errors.amount" class="text-red-500 text-sm mt-1">{{ paymentForm.errors.amount }}</div>
                            </div>

                            <div class="mb-4">
                                <label for="invoice_id" class="block text-sm font-medium text-gray-700">Destino del Pago</label>
                                <select id="invoice_id" v-model="paymentForm.invoice_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                                    <option value="" disabled selected>Seleccione el destino del dinero...</option>
                                    <option :value="null">Abono General al Apartamento (Sin factura)</option>
                                    <option v-for="invoice in selectedUnit?.invoices" :key="invoice.id" :value="invoice.id">
                                        Factura #{{ invoice.id.substring(0,8) }} - {{ invoice.billing_period || '-' }} ($ {{ parseFloat(invoice.total || invoice.amount).toLocaleString('es-CO') }})
                                    </option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Método de Pago</label>
                                <select v-model="paymentForm.payment_method" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                                    <option value="" disabled selected>Seleccione un método...</option>
                                    <option value="cash">Efectivo</option>
                                    <option value="bank_transfer">Transferencia Bancaria</option>
                                    <option value="check">Cheque</option>
                                    <option value="pos_terminal">Datáfono en Oficina</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Número de Referencia</label>
                                <input v-model="paymentForm.reference_number" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Observaciones / Detalles del Banco</label>
                                <textarea v-model="paymentForm.notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                                <div v-if="paymentForm.errors.notes" class="text-red-500 text-sm mt-1">{{ paymentForm.errors.notes }}</div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm" :disabled="paymentForm.processing">Guardar Pago</button>
                            <button type="button" @click="isPaymentModalOpen = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Adjustment Modal -->
        <div v-if="isAdjustmentModalOpen" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="isAdjustmentModalOpen = false"></div>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form @submit.prevent="submitAdjustment">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Emitir Nota Contable</h3>
                            
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Tipo</label>
                                <select v-model="adjustmentForm.type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" required>
                                    <option value="" disabled selected>Seleccione un tipo de nota...</option>
                                    <option value="credit">Nota de Crédito (A favor del residente)</option>
                                    <option value="debit">Nota de Débito (Deuda para el residente)</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Monto</label>
                                <input v-model="formattedAdjustmentAmount" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" required>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Concepto de Facturación</label>
                                <select v-model="adjustmentForm.billing_concept_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" required>
                                    <option value="" disabled selected>Seleccione un concepto...</option>
                                    <option v-for="concept in filteredBillingConcepts" :key="concept.id" :value="concept.id">
                                        {{ concept.name }}
                                    </option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Descripción / Justificación</label>
                                <textarea v-model="adjustmentForm.description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" required></textarea>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm" :disabled="adjustmentForm.processing">Guardar Nota</button>
                            <button type="button" @click="isAdjustmentModalOpen = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
