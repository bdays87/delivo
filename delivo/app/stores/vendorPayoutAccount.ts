import { defineStore } from 'pinia';

export interface VendorPayoutAccount {
  id: number;
  vendor_id: number;
  type: 'MOBILE_WALLET' | 'BANK_TRANSFER';
  label: string | null;
  is_primary: boolean;
  status: 'ACTIVE' | 'ARCHIVED';
  mobile_wallet_id: number | null;
  mobile_wallet?: { id: number; code: string; name: string } | null;
  mobile_wallet_msisdn: string | null;
  bank_name: string | null;
  bank_account_name: string | null;
  bank_account_number: string | null;
  bank_currency: 'USD' | 'ZWG' | null;
  created_at: string;
}

interface UpsertPayload {
  type: 'MOBILE_WALLET' | 'BANK_TRANSFER';
  label?: string | null;
  is_primary?: boolean;
  mobile_wallet_id?: number | null;
  mobile_wallet_msisdn?: string | null;
  bank_name?: string | null;
  bank_account_name?: string | null;
  bank_account_number?: string | null;
  bank_currency?: 'USD' | 'ZWG' | null;
}

export const useVendorPayoutAccountStore = defineStore('vendorPayoutAccount', () => {
  const items = ref<VendorPayoutAccount[]>([]);
  const loading = ref(false);
  const submitting = ref(false);

  const api = useVendorPayoutAccountHelper();
  const toast = useToast();

  const fetchAll = async () => {
    loading.value = true;
    const { data, error } = await api.list();
    if (!error.value) {
      items.value = (data.value as any)?.data ?? [];
    } else {
      const msg = (error.value as any)?.data?.message || 'Failed to load payout accounts.';
      toast.error({ title: 'Error', message: msg, position: 'topRight', layout: 2 });
    }
    loading.value = false;
  };

  const create = async (payload: UpsertPayload): Promise<boolean> => {
    submitting.value = true;
    try {
      const { status, error } = await api.create(payload as any);
      if (status?.value) {
        toast.success({ title: 'Payout account added', message: 'Saved.', position: 'topRight', layout: 2 });
        await fetchAll();
        return true;
      }
      toast.error({
        title: 'Error',
        message: (error?.value as any)?.data?.message || 'Failed to add account.',
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
      const { status, error } = await api.update(id, payload as any);
      if (status?.value) {
        toast.success({ title: 'Payout account updated', message: 'Saved.', position: 'topRight', layout: 2 });
        await fetchAll();
        return true;
      }
      toast.error({
        title: 'Error',
        message: (error?.value as any)?.data?.message || 'Failed to update account.',
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
      const { status, error } = await api.archive(id);
      if (status?.value) {
        toast.success({ title: 'Payout account archived', message: 'Saved.', position: 'topRight', layout: 2 });
        await fetchAll();
        return true;
      }
      toast.error({
        title: 'Error',
        message: (error?.value as any)?.data?.message || 'Failed to archive account.',
        position: 'topRight',
        layout: 2,
      });
      return false;
    } finally {
      submitting.value = false;
    }
  };

  const setPrimary = async (id: number): Promise<boolean> => {
    submitting.value = true;
    try {
      const { status, error } = await api.setPrimary(id);
      if (status?.value) {
        toast.success({ title: 'Primary updated', message: 'Saved.', position: 'topRight', layout: 2 });
        await fetchAll();
        return true;
      }
      toast.error({
        title: 'Error',
        message: (error?.value as any)?.data?.message || 'Failed to set primary.',
        position: 'topRight',
        layout: 2,
      });
      return false;
    } finally {
      submitting.value = false;
    }
  };

  return { items, loading, submitting, fetchAll, create, update, archive, setPrimary };
});
