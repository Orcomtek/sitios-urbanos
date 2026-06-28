<script setup>
import { ref, watch } from 'vue';
import { useForm, Head, usePage, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputError from '@/Components/InputError.vue';
import { TrashIcon } from '@heroicons/vue/24/outline';
import { useToast } from '@/Composables/useToast';
import ConfirmDeleteModal from '@/Components/ui/ConfirmDeleteModal.vue';

const { show: showToast } = useToast();
const page = usePage();

watch(() => page.props.errors, (errors) => {
    if (errors && errors.error) {
        showToast(errors.error, 'error');
    }
}, { deep: true });

const props = defineProps({
    settings: Object,
    billingConcepts: Array,
    dunning_policies: Object,
});

const getInitialAccounts = () => {
    const details = props.settings?.bank_account_details;
    if (Array.isArray(details) && details.length > 0) return details;
    return [{ bank_name: '', account_type: '', account_number: '' }];
};

const form = useForm({
    base_budget: props.settings.base_budget ? String(props.settings.base_budget) : '0',
    late_fee_interest_rate: props.settings.late_fee_interest_rate ? String(props.settings.late_fee_interest_rate) : '0',
    billing_day: String(props.settings.billing_day || 1),
    due_day: String(props.settings.due_day || 10),
    bank_account_details: getInitialAccounts(),
    epayco_allied_account_id: props.settings.epayco_allied_account_id || '',
    dunning_policies: {
        enabled: props.dunning_policies?.enabled ?? false,
        grace_period_days: props.dunning_policies?.grace_period_days ?? 0,
        restrictions: {
            restrict_ecosystem: props.dunning_policies?.restrictions?.restrict_ecosystem ?? false,
            restrict_pqrs: props.dunning_policies?.restrictions?.restrict_pqrs ?? false,
            restrict_moving_permits: props.dunning_policies?.restrictions?.restrict_moving_permits ?? false,
            restrict_amenities: props.dunning_policies?.restrictions?.restrict_amenities ?? false,
        },
    },
});

// Read-only display values for commission — controlled exclusively by Sitios Urbanos.
const commissionTypeDisplay = props.settings.commission_type || 'fixed';
const commissionValueDisplay = String(props.settings.commission_value ?? 1500);

const addAccount = () => form.bank_account_details.push({ bank_name: '', account_type: '', account_number: '' });

const confirmingAccountDeletion = ref(false);
const accountToDeleteIndex = ref(null);

const confirmAccountDeletion = (index) => {
    accountToDeleteIndex.value = index;
    confirmingAccountDeletion.value = true;
};

const executeAccountDeletion = () => {
    if (accountToDeleteIndex.value !== null) {
        form.bank_account_details.splice(accountToDeleteIndex.value, 1);
        confirmingAccountDeletion.value = false;
        accountToDeleteIndex.value = null;
        submit();
    }
};
const submit = () => {
    form.put(route('tenant.admin.financial.settings.update', { community_slug: route().params.community_slug }), {
        preserveScroll: true,
        onSuccess: () => {
            showToast('Configuración financiera guardada exitosamente.', 'success');
        }
    });
};



const conceptForm = useForm({
    name: '',
    type: '',
});

const submitConcept = () => {
    conceptForm.post(route('tenant.admin.financial.settings.concepts.store', { community_slug: route().params.community_slug }), {
        preserveScroll: true,
        onSuccess: () => {
            conceptForm.reset();
            showToast('Concepto creado exitosamente.', 'success');
        }
    });
};

const confirmingConceptDeletion = ref(false);
const conceptToDelete = ref(null);

const confirmConceptDeletion = (concept) => {
    conceptToDelete.value = concept;
    confirmingConceptDeletion.value = true;
};

const executeConceptDeletion = () => {
    if (!conceptToDelete.value) return;
    router.delete(route('tenant.admin.financial.settings.concepts.destroy', { community_slug: route().params.community_slug, concept: conceptToDelete.value.id }), {
        preserveScroll: true,
        onSuccess: () => {
            showToast('Concepto eliminado exitosamente.', 'success');
            confirmingConceptDeletion.value = false;
            conceptToDelete.value = null;
        }
    });
};
</script>

<template>
    <AppLayout title="Configuración Financiera">
        <template #header>
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                Configuración Financiera
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="space-y-6">
                    
                    <!-- Parámetros de Facturación -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-200">
                        <div class="p-6 border-b border-slate-200 bg-slate-50">
                            <h3 class="text-lg font-medium text-slate-900">Parámetros de Facturación</h3>
                            <p class="mt-1 text-sm text-slate-500">Configura los montos base, fechas e intereses de mora.</p>
                        </div>
                        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            <!-- Presupuesto Base -->
                            <div>
                                <InputLabel for="base_budget" value="Presupuesto Base Mensual (COP)" />
                                <TextInput
                                    id="base_budget"
                                    type="number"
                                    class="mt-1 block w-full focus:ring-emerald-500 focus:border-emerald-500"
                                    v-model="form.base_budget"
                                    required
                                    min="0"
                                    step="0.01"
                                />
                                <InputError class="mt-2" :message="form.errors.base_budget" />
                            </div>

                            <!-- Interés por Mora -->
                            <div>
                                <InputLabel for="late_fee_interest_rate" value="Interés por Mora (%)" />
                                <TextInput
                                    id="late_fee_interest_rate"
                                    type="number"
                                    class="mt-1 block w-full focus:ring-emerald-500 focus:border-emerald-500"
                                    v-model="form.late_fee_interest_rate"
                                    required
                                    min="0"
                                    max="100"
                                    step="0.01"
                                />
                                <InputError class="mt-2" :message="form.errors.late_fee_interest_rate" />
                            </div>

                            <!-- Día de Facturación -->
                            <div>
                                <InputLabel for="billing_day" value="Día de Facturación" />
                                <TextInput
                                    id="billing_day"
                                    type="number"
                                    class="mt-1 block w-full focus:ring-emerald-500 focus:border-emerald-500"
                                    v-model="form.billing_day"
                                    required
                                    min="1"
                                    max="31"
                                />
                                <InputError class="mt-2" :message="form.errors.billing_day" />
                            </div>

                            <!-- Día de Vencimiento -->
                            <div>
                                <InputLabel for="due_day" value="Día de Vencimiento" />
                                <TextInput
                                    id="due_day"
                                    type="number"
                                    class="mt-1 block w-full focus:ring-emerald-500 focus:border-emerald-500"
                                    v-model="form.due_day"
                                    required
                                    min="1"
                                    max="31"
                                />
                                <InputError class="mt-2" :message="form.errors.due_day" />
                            </div>

                        </div>
                    </div>

                    <!-- Cuentas de Recaudo -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-200">
                        <div class="p-6 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-medium text-slate-900">Cuentas de Recaudo</h3>
                                <p class="mt-1 text-sm text-slate-500">Información de la cuenta bancaria para pagos externos.</p>
                            </div>
                            <button
                                type="button"
                                @click="addAccount"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-emerald-700 bg-emerald-100 hover:bg-emerald-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500"
                            >
                                + Agregar cuenta
                            </button>
                        </div>
                        <div class="p-6 space-y-6">
                            <div v-for="(account, index) in form.bank_account_details" :key="index" class="relative grid grid-cols-1 md:grid-cols-12 gap-6 p-4 border border-gray-100 rounded-lg bg-gray-50">
                                
                                <button
                                    v-if="form.bank_account_details.length > 1"
                                    type="button"
                                    @click="confirmAccountDeletion(index)"
                                    class="absolute top-4 right-4 text-red-500 hover:text-red-700"
                                >
                                    <TrashIcon class="w-5 h-5" />
                                </button>

                                <!-- Banco -->
                                <div class="md:col-span-5">
                                    <InputLabel :for="'bank_name_' + index" value="Nombre del Banco" />
                                    <TextInput
                                        :id="'bank_name_' + index"
                                        type="text"
                                        class="mt-1 block w-full focus:ring-emerald-500 focus:border-emerald-500"
                                        v-model="account.bank_name"
                                    />
                                    <InputError class="mt-2" :message="form.errors['bank_account_details.' + index + '.bank_name']" />
                                </div>

                                <!-- Tipo de Cuenta -->
                                <div class="md:col-span-3">
                                    <InputLabel :for="'account_type_' + index" value="Tipo de Cuenta" />
                                    <select
                                        :id="'account_type_' + index"
                                        v-model="account.account_type"
                                        class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-md shadow-sm"
                                    >
                                        <option value="">Seleccione...</option>
                                        <option value="Ahorros">Ahorros</option>
                                        <option value="Corriente">Corriente</option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors['bank_account_details.' + index + '.account_type']" />
                                </div>

                                <!-- Número de Cuenta -->
                                <div class="md:col-span-4 pr-8">
                                    <InputLabel :for="'account_number_' + index" value="Número de Cuenta" />
                                    <TextInput
                                        :id="'account_number_' + index"
                                        type="text"
                                        class="mt-1 block w-full focus:ring-emerald-500 focus:border-emerald-500"
                                        v-model="account.account_number"
                                    />
                                    <InputError class="mt-2" :message="form.errors['bank_account_details.' + index + '.account_number']" />
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- Configuración de Pagos ePayco -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-200">
                        <div class="p-6 border-b border-slate-200 bg-slate-50">
                            <h3 class="text-lg font-medium text-slate-900">Configuración de Pagos ePayco</h3>
                            <p class="mt-1 text-sm text-slate-500">Configura la cuenta aliada y comisión de plataforma para pagos en línea.</p>
                        </div>
                        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">

                            <!-- ID Cuenta Aliada -->
                            <div class="md:col-span-2">
                                <InputLabel for="epayco_allied_account_id" value="ID Cuenta Aliada ePayco" />
                                <TextInput
                                    id="epayco_allied_account_id"
                                    type="text"
                                    class="mt-1 block w-full focus:ring-emerald-500 focus:border-emerald-500"
                                    v-model="form.epayco_allied_account_id"
                                    placeholder="Ej: 12345"
                                />
                                <p class="mt-1 text-xs text-slate-400">Identificador de la cuenta aliada (Cuentas Aliadas) proporcionado por ePayco.</p>
                                <InputError class="mt-2" :message="form.errors.epayco_allied_account_id" />
                            </div>

                            <!-- Tipo de Comisión (solo lectura — gestionado por Sitios Urbanos) -->
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <InputLabel for="commission_type" value="Tipo de Comisión" />
                                    <span class="inline-flex items-center gap-1 text-xs font-medium text-indigo-700 bg-indigo-50 border border-indigo-200 rounded-full px-2 py-0.5">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd" /></svg>
                                        Configurado por Sitios Urbanos
                                    </span>
                                </div>
                                <select
                                    id="commission_type"
                                    :model-value="commissionTypeDisplay"
                                    disabled
                                    class="mt-1 block w-full border-gray-200 bg-gray-50 text-gray-500 rounded-md shadow-sm cursor-not-allowed opacity-75"
                                >
                                    <option value="fixed">Fija (COP)</option>
                                    <option value="percentage">Porcentaje (%)</option>
                                </select>
                            </div>

                            <!-- Valor de Comisión (solo lectura — gestionado por Sitios Urbanos) -->
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <InputLabel for="commission_value" :value="commissionTypeDisplay === 'percentage' ? 'Valor de Comisión (centésimas de %)' : 'Valor de Comisión (COP)'" />
                                    <span class="inline-flex items-center gap-1 text-xs font-medium text-indigo-700 bg-indigo-50 border border-indigo-200 rounded-full px-2 py-0.5">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd" /></svg>
                                        Configurado por Sitios Urbanos
                                    </span>
                                </div>
                                <TextInput
                                    id="commission_value"
                                    type="number"
                                    :model-value="commissionValueDisplay"
                                    readonly
                                    class="mt-1 block w-full bg-gray-50 text-gray-500 border-gray-200 cursor-not-allowed opacity-75"
                                />
                                <p class="mt-1 text-xs text-slate-400">
                                    {{ commissionTypeDisplay === 'percentage' ? 'Ej: 350 = 3.50% de comisión por transacción.' : 'Ej: 1500 = $1.500 COP fijos por transacción.' }}
                                </p>
                            </div>

                        </div>
                    </div>

                    <!-- =====================================================
                         SECCIÓN: Políticas de Cobro (Dunning)
                         ===================================================== -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-200">
                        <div class="p-6 border-b border-slate-200 bg-slate-50 flex items-start justify-between">
                            <div>
                                <h3 class="text-lg font-medium text-slate-900">Políticas de Cobro</h3>
                                <p class="mt-1 text-sm text-slate-500">Configura restricciones de acceso a módulos para residentes con obligaciones vencidas. <span class="font-medium text-amber-700">De acuerdo con la Ley 675 de 2001, ninguna restricción aplica automáticamente. El administrador debe habilitarlas explícitamente.</span></p>
                            </div>
                            <!-- Master enable toggle -->
                            <button
                                type="button"
                                id="dunning-master-toggle"
                                @click="form.dunning_policies.enabled = !form.dunning_policies.enabled"
                                class="ml-4 flex-shrink-0 relative inline-flex h-6 w-11 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
                                :class="form.dunning_policies.enabled ? 'bg-emerald-500' : 'bg-gray-200'"
                                :aria-checked="form.dunning_policies.enabled"
                                role="switch"
                            >
                                <span class="sr-only">Activar restricciones de cobro</span>
                                <span
                                    class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                    :class="form.dunning_policies.enabled ? 'translate-x-5' : 'translate-x-0'"
                                />
                            </button>
                        </div>

                        <div class="p-6">
                            <div v-if="form.dunning_policies.enabled" class="space-y-6">
                                <!-- Grace period -->
                                <div class="max-w-xs">
                                    <InputLabel for="grace_period_days" value="Días de Gracia" />
                                    <p class="text-xs text-gray-500 mb-1">Número de días después del vencimiento antes de que aplique la restricción. Recomendado: 5–15 días.</p>
                                    <TextInput
                                        id="grace_period_days"
                                        type="number"
                                        min="0"
                                        max="90"
                                        v-model="form.dunning_policies.grace_period_days"
                                        class="mt-1 block w-full focus:ring-emerald-500 focus:border-emerald-500"
                                    />
                                    <InputError class="mt-1" :message="form.errors['dunning_policies.grace_period_days']" />
                                </div>

                                <!-- Module restriction toggles -->
                                <div>
                                    <p class="text-sm font-medium text-gray-700 mb-3">Módulos restringidos para residentes en mora</p>
                                    <div class="space-y-3">

                                        <div class="flex items-start gap-4 p-3 rounded-lg border border-gray-200">
                                            <button
                                                type="button"
                                                id="dunning-restrict-ecosystem"
                                                @click="form.dunning_policies.restrictions.restrict_ecosystem = !form.dunning_policies.restrictions.restrict_ecosystem"
                                                class="mt-0.5 relative inline-flex h-5 w-9 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2"
                                                :class="form.dunning_policies.restrictions.restrict_ecosystem ? 'bg-amber-500' : 'bg-gray-200'"
                                                role="switch" :aria-checked="form.dunning_policies.restrictions.restrict_ecosystem"
                                            >
                                                <span class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out" :class="form.dunning_policies.restrictions.restrict_ecosystem ? 'translate-x-4' : 'translate-x-0'" />
                                            </button>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">Ecosistema (Marketplace y Proveedores)</p>
                                                <p class="text-xs text-gray-500">Restringe el acceso al marketplace y directorio de proveedores para residentes en mora.</p>
                                            </div>
                                        </div>

                                        <div class="flex items-start gap-4 p-3 rounded-lg border border-gray-200">
                                            <button
                                                type="button"
                                                id="dunning-restrict-pqrs"
                                                @click="form.dunning_policies.restrictions.restrict_pqrs = !form.dunning_policies.restrictions.restrict_pqrs"
                                                class="mt-0.5 relative inline-flex h-5 w-9 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2"
                                                :class="form.dunning_policies.restrictions.restrict_pqrs ? 'bg-amber-500' : 'bg-gray-200'"
                                                role="switch" :aria-checked="form.dunning_policies.restrictions.restrict_pqrs"
                                            >
                                                <span class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out" :class="form.dunning_policies.restrictions.restrict_pqrs ? 'translate-x-4' : 'translate-x-0'" />
                                            </button>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">PQRS (Peticiones y Quejas)</p>
                                                <p class="text-xs text-gray-500">Restringe el envío y consulta de PQRS. Nota: la Ley 675 puede limitar esta restricción en ciertos casos.</p>
                                            </div>
                                        </div>

                                        <div class="flex items-start gap-4 p-3 rounded-lg border border-gray-200">
                                            <button
                                                type="button"
                                                id="dunning-restrict-moving-permits"
                                                @click="form.dunning_policies.restrictions.restrict_moving_permits = !form.dunning_policies.restrictions.restrict_moving_permits"
                                                class="mt-0.5 relative inline-flex h-5 w-9 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2"
                                                :class="form.dunning_policies.restrictions.restrict_moving_permits ? 'bg-amber-500' : 'bg-gray-200'"
                                                role="switch" :aria-checked="form.dunning_policies.restrictions.restrict_moving_permits"
                                            >
                                                <span class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out" :class="form.dunning_policies.restrictions.restrict_moving_permits ? 'translate-x-4' : 'translate-x-0'" />
                                            </button>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">Permisos de Mudanza</p>
                                                <p class="text-xs text-gray-500">Restringe la solicitud de permisos de mudanza para residentes en mora.</p>
                                            </div>
                                        </div>

                                        <div class="flex items-start gap-4 p-3 rounded-lg border border-gray-200 opacity-60">
                                            <button
                                                type="button"
                                                id="dunning-restrict-amenities"
                                                disabled
                                                class="mt-0.5 relative inline-flex h-5 w-9 flex-shrink-0 cursor-not-allowed rounded-full border-2 border-transparent bg-gray-200"
                                                role="switch" aria-checked="false"
                                            >
                                                <span class="pointer-events-none inline-block h-4 w-4 translate-x-0 transform rounded-full bg-white shadow ring-0" />
                                            </button>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">Reserva de Amenidades <span class="ml-1 text-xs text-gray-400 font-normal">(Próximamente)</span></p>
                                                <p class="text-xs text-gray-500">Restringe la reserva de zonas comunes. Disponible cuando el módulo de amenidades esté activo.</p>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <div v-else class="rounded-md bg-gray-50 border border-gray-200 p-4">
                                <p class="text-sm text-gray-500 text-center">Las políticas de cobro están <strong>desactivadas</strong>. Los residentes con obligaciones vencidas tendrán acceso completo a todos los módulos.</p>
                            </div>
                        </div>
                    </div>
                    <!-- /DUNNING -->

                    <div class="flex items-center justify-end">
                        <transition leave-active-class="transition ease-in duration-1000" leave-from-class="opacity-100" leave-to-class="opacity-0">
                            <span v-if="form.recentlySuccessful" class="text-sm text-emerald-600 mr-3">Guardado correctamente.</span>
                        </transition>
                        <PrimaryButton
                            class="bg-emerald-600 hover:bg-emerald-700 focus:bg-emerald-700 active:bg-emerald-900"
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                        >
                            Guardar Configuración
                        </PrimaryButton>
                    </div>
                </form>

                <!-- Conceptos de Facturación Extraordinaria -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-200 mt-6">
                    <div class="p-6 border-b border-slate-200 bg-slate-50">
                        <h3 class="text-lg font-medium text-slate-900">Conceptos de Facturación Extraordinaria</h3>
                        <p class="mt-1 text-sm text-slate-500">Configura los conceptos para multas, cobros extra y alquileres.</p>
                    </div>
                    <div class="p-6 space-y-6">
                        <!-- Form to add concept -->
                        <form @submit.prevent="submitConcept" class="grid grid-cols-1 md:grid-cols-12 gap-6 items-end">
                            <div class="md:col-span-5">
                                <InputLabel for="concept_name" value="Nombre del Concepto" />
                                <TextInput
                                    id="concept_name"
                                    type="text"
                                    class="mt-1 block w-full focus:ring-emerald-500 focus:border-emerald-500"
                                    v-model="conceptForm.name"
                                    required
                                />
                                <InputError class="mt-2" :message="conceptForm.errors.name" />
                            </div>
                            <div class="md:col-span-4">
                                <InputLabel for="concept_type" value="Tipo" />
                                <select
                                    id="concept_type"
                                    v-model="conceptForm.type"
                                    required
                                    class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-md shadow-sm"
                                >
                                    <option value="" disabled selected>Seleccione un tipo...</option>
                                    <option value="fine">Multa</option>
                                    <option value="extraordinary">Cuota Extraordinaria</option>
                                    <option value="amenity_rental">Alquiler</option>
                                    <option value="discount">Descuento / Condonación</option>
                                </select>
                                <InputError class="mt-2" :message="conceptForm.errors.type" />
                            </div>
                            <div class="md:col-span-3">
                                <PrimaryButton
                                    class="bg-emerald-600 hover:bg-emerald-700 w-full justify-center"
                                    :class="{ 'opacity-25': conceptForm.processing }"
                                    :disabled="conceptForm.processing"
                                >
                                    + Agregar concepto
                                </PrimaryButton>
                            </div>
                        </form>

                        <!-- List of existing concepts -->
                        <div v-if="props.billingConcepts && props.billingConcepts.length > 0" class="mt-6">
                            <table class="min-w-full divide-y divide-gray-200 border border-gray-200 rounded-md">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="concept in props.billingConcepts" :key="concept.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ concept.name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <span v-if="concept.type === 'fine'">Multa</span>
                                            <span v-else-if="concept.type === 'extraordinary'">Cuota Extraordinaria</span>
                                            <span v-else-if="concept.type === 'amenity_rental'">Alquiler</span>
                                            <span v-else-if="concept.type === 'discount'">Descuento / Condonación</span>
                                            <span v-else>{{ concept.type }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <button @click="confirmConceptDeletion(concept)" class="text-red-500 hover:text-red-700" title="Eliminar">
                                                <TrashIcon class="w-5 h-5 inline-block" />
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <ConfirmDeleteModal
            :show="confirmingAccountDeletion"
            title="¿Eliminar Cuenta Bancaria?"
            message="Esta acción eliminará la cuenta de las opciones disponibles y actualizará la configuración inmediatamente. ¿Estás seguro?"
            @close="confirmingAccountDeletion = false"
            @confirm="executeAccountDeletion"
        />

        <ConfirmDeleteModal
            :show="confirmingConceptDeletion"
            title="¿Eliminar Concepto de Facturación?"
            message="Si eliminas este concepto no podrá usarse en nuevas facturas. Esta acción no se puede deshacer."
            @close="confirmingConceptDeletion = false"
            @confirm="executeConceptDeletion"
        />
    </AppLayout>
</template>
