<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
}>();

const formatLabel = (label: string) => {
    if (label.includes('pagination.previous') || label.includes('Previous')) {
        return '&laquo; Anterior';
    }
    if (label.includes('pagination.next') || label.includes('Next')) {
        return 'Siguiente &raquo;';
    }
    return label;
};
</script>

<template>
    <div v-if="links.length > 3" class="flex flex-wrap -mb-1 mt-4">
        <template v-for="(link, key) in links">
            <div v-if="link.url === null" :key="key" class="mr-1 mb-1 px-4 py-3 text-sm leading-4 text-gray-400 border border-gray-200 rounded" v-html="formatLabel(link.label)" />
            <Link v-else :key="'link-'+key" class="mr-1 mb-1 px-4 py-3 text-sm leading-4 border rounded hover:bg-gray-50 focus:border-indigo-500 focus:text-indigo-500" :class="{ 'bg-indigo-50 border-indigo-500 text-indigo-600': link.active, 'bg-white border-gray-300 text-gray-700': !link.active }" :href="link.url" v-html="formatLabel(link.label)" />
        </template>
    </div>
</template>
