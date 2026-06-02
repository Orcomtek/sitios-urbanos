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
    pet: any | null;
}>();

const emit = defineEmits(['close']);
const page = usePage();
const communitySlug = computed(() => (page.props.tenant as any)?.community?.slug);
const { show: showToast } = useToast();

const form = useForm({
    name: '',
    species: 'dog',
    breed: '',
});

watch(() => props.show, (show) => {
    if (show) {
        if (props.pet) {
            form.name = props.pet.name;
            form.species = props.pet.species;
            form.breed = props.pet.breed || '';
        } else {
            form.reset();
        }
    }
});

const submit = () => {
    if (props.pet) {
        form.put(route('tenant.resident.census.pets.update', { community_slug: communitySlug.value, pet: props.pet.id }), {
            preserveScroll: true,
            onSuccess: () => {
                showToast('Mascota actualizada exitosamente', 'success');
                emit('close');
            },
        });
    } else {
        form.post(route('tenant.resident.census.pets.store', { community_slug: communitySlug.value }), {
            preserveScroll: true,
            onSuccess: () => {
                showToast('Mascota agregada exitosamente', 'success');
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
                {{ pet ? 'Editar Mascota' : 'Agregar Mascota' }}
            </h2>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <InputLabel for="name" value="Nombre" />
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
                    <InputLabel for="species" value="Especie" />
                    <select
                        id="species"
                        v-model="form.species"
                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        required
                    >
                        <option value="dog">Perro</option>
                        <option value="cat">Gato</option>
                        <option value="other">Otro</option>
                    </select>
                    <InputError :message="form.errors.species" class="mt-2" />
                </div>

                <div>
                    <InputLabel for="breed" value="Raza (Opcional)" />
                    <TextInput
                        id="breed"
                        v-model="form.breed"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.breed" class="mt-2" />
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="$emit('close')">Cancelar</SecondaryButton>
                    <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        {{ pet ? 'Actualizar' : 'Guardar' }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </Modal>
</template>
