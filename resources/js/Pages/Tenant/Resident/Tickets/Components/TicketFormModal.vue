<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { computed, watch } from 'vue';
import { useToast } from '@/Composables/useToast';

const props = defineProps<{
    show: boolean;
    ticket: any | null;
}>();

const emit = defineEmits(['close']);
const page = usePage();
const communitySlug = computed(() => (page.props.tenant as any)?.community?.slug);
const { show: showToast } = useToast();

const form = useForm({
    subject: '',
    description: '',
    type: 'peticion',
    is_anonymous: false,
});

watch(() => props.show, (show) => {
    if (show) {
        if (props.ticket) {
            form.subject = props.ticket.subject;
            form.description = props.ticket.description;
            form.type = props.ticket.type;
            form.is_anonymous = props.ticket.is_anonymous || false;
        } else {
            form.reset();
        }
    }
});

const submit = () => {
    if (props.ticket) {
        form.put(route('tenant.resident.governance.pqrs.update', { community_slug: communitySlug.value, ticket: props.ticket.id }), {
            preserveScroll: true,
            onSuccess: () => {
                showToast('Ticket actualizado exitosamente', 'success');
                emit('close');
            },
        });
    } else {
        form.post(route('tenant.resident.governance.pqrs.store', { community_slug: communitySlug.value }), {
            preserveScroll: true,
            onSuccess: () => {
                showToast('Ticket creado exitosamente', 'success');
                emit('close');
            },
        });
    }
};
</script>

<template>
    <Modal :show="show" @close="$emit('close')" maxWidth="2xl">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">
                {{ ticket ? 'Editar Ticket' : 'Crear Nuevo Ticket' }}
            </h2>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <InputLabel for="type" value="Tipo de Solicitud" />
                    <select
                        id="type"
                        v-model="form.type"
                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        required
                    >
                        <option value="peticion">Petición (P)</option>
                        <option value="queja">Queja (Q)</option>
                        <option value="reclamo">Reclamo (R)</option>
                        <option value="sugerencia">Sugerencia (S)</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-500">
                        Selecciona el tipo que mejor describa tu solicitud.
                    </p>
                    <InputError :message="form.errors.type" class="mt-2" />
                </div>

                <div>
                    <InputLabel for="subject" value="Asunto" />
                    <TextInput
                        id="subject"
                        v-model="form.subject"
                        type="text"
                        class="mt-1 block w-full"
                        required
                        placeholder="Ej: Mantenimiento de luminarias piso 4"
                    />
                    <InputError :message="form.errors.subject" class="mt-2" />
                </div>

                <div>
                    <InputLabel for="description" value="Descripción detallada" />
                    <textarea
                        id="description"
                        v-model="form.description"
                        rows="4"
                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        required
                        placeholder="Describe detalladamente tu solicitud..."
                    ></textarea>
                    <InputError :message="form.errors.description" class="mt-2" />
                </div>

                <div class="relative flex items-start mt-4 bg-gray-50 p-4 rounded-lg border border-gray-100">
                    <div class="flex h-6 items-center">
                        <input
                            id="is_anonymous"
                            v-model="form.is_anonymous"
                            type="checkbox"
                            class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600"
                        />
                    </div>
                    <div class="ml-3 text-sm leading-6">
                        <label for="is_anonymous" class="font-medium text-gray-900">Radicar como Anónimo</label>
                        <p class="text-gray-500">Tu nombre y unidad se ocultarán para el equipo de seguridad y otros residentes.</p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="$emit('close')">Cancelar</SecondaryButton>
                    <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        {{ ticket ? 'Actualizar Ticket' : 'Enviar Ticket' }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </Modal>
</template>
