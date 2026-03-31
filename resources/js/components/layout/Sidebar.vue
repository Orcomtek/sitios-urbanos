<script setup lang="ts">
// Minimal structural placeholder sidebar map
const navigation = [
  { name: 'Opción 1', href: '#' },
  { name: 'Opción 2', href: '#' },
  { name: 'Opción 3', href: '#' },
]
</script>

<template>
  <div class="hidden md:fixed md:inset-y-0 md:flex md:w-64 md:flex-col">
    <!-- Sidebar component, swap this element with another sidebar if you like -->
    <div class="flex flex-grow flex-col overflow-y-auto border-r border-gray-200 bg-white pt-5 pb-4">
      <div class="flex flex-shrink-0 items-center px-4">
        <!-- Logo placeholder -->
        <div class="h-8 w-auto font-bold text-xl tracking-tight text-gray-900">Sitios Urbanos</div>
      </div>
      <div class="mt-8 flex flex-1 flex-col">
        <nav class="flex-1 space-y-1 px-2 bg-white" aria-label="Sidebar">
          <a v-for="item in navigation" :key="item.name" :href="item.href" class="text-gray-600 hover:bg-gray-50 hover:text-gray-900 group flex items-center px-2 py-2 text-sm font-medium rounded-md">
            {{ item.name }}
          </a>
        </nav>
      </div>
    </div>
  </div>
</template>
