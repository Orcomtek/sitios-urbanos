<script setup>
import { ref, onMounted, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import ListingCard from './Partials/ListingCard.vue';
import ListingForm from './Partials/ListingForm.vue';

const props = defineProps({
    resident_id: {
        type: Number,
        required: true
    }
});

const activeTab = ref('explore');
const allListings = ref([]);
const loading = ref(true);
const processing = ref(false);
const showForm = ref(false);
const editingListing = ref(null);
const successMessage = ref('');

const fetchListings = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/ecosystem/listings');
        allListings.value = response.data.data;
    } catch (e) {
        console.error('Error fetching listings', e);
        alert('Error al cargar los anuncios');
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchListings();
});

const exploreListings = computed(() => {
    return allListings.value.filter(l => 
        l.status === 'active' && 
        (l.resident && l.resident.id !== props.resident_id)
    );
});

const myListings = computed(() => {
    return allListings.value.filter(l => 
        l.resident && l.resident.id === props.resident_id
    );
});

const submitForm = async (formData) => {
    processing.value = true;
    successMessage.value = '';
    
    try {
        if (editingListing.value) {
            await axios.patch(`/api/ecosystem/listings/${editingListing.value.id}`, formData);
            successMessage.value = 'Anuncio actualizado correctamente.';
        } else {
            await axios.post('/api/ecosystem/listings', formData);
            successMessage.value = 'Anuncio publicado correctamente.';
        }
        
        await fetchListings();
        showForm.value = false;
        editingListing.value = null;
        
        setTimeout(() => { successMessage.value = ''; }, 3000);
    } catch (e) {
        console.error('Error saving listing', e);
        alert(e.response?.data?.message || 'Error al guardar el anuncio.');
    } finally {
        processing.value = false;
    }
};

const handleEdit = (listing) => {
    editingListing.value = listing;
    showForm.value = true;
    activeTab.value = 'my_listings';
};

const toggleStatus = async (listing) => {
    if (processing.value) return;
    
    const newStatus = listing.status === 'active' ? 'paused' : 'active';
    
    try {
        processing.value = true;
        await axios.patch(`/api/ecosystem/listings/${listing.id}`, {
            status: newStatus
        });
        await fetchListings();
    } catch (e) {
        console.error('Error toggling status', e);
        alert(e.response?.data?.message || 'Error al cambiar el estado del anuncio.');
    } finally {
        processing.value = false;
    }
};

const cancelForm = () => {
    showForm.value = false;
    editingListing.value = null;
};
</script>

<template>
    <AppLayout title="Ecosistema Local">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Ecosistema Local (Mercado de Vecinos)
                </h2>
                <button 
                    v-if="!showForm"
                    @click="showForm = true; editingListing = null; activeTab = 'my_listings';"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700"
                >
                    + Publicar Anuncio
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <div v-if="successMessage" class="bg-green-50 border-l-4 border-green-500 p-4 rounded-md shadow-sm">
                    <p class="text-green-700 text-sm font-medium">{{ successMessage }}</p>
                </div>

                <div v-if="showForm" class="mb-8">
                    <h3 class="text-lg font-bold mb-4 text-gray-800">{{ editingListing ? 'Editar Anuncio' : 'Nuevo Anuncio' }}</h3>
                    <ListingForm 
                        :listing="editingListing" 
                        :processing="processing"
                        @submit="submitForm" 
                        @cancel="cancelForm" 
                    />
                </div>

                <div v-else class="bg-white border rounded-lg shadow-sm border-gray-200 overflow-hidden">
                    <div class="border-b border-gray-200 bg-gray-50 flex">
                        <button 
                            @click="activeTab = 'explore'" 
                            class="px-6 py-4 font-medium text-sm flex-1 sm:flex-none border-b-2 outline-none"
                            :class="activeTab === 'explore' ? 'border-indigo-600 text-indigo-600 bg-white' : 'border-transparent text-gray-500 hover:text-gray-700'"
                        >
                            Explorar Comunidad
                        </button>
                        <button 
                            @click="activeTab = 'my_listings'" 
                            class="px-6 py-4 font-medium text-sm flex-1 sm:flex-none border-b-2 outline-none"
                            :class="activeTab === 'my_listings' ? 'border-indigo-600 text-indigo-600 bg-white' : 'border-transparent text-gray-500 hover:text-gray-700'"
                        >
                            Mis Anuncios
                        </button>
                    </div>

                    <div class="p-6">
                        <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div v-for="i in 3" :key="i" class="animate-pulse bg-gray-200 h-64 rounded-xl" />
                        </div>

                        <div v-else>
                            <!-- Tab: Explore -->
                            <div v-show="activeTab === 'explore'">
                                <div v-if="exploreListings.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    <ListingCard 
                                        v-for="listing in exploreListings" 
                                        :key="listing.id" 
                                        :listing="listing" 
                                    />
                                </div>
                                <div v-else class="text-center py-12">
                                    <p class="text-gray-500 italic">No hay anuncios activos en la comunidad en este momento.</p>
                                </div>
                            </div>

                            <!-- Tab: My Listings -->
                            <div v-show="activeTab === 'my_listings'">
                                <div v-if="myListings.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    <ListingCard 
                                        v-for="listing in myListings" 
                                        :key="listing.id" 
                                        :listing="listing"
                                        :is-owner="true"
                                        @edit="handleEdit"
                                        @toggle-status="toggleStatus"
                                    />
                                </div>
                                <div v-else class="text-center py-12">
                                    <p class="text-gray-500 italic mb-4">Aún no has publicado ningún anuncio.</p>
                                    <button 
                                        @click="showForm = true"
                                        class="inline-flex items-center px-4 py-2 border border-indigo-300 rounded-md font-semibold text-xs text-indigo-700 uppercase tracking-widest hover:bg-indigo-50"
                                    >
                                        Crear mi primer anuncio
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>
