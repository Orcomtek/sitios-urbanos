<script setup>
import { ref, onMounted } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    activeUnits: {
        type: Array,
        required: true
    }
});

const invitations = ref([]);
const visitors = ref([]);
const packages = ref([]);

const loadingInvitations = ref(true);
const loadingVisitors = ref(true);
const loadingPackages = ref(true);

const error = ref(null);

const isCreatingInvitation = ref(false);
const isCreatingVisitor = ref(false);

const invitationForm = useForm({
    unit_id: props.activeUnits.length === 1 ? props.activeUnits[0].id : '',
    visitor_id: '',
    type: 'manual_code',
    expires_at: '',
    notes: '',
});

const visitorForm = useForm({
    unit_id: props.activeUnits.length === 1 ? props.activeUnits[0].id : '',
    name: '',
    document_number: '',
    type: 'visitor',
    expected_at: '',
    notes: '',
});

const formSuccess = ref('');
const formError = ref('');

const fetchInvitations = async () => {
    loadingInvitations.value = true;
    try {
        const response = await axios.get('/api/security/invitations');
        invitations.value = response.data.data || [];
    } catch (e) {
        console.error(e);
        error.value = 'Error al cargar invitaciones.';
    } finally {
        loadingInvitations.value = false;
    }
};

const fetchVisitors = async () => {
    loadingVisitors.value = true;
    try {
        const response = await axios.get('/api/security/visitors');
        visitors.value = response.data.data || [];
    } catch (e) {
        console.error(e);
        error.value = 'Error al cargar visitantes.';
    } finally {
        loadingVisitors.value = false;
    }
};

const fetchPackages = async () => {
    loadingPackages.value = true;
    try {
        const response = await axios.get('/api/security/packages');
        packages.value = response.data.data || [];
    } catch (e) {
        console.error(e);
        error.value = 'Error al cargar paquetes.';
    } finally {
        loadingPackages.value = false;
    }
};

onMounted(() => {
    fetchInvitations();
    fetchVisitors();
    fetchPackages();
});

const submitInvitation = async () => {
    formError.value = '';
    formSuccess.value = '';
    
    try {
        await axios.post('/api/security/invitations', invitationForm);
        formSuccess.value = 'Invitación creada con éxito.';
        invitationForm.reset();
        // Reset defaults
        invitationForm.unit_id = props.activeUnits.length === 1 ? props.activeUnits[0].id : '';
        invitationForm.type = 'manual_code';
        isCreatingInvitation.value = false;
        await fetchInvitations();
        setTimeout(() => formSuccess.value = '', 4000);
    } catch (e) {
        if (e.response && e.response.status === 422) {
            formError.value = 'Por favor verifica la información ingresada.';
        } else {
            formError.value = e.response?.data?.message || 'Hubo un error al crear la invitación.';
        }
    }
};

const revokeInvitation = async (invitationId) => {
    if (!confirm('¿Seguro que deseas revocar esta invitación?')) return;
    try {
        await axios.patch(`/api/security/invitations/${invitationId}/revoke`);
        await fetchInvitations();
    } catch (e) {
        alert('Error al revocar la invitación');
    }
};

const submitVisitor = async () => {
    formError.value = '';
    formSuccess.value = '';
    
    try {
        await axios.post('/api/security/visitors', visitorForm);
        formSuccess.value = 'Visitante registrado con éxito.';
        visitorForm.reset();
        // Reset defaults
        visitorForm.unit_id = props.activeUnits.length === 1 ? props.activeUnits[0].id : '';
        visitorForm.type = 'visitor';
        isCreatingVisitor.value = false;
        await fetchVisitors();
        setTimeout(() => formSuccess.value = '', 4000);
    } catch (e) {
        if (e.response && e.response.status === 422) {
            formError.value = 'Por favor verifica la información ingresada. ' + (e.response.data.message || '');
        } else {
            formError.value = e.response?.data?.message || 'Hubo un error al registrar visitante.';
        }
    }
};

const formatDate = (dateString, includeTime = true) => {
    if (!dateString) return '';
    const options = {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    };
    if (includeTime) {
        options.hour = '2-digit';
        options.minute = '2-digit';
    }
    return new Intl.DateTimeFormat('es-CO', options).format(new Date(dateString));
};

const getStatusBadgeClass = (status) => {
    switch(status) {
        case 'active': return 'bg-green-100 text-green-800 border-green-200';
        case 'pending': return 'bg-yellow-100 text-yellow-800 border-yellow-200';
        case 'entered': return 'bg-blue-100 text-blue-800 border-blue-200';
        case 'exited': return 'bg-gray-100 text-gray-800 border-gray-200';
        case 'used': return 'bg-gray-100 text-gray-800 border-gray-200';
        case 'revoked': return 'bg-red-100 text-red-800 border-red-200';
        case 'expired': return 'bg-red-50 text-red-600 border-red-100';
        case 'received': return 'bg-blue-100 text-blue-800 border-blue-200';
        case 'delivered': return 'bg-green-100 text-green-800 border-green-200';
        default: return 'bg-gray-100 text-gray-800 border-gray-200';
    }
};

