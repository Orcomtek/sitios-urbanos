<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';

const page = usePage();
const communitySlug = (page.props.tenant as any)?.community?.slug;

interface ContactDetail {
    type: string;
    value: string;
}

interface Provider {
    id: number;
    name: string;
    category: string;
    description: string | null;
    contact_details: ContactDetail[];
    status: string;
}

const providers = ref<Provider[]>([]);
const loading = ref(true);
const selectedCategory = ref<string>('');

const isModalOpen = ref(false);
const selectedProvider = ref<Provider | null>(null);

const form = ref({
    provider_id: null as number | null,
    description: '',
    urgency: 'low',
    scheduled_date: '',
});

const formErrors = ref<Record<string, string>>({});
const processing = ref(false);
const successMessage = ref('');

const categories = [
    { value: 'plumbing', label: 'Plomería' },
    { value: 'electrical', label: 'Electricidad' },
    { value: 'cleaning', label: 'Limpieza' },
    { value: 'maintenance', label: 'Mantenimiento' },
    { value: 'others', label: 'Otros' }
];

const filteredProviders = computed(() => {
    // Only active providers should be shown
    let activeProviders = providers.value.filter(p => p.status === 'active');
    
    if (selectedCategory.value) {
        return activeProviders.filter(p => p.category === selectedCategory.value);
    }
    return activeProviders;
});

const fetchProviders = async () => {
    loading.value = true;
    try {
        const response = await axios.get(route('api.ecosystem.providers.index', { community_slug: communitySlug }));
        providers.value = response.data.data;
    } catch (error) {
        console.error('Error fetching providers:', error);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchProviders();
});

const openRequestModal = (provider: Provider) => {
    selectedProvider.value = provider;
    form.value = {
        provider_id: provider.id,
        description: '',
        urgency: 'low',
        scheduled_date: '',
    };
    formErrors.value = {};
    successMessage.value = '';
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    selectedProvider.value = null;
};

const submitRequest = async () => {
    processing.value = true;
    formErrors.value = {};
    successMessage.value = '';
    try {
        await axios.post(route('api.ecosystem.service-requests.store', { community_slug: communitySlug }), form.value);
        successMessage.value = '¡Su solicitud de servicio ha sido enviada exitosamente!';
        
        // Close modal after brief delay
        setTimeout(() => {
            closeModal();
        }, 2000);
    } catch (error: any) {
        if (error.response?.data?.errors) {
            formErrors.value = error.response.data.errors;
        } else {
            console.error('Error submitting request:', error);
        }
    } finally {
        processing.value = false;
    }
};

const translateContactType = (type: string) => {
    switch (type) {
        case 'phone': return 'Teléfono';
        case 'email': return 'Correo Electrónico';
        case 'whatsapp': return 'WhatsApp';
        default: return type;
    }
};

// Urgency Enum Options based on PHP Backend
const urgencies = [
    { value: 'low', label: 'Baja (Cuando haya disponibilidad)' },
    { value: 'medium', label: 'Media (En los próximos días)' },
    { value: 'high', label: 'Alta (Lo antes posible)' }
];

const translateCategory = (catValue: string) => {
    const category = categories.find(c => c.value === catValue);
    return category ? category.label : catValue;
};
</script>

