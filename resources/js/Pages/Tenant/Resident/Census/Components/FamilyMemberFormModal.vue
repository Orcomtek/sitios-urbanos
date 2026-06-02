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
    familyMember: any | null;
}>();

const emit = defineEmits(['close']);
const page = usePage();
const communitySlug = computed(() => (page.props.tenant as any)?.community?.slug);
const { show: showToast } = useToast();

const form = useForm({
    name: '',
    relationship: 'other',
    is_minor: false,
    contact_phone: '',
});

watch(() => props.show, (show) => {
    if (show) {
        if (props.familyMember) {
            form.name = props.familyMember.name;
            form.relationship = props.familyMember.relationship;
            form.is_minor = props.familyMember.is_minor;
            form.contact_phone = props.familyMember.contact_phone || '';
        } else {
            form.reset();
        }
    }
});

const submit = () => {
    if (props.familyMember) {
        form.put(route('tenant.resident.census.family.update', { community_slug: communitySlug.value, familyMember: props.familyMember.id }), {
            preserveScroll: true,
            onSuccess: () => {
                showToast('Familiar actualizado exitosamente', 'success');
                emit('close');
            },
        });
    } else {
        form.post(route('tenant.resident.census.family.store', { community_slug: communitySlug.value }), {
            preserveScroll: true,
            onSuccess: () => {
                showToast('Familiar agregado exitosamente', 'success');
                emit('close');
            },
        });
    }
};
</script>

<template>
    <Modal :show="show" @close="$emit('close')">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">
                {{ familyMember ? 'Editar Familiar' : 'Agregar Familiar' }}
            </h2>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <InputLabel for="name" value="Nombre Completo" />
                    <TextInput
                        id="name"
                        v-model="form.name"
                        type="text"
                        class="mt-1 block w-full"
                        required
                    />
                    <InputError :message="form.errors.name" class="mt-2" />
                </div>

                <div>
                    <InputLabel for="relationship" value="Parentesco" />
                    <select
                        id="relationship"
                        v-model="form.relationship"
                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        required
                    >
                        <option value="spouse">Cónyuge</option>
                        <option value="child">Hijo(a)</option>
                        <option value="parent">Padre/Madre</option>
                        <option value="other">Otro</option>
                    </select>
                    <InputError :message="form.errors.relationship" class="mt-2" />
                </div>

                <div>
                    <InputLabel for="contact_phone" value="Teléfono de Contacto (Opcional)" />
                    <TextInput
                        id="contact_phone"
                        v-model="form.contact_phone"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.contact_phone" class="mt-2" />
                </div>

                <div class="flex items-center mt-4">
                    <input
                        id="is_minor"
                        type="checkbox"
                        v-model="form.is_minor"
                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                    />
                    <label for="is_minor" class="ml-2 block text-sm text-gray-900">
                        Es menor de edad
                    </label>
                </div>
                <InputError :message="form.errors.is_minor" class="mt-2" />

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="$emit('close')">Cancelar</SecondaryButton>
                    <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        {{ familyMember ? 'Actualizar' : 'Guardar' }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </Modal>
</template>
