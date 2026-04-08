<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';

const data = ref(null);
const loading = ref(true);
const error = ref(null);
const processingPayment = ref(null);
const paymentMessage = ref('');

const payInvoice = async (invoiceId) => {
    if (processingPayment.value) return;
    processingPayment.value = invoiceId;
    paymentMessage.value = '';
    
    try {
        await axios.post(`/api/finance/invoices/${invoiceId}/pay`);
        paymentMessage.value = 'Pago iniciado correctamente. Redirigiendo a pasarela...';
        await fetchData();
    } catch (e) {
        console.error('Error initiating payment:', e);
        const errorMsg = e.response?.data?.message || 'Error al iniciar el pago.';
        alert(errorMsg);
    } finally {
        processingPayment.value = null;
        setTimeout(() => paymentMessage.value = '', 3000);
    }
};

const fetchData = async () => {
    try {
        const response = await axios.get('/api/cockpit/resident');
        data.value = response.data.data;
        error.value = null;
    } catch (e) {
        console.error('Error fetching resident cockpit data:', e);
        error.value = 'Failed to load cockpit data. Please try again later.';
    } finally {
        loading.value = false;
    }
};

let pollInterval;

onMounted(() => {
    fetchData();
    // Poll every 30 seconds
    pollInterval = setInterval(fetchData, 30000);
});

onUnmounted(() => {
    if (pollInterval) clearInterval(pollInterval);
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP',
        minimumFractionDigits: 0
    }).format(value);
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    return new Intl.DateTimeFormat('es-CO', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    }).format(new Date(dateString));
};
</script>

