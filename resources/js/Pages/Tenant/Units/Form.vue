<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    unit: {
        id?: number;
        identifier: string;
        type: string;
        status: string;
    };
}>();

const page = usePage();
const communitySlug = computed(() => page.url.split('/')[2]);

const isEditing = computed(() => !!props.unit.id);

const form = useForm({
    identifier: props.unit.identifier || '',
    type: props.unit.type || 'apartment',
    status: props.unit.status || 'occupied',
});

const submit = () => {
    if (isEditing.value) {
        form.put(route('units.update', { community_slug: communitySlug.value, unit: props.unit.id }));
    } else {
        form.post(route('units.store', { community_slug: communitySlug.value }));
    }
};
</script>

<template>
    <Head :title="isEditing ? 'Editar Unidad' : 'Nueva Unidad'" />

    <AppLayout>
        <template #header>
            <div class="px-4 py-4 sm:px-6 lg:px-8">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    {{ isEditing ? 'Editar Unidad' : 'Nueva Unidad' }}
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="bg-white shadow sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6">
                        <div class="space-y-6">
                            <div>
                                <label for="identifier" class="block text-sm font-medium leading-6 text-gray-900">Identificador</label>
                                <div class="mt-2">
                                    <input type="text" id="identifier" v-model="form.identifier" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" placeholder="Ej: Apto 101" />
                                </div>
                                <p v-if="form.errors.identifier" class="mt-2 text-sm text-red-600">{{ form.errors.identifier }}</p>
                            </div>

                            <div>
                                <label for="type" class="block text-sm font-medium leading-6 text-gray-900">Tipo de Unidad</label>
                                <div class="mt-2">
                                    <select id="type" v-model="form.type" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                        <option value="apartment">Apartamento</option>
                                        <option value="house">Casa</option>
                                        <option value="local">Local</option>
                                        <option value="storage">Depósito</option>
                                        <option value="parking">Parqueadero</option>
                                    </select>
                                </div>
                                <p v-if="form.errors.type" class="mt-2 text-sm text-red-600">{{ form.errors.type }}</p>
                            </div>

                            <div>
                                <label for="status" class="block text-sm font-medium leading-6 text-gray-900">Estado</label>
                                <div class="mt-2">
                                    <select id="status" v-model="form.status" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                        <option value="occupied">Ocupada</option>
                                        <option value="vacant">Vacante</option>
                                        <option value="maintenance">Mantenimiento</option>
                                    </select>
                                </div>
                                <p v-if="form.errors.status" class="mt-2 text-sm text-red-600">{{ form.errors.status }}</p>
                            </div>
                        </div>

                        <div class="mt-6 flex items-center justify-end gap-x-6">
                            <button type="button" onclick="window.history.back()" class="text-sm font-semibold leading-6 text-gray-900">Cancelar</button>
                            <button type="submit" :disabled="form.processing" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                                {{ isEditing ? 'Guardar Cambios' : 'Crear Unidad' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
