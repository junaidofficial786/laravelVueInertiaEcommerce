<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

interface LinkItem { url: string | null; label: string; active: boolean }

defineProps<{
  links: LinkItem[];
}>();

function clean(label: string) {
  return label.replaceAll('&laquo;', '«').replaceAll('&raquo;', '»');
}
</script>

<template>
  <nav v-if="links && links.length > 1" class="mt-4 flex items-center justify-center gap-2">
    <template v-for="(l, i) in links" :key="i">
      <span v-if="!l.url" class="select-none rounded border px-3 py-1 text-sm text-neutral-400">{{ clean(l.label) }}</span>
      <Link v-else :href="l.url" class="rounded border px-3 py-1 text-sm hover:bg-neutral-50"
            :class="l.active ? 'bg-neutral-900 text-white dark:bg-neutral-100 dark:text-neutral-900' : ''">
        {{ clean(l.label) }}
      </Link>
    </template>
  </nav>
</template>


