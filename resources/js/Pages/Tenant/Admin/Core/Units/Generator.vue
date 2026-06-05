<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import GeneratorForm from './Components/GeneratorForm.vue';
import VisualMatrix from './Components/VisualMatrix.vue';

const props = defineProps<{
    units: Array<{
        id: number;
        identifier: string;
        amenities: string[];
        status: string;
    }>;
}>();

const page = usePage();
const communitySlug = computed(() => (page.props.tenant as any)?.community?.slug);

const currentStep = ref(1);

const onGenerationSuccess = () => {
    currentStep.value = 2;
};

const finish = () => {
    router.get(route('tenant.admin.core.units.index', { community_slug: communitySlug.value }));
};
</script>

<template>
    <Head title="Generador de Matriz Topológica" />

    <AppLayout>
        <template #header>
            <div class="flex items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">
                        Generador Topológico
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">Crea unidades masivamente y asigna amenidades visualmente.</p>
                </div>
                <div class="flex gap-3">
                    <button v-if="currentStep === 2" @click="currentStep = 1" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                        Volver al Generador
                    </button>
                    <button v-if="currentStep === 1 && units.length > 0" @click="currentStep = 2" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                        Ver Matriz Actual
                    </button>
                    <Link
                        :href="route('tenant.admin.core.units.index', { community_slug: communitySlug })"
                        class="text-sm font-semibold text-slate-600 hover:text-slate-900 py-2"
                    >
                        Salir
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                
                <!-- Step Tabs / Indicators -->
                <nav aria-label="Progress" class="mb-8">
                    <ol role="list" class="space-y-4 md:flex md:space-x-8 md:space-y-0">
                        <li class="md:flex-1">
                            <button @click="currentStep = 1" :class="[currentStep === 1 ? 'border-emerald-600' : 'border-gray-200 hover:border-gray-300', 'group flex flex-col border-t-4 py-2 w-full text-left']">
                                <span :class="[currentStep === 1 ? 'text-emerald-600' : 'text-gray-500 group-hover:text-gray-700', 'text-sm font-medium']">Paso 1</span>
                                <span class="text-sm font-medium">Generación Estructural</span>
                            </button>
                        </li>
                        <li class="md:flex-1">
                            <button @click="currentStep = 2" :disabled="units.length === 0" :class="[currentStep === 2 ? 'border-emerald-600' : 'border-gray-200 hover:border-gray-300', 'group flex flex-col border-t-4 py-2 w-full text-left disabled:opacity-50 disabled:cursor-not-allowed']">
                                <span :class="[currentStep === 2 ? 'text-emerald-600' : 'text-gray-500 group-hover:text-gray-700', 'text-sm font-medium']">Paso 2</span>
                                <span class="text-sm font-medium">Asignación Visual (Matriz)</span>
                            </button>
                        </li>
                    </ol>
                </nav>

                <!-- Content -->
                <div v-show="currentStep === 1">
                    <GeneratorForm @success="onGenerationSuccess" />
                </div>
                
                <div v-show="currentStep === 2">
                    <VisualMatrix :units="units" @finish="finish" />
                </div>

            </div>
        </div>
    </AppLayout>
</template>
