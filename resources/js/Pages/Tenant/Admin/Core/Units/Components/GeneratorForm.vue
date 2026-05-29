<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const emit = defineEmits(['success']);

const page = usePage();
const communitySlug = computed(() => (page.props.tenant as any)?.community?.slug);

const form = useForm({
    blocks: 1,
    floors: 1,
    units: 1,
    pattern: '{B}-{P}{U}',
    property_type: 'apartment',
});

const submit = () => {
    form.post(route('tenant.admin.core.units.generator.generate', { community_slug: communitySlug.value }), {
        preserveState: true,
        onSuccess: () => {
            emit('success');
            form.reset();
        }
    });
};

const getTaxonomies = (type: string) => {
    return (page.props.taxonomies as any)?.[type] || [
        { value: 'apartment', label: 'Apartamento' },
        { value: 'house', label: 'Casa' },
        { value: 'commercial', label: 'Local Comercial' },
    ];
};
</script>

<template>
    <div class="bg-white shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-base font-semibold leading-6 text-gray-900">Paso 1: Parámetros del Sector</h3>
            <div class="mt-2 max-w-xl text-sm text-gray-500">
                <p>Define la estructura topológica. Los identificadores se generarán usando el patrón de nomenclatura.</p>
            </div>
            <form @submit.prevent="submit" class="mt-5 sm:flex sm:items-center flex-wrap gap-4">
                
                <div class="w-full sm:max-w-xs">
                    <label for="pattern" class="block text-sm font-medium leading-6 text-gray-900">Patrón de Nomenclatura</label>
                    <input type="text" v-model="form.pattern" id="pattern" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" placeholder="Torre {B} - Apto {P}{U}" />
                    <p class="text-xs text-gray-500 mt-1">{B} = Bloque, {P} = Piso, {U} = Unidad</p>
                </div>

                <div class="w-full sm:max-w-[100px]">
                    <label for="blocks" class="block text-sm font-medium leading-6 text-gray-900">Bloques</label>
                    <input type="number" v-model="form.blocks" id="blocks" min="1" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                </div>

                <div class="w-full sm:max-w-[100px]">
                    <label for="floors" class="block text-sm font-medium leading-6 text-gray-900">Pisos</label>
                    <input type="number" v-model="form.floors" id="floors" min="1" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                </div>

                <div class="w-full sm:max-w-[100px]">
                    <label for="units" class="block text-sm font-medium leading-6 text-gray-900">Unidades x Piso</label>
                    <input type="number" v-model="form.units" id="units" min="1" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                </div>

                <div class="w-full sm:max-w-xs">
                    <label for="property_type" class="block text-sm font-medium leading-6 text-gray-900">Tipo de Propiedad</label>
                    <select id="property_type" v-model="form.property_type" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                        <option v-for="type in getTaxonomies('property_type')" :key="type.value" :value="type.value">
                            {{ type.label }}
                        </option>
                    </select>
                </div>

                <div class="w-full mt-4 sm:mt-0 sm:flex sm:flex-shrink-0 sm:items-end">
                    <button type="submit" :disabled="form.processing" class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 sm:mt-6">
                        {{ form.processing ? 'Generando...' : 'Generar' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
