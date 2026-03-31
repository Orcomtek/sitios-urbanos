<script setup lang="ts">
// Minimal structural placeholder topbar
</script>

<template>
  <div class="sticky top-0 z-10 flex h-16 flex-shrink-0 bg-white shadow-sm border-b border-gray-200">
    <button type="button" class="border-r border-gray-200 px-4 text-gray-500 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-gray-500 md:hidden">
      <span class="sr-only">Abrir menú</span>
      <!-- Hamburger icon placeholder -->
      <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
      </svg>
    </button>
    <div class="flex flex-1 justify-between px-4">
      <div class="flex flex-1">
        <!-- Search or generic placeholder -->
      </div>
      <div class="ml-4 flex items-center md:ml-6">
        <!-- Profile dropdown placeholder -->
        <div class="relative ml-3">
          <div>
            <button type="button" class="flex max-w-xs items-center rounded-full bg-white text-sm focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2" id="user-menu-button" aria-expanded="false" aria-haspopup="true">
              <span class="sr-only">Abrir menú de usuario</span>
              <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 font-medium">U</div>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
