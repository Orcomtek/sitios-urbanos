<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { DocumentArrowDownIcon } from '@heroicons/vue/24/outline';

const page = usePage<any>();

const props = defineProps<{
    unit: {
        id: number;
        identifier: string;
        net_balance: number;
        invoices: any[];
        payments: any[];
        financial_adjustments: any[];
    }
}>();

const statusMap: Record<string, string> = {
    'pending': 'Pendiente',
    'confirmed': 'Confirmado',
    'failed': 'Fallido',
    'refunded': 'Reembolsado'
};

const adjustmentTypeMap: Record<string, string> = {
    'credit': 'A favor',
    'debit': 'Cargo extra'
};
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
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="invoice in props.unit.invoices" :key="invoice.id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">#{{ invoice.id.substring(0, 8) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ invoice.billing_period || '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ new Date(invoice.created_at).toLocaleDateString('es-CO') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="(invoice.status === 'paid' || props.unit.net_balance <= 0) ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'">
                                        {{ (invoice.status === 'paid' || props.unit.net_balance <= 0) ? 'Pagada' : 'Pendiente' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right font-medium">
                                    $ {{ parseFloat(invoice.total || invoice.amount).toLocaleString('es-CO') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                    <a 
                                        :href="route('tenant.resident.financial.statement.invoice.download', { community_slug: page.props.tenant.community.slug, invoice: invoice.id })" 
                                        target="_blank"
                                        class="text-gray-400 hover:text-gray-600 inline-flex items-center transition"
                                        title="Descargar PDF"
                                    >
                                        <DocumentArrowDownIcon class="w-5 h-5" />
                                    </a>
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Referencia / Factura</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="payment in props.unit.payments" :key="payment.id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">#{{ payment.id.toString().substring(0, 8) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ payment.invoice_id ? 'Factura #' + payment.invoice_id.toString().substring(0, 8) : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ new Date(payment.created_at).toLocaleDateString('es-CO') }}</td>
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Periodo / Factura</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="adj in props.unit.financial_adjustments" :key="adj.id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">#{{ adj.id.toString().substring(0, 8) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ adj.billing_period || (adj.invoice_id ? 'Factura #' + adj.invoice_id.toString().substring(0, 8) : '-') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ new Date(adj.created_at).toLocaleDateString('es-CO') }}</td>
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
    </AppLayout>
</template>
