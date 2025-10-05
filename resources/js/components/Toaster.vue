<script setup lang="ts">
import { useToast } from '@/composables/useToast';
const { toasts, remove } = useToast();
</script>

<template>
	<div class="fixed inset-x-0 top-4 z-50 flex flex-col items-center gap-2">
		<div v-for="t in toasts" :key="t.id" class="pointer-events-auto rounded border px-4 py-2 shadow backdrop-blur-sm"
			:class="{
				'bg-green-50 border-green-200 text-green-800': t.type==='success',
				'bg-red-50 border-red-200 text-red-800': t.type==='error',
				'bg-blue-50 border-blue-200 text-blue-800': t.type==='info',
			}">
			<div class="flex items-center gap-3">
				<span class="text-sm">{{ t.message }}</span>
				<button class="text-xs opacity-70 hover:opacity-100" @click="remove(t.id)">Dismiss</button>
			</div>
		</div>
	</div>
</template>
