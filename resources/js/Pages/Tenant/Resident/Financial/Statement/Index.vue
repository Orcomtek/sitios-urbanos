<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { DocumentArrowDownIcon, LockClosedIcon } from '@heroicons/vue/24/outline';
import { onMounted, ref } from 'vue';
import axios from 'axios';

const page = usePage<any>();

const props = defineProps<{
    unit: {
        id: number;
        identifier: string;
        net_balance: number;
        invoices: any[];
        payments: any[];
        financial_adjustments: any[];
    };
    epaycoConfig: {
        public_key: string;
        testing: boolean;
    };
}>();

const statusMap: Record<string, string> = {
    pending: 'Pendiente',
    confirmed: 'Confirmado',
    failed: 'Fallido',
    refunded: 'Reembolsado',
};

const adjustmentTypeMap: Record<string, string> = {
    credit: 'A favor',
    debit: 'Cargo extra',
};

// --- ePayco SDK ---
onMounted(() => {
    const script = document.createElement('script');
    script.src = 'https://checkout.epayco.co/checkout.js';
    script.async = true;
    document.head.appendChild(script);
});

// --- Payment Modal ---
const showPaymentModal = ref(false);
const selectedInvoice = ref<any>(null);
const isProcessingPayment = ref(false);
const paymentError = ref<string | null>(null);

function openPaymentModal(invoice: any) {
    selectedInvoice.value = invoice;
    paymentError.value = null;
    showPaymentModal.value = true;
}

function closePaymentModal() {
    showPaymentModal.value = false;
    selectedInvoice.value = null;
    paymentError.value = null;
}

async function proceedToPayment() {
    if (!selectedInvoice.value) {
        return;
    }

    isProcessingPayment.value = true;
    paymentError.value = null;

    try {
        const response = await axios.post(
            `/api/finance/invoices/${selectedInvoice.value.id}/pay`
        );

        const paymentIntent = response.data.data;

        const handler = (window as any).ePayco.checkout.configure({
            key: props.epaycoConfig.public_key,
            test: props.epaycoConfig.testing,
        });

        const openParams: Record<string, any> = {
            name: `Factura ${selectedInvoice.value.invoice_number ? '#' + selectedInvoice.value.invoice_number : '#' + selectedInvoice.value.id.substring(0, 8)}`,
            description: `Pago de administración — Período: ${selectedInvoice.value.billing_period || 'N/A'}`,
            invoice: paymentIntent.id,
            currency: 'cop',
            amount: parseFloat(paymentIntent.amount).toFixed(2),
            tax_base: '0',
            tax: '0',
            country: 'co',
            lang: 'es',
            external: 'false',
            response: window.location.href,
            confirmation: window.location.href,
        };

        // Inject split params when available
        if (paymentIntent.gateway?.split) {
            const split = paymentIntent.gateway.split;
            openParams.splitpayment = split.splitpayment;
            openParams.split_app_id = split.split_app_id;
            openParams.split_merchant_id = split.split_merchant_id;
            openParams.split_type = split.split_type;
            openParams.split_primary_receiver = split.split_primary_receiver;
            openParams.split_primary_receiver_fee = split.split_primary_receiver_fee;
        }

        handler.open(openParams);
        closePaymentModal();
    } catch (error: any) {
        const message = error?.response?.data?.message
            || 'Ocurrió un error al preparar el pago. Intente de nuevo.';
        paymentError.value = message;
    } finally {
        isProcessingPayment.value = false;
    }
}
</script>

