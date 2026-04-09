<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Head, usePage, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
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

const isModalOpen = ref(false);
const isEditing = ref(false);
const currentProviderId = ref<number | null>(null);

const form = useForm({
    name: '',
    category: 'maintenance',
    description: '',
    status: 'active',
    contact_details: [{ type: 'phone', value: '' }] as ContactDetail[],
});

const categories = [
    { value: 'plumbing', label: 'Plomería' },
    { value: 'electrical', label: 'Electricidad' },
    { value: 'cleaning', label: 'Limpieza' },
    { value: 'maintenance', label: 'Mantenimiento' },
    { value: 'others', label: 'Otros' }
];
const contactTypes = ['phone', 'email', 'whatsapp'];

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

const openCreateModal = () => {
    isEditing.value = false;
    currentProviderId.value = null;
    form.clearErrors();
    form.defaults({
        name: '',
        category: 'maintenance',
        description: '',
        status: 'active',
        contact_details: [{ type: 'phone', value: '' }],
    }).reset();
    isModalOpen.value = true;
};

const openEditModal = (provider: Provider) => {
    isEditing.value = true;
    currentProviderId.value = provider.id;
    form.clearErrors();
    form.defaults({
        name: provider.name,
        category: provider.category,
        description: provider.description || '',
        status: provider.status,
        contact_details: provider.contact_details && provider.contact_details.length > 0
            ? JSON.parse(JSON.stringify(provider.contact_details))
            : [{ type: 'phone', value: '' }],
    }).reset();
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
};

const addContact = () => {
    form.contact_details.push({ type: 'phone', value: '' });
};

const removeContact = (index: number) => {
    form.contact_details.splice(index, 1);
};

const submitForm = async () => {
    form.clearErrors();
    try {
        if (isEditing.value && currentProviderId.value) {
            await axios.patch(route('api.ecosystem.providers.update', { community_slug: communitySlug, provider: currentProviderId.value }), form.data());
        } else {
            await axios.post(route('api.ecosystem.providers.store', { community_slug: communitySlug }), form.data());
        }
        closeModal();
        fetchProviders();
    } catch (e: any) {
        if (e.response && e.response.status === 422) {
            console.error("VALIDATION ERRORS:", e.response.data.errors);
            form.clearErrors();
            const errors = e.response.data.errors;
            for (const key in errors) {
                form.setError(key as any, errors[key][0]); 
            }
        } else {
            console.error('Error submitting form:', e);
        }
    }
};

const toggleStatus = async (provider: Provider) => {
    try {
        const newStatus = provider.status === 'active' ? 'inactive' : 'active';
        await axios.patch(route('api.ecosystem.providers.update', { community_slug: communitySlug, provider: provider.id }), {
            status: newStatus,
        });
        fetchProviders();
    } catch (error) {
        console.error('Error toggling status:', error);
    }
};

