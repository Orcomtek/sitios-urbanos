<script setup>
import { computed } from 'vue';

const props = defineProps({
    listing: {
        type: Object,
        required: true
    },
    isOwner: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['edit', 'toggle-status']);

const statusMap = {
    active: { label: 'Activo', bg: 'bg-green-100', text: 'text-green-800' },
    paused: { label: 'Pausado', bg: 'bg-yellow-100', text: 'text-yellow-800' },
    reported: { label: 'Reportado', bg: 'bg-red-100', text: 'text-red-800' },
    removed: { label: 'Removido', bg: 'bg-gray-100', text: 'text-gray-800' }
};

const categoryMap = {
    items: 'Artículos',
    services: 'Servicios',
    real_estate: 'Inmuebles'
};

const formattedPrice = computed(() => {
    if (!props.listing.price) return 'Gratis o a convenir';
    return new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP',
        minimumFractionDigits: 0
    }).format(props.listing.price);
});
</script>

<template>
    <div class="bg-white border rounded-lg shadow-sm border-gray-200 overflow-hidden flex flex-col h-full">
        <div class="p-5 flex-1 flex flex-col space-y-4">
            <div class="flex justify-between items-start">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    {{ categoryMap[listing.category] || listing.category }}
                </span>
                <span v-if="isOwner && statusMap[listing.status]" 
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" 
                      :class="[statusMap[listing.status].bg, statusMap[listing.status].text]">
                    {{ statusMap[listing.status].label }}
                </span>
            </div>
            
            <div>
                <h3 class="text-lg font-bold text-gray-900 line-clamp-2">{{ listing.title }}</h3>
                <p class="text-xl font-semibold text-indigo-600 mt-1">{{ formattedPrice }}</p>
            </div>
            
            <p class="text-gray-600 text-sm flex-1 whitespace-pre-wrap line-clamp-4">{{ listing.description }}</p>
            
            <div class="pt-4 mt-4 border-t border-gray-100">
                <div v-if="listing.resident" class="space-y-2">
                    <p class="text-sm font-medium text-gray-900">Por: {{ listing.resident.name }}</p>
                    
                    <div v-if="listing.show_contact_info && (listing.resident.phone || listing.resident.email)" class="text-xs text-gray-600 space-y-1">
                        <p v-if="listing.resident.phone" class="flex items-center">
                            <span class="font-medium mr-2">Tel:</span> {{ listing.resident.phone }}
                        </p>
                        <p v-if="listing.resident.email" class="flex items-center">
                            <span class="font-medium mr-2">Email:</span> {{ listing.resident.email }}
                        </p>
                    </div>
                    <div v-else class="text-xs text-gray-500 italic bg-gray-50 p-2 rounded">
                        Contacto vía administración (Info protegida)
                    </div>
                </div>
            </div>
        </div>
        
        <div v-if="isOwner" class="bg-gray-50 px-5 py-3 border-t border-gray-200 flex justify-end space-x-3">
            <button 
                v-if="['active', 'paused'].includes(listing.status)"
                @click="emit('toggle-status', listing)" 
                class="text-sm font-medium hover:underline focus:outline-none"
                :class="listing.status === 'active' ? 'text-yellow-600 hover:text-yellow-800' : 'text-green-600 hover:text-green-800'"
            >
                {{ listing.status === 'active' ? 'Pausar' : 'Reactivar' }}
            </button>
            <button 
                v-if="['active', 'paused'].includes(listing.status)"
                @click="emit('edit', listing)" 
                class="text-sm font-medium text-indigo-600 hover:text-indigo-900 focus:outline-none hover:underline"
            >
                Editar
            </button>
            <span v-if="['reported', 'removed'].includes(listing.status)" class="text-xs text-red-600 italic">
                Requiere revisión administrativa
            </span>
        </div>
    </div>
</template>
