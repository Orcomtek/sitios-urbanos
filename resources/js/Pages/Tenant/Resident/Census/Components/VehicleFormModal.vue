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
    vehicle: any | null;
}>();

const emit = defineEmits(['close']);
const page = usePage();
const communitySlug = computed(() => (page.props.tenant as any)?.community?.slug);
const { show: showToast } = useToast();

const form = useForm({
    license_plate: '',
    brand: '',
    color: '',
    type: 'car',
});

watch(() => props.show, (show) => {
    if (show) {
        if (props.vehicle) {
            form.license_plate = props.vehicle.license_plate;
            form.brand = props.vehicle.brand || '';
            form.color = props.vehicle.color || '';
            form.type = props.vehicle.type;
        } else {
            form.reset();
        }
    }
});

const submit = () => {
    if (props.vehicle) {
        form.put(route('tenant.resident.census.vehicles.update', { community_slug: communitySlug.value, vehicle: props.vehicle.id }), {
            preserveScroll: true,
            onSuccess: () => {
                showToast('Vehículo actualizado exitosamente', 'success');
                emit('close');
            },
        });
    } else {
        form.post(route('tenant.resident.census.vehicles.store', { community_slug: communitySlug.value }), {
            preserveScroll: true,
            onSuccess: () => {
                showToast('Vehículo agregado exitosamente', 'success');
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
                {{ vehicle ? 'Editar Vehículo' : 'Agregar Vehículo' }}
            </h2>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <InputLabel for="license_plate" value="Placa" />
                    <TextInput
                        id="license_plate"
                        v-model="form.license_plate"
                        type="text"
                        class="mt-1 block w-full uppercase"
                        required
                    />
                    <InputError :message="form.errors.license_plate" class="mt-2" />
                </div>

                <div>
                    <InputLabel for="type" value="Tipo de Vehículo" />
                    <select
                        id="type"
                        v-model="form.type"
                        class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-md shadow-sm"
                        required
                    >
                        <option value="car">Carro</option>
                        <option value="motorcycle">Moto</option>
                        <option value="bicycle">Bicicleta</option>
                        <option value="other">Otro</option>
                    </select>
                    <InputError :message="form.errors.type" class="mt-2" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="brand" value="Marca (Opcional)" />
                        <TextInput
                            id="brand"
                            v-model="form.brand"
                            type="text"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.brand" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="color" value="Color (Opcional)" />
                        <TextInput
                            id="color"
                            v-model="form.color"
                            type="text"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.color" class="mt-2" />
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="$emit('close')">Cancelar</SecondaryButton>
                    <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        {{ vehicle ? 'Actualizar' : 'Guardar' }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </Modal>
</template>
