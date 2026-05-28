<script setup>
import { ref, onMounted } from 'vue';

const props = defineProps({
    listing: {
        type: Object,
        default: null
    },
    processing: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['submit', 'cancel']);

const form = ref({
    title: '',
    description: '',
    price: '',
    category: 'items',
    show_contact_info: true
});

onMounted(() => {
    if (props.listing) {
        form.value = {
            title: props.listing.title,
            description: props.listing.description,
            price: props.listing.price || '',
            category: props.listing.category,
            show_contact_info: props.listing.show_contact_info
        };
    }
});

const handleSubmit = () => {
    emit('submit', { ...form.value });
};
</script>

<template>
    <form @submit.prevent="handleSubmit" class="space-y-6 bg-white p-6 rounded-lg border border-gray-200 shadow-sm">
        <div>
            <label class="block text-sm font-medium text-gray-700">Título del Anuncio *</label>
            <input 
                v-model="form.title" 
                type="text" 
                required 
                maxlength="100"
                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 sm:text-sm"
                placeholder="Ej. Vendo Bicicleta"
            >
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Descripción *</label>
            <textarea 
                v-model="form.description" 
                required 
                rows="4"
                maxlength="1000"
                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 sm:text-sm"
                placeholder="Describe el artículo o servicio..."
            ></textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700">Categoría *</label>
                <select 
                    v-model="form.category" 
                    required
                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 sm:text-sm"
                >
                    <option value="items">Artículos</option>
                    <option value="services">Servicios</option>
                    <option value="real_estate">Inmuebles</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Precio (COP)</label>
                <input 
                    v-model="form.price" 
                    type="number" 
                    min="0"
                    step="0.01"
                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 sm:text-sm"
                    placeholder="Dejar en blanco si es a convenir"
                >
            </div>
        </div>

        <div class="flex items-start">
            <div class="flex h-5 items-center">
                <input 
                    id="show_contact_info" 
                    v-model="form.show_contact_info" 
                    type="checkbox" 
                    class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                >
            </div>
            <div class="ml-3 text-sm">
                <label for="show_contact_info" class="font-medium text-gray-700">Mostrar mi información de contacto</label>
                <p class="text-gray-500">Si se desmarca, los vecinos no podrán ver tu número o email. Contactarán vía administración.</p>
            </div>
        </div>

        <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
            <button 
                type="button" 
                @click="emit('cancel')"
                class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            >
                Cancelar
            </button>
            <button 
                type="submit" 
                :disabled="processing"
                class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50"
            >
                <svg v-if="processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ processing ? 'Guardando...' : (listing ? 'Actualizar Anuncio' : 'Publicar Anuncio') }}
            </button>
        </div>
    </form>
</template>
