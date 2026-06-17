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
});

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
