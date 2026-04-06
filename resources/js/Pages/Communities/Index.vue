<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

type Community = {
    id: number;
    name: string;
    slug: string;
    subdomain: string | null;
    status: 'active' | 'inactive';
    pivot: {
        role: 'admin' | 'resident' | 'guard';
        unit_id: number | null;
    };
};

const props = defineProps<{
    communities: Community[];
}>();

const getDisplayDomain = (community: Community): string => {
    return community.subdomain
        ? `${community.subdomain}.sitiosurbanos.com`
        : community.slug;
};

const roleLabel = (role: string): string => {
    const roles: Record<string, string> = {
        admin: 'Administrador',
        resident: 'Residente',
        guard: 'Guardia',
    };
    return roles[role] || role;
};
</script>

<template>
    <AppLayout>
        <Head title="Mis Comunidades" />

        <div class="px-4 py-12 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-7xl">
                <h1 class="mb-6 text-2xl font-bold text-gray-900">
                    Mis Comunidades
                </h1>

                <!-- Empty State -->
                <div
                    v-if="communities.length === 0"
                    class="rounded-xl border border-gray-200 bg-white p-12 text-center shadow-sm"
                >
                    <svg
                        class="mx-auto h-12 w-12 text-gray-400"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        aria-hidden="true"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                        />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">
                        No tienes comunidades
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Actualmente no estás asociado a ninguna comunidad del
                        sistema.
                    </p>
                </div>

                <!-- Community Grid -->
                <div
                    v-else
                    class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3"
                >
                    <article
                        v-for="community in communities"
                        :key="community.id"
                        class="flex flex-col rounded-xl border border-gray-200 bg-white p-6 shadow-sm transition hover:shadow-md"
                    >
                        <div class="mb-4 flex flex-1 flex-col">
                            <h2 class="text-xl font-semibold text-gray-900">
                                {{ community.name }}
                            </h2>
                            <span class="mt-1 text-sm text-gray-500">{{
                                getDisplayDomain(community)
                            }}</span>

                            <div class="mt-3 flex">
                                <span
                                    class="inline-flex w-max items-center rounded-full bg-indigo-50 px-2 py-1 text-xs font-medium uppercase text-indigo-700 ring-1 ring-inset ring-indigo-600/20"
                                >
                                    {{ roleLabel(community.pivot.role) }}
                                </span>
                            </div>
                        </div>

                        <!-- Action Button -->
                        <div
                            class="mt-auto flex justify-end border-t border-gray-100 pt-4"
                        >
                            <a
                                :href="route('communities.enter', community.slug)"
                                class="text-sm font-medium text-indigo-600 hover:text-indigo-500"
                            >
                                Ingresar &rarr;
                            </a>
                        </div>
                    </article>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
