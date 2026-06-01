<script setup>
import { ref } from 'vue';
import { Head, useForm, usePage, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ConfirmDeleteModal from '@/Components/ui/ConfirmDeleteModal.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import { PencilIcon, TrashIcon } from '@heroicons/vue/24/outline';
import { useToast } from '@/Composables/useToast';

const props = defineProps({
    team: Array,
    pendingInvitations: Array,
});

const page = usePage();
const { show: showToast } = useToast();

const showInviteModal = ref(false);
const isEditMode = ref(false);
const editMemberId = ref(null);

const form = useForm({
    name: '',
    email: '',
    role: 'sub_admin',
});

const roleLabels = {
    tenant_admin: 'Administrador Principal',
    sub_admin: 'Sub Administrador',
    accountant: 'Contador',
    auditor: 'Revisor Fiscal',
    guard: 'Guarda de Seguridad',
};

const submitForm = () => {
    if (isEditMode.value && editMemberId.value) {
        form.put(route('tenant.admin.settings.team.update', { community_slug: page.props.tenant.community.slug, user: editMemberId.value }), {
            preserveScroll: true,
            onSuccess: () => {
                closeModal();
                showToast('Rol de usuario actualizado exitosamente.', 'success');
            },
        });
    } else {
        form.post(route('tenant.admin.settings.team.invite', { community_slug: page.props.tenant.community.slug }), {
            preserveScroll: true,
            onSuccess: () => {
                closeModal();
                showToast('Invitación enviada exitosamente al miembro del equipo.', 'success');
            },
        });
    }
};

const closeModal = () => {
    showInviteModal.value = false;
    isEditMode.value = false;
    editMemberId.value = null;
    form.reset();
    form.clearErrors();
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    return new Intl.DateTimeFormat('es-CO', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    }).format(new Date(dateString));
};

const isDeleteModalOpen = ref(false);
const deleteMemberId = ref(null);
const isDeleting = ref(false);

const confirmDeleteMember = (id) => {
    deleteMemberId.value = id;
    isDeleteModalOpen.value = true;
};

const executeDeleteMember = () => {
    if (!deleteMemberId.value) return;
    isDeleting.value = true;
    router.delete(route('tenant.admin.settings.team.destroy', { community_slug: page.props.tenant.community.slug, user: deleteMemberId.value }), {
        preserveScroll: true,
        onSuccess: () => {
            isDeleteModalOpen.value = false;
            showToast('Miembro revocado exitosamente', 'success');
        },
        onError: () => {
            isDeleteModalOpen.value = false;
            showToast('Error al intentar revocar (ruta no implementada)', 'error');
        },
        onFinish: () => {
            isDeleting.value = false;
            deleteMemberId.value = null;
        }
    });
};

const openEditModal = (member) => {
    isEditMode.value = true;
    editMemberId.value = member.id;
    form.name = member.name;
    form.email = member.email;
    form.role = member.role;
    showInviteModal.value = true;
};
</script>

