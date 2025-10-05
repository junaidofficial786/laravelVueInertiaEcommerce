import { reactive, readonly } from 'vue';

export type ToastType = 'success' | 'error' | 'info';
export interface ToastItem { id: number; type: ToastType; message: string }

const state = reactive({ items: [] as ToastItem[], nextId: 1 });

export function useToast() {
	function push(type: ToastType, message: string) {
		const id = state.nextId++;
		state.items.push({ id, type, message });
		setTimeout(() => remove(id), 4000);
	}
	function success(message: string) { push('success', message); }
	function error(message: string) { push('error', message); }
	function info(message: string) { push('info', message); }
	function remove(id: number) {
		const idx = state.items.findIndex(i => i.id === id);
		if (idx !== -1) state.items.splice(idx, 1);
	}
	return { toasts: readonly(state.items), success, error, info, remove };
}
