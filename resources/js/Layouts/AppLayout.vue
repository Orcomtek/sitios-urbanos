<script setup lang="ts">
import Sidebar from '@/Components/layout/Sidebar.vue';
import Topbar from '@/Components/layout/Topbar.vue';
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <Sidebar />
        <div class="flex flex-1 flex-col md:pl-64">
            <Topbar />
            <main class="flex-1">
                <!-- Page specific content will be injected here -->
                <slot />
            </main>
        </div>
    </div>
</template>