const deleteProvider = async (id: number) => {
    if (!confirm('¿Está seguro de que desea eliminar este proveedor?')) return;
    try {
        await axios.delete(route('api.ecosystem.providers.destroy', { community_slug: communitySlug, provider: id }));
        fetchProviders();
    } catch (error) {
        console.error('Error deleting provider:', error);
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
const translateCategory = (catValue: string) => {
    const category = categories.find(c => c.value === catValue);
    return category ? category.label : catValue;
};

const getError = (key: string): string | undefined => {
    return (form.errors as any)[key];
};
</script>

<template>
    <Head title="Directorio de Proveedores" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Gestión de Proveedores</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 border-b border-gray-200">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-bold text-gray-800">Directorio Interno</h3>
                            <button @click="openCreateModal" class="px-4 py-2 bg-emerald-600 text-white text-sm font-bold rounded-md hover:bg-emerald-700 transition-colors">
                                + Añadir Proveedor
                            </button>
                        </div>
                        
                        <div v-if="loading" class="text-center py-10">
                            <svg class="animate-spin h-8 w-8 text-indigo-600 mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <p class="text-gray-500">Cargando proveedores...</p>
                        </div>

                        <div v-else-if="providers.length === 0" class="text-center py-16 px-4 sm:px-6 lg:px-8 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-semibold text-gray-900">Mesa limpia</h3>
                            <p class="mt-1 text-sm text-gray-500">Aún no hay proveedores registrados. Añade uno para comenzar.</p>
                            <div class="mt-6">
                                <PrimaryButton @click="openCreateModal">
                                    <svg class="-ml-0.5 mr-1.5 h-5 w-5 inline" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                                    </svg>
                                    Añadir Proveedor
                                </PrimaryButton>
                            </div>
                        </div>

                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-300">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Nombre del Proveedor</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Categoría</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Contactos</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Estado</th>
                                        <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                            <span class="sr-only">Acciones</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    <tr v-for="provider in providers" :key="provider.id" :class="{'opacity-50': provider.status !== 'active'}">
                                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                            {{ provider.name }}
                                            <div class="font-normal text-gray-500 text-xs truncate max-w-xs">{{ provider.description }}</div>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">{{ translateCategory(provider.category) }}</span>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            <ul class="list-none m-0 p-0">
                                                <li v-for="(contact, idx) in provider.contact_details" :key="idx">
                                                    {{ translateContactType(contact.type) }}: {{ contact.value }}
                                                </li>
                                            </ul>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            <span v-if="provider.status === 'active'" class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">Activo</span>
                                            <span v-else class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">Inactivo</span>
                                        </td>
                                        <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                            <button @click="toggleStatus(provider)" class="text-indigo-600 hover:text-indigo-900 mr-4">
                                                {{ provider.status === 'active' ? 'Desactivar' : 'Activar' }}
                                            </button>
                                            <button @click="openEditModal(provider)" class="text-indigo-600 hover:text-indigo-900 mr-4">Editar</button>
                                            <button @click="deleteProvider(provider.id)" class="text-red-600 hover:text-red-900">Eliminar</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <Modal :show="isModalOpen" @close="closeModal" max-width="2xl">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    {{ isEditing ? 'Editar Proveedor' : 'Enlistar Nuevo Proveedor' }}
                </h2>

                <div class="mt-6 space-y-4">
                    <div>
                        <InputLabel for="name" value="Nombre del Proveedor" />
                        <TextInput
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="mt-1 block w-full"
                            required
                        />
                        <p class="text-sm text-red-600 mt-1" v-if="form.errors.name">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <InputLabel for="category" value="Categoría" />
                        <select
                            id="category"
                            v-model="form.category"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        >
                            <option v-for="cat in categories" :key="cat.value" :value="cat.value">{{ cat.label }}</option>
                        </select>
                        <p class="text-sm text-red-600 mt-1" v-if="form.errors.category">{{ form.errors.category }}</p>
                    </div>

                    <div>
                        <InputLabel for="description" value="Descripción (Opcional)" />
                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="3"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        ></textarea>
                        <p class="text-sm text-red-600 mt-1" v-if="form.errors.description">{{ form.errors.description }}</p>
                    </div>

                    <div class="border-t border-gray-200 pt-4 mt-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-sm font-medium text-gray-900">Datos de Contacto</h3>
                            <button type="button" @click="addContact" class="text-sm text-indigo-600 hover:text-indigo-900 font-medium">
                                + Agregar contacto
                            </button>
                        </div>
                        
                        <div v-for="(contact, index) in form.contact_details" :key="index" class="flex gap-4 items-start mb-4">
                            <div class="w-1/3">
                                <select
                                    v-model="contact.type"
                                    class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                                >
                                    <option value="phone">Teléfono</option>
                                    <option value="whatsapp">WhatsApp</option>
                                    <option value="email">Correo Electrónico</option>
                                </select>
                                <p class="text-sm text-red-600 mt-1" v-if="getError(`contact_details.${index}.type`)">{{ getError(`contact_details.${index}.type`) }}</p>
                            </div>
                            <div class="w-full">
                                <TextInput
                                    v-model="contact.value"
                                    type="text"
                                    class="block w-full text-sm"
                                    placeholder="Ej. +57 300..."
                                    required
                                />
                                <p class="text-sm text-red-600 mt-1" v-if="getError(`contact_details.${index}.value`)">{{ getError(`contact_details.${index}.value`) }}</p>
                            </div>
                            <button type="button" @click="removeContact(index)" class="mt-2 text-red-600 hover:text-red-900" :disabled="form.contact_details.length === 1">
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 006 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 10.23 1.482l.149-.022.841 10.518A2.75 2.75 0 007.596 19h4.807a2.75 2.75 0 002.742-2.53l.841-10.52.149.023a.75.75 0 00.23-1.482A41.03 41.03 0 0014 4.193V3.75A2.75 2.75 0 0011.25 1h-2.5zM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4zM8.58 7.72a.75.75 0 00-1.5.06l.3 7.5a.75.75 0 101.5-.06l-.3-7.5zm4.34.06a.75.75 0 10-1.5-.06l-.3 7.5a.75.75 0 101.5.06l.3-7.5z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>

                </div>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="closeModal">Cancelar</SecondaryButton>
                    <PrimaryButton
                        class="ml-3"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="submitForm"
                    >
                        {{ isEditing ? 'Guardar Cambios' : 'Añadir Proveedor' }}
                    </PrimaryButton>
                </div>
            </div>
        </Modal>

    </AppLayout>
</template>
