import { defineStore } from 'pinia';
import type { MobileWallet } from './mobileWallet';

interface UpsertPayload {
  code?: string;
  name?: string;
  sort_order?: number;
  status?: MobileWallet['status'];
}

export const useAdminMobileWalletStore = defineStore('adminMobileWallet', () => {
  const items = ref<MobileWallet[]>([]);
  const loading = ref(false);
  const submitting = ref(false);

  const { listWallets, getWallet, createWallet, updateWallet, archiveWallet } = useAdminMobileWalletHelper();
  const toast = useToast();

  const fetchAll = async () => {
    loading.value = true;
    const { data, error } = await listWallets();
    if (!error.value) {
      items.value = (data.value as any)?.data ?? [];
    } else {
      const msg = (error.value as any)?.data?.message || 'Failed to fetch mobile wallets.';
      toast.error({ title: 'Error', message: msg, position: 'topRight', layout: 2 });
    }
    loading.value = false;
  };

  const findOne = async (id: number): Promise<MobileWallet | null> => {
    const { data, error } = await getWallet(id);
    if (!error.value) return ((data.value as any)?.data ?? null) as MobileWallet | null;
    const msg = (error.value as any)?.data?.message || 'Failed to fetch wallet.';
    toast.error({ title: 'Error', message: msg, position: 'topRight', layout: 2 });
    return null;
  };

  const create = async (payload: UpsertPayload): Promise<boolean> => {
    submitting.value = true;
    try {
      const { status, error } = await createWallet(payload as Record<string, unknown>);
      if (status?.value) {
        toast.success({ title: 'Mobile wallet created', message: 'Saved.', position: 'topRight', layout: 2 });
        await fetchAll();
        return true;
      }
      toast.error({
        title: 'Error',
        message: (error?.value as any)?.data?.message || 'Failed to create wallet.',
        position: 'topRight',
        layout: 2,
      });
      return false;
    } finally {
      submitting.value = false;
    }
  };

  const update = async (id: number, payload: UpsertPayload): Promise<boolean> => {
    submitting.value = true;
    try {
      const { status, error } = await updateWallet(id, payload as Record<string, unknown>);
      if (status?.value) {
        toast.success({ title: 'Mobile wallet updated', message: 'Saved.', position: 'topRight', layout: 2 });
        await fetchAll();
        return true;
      }
      toast.error({
        title: 'Error',
        message: (error?.value as any)?.data?.message || 'Failed to update wallet.',
        position: 'topRight',
        layout: 2,
      });
      return false;
    } finally {
      submitting.value = false;
    }
  };

  const archive = async (id: number): Promise<boolean> => {
    submitting.value = true;
    try {
      const { status, error } = await archiveWallet(id);
      if (status?.value) {
        toast.success({ title: 'Mobile wallet archived', message: 'Saved.', position: 'topRight', layout: 2 });
        await fetchAll();
        return true;
      }
      toast.error({
        title: 'Error',
        message: (error?.value as any)?.data?.message || 'Failed to archive wallet.',
        position: 'topRight',
        layout: 2,
      });
      return false;
    } finally {
      submitting.value = false;
    }
  };

  return { items, loading, submitting, fetchAll, findOne, create, update, archive };
});
