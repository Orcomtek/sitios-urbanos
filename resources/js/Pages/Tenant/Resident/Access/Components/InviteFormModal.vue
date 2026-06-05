<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import { computed, watch } from 'vue';

const props = defineProps<{
    show: boolean;
    roleType: 'tenant' | 'family';
    activeUnitTenant?: { id: number, name: string } | null;
}>();

const emit = defineEmits(['close']);

const page = usePage();
const communitySlug = computed(() => (page.props.tenant as any)?.community?.slug);

const form = useForm({
    name: '',
    email: '',
    role: props.roleType,
});

watch(() => props.roleType, (newRole) => {
    form.role = newRole;
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        role: props.roleType,
        replace_existing: props.roleType === 'tenant' && !!props.activeUnitTenant,
    })).post(route('tenant.resident.access.store', { community_slug: communitySlug.value }), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            emit('close');
        },
    });
};

const close = () => {
    form.reset();
    form.clearErrors();
    emit('close');
};
</script>

<template>
    <Modal :show="show" @close="close" maxWidth="sm">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900">
                {{ roleType === 'tenant' ? 'Invitar Inquilino Principal' : 'Invitar Familiar' }}
            </h2>
            <p class="mt-1 text-sm text-gray-600">
                Ingresa los datos para enviar la invitación de acceso al sistema.
            </p>

            <div v-if="roleType === 'tenant' && activeUnitTenant" class="mb-4 p-4 bg-yellow-50 border-l-4 border-yellow-400 rounded-md">
                <div class="flex">
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            ⚠️ El sistema detecta que <strong>{{ activeUnitTenant.name }}</strong> es el inquilino actual. Al continuar, su acceso será revocado automáticamente.
                        </p>
                    </div>
                </div>
            </div>

            <form @submit.prevent="submit" class="mt-6 space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Nombre Completo</label>
                    <input id="name" v-model="form.name" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm" required />
                    <p v-if="form.errors.name" class="mt-2 text-sm text-red-600">{{ form.errors.name }}</p>
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Correo Electrónico</label>
                    <input id="email" v-model="form.email" type="email" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm" required />
                    <p v-if="form.errors.email" class="mt-2 text-sm text-red-600">{{ form.errors.email }}</p>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="button" @click="close" class="inline-flex justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 mr-3">
                        Cancelar
                    </button>
                    <button type="submit" :disabled="form.processing" class="inline-flex justify-center rounded-md border border-transparent bg-emerald-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                        {{ (roleType === 'tenant' && activeUnitTenant) ? 'Reemplazar e Invitar' : 'Enviar Invitación' }}
                    </button>
                </div>
            </form>
        </div>
    </Modal>
</template>
