interface ToastOptions {
  title?: string;
  message: string;
  position?: 'topRight' | 'topLeft' | 'bottomRight' | 'bottomLeft';
  layout?: number;
}

export interface Toast {
  id: number;
  title?: string;
  message: string;
  level: 'success' | 'error' | 'info';
}

let nextId = 1;

/**
 * Tiny in-app toast system. Use `useToast()` from any component/store; the
 * `<StorefrontToasts />` component renders the active queue.
 */
export const useToast = () => {
  const queue = useState<Toast[]>('app:toasts', () => []);

  const push = (level: Toast['level'], options: ToastOptions) => {
    const id = nextId++;
    queue.value = [
      ...queue.value,
      { id, level, title: options.title, message: options.message },
    ];
    setTimeout(() => {
      queue.value = queue.value.filter((t) => t.id !== id);
    }, 4000);
  };

  return {
    queue,
    success: (o: ToastOptions) => push('success', o),
    error: (o: ToastOptions) => push('error', o),
    info: (o: ToastOptions) => push('info', o),
    dismiss: (id: number) => {
      queue.value = queue.value.filter((t) => t.id !== id);
    },
  };
};
