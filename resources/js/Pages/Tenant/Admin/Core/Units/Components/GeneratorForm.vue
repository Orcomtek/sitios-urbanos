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
            <form @submit.prevent="submit" class="mt-5 grid grid-cols-1 md:grid-cols-5 gap-4 items-start">
                
                <div>
                    <label for="pattern" class="block text-sm font-medium leading-6 text-gray-900">Patrón de Nomenclatura</label>
                    <input type="text" v-model="form.pattern" id="pattern" class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-slate-900 sm:text-sm sm:leading-6" placeholder="Torre {B} - Apto {P}{U}" />
                    <p class="text-xs text-gray-500 mt-1">{B} = Bloque, {P} = Piso, {U} = Unidad</p>
                </div>

                <div>
                    <label for="blocks" class="block text-sm font-medium leading-6 text-gray-900">Bloques</label>
                    <input type="number" v-model="form.blocks" id="blocks" min="1" class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-slate-900 sm:text-sm sm:leading-6" />
                </div>

                <div>
                    <label for="floors" class="block text-sm font-medium leading-6 text-gray-900">Pisos</label>
                    <input type="number" v-model="form.floors" id="floors" min="1" class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-slate-900 sm:text-sm sm:leading-6" />
                </div>

                <div>
                    <label for="units" class="block text-sm font-medium leading-6 text-gray-900">Unidades x Piso</label>
                    <input type="number" v-model="form.units" id="units" min="1" class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-slate-900 sm:text-sm sm:leading-6" />
                </div>

                <div>
                    <label for="property_type" class="block text-sm font-medium leading-6 text-gray-900">Tipo de Propiedad</label>
                    <select id="property_type" v-model="form.property_type" class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-slate-900 sm:text-sm sm:leading-6">
                        <option v-for="type in getTaxonomies('property_type')" :key="type.value" :value="type.value">
                            {{ type.label }}
                        </option>
                    </select>
                </div>

                <div class="md:col-span-5 flex justify-end mt-2">
                    <button type="submit" :disabled="form.processing" class="inline-flex items-center justify-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus:ring-emerald-500 focus-visible:outline-emerald-500 transition-colors">
                        {{ form.processing ? 'Generando...' : 'Generar' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
