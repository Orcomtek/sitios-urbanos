<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, usePage, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { TrashIcon, UserPlusIcon, EnvelopeIcon } from '@heroicons/vue/24/outline';
import ConfirmDeleteModal from '@/Components/ui/ConfirmDeleteModal.vue';
import InviteFormModal from './Components/InviteFormModal.vue';
import { useToast } from '@/Composables/useToast';

const props = defineProps<{
    sponsoredUsers: Array<any>;
    pendingInvitations: Array<any>;
    residentRole: string;
    currentResidentRole: string | null;
    unit: string;
    activeUnitTenant: { id: number, name: string } | null;
}>();

const page = usePage();
const communitySlug = computed(() => (page.props.tenant as any)?.community?.slug);
const { show: showToast } = useToast();

const isInviteModalOpen = ref(false);
const inviteRole = ref<'tenant' | 'family'>('family');

const openInviteModal = (role: 'tenant' | 'family') => {
    inviteRole.value = role;
    isInviteModalOpen.value = true;
};

// Deletion Logic
const isDeleteModalOpen = ref(false);
const isDeleting = ref(false);
const userToRevoke = ref<number | null>(null);

const confirmRevoke = (id: number) => {
    userToRevoke.value = id;
    isDeleteModalOpen.value = true;
};

const executeRevoke = () => {
    if (!userToRevoke.value) return;
    isDeleting.value = true;

    router.delete(route('tenant.resident.access.revoke', { 
        community_slug: communitySlug.value, 
        sponsoredUser: userToRevoke.value 
    }), {
        onSuccess: () => {
            showToast('Acceso revocado exitosamente. La desactivación en cascada se ha ejecutado.', 'success');
            isDeleteModalOpen.value = false;
            userToRevoke.value = null;
        },
        onFinish: () => {
            isDeleting.value = false;
        }
    });
};

const getRoleLabel = (role: string) => {
    if (role === 'owner' || role === 'propietario') return 'Propietario';
    if (role === 'tenant' || role === 'inquilino') return 'Inquilino';
    if (role === 'family') return 'Familiar';
    if (role === 'family_owner') return 'Familiar (Propietario)';
    if (role === 'family_tenant') return 'Familiar (Inquilino)';
    return role;
};

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('es-CO', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    }).format(date);
};
</script>

<template>
    <Head title="Mis Accesos" />

    <AppLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                
                <!-- NUEVA CABECERA INYECTADA EN EL MAIN SLOT (FUERA DEL AGUJERO NEGRO) -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0 border-b border-gray-200 pb-5">
                    <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                        Mis Accesos <span class="text-lg font-normal text-gray-500">(Usuarios Patrocinados)</span>
                    </h2>
                    <div class="flex space-x-4">
                        <!-- El botón de inquilino solo se muestra si es owner/propietario -->
                        <button 
                            v-if="currentResidentRole === 'owner' || currentResidentRole === 'propietario'" 
                            @click="openInviteModal('tenant')"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700 transition">
                            Invitar Inquilino
                        </button>
                        <!-- El botón de familiar se muestra siempre -->
                        <button 
                            @click="openInviteModal('family')"
                            class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-md text-sm font-medium hover:bg-gray-50 transition">
                            Invitar Familiar
                        </button>
                    </div>
                </div>

                <!-- Usuarios Patrocinados (Bento Grid Style) -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Usuarios Patrocinados</h3>
                    <div v-if="sponsoredUsers.length === 0" class="bg-white border text-card-foreground rounded-lg shadow-sm border-gray-100 p-8 text-center text-gray-500">
                        No has invitado a ningún usuario aún.
                    </div>
                    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div v-for="user in sponsoredUsers" :key="user.id" class="bg-white border text-card-foreground rounded-lg shadow-sm border-gray-100 p-6 flex flex-col justify-between">
                            <div>
                                <div class="flex items-start justify-between">
                                    <div>
                                        <h4 class="font-semibold text-gray-900">{{ user.name }}</h4>
                                        <p class="text-sm text-gray-500">{{ user.email }}</p>
                                    </div>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-indigo-50 text-indigo-700">
                                        {{ getRoleLabel(user.resident_role) }}
                                    </span>
                                </div>
                                <div class="mt-4 text-xs text-gray-400">
                                    Acceso otorgado el: {{ formatDate(user.created_at) }}
                                </div>
                            </div>
                            <div class="mt-6 pt-4 border-t border-gray-100 flex justify-end">
                                <button @click="confirmRevoke(user.id)" class="text-sm font-medium text-red-600 hover:text-red-800 flex items-center transition">
                                    <TrashIcon class="w-4 h-4 mr-1" />
                                    Revocar Acceso
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Invitaciones Pendientes -->
                <div v-if="pendingInvitations.length > 0">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Invitaciones Pendientes</h3>
                    <div class="bg-white border text-card-foreground rounded-lg shadow-sm border-gray-100">
                        <ul class="divide-y divide-gray-200">
                            <li v-for="invitation in pendingInvitations" :key="invitation.id" class="px-6 py-4 flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="bg-gray-100 p-2 rounded-full mr-4 text-gray-500">
                                        <EnvelopeIcon class="w-5 h-5" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ invitation.email }}</p>
                                        <div class="text-xs text-gray-500 flex gap-2 mt-1">
                                            <span>{{ getRoleLabel(invitation.resident_role) }}</span>
                                            <span>&bull;</span>
                                            <span>Enviada: {{ formatDate(invitation.created_at) }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        Pendiente
                                    </span>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>

        <InviteFormModal :show="isInviteModalOpen" :role-type="inviteRole" :active-unit-tenant="activeUnitTenant" @close="isInviteModalOpen = false" />

        <ConfirmDeleteModal
            :show="isDeleteModalOpen"
            :processing="isDeleting"
            title="Revocar Acceso"
            message="¿Estás seguro de que deseas revocar el acceso a este usuario? Esto también revocará el acceso de cualquier persona que este usuario haya invitado (Baja en cascada)."
            @close="isDeleteModalOpen = false"
            @confirm="executeRevoke"
        />
    </AppLayout>
</template>