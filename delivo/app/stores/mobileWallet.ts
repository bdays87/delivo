import { defineStore } from 'pinia';

export interface MobileWallet {
  id: number;
  code: string;
  name: string;
  status: 'ACTIVE' | 'ARCHIVED';
  sort_order: number;
}

/**
 * Lightweight store for vendor-facing wallet lookups. Caches the active
 * list after the first fetch so multiple components can read it without
 * triggering repeat network calls.
 */
export const useMobileWalletStore = defineStore('mobileWallet', () => {
  const items = ref<MobileWallet[]>([]);
  const loading = ref(false);
  const loaded = ref(false);

  const { getActiveWallets } = useMobileWalletHelper();
  const toast = useToast();

  const fetchActive = async (force = false): Promise<void> => {
    if (loaded.value && !force) return;
    loading.value = true;
    const { data, error } = await getActiveWallets();
    if (!error.value) {
      items.value = (data.value as any)?.data ?? [];
      loaded.value = true;
    } else {
      const msg = (error.value as any)?.data?.message || 'Failed to load mobile wallets.';
      toast.error({ title: 'Error', message: msg, position: 'topRight', layout: 2 });
    }
    loading.value = false;
  };

  return { items, loading, loaded, fetchActive };
});
