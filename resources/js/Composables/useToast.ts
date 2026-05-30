import { ref } from 'vue';

export type ToastType = 'success' | 'error' | 'info';

export interface ToastMessage {
    id: number;
    message: string;
    type: ToastType;
}

const toasts = ref<ToastMessage[]>([]);
let nextId = 0;

export function useToast() {
    const show = (message: string, type: ToastType = 'info') => {
        const id = nextId++;
        toasts.value.push({ id, message, type });
        setTimeout(() => {
            remove(id);
        }, 4000);
    };

    const remove = (id: number) => {
        const index = toasts.value.findIndex(t => t.id === id);
        if (index > -1) {
            toasts.value.splice(index, 1);
        }
    };

    return {
        toasts,
        show,
        remove
    };
}