<template>
    <Head title="Estado de Cuenta" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Estado de Cuenta Financiero</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Balance Widget -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Unidad {{ props.unit.identifier }}</h3>
                        <p class="text-sm text-gray-500">Balance Neto Actual</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="text-3xl font-bold" :class="props.unit.net_balance > 0 ? 'text-red-600' : 'text-green-600'">
                            $ {{ parseFloat(props.unit.net_balance.toString()).toLocaleString('es-CO') }}
                        </div>
                        <a
                            v-if="props.unit.net_balance <= 0"
                            :href="route('tenant.resident.financial.statement.paz-y-salvo.download', { community_slug: page.props.tenant.community.slug })"
                            target="_blank"
                            class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 focus:bg-emerald-700 active:bg-emerald-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                        >
                            <DocumentArrowDownIcon class="w-5 h-5 mr-2" />
                            Descargar Paz y Salvo
                        </a>
                    </div>
                </div>

                <!-- Invoices -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Mis Facturas</h3>
                    <div v-if="props.unit.invoices.length === 0" class="text-gray-500 text-sm">No tienes facturas emitidas.</div>
                    <table v-else class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Factura</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Periodo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Saldo Pendiente</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="invoice in props.unit.invoices" :key="invoice.id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ invoice.invoice_number ? '#' + invoice.invoice_number : '#' + invoice.id.substring(0, 8) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ invoice.billing_period || '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ invoice.created_at_formatted || new Date(invoice.created_at).toLocaleDateString('es-CO') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                        :class="(invoice.status === 'paid' || props.unit.net_balance <= 0) ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'"
                                    >
                                        {{ (invoice.status === 'paid' || props.unit.net_balance <= 0) ? 'Pagada' : 'Pendiente' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right font-medium">
                                    $ {{ parseFloat(invoice.total || invoice.amount).toLocaleString('es-CO') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold" :class="invoice.pending_amount > 0 ? 'text-red-600' : 'text-green-600'">
                                    $ {{ parseFloat(invoice.pending_amount).toLocaleString('es-CO') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                    <div class="flex items-center justify-center gap-3">
                                        <!-- Pay Online button — only for pending invoices -->
                                        <button
                                            v-if="invoice.status === 'pending' && invoice.pending_amount > 0"
                                            @click="openPaymentModal(invoice)"
                                            class="inline-flex items-center px-3 py-1.5 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                        >
                                            Pagar en Línea
                                        </button>
                                        <!-- Download PDF -->
                                        <a
                                            :href="route('tenant.resident.financial.statement.invoice.download', { community_slug: page.props.tenant.community.slug, invoice: invoice.id })"
                                            target="_blank"
                                            class="text-gray-400 hover:text-gray-600 inline-flex items-center transition"
                                            title="Descargar PDF"
                                        >
                                            <DocumentArrowDownIcon class="w-5 h-5" />
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Payments -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Historial de Pagos</h3>
                    <div v-if="props.unit.payments.length === 0" class="text-gray-500 text-sm">No hay pagos registrados.</div>
                    <table v-else class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PERIODO</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">FACTURA</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">FECHA</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ESTADO</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">MONTO</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="payment in props.unit.payments" :key="payment.id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">#{{ payment.id.toString().substring(0, 8) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ payment.invoice ? payment.invoice.billing_period : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ payment.invoice ? '#' + (payment.invoice.invoice_number ?? payment.invoice.id.toString().substring(0, 8)) : 'Abono General' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ payment.created_at_formatted || new Date(payment.created_at).toLocaleDateString('es-CO') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        {{ statusMap[payment.status] || payment.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right font-medium">
                                    $ {{ parseFloat(payment.amount).toLocaleString('es-CO') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Adjustments -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Notas y Ajustes Contables</h3>
                    <div v-if="!props.unit.financial_adjustments || props.unit.financial_adjustments.length === 0" class="text-gray-500 text-sm">No hay notas ni ajustes contables registrados.</div>
                    <table v-else class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PERIODO</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">FACTURA</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">FECHA</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">TIPO</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">DESCRIPCIÓN</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">MONTO</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="adj in props.unit.financial_adjustments" :key="adj.id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">#{{ adj.id.toString().substring(0, 8) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ adj.invoice ? adj.invoice.billing_period : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ adj.invoice ? '#' + (adj.invoice.invoice_number ?? adj.invoice.id.toString().substring(0, 8)) : 'Abono General' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ adj.created_at_formatted || new Date(adj.created_at).toLocaleDateString('es-CO') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="adj.type === 'credit' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                        {{ adjustmentTypeMap[adj.type] || adj.type }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ adj.description || '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right font-medium">
                                    $ {{ parseFloat(adj.amount).toLocaleString('es-CO') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Confirmación de Pago Modal -->
        <Teleport to="body">
            <div
                v-if="showPaymentModal && selectedInvoice"
                class="fixed inset-0 z-50 flex items-center justify-center"
                role="dialog"
                aria-modal="true"
                aria-labelledby="modal-title"
            >
                <!-- Backdrop -->
                <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="closePaymentModal" />

                <!-- Modal Panel -->
                <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
                    <!-- Header -->
                    <div class="bg-indigo-600 px-6 py-4">
                        <h3 id="modal-title" class="text-lg font-semibold text-white flex items-center gap-2">
                            <LockClosedIcon class="w-5 h-5" />
                            Confirmación de Pago
                        </h3>
                    </div>

                    <!-- Body -->
                    <div class="px-6 py-5 space-y-4">
                        <!-- Error Alert -->
                        <div
                            v-if="paymentError"
                            class="flex items-start gap-3 bg-red-50 border border-red-200 rounded-lg px-4 py-3"
                        >
                            <span class="text-xl leading-none mt-0.5">⚠️</span>
                            <p class="text-sm text-red-800 leading-relaxed">
                                {{ paymentError }}
                            </p>
                        </div>

                        <!-- Invoice Summary -->
                        <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Factura</span>
                                <span class="font-medium text-gray-900">
                                    {{ selectedInvoice.invoice_number ? '#' + selectedInvoice.invoice_number : '#' + selectedInvoice.id.substring(0, 8) }}
                                </span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Período</span>
                                <span class="font-medium text-gray-900">{{ selectedInvoice.billing_period || 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between text-sm border-t border-gray-200 pt-2 mt-2">
                                <span class="text-gray-700 font-semibold">Total a Pagar</span>
                                <span class="text-xl font-bold text-indigo-700">
                                    $ {{ parseFloat(selectedInvoice.pending_amount).toLocaleString('es-CO') }}
                                </span>
                            </div>
                        </div>

                        <!-- Trust Badge -->
                        <div class="flex items-start gap-3 bg-green-50 border border-green-200 rounded-lg px-4 py-3">
                            <span class="text-xl leading-none mt-0.5">🔒</span>
                            <p class="text-xs text-green-800 leading-relaxed">
                                <strong>Transacción 100% Segura.</strong> Procesado por ePayco con certificación PCI DSS y encriptación de grado bancario.
                            </p>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="px-6 py-4 bg-gray-50 flex gap-3 justify-end">
                        <button
                            @click="closePaymentModal"
                            :disabled="isProcessingPayment"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition disabled:opacity-50"
                        >
                            Cancelar
                        </button>
                        <button
                            id="btn-proceder-pago"
                            @click="proceedToPayment"
                            :disabled="isProcessingPayment"
                            class="inline-flex items-center gap-2 px-5 py-2 text-sm font-semibold text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition disabled:opacity-50"
                        >
                            <svg v-if="isProcessingPayment" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <LockClosedIcon v-else class="w-4 h-4" />
                            {{ isProcessingPayment ? 'Procesando...' : 'Proceder al Pago Seguro' }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AppLayout>
</template>