const translateStatus = (status) => {
    const translations = {
        'active': 'Activa',
        'pending': 'Pendiente',
        'entered': 'Ingresó',
        'exited': 'Salió',
        'used': 'Usada',
        'revoked': 'Revocada',
        'expired': 'Expirada',
        'received': 'Recibido',
        'delivered': 'Entregado',
        'returned': 'Devuelto'
    };
    return translations[status] || status;
};

const translateVisitorType = (type) => {
    const types = {
        'visitor': 'Visitante',
        'delivery': 'Domicilio/Entrega',
        'service': 'Servicio Técnico',
        'other': 'Otro'
    };
    return types[type] || type;
};
</script>

<template>
    <AppLayout title="Operaciones">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Gestión Operativa
                </h2>
                <Link :href="route('tenant.cockpit.resident')" class="text-sm text-gray-500 hover:text-gray-700 underline">
                    &larr; Volver a Cabina
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                
                <div v-if="formSuccess" class="bg-green-50 border-l-4 border-green-500 p-4 rounded-md shadow-sm">
                    <p class="text-green-700 text-sm font-medium">{{ formSuccess }}</p>
                </div>

                <!-- INVITATIONS SECTION -->
                <section class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center cursor-pointer" @click="isCreatingInvitation = !isCreatingInvitation">
                        <div>
                            <h3 class="font-semibold text-lg text-gray-800">Invitaciones de Acceso</h3>
                            <p class="text-xs text-gray-500 mt-1">Genera códigos de acceso para tus invitados.</p>
                        </div>
                        <button class="text-indigo-600 hover:text-indigo-800 font-medium text-sm focus:outline-none">
                            {{ isCreatingInvitation ? 'Cancelar' : 'Crear Nueva' }}
                        </button>
                    </div>

                    <div v-show="isCreatingInvitation" class="p-6 transition-all duration-300 border-b border-gray-100">
                        <form @submit.prevent="submitInvitation" class="space-y-4">
                            <div v-if="formError" class="bg-red-50 text-red-700 p-3 rounded-md text-sm border border-red-100">
                                {{ formError }}
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Unidad Autorizada</label>
                                    <select v-model="invitationForm.unit_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="" disabled>Selecciona tu unidad</option>
                                        <option v-for="unit in activeUnits" :key="unit.id" :value="unit.id">
                                            {{ unit.unit_number }}
                                        </option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Visitante Previo (Opcional)</label>
                                    <select v-model="invitationForm.visitor_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="">Ninguno</option>
                                        <!-- Only showing loaded expected visitors for convenience, or could be empty. In the current API we just list recent visitors -->
                                        <option v-for="v in visitors" :key="v.id" :value="v.id">
                                            {{ v.name }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Válido hasta (Opcional)</label>
                                    <input type="datetime-local" v-model="invitationForm.expires_at" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Notas (Nombre del invitado / Detalle)</label>
                                    <input type="text" v-model="invitationForm.notes" maxlength="1000" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Ej: Juan Pérez para cena" />
                                </div>
                            </div>

                            <div class="flex justify-end pt-2">
                                <button type="submit" :disabled="invitationForm.processing" class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 disabled:opacity-50">
                                    <span v-if="invitationForm.processing">Enviando...</span>
                                    <span v-else>Generar Invitación</span>
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="p-0">
                        <div v-if="loadingInvitations" class="p-6 text-center text-gray-500">Cargando...</div>
                        <div v-else-if="invitations.length === 0" class="p-6 text-center text-sm text-gray-500 italic">
                            No tienes invitaciones activas.
                        </div>
                        <ul v-else class="divide-y divide-gray-100">
                            <li v-for="inv in invitations" :key="inv.id" class="p-4 sm:px-6 hover:bg-gray-50 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                                <div>
                                    <div class="flex items-center space-x-2">
                                        <span class="font-mono font-bold text-lg text-indigo-700 tracking-wider bg-indigo-50 px-2 py-1 rounded">{{ inv.code }}</span>
                                        <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium border" :class="getStatusBadgeClass(inv.status)">
                                            {{ translateStatus(inv.status) }}
                                        </span>
                                    </div>
                                    <div class="text-sm text-gray-600 mt-2">
                                        <span class="font-medium">Unidad {{ inv.unit?.unit_number }}</span>
                                        <span v-if="inv.visitor"> • Pasajero: {{ inv.visitor.name }}</span>
                                        <span v-if="inv.notes"> • {{ inv.notes }}</span>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">Expira: {{ inv.expires_at ? formatDate(inv.expires_at) : 'Sin límite' }}</div>
                                </div>
                                <div class="flex-shrink-0" v-if="inv.status === 'active'">
                                    <button @click="revokeInvitation(inv.id)" class="text-red-600 hover:text-red-800 text-sm font-medium focus:outline-none bg-red-50 hover:bg-red-100 px-3 py-1 rounded-md transition-colors">
                                        Revocar
                                    </button>
                                </div>
                            </li>
                        </ul>
                    </div>
                </section>

                <!-- VISITORS SECTION -->
                <section class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center cursor-pointer" @click="isCreatingVisitor = !isCreatingVisitor">
                        <div>
                            <h3 class="font-semibold text-lg text-gray-800">Visitantes</h3>
                            <p class="text-xs text-gray-500 mt-1">Registra visitantes esperados y revisa el historial.</p>
                        </div>
                        <button class="text-indigo-600 hover:text-indigo-800 font-medium text-sm focus:outline-none">
                            {{ isCreatingVisitor ? 'Cancelar' : 'Registrar Nuevo' }}
                        </button>
                    </div>

                    <div v-show="isCreatingVisitor" class="p-6 transition-all duration-300 border-b border-gray-100">
                        <form @submit.prevent="submitVisitor" class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre Completo</label>
                                    <input type="text" v-model="visitorForm.name" required maxlength="255" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Identificación (Opcional)</label>
                                    <input type="text" v-model="visitorForm.document_number" maxlength="255" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Unidad</label>
                                    <select v-model="visitorForm.unit_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="" disabled>Selecciona tu unidad</option>
                                        <option v-for="unit in activeUnits" :key="unit.id" :value="unit.id">
                                            {{ unit.unit_number }}
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Visitante</label>
                                    <select v-model="visitorForm.type" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="visitor">Visita</option>
                                        <option value="delivery">Domicilio / Entrega</option>
                                        <option value="service">Servicio Técnico</option>
                                        <option value="other">Otro</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Esperada (Opcional)</label>
                                    <input type="datetime-local" v-model="visitorForm.expected_at" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Notas</label>
                                <input type="text" v-model="visitorForm.notes" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Detalles de la visita..." />
                            </div>

                            <div class="flex justify-end pt-2">
                                <button type="submit" :disabled="visitorForm.processing" class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 disabled:opacity-50">
                                    <span v-if="visitorForm.processing">Enviando...</span>
                                    <span v-else>Guardar Visitante</span>
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="p-0">
                        <div v-if="loadingVisitors" class="p-6 text-center text-gray-500">Cargando...</div>
                        <div v-else-if="visitors.length === 0" class="p-6 text-center text-sm text-gray-500 italic">
                            No hay visitantes recientes.
                        </div>
                        <ul v-else class="divide-y divide-gray-100">
                            <li v-for="visitor in visitors" :key="visitor.id" class="p-4 sm:px-6 hover:bg-gray-50">
                                <div class="flex justify-between items-start mb-1">
                                    <div class="font-medium text-gray-900">{{ visitor.name }}</div>
                                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium border" :class="getStatusBadgeClass(visitor.status)">
                                        {{ translateStatus(visitor.status) }}
                                    </span>
                                </div>
                                <div class="text-sm text-gray-600">
                                    <span class="font-medium">{{ translateVisitorType(visitor.type) }}</span>
                                    <span> a la Unidad {{ visitor.unit?.unit_number }}</span>
                                </div>
                                <div class="text-xs text-gray-500 mt-2">
                                    Creado el: {{ formatDate(visitor.created_at) }}
                                    <span v-if="visitor.expected_at"> • Esperado el: {{ formatDate(visitor.expected_at) }}</span>
                                </div>
                            </li>
                        </ul>
                    </div>
                </section>

                <!-- PACKAGES SECTION -->
                <section class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h3 class="font-semibold text-lg text-gray-800">Paquetería</h3>
                        <p class="text-xs text-gray-500 mt-1">Revisa el estado de tus paquetes y correspondencia.</p>
                    </div>

                    <div class="p-0">
                        <div v-if="loadingPackages" class="p-6 text-center text-gray-500">Cargando...</div>
                        <div v-else-if="packages.length === 0" class="p-6 text-center text-sm text-gray-500 italic">
                            No hay paquetes recientes en el sistema.
                        </div>
                        <ul v-else class="divide-y divide-gray-100">
                            <li v-for="pkg in packages" :key="pkg.id" class="p-4 sm:px-6 hover:bg-gray-50">
                                <div class="flex justify-between items-start mb-1">
                                    <div class="font-medium text-gray-900">
                                        {{ pkg.carrier || 'Paquete' }}
                                        <span v-if="pkg.tracking_number" class="ml-2 text-xs font-mono bg-gray-100 px-1 py-0.5 rounded text-gray-600">{{ pkg.tracking_number }}</span>
                                    </div>
                                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium border" :class="getStatusBadgeClass(pkg.status)">
                                        {{ translateStatus(pkg.status) }}
                                    </span>
                                </div>
                                <div class="text-sm text-gray-600 mt-1">
                                    Unidad {{ pkg.unit?.unit_number }} 
                                    <span v-if="pkg.recipient_name"> • Para: {{ pkg.recipient_name }}</span>
                                </div>
                                <div class="text-xs text-gray-500 mt-2">
                                    <span v-if="pkg.received_at">Recibido: {{ formatDate(pkg.received_at) }} </span>
                                    <span v-if="pkg.status === 'delivered' && pkg.delivered_at"> • Entregado: {{ formatDate(pkg.delivered_at) }}</span>
                                </div>
                            </li>
                        </ul>
                    </div>
                </section>

            </div>
        </div>
    </AppLayout>
</template>