<template>
    <AppLayout title="Equipo de Administración">

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-xl font-semibold leading-tight text-gray-800">Administración de Equipo</h2>
                    </div>
                    <PrimaryButton @click="showInviteModal = true">
                        + Invitar Miembro
                    </PrimaryButton>
                </div>

                <!-- Team Members Grid (Quiet Luxury style) -->
                <div class="bg-white border text-card-foreground rounded-lg shadow-sm border-gray-100">
                    <div class="px-6 py-4 flex flex-col space-y-1.5 border-b border-gray-100 bg-gray-50/50">
                        <h3 class="font-semibold leading-none tracking-tight text-gray-900">Miembros Activos</h3>
                        <p class="text-sm text-gray-500">Administradores y personal con acceso al sistema.</p>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-gray-500 uppercase bg-gray-50/50 border-b border-gray-100">
                                <tr>
                                    <th class="px-6 py-3 font-semibold">Nombre</th>
                                    <th class="px-6 py-3 font-semibold">Correo</th>
                                    <th class="px-6 py-3 font-semibold">Rol</th>
                                    <th class="px-6 py-3 font-semibold text-right"><span class="sr-only">Acciones</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="member in team" :key="member.id" class="border-b border-gray-50 hover:bg-gray-50/30 transition-colors">
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ member.name }}</td>
                                    <td class="px-6 py-4 text-gray-500">{{ member.email }}</td>
                                    <td class="px-6 py-4">
                                        <span class="px-2.5 py-1 text-xs rounded-full font-medium" :class="{
                                            'bg-indigo-100 text-indigo-800': member.role === 'tenant_admin',
                                            'bg-blue-100 text-blue-800': member.role === 'sub_admin',
                                            'bg-green-100 text-green-800': member.role === 'accountant',
                                            'bg-yellow-100 text-yellow-800': member.role === 'auditor',
                                            'bg-gray-100 text-gray-800': member.role === 'guard'
                                        }">
                                            {{ roleLabels[member.role] || member.role }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-3">
                                            <button @click="openEditModal(member)" type="button" class="text-gray-400 hover:text-primary transition" title="Editar" aria-label="Editar">
                                                <PencilIcon class="w-5 h-5" />
                                            </button>
                                            <button @click="confirmDeleteMember(member.id)" type="button" class="text-gray-400 hover:text-red-600 transition" title="Revocar" aria-label="Revocar">
                                                <TrashIcon class="w-5 h-5" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="team.length === 0">
                                    <td colspan="4" class="px-6 py-8 text-center text-gray-500 italic">No hay miembros en el equipo.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pending Invitations -->
                <div v-if="pendingInvitations.length > 0" class="bg-white border text-card-foreground rounded-lg shadow-sm border-gray-100 mt-6">
                    <div class="px-6 py-4 flex flex-col space-y-1.5 border-b border-gray-100 bg-gray-50/50">
                        <h3 class="font-semibold leading-none tracking-tight text-gray-900">Invitaciones Pendientes</h3>
                        <p class="text-sm text-gray-500">Personal que aún no ha configurado su contraseña.</p>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-gray-500 uppercase bg-gray-50/50 border-b border-gray-100">
                                <tr>
                                    <th class="px-6 py-3 font-semibold">Nombre</th>
                                    <th class="px-6 py-3 font-semibold">Correo</th>
                                    <th class="px-6 py-3 font-semibold">Rol</th>
                                    <th class="px-6 py-3 font-semibold">Expira</th>
                                    <th class="px-6 py-3 font-semibold text-right"><span class="sr-only">Acciones</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="inv in pendingInvitations" :key="inv.id" class="border-b border-gray-50 hover:bg-gray-50/30 transition-colors">
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ inv.name }}</td>
                                    <td class="px-6 py-4 text-gray-500">{{ inv.email }}</td>
                                    <td class="px-6 py-4 text-gray-500">{{ roleLabels[inv.role] || inv.role }}</td>
                                    <td class="px-6 py-4 text-gray-500">{{ formatDate(inv.expires_at) }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-3">
                                            <button @click="showToast('Función de revocación de invitación en desarrollo', 'info')" type="button" class="text-gray-400 hover:text-red-600 transition" title="Revocar Invitación" aria-label="Revocar Invitación">
                                                <TrashIcon class="w-5 h-5" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

        <!-- Invite Modal -->
        <Modal :show="showInviteModal" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">
                    {{ isEditMode ? 'Editar Miembro del Equipo' : 'Invitar Miembro del Equipo' }}
                </h2>

                <form @submit.prevent="submitForm" class="space-y-4">
                    <div>
                        <InputLabel for="name" value="Nombre Completo" />
                        <TextInput
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="mt-1 block w-full"
                            :readonly="isEditMode"
                            :class="{ 'bg-gray-100 cursor-not-allowed': isEditMode }"
                            required
                        />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <div>
                        <InputLabel for="email" value="Correo Electrónico" />
                        <TextInput
                            id="email"
                            v-model="form.email"
                            type="email"
                            class="mt-1 block w-full"
                            :readonly="isEditMode"
                            :class="{ 'bg-gray-100 cursor-not-allowed': isEditMode }"
                            required
                        />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div>
                        <InputLabel for="role" value="Rol" />
                        <select
                            id="role"
                            v-model="form.role"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            required
                        >
                            <option value="sub_admin">Sub Administrador</option>
                            <option value="accountant">Contador</option>
                            <option value="auditor">Revisor Fiscal</option>
                            <option value="guard">Guarda de Seguridad</option>
                            <option value="tenant_admin">Administrador Principal</option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.role" />
                    </div>

                    <div class="mt-6 flex justify-end">
                        <SecondaryButton @click="closeModal" class="mr-3">
                            Cancelar
                        </SecondaryButton>
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            {{ isEditMode ? 'Guardar Cambios' : 'Enviar Invitación' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <ConfirmDeleteModal
            :show="isDeleteModalOpen"
            title="Revocar Acceso"
            message="¿Estás seguro de que deseas revocar el acceso a este miembro del equipo? Perderá todos los permisos en esta comunidad de inmediato."
            :processing="isDeleting"
            @close="isDeleteModalOpen = false"
            @confirm="executeDeleteMember"
        />

    </AppLayout>
</template>
