<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';

defineOptions({ layout: AppLayout });
</script>

<template>
    <Head title="Dashboard" />

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="border-b border-gray-200 p-6 text-gray-900">
                    <h2
                        class="mb-4 text-xl font-semibold leading-tight text-gray-800"
                    >
                        Dashboard
                    </h2>
                    You're logged in!
                </div>
            </div>
        </div>
    </div>
</template>