<template>
    <AppLayout title="Cabina del Residente">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Cabina del Residente
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Loading State -->
                <div v-if="loading" class="space-y-4">
                    <div class="animate-pulse bg-gray-200 h-32 w-full rounded-md" />
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="animate-pulse bg-gray-200 h-48 rounded-md" />
                        <div class="animate-pulse bg-gray-200 h-48 rounded-md" />
                        <div class="animate-pulse bg-gray-200 h-48 rounded-md" />
                        <div class="animate-pulse bg-gray-200 h-48 rounded-md" />
                    </div>
                </div>

                <!-- Error State -->
                <div v-else-if="error" class="bg-red-50 border-l-4 border-red-500 p-4 mb-4">
                    <p class="text-red-700">{{ error }}</p>
                    <button @click="fetchData" class="mt-2 text-sm text-red-600 hover:text-red-800 underline">Reintentar</button>
                </div>

                <!-- Content State -->
                <div v-else-if="data" class="space-y-6">
                    
                    <!-- Finance Summary (Full width highlight) -->
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-100 rounded-lg shadow-sm">
                        <div class="px-6 py-4 border-b border-blue-100">
                            <h3 class="text-blue-900 text-lg font-medium">Resumen Financiero</h3>
                        </div>
                        <div class="px-6 py-4">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between">
                                <div>
                                    <p class="text-sm text-blue-700 font-medium">Deuda Pendiente</p>
                                    <p class="text-3xl font-bold text-blue-900">{{ formatCurrency(data.finance.pending_amount) }}</p>
                                </div>
                                <div class="mt-4 sm:mt-0 text-left sm:text-right">
                                    <p class="text-sm text-blue-700 font-medium">Facturas Pendientes</p>
                                    <p class="text-2xl font-semibold text-blue-800">{{ data.finance.pending_count }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Feedback Message -->
                    <div v-if="paymentMessage" class="bg-green-50 border-l-4 border-green-500 p-4 rounded-md shadow-sm">
                        <p class="text-green-700 text-sm font-medium">{{ paymentMessage }}</p>
                    </div>

                    <!-- Pending Invoices List -->
                    <div v-if="data.finance.recent_invoices && data.finance.recent_invoices.length > 0" class="bg-white border text-card-foreground rounded-lg shadow-sm border-gray-100">
                        <div class="px-6 py-4 flex flex-col space-y-1.5 border-b border-gray-100 bg-gray-50/50">
                            <h3 class="font-semibold leading-none tracking-tight">Tus Facturas</h3>
                        </div>
                        <div class="px-6 py-4">
                            <ul class="space-y-4">
                                <li v-for="invoice in data.finance.recent_invoices" :key="invoice.id" class="flex flex-col sm:flex-row sm:justify-between sm:items-center p-4 bg-gray-50 rounded-lg border border-gray-100">
                                    <div class="mb-3 sm:mb-0">
                                        <p class="font-semibold text-gray-900">{{ invoice.description || 'Factura de Administración' }}</p>
                                        <p class="text-sm text-gray-500">
                                            Vencimiento: {{ formatDate(invoice.due_date) }} 
                                            <span v-if="invoice.unit" class="ml-2 font-medium">Unidad: {{ invoice.unit.unit_number }}</span>
                                        </p>
                                        <p class="text-lg font-bold text-gray-900 mt-1">{{ formatCurrency(invoice.amount) }}</p>
                                    </div>
                                    <div class="flex items-center space-x-3">
                                        <span class="px-2 py-1 text-xs rounded-full font-medium" :class="{
                                            'bg-yellow-100 text-yellow-800': invoice.status === 'pending',
                                            'bg-green-100 text-green-800': invoice.status === 'paid'
                                        }">
                                            {{ invoice.status === 'pending' ? 'Pendiente' : (invoice.status === 'paid' ? 'Pagada' : invoice.status) }}
                                        </span>
                                        <button 
                                            v-if="invoice.status === 'pending'"
                                            @click="payInvoice(invoice.id)" 
                                            :disabled="processingPayment === invoice.id"
                                            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50 gap-2"
                                        >
                                            <svg v-if="processingPayment === invoice.id" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <span v-else>Pagar</span>
                                            <span v-if="processingPayment === invoice.id">Procesando...</span>
                                        </button>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Widget Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <!-- Packages Widget -->
                        <div class="bg-white border text-card-foreground rounded-lg shadow-sm border-gray-100">
                            <div class="px-6 py-4 flex flex-col space-y-1.5 border-b border-gray-100 bg-gray-50/50">
                                <h3 class="font-semibold leading-none tracking-tight">Paquetes por Recoger</h3>
                                <p class="text-sm text-gray-500">{{ data.packages.length }} paquete(s) pendiente(s)</p>
                            </div>
                            <div class="p-6 pt-0">
                                <ul v-if="data.packages.length > 0" class="space-y-3 mt-4">
                                    <li v-for="pkg in data.packages" :key="pkg.id" class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                        <div>
                                            <p class="font-medium text-sm">{{ pkg.carrier }}</p>
                                            <p class="text-xs text-gray-500">Recibido: {{ formatDate(pkg.received_at) }}</p>
                                        </div>
                                        <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded-full font-medium">Pendiente</span>
                                    </li>
                                </ul>
                                <p v-else class="text-sm text-gray-500 italic mt-4">No tienes paquetes pendientes por recoger.</p>
                            </div>
                        </div>

                        <!-- Visitors Widget -->
                        <div class="bg-white border text-card-foreground rounded-lg shadow-sm border-gray-100">
                            <div class="px-6 py-4 flex flex-col space-y-1.5 border-b border-gray-100 bg-gray-50/50">
                                <h3 class="font-semibold leading-none tracking-tight">Visitantes Recientes</h3>
                                <p class="text-sm text-gray-500">{{ data.visitors.length }} visitante(s) esperado(s) o en proceso</p>
                            </div>
                            <div class="p-6 pt-0">
                                <ul v-if="data.visitors.length > 0" class="space-y-3 mt-4">
                                    <li v-for="visitor in data.visitors" :key="visitor.id" class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                        <div>
                                            <p class="font-medium text-sm">{{ visitor.name }}</p>
                                            <p class="text-xs text-gray-500">Unidad: {{ visitor.unit ? visitor.unit.unit_number : 'N/A' }}</p>
                                        </div>
                                        <span class="px-2 py-1 text-xs rounded-full font-medium" :class="{
                                            'bg-blue-100 text-blue-800': visitor.status === 'pending',
                                            'bg-green-100 text-green-800': visitor.status === 'entered'
                                        }">
                                            {{ visitor.status === 'pending' ? 'Esperado' : 'Ingresó' }}
                                        </span>
                                    </li>
                                </ul>
                                <p v-else class="text-sm text-gray-500 italic mt-4">No hay visitantes esperados en este momento.</p>
                            </div>
                        </div>

                        <!-- Invitations Widget -->
                        <div class="bg-white border text-card-foreground rounded-lg shadow-sm border-gray-100">
                            <div class="px-6 py-4 flex flex-col space-y-1.5 border-b border-gray-100 bg-gray-50/50">
                                <h3 class="font-semibold leading-none tracking-tight">Invitaciones Activas</h3>
                                <p class="text-sm text-gray-500">{{ data.invitations.length }} invitación(es) vigente(s)</p>
                            </div>
                            <div class="p-6 pt-0">
                                <ul v-if="data.invitations.length > 0" class="space-y-3 mt-4">
                                    <li v-for="invitation in data.invitations" :key="invitation.id" class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                        <div>
                                            <p class="font-medium text-sm">{{ invitation.guest_name }}</p>
                                            <p class="text-xs text-gray-500">Expira: {{ formatDate(invitation.expires_at) }}</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-xs font-mono font-bold">{{ invitation.code }}</p>
                                        </div>
                                    </li>
                                </ul>
                                <p v-else class="text-sm text-gray-500 italic mt-4">No tienes invitaciones activas.</p>
                            </div>
                        </div>

                        <!-- PQRS Widget -->
                        <div class="bg-white border text-card-foreground rounded-lg shadow-sm border-gray-100 flex flex-col">
                            <div class="px-6 py-4 flex flex-col space-y-1.5 border-b border-gray-100 bg-gray-50/50">
                                <div class="flex justify-between items-center">
                                    <h3 class="font-semibold leading-none tracking-tight">Tus PQRS en Curso</h3>
                                    <Link :href="route('tenant.cockpit.resident.pqrs')" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">
                                        Gestionar PQRS &rarr;
                                    </Link>
                                </div>
                                <p class="text-sm text-gray-500">{{ data.pqrs.length }} solicitud(es) abierta(s)</p>
                            </div>
                            <div class="p-6 pt-0">
                                <ul v-if="data.pqrs.length > 0" class="space-y-3 mt-4">
                                    <li v-for="pqrs in data.pqrs" :key="pqrs.id" class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                        <div class="truncate mr-4">
                                            <p class="font-medium text-sm truncate">{{ pqrs.subject }}</p>
                                            <p class="text-xs text-gray-500">{{ formatDate(pqrs.created_at) }}</p>
                                        </div>
                                        <span class="px-2 py-1 text-xs rounded-full font-medium whitespace-nowrap" :class="{
                                            'bg-yellow-100 text-yellow-800': pqrs.status === 'open',
                                            'bg-blue-100 text-blue-800': pqrs.status === 'in_progress'
                                        }">
                                            {{ pqrs.status === 'open' ? 'Abierta' : 'En Progreso' }}
                                        </span>
                                    </li>
                                </ul>
                                <p v-else class="text-sm text-gray-500 italic mt-4">No tienes solicitudes en progreso.</p>
                            </div>
                        </div>

                    </div>
                    
                    <div class="text-right text-xs text-gray-400">
                        Última actualización: {{ new Date(data.generated_at).toLocaleTimeString('es-CO') }}
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