<template>
    <Head title="Directorio de Proveedores" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Directorio de Profesionales</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <!-- Filters -->
                <div class="mb-6 flex overflow-x-auto pb-2 gap-2 hide-scrollbar">
                    <button 
                        @click="selectedCategory = ''"
                        :class="['px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-colors', 
                                selectedCategory === '' ? 'bg-indigo-600 text-white shadow' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200']"
                    >
                        Todos
                    </button>
                    <button 
                        v-for="cat in categories" :key="cat.value"
                        @click="selectedCategory = cat.value"
                        :class="['px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-colors', 
                                selectedCategory === cat.value ? 'bg-indigo-600 text-white shadow' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200']"
                    >
                        {{ cat.label }}
                    </button>
                </div>

                <div v-if="loading" class="flex justify-center items-center py-20">
                    <svg class="animate-spin h-10 w-10 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>

                <div v-else-if="filteredProviders.length === 0" class="text-center py-20 bg-white rounded-xl shadow-sm border border-gray-100">
                    <svg class="mx-auto h-16 w-16 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <h3 class="mt-4 text-base font-semibold text-gray-900">Mesa limpia</h3>
                    <p class="mt-1 text-sm text-gray-500">No hay profesionales disponibles en esta categoría en este momento.</p>
                </div>

                <div v-else class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <!-- Cards -->
                    <div v-for="provider in filteredProviders" :key="provider.id" class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-100 flex flex-col hover:shadow-md transition-shadow">
                        <div class="p-6 flex-1">
                            <div class="flex items-center justify-between mb-4">
                                <span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-semibold text-indigo-700">
                                    {{ translateCategory(provider.category) }}
                                </span>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900 mb-2 truncate" :title="provider.name">{{ provider.name }}</h3>
                            <p class="text-sm text-gray-500 mb-4 line-clamp-2" :title="provider.description || ''">
                                {{ provider.description || 'Sin descripción detallada.' }}
                            </p>
                            
                            <div class="border-t border-gray-100 pt-4 mt-auto">
                                <h4 class="text-xs font-semibold text-gray-900 uppercase tracking-wider mb-2">Contacto Público</h4>
                                <ul class="space-y-2">
                                    <li v-for="(contact, idx) in provider.contact_details" :key="idx" class="text-sm text-gray-600 flex items-center">
                                        <svg v-if="contact.type === 'phone' || contact.type === 'whatsapp'" class="mr-1.5 h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.438a1 1 0 01-.328.968l-2.382 2.14a17.29 17.29 0 007.01 7.01l2.14-2.382a1 1 0 01.968-.328l4.438.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" />
                                        </svg>
                                        <svg v-else-if="contact.type === 'email'" class="mr-1.5 h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                                            <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                                        </svg>
                                        <svg v-else class="mr-1.5 h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                        </svg>
                                        {{ contact.value }}
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end">
                            <PrimaryButton @click="openRequestModal(provider)">Solicitar Servicio</PrimaryButton>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Solicitar Servicio Modal -->
        <Modal :show="isModalOpen" @close="closeModal" max-width="lg">
            <div class="p-6 relative">
                <h2 class="text-lg font-medium text-gray-900 mb-1">
                    Solicitar Servicio a {{ selectedProvider?.name }}
                </h2>
                <p class="text-sm text-gray-500 mb-6">Por favor, detalle el servicio que necesita y el nivel de urgencia.</p>

                <div v-if="successMessage" class="mb-4 rounded-md bg-green-50 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">{{ successMessage }}</p>
                        </div>
                    </div>
                </div>

                <div v-else class="space-y-5">
                    <div>
                        <InputLabel for="description" value="Descripción del trabajo requerido" />
                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="4"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm sm:text-sm"
                            placeholder="Ej. Fuga de agua en el lavamanos principal..."
                            required
                        ></textarea>
                        <InputError :message="formErrors.description" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="urgency" value="Nivel de Urgencia" />
                        <select
                            id="urgency"
                            v-model="form.urgency"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm sm:text-sm"
                        >
                            <option v-for="u in urgencies" :key="u.value" :value="u.value">{{ u.label }}</option>
                        </select>
                        <InputError :message="formErrors.urgency" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="scheduled_date" value="Fecha estimada (Opcional)" />
                        <TextInput
                            id="scheduled_date"
                            v-model="form.scheduled_date"
                            type="date"
                            class="mt-1 block w-full sm:text-sm"
                        />
                        <InputError :message="formErrors.scheduled_date" class="mt-2" />
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3" v-if="!successMessage">
                    <SecondaryButton @click="closeModal">Cancelar</SecondaryButton>
                    <PrimaryButton
                        :class="{ 'opacity-25': processing }"
                        :disabled="processing"
                        @click="submitRequest"
                    >
                        Enviar Solicitud
                    </PrimaryButton>
                </div>
            </div>
        </Modal>

    </AppLayout>
</template>

<style scoped>
.hide-scrollbar::-webkit-scrollbar {
    display: none;
}
.hide-scrollbar {
    -ms-overflow-style: none;  /* IE and Edge */
    scrollbar-width: none;  /* Firefox */
}
</style>
