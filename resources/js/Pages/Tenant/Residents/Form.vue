<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    resident: {
        id?: number;
        unit_id?: number;
        first_name: string;
        last_name: string;
        email: string | null;
        phone: string | null;
        type: string;
        status: string;
    };
    units: Array<{
        id: number;
        identifier: string;
    }>;
}>();

const page = usePage();
const communitySlug = computed(() => page.url.split('/')[2]);

const isEditing = computed(() => !!props.resident.id);

const form = useForm({
    unit_id: props.resident.unit_id || '',
    first_name: props.resident.first_name || '',
    last_name: props.resident.last_name || '',
    email: props.resident.email || '',
    phone: props.resident.phone || '',
    type: props.resident.type || 'tenant',
    status: props.resident.status || 'active',
});

const submit = () => {
    if (isEditing.value) {
        form.put(route('residents.update', { community_slug: communitySlug.value, resident: props.resident.id }));
    } else {
        form.post(route('residents.store', { community_slug: communitySlug.value }));
    }
};

const goBack = () => {
    window.history.back();
};
</script>

<template>
    <Head :title="isEditing ? 'Editar Residente' : 'Nuevo Residente'" />

    <AppLayout>
        <template #header>
            <div class="px-4 py-4 sm:px-6 lg:px-8">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    {{ isEditing ? 'Editar Residente' : 'Nuevo Residente' }}
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="bg-white shadow sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6">
                        <div class="space-y-6">
                            
                            <div>
                                <label for="unit_id" class="block text-sm font-medium leading-6 text-gray-900">Unidad</label>
                                <div class="mt-2">
                                    <select id="unit_id" v-model="form.unit_id" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                        <option value="" disabled>Seleccione una unidad</option>
                                        <option v-for="unit in units" :key="unit.id" :value="unit.id">{{ unit.identifier }}</option>
                                    </select>
                                </div>
                                <p v-if="form.errors.unit_id" class="mt-2 text-sm text-red-600">{{ form.errors.unit_id }}</p>
                            </div>

                            <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-2">
                                <div>
                                    <label for="first_name" class="block text-sm font-medium leading-6 text-gray-900">Nombres</label>
                                    <div class="mt-2">
                                        <input type="text" id="first_name" v-model="form.first_name" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                                    </div>
                                    <p v-if="form.errors.first_name" class="mt-2 text-sm text-red-600">{{ form.errors.first_name }}</p>
                                </div>

                                <div>
                                    <label for="last_name" class="block text-sm font-medium leading-6 text-gray-900">Apellidos</label>
                                    <div class="mt-2">
                                        <input type="text" id="last_name" v-model="form.last_name" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                                    </div>
                                    <p v-if="form.errors.last_name" class="mt-2 text-sm text-red-600">{{ form.errors.last_name }}</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-2">
                                <div>
                                    <label for="email" class="block text-sm font-medium leading-6 text-gray-900">Correo Electrónico (Opcional)</label>
                                    <div class="mt-2">
                                        <input type="email" id="email" v-model="form.email" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                                    </div>
                                    <p v-if="form.errors.email" class="mt-2 text-sm text-red-600">{{ form.errors.email }}</p>
                                </div>

                                <div>
                                    <label for="phone" class="block text-sm font-medium leading-6 text-gray-900">Teléfono (Opcional)</label>
                                    <div class="mt-2">
                                        <input type="text" id="phone" v-model="form.phone" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                                    </div>
                                    <p v-if="form.errors.phone" class="mt-2 text-sm text-red-600">{{ form.errors.phone }}</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-2">
                                <div>
                                    <label for="type" class="block text-sm font-medium leading-6 text-gray-900">Tipo de Residente</label>
                                    <div class="mt-2">
                                        <select id="type" v-model="form.type" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                            <option value="owner">Propietario</option>
                                            <option value="tenant">Arrendatario</option>
                                        </select>
                                    </div>
                                    <p v-if="form.errors.type" class="mt-2 text-sm text-red-600">{{ form.errors.type }}</p>
                                </div>

                                <div>
                                    <label for="status" class="block text-sm font-medium leading-6 text-gray-900">Estado</label>
                                    <div class="mt-2">
                                        <select id="status" v-model="form.status" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                            <option value="active">Activo</option>
                                            <option value="inactive">Inactivo</option>
                                        </select>
                                    </div>
                                    <p v-if="form.errors.status" class="mt-2 text-sm text-red-600">{{ form.errors.status }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex items-center justify-end gap-x-6">
                            <button type="button" @click="goBack" class="text-sm font-semibold leading-6 text-gray-900">Cancelar</button>
                            <button type="submit" :disabled="form.processing" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                                {{ isEditing ? 'Guardar Cambios' : 'Crear Residente' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
