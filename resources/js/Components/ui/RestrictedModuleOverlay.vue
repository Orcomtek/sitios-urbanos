<script setup lang="ts">
/**
 * RestrictedModuleOverlay
 *
 * Wraps any module navigation item with a grayscale + lock icon overlay
 * when the module is restricted by the dunning engine.
 *
 * The slot content remains visible (per legal requirement — modules MUST NOT
 * disappear) but is rendered as grayscale with a semi-transparent lock overlay.
 * Clicking triggers the RestrictionModal instead of navigating.
 *
 * Usage:
 *   <RestrictedModuleOverlay :is-restricted="true" @click-restricted="showModal = true">
 *       <Link :href="...">...</Link>
 *   </RestrictedModuleOverlay>
 */
defineProps<{
    isRestricted: boolean;
}>();

const emit = defineEmits<{
    (e: 'click-restricted'): void;
}>();
</script>

<template>
    <div class="relative">
        <!-- Normal content (always rendered) -->
        <div
            :class="isRestricted ? 'grayscale opacity-60 pointer-events-none select-none' : ''"
        >
            <slot />
        </div>

        <!-- Lock overlay — only shown when restricted -->
        <div
            v-if="isRestricted"
            class="absolute inset-0 flex items-center justify-center cursor-pointer rounded-md z-10 group"
            @click.prevent.stop="emit('click-restricted')"
            role="button"
            :tabindex="0"
            @keydown.enter.prevent="emit('click-restricted')"
            aria-label="Módulo restringido. Haz clic para más información."
        >
            <span
                class="flex items-center gap-1 text-xs font-semibold text-red-700 bg-red-50 border border-red-200 rounded px-1.5 py-0.5 shadow-sm group-hover:bg-red-100 transition-colors"
            >
                <svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path
                        fill-rule="evenodd"
                        d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z"
                        clip-rule="evenodd"
                    />
                </svg>
                Restringido
            </span>
        </div>
    </div>
</template>
