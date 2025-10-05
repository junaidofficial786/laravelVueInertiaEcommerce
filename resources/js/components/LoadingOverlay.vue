<script setup lang="ts">
interface Props {
    message?: string;
}

withDefaults(defineProps<Props>(), {
    message: 'Loading...'
});
</script>

<template>
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="rounded-md bg-white px-6 py-4 text-sm shadow dark:bg-neutral-900 dark:text-neutral-100">
            <div class="flex items-center gap-2">
                <svg class="h-5 w-5 animate-spin text-neutral-700 dark:text-neutral-300" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                </svg>
                <span>{{ message }}</span>
            </div>
        </div>
    </div>
    <slot />
</template>


