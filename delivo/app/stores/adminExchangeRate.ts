import { defineStore } from 'pinia';

export interface ExchangeRate {
  id?: number;
  from_currency: string;
  to_currency: string;
  rate: string | number | null;
  updated_at?: string | null;
  updated_by_user_id?: number | null;
}

export const useAdminExchangeRateStore = defineStore('adminExchangeRate', () => {
  const usdZwg = ref<ExchangeRate | null>(null);
  const loading = ref(false);
  const submitting = ref(false);

  const { getUsdZwgRate, updateUsdZwgRate } = useAdminExchangeRateHelper();
  const toast = useToast();

  const fetch = async () => {
    loading.value = true;
    const { data, error } = await getUsdZwgRate();
    if (!error.value) {
      usdZwg.value = ((data.value as any)?.data ?? null) as ExchangeRate | null;
    } else {
      const msg = (error.value as any)?.data?.message || 'Failed to load rate.';
      toast.error({ title: 'Error', message: msg, position: 'topRight', layout: 2 });
    }
    loading.value = false;
  };

  const save = async (rate: number): Promise<boolean> => {
    submitting.value = true;
    try {
      const { data, status, error } = await updateUsdZwgRate(rate);
      if (status?.value) {
        usdZwg.value = ((data.value as any)?.data ?? null) as ExchangeRate | null;
        toast.success({ title: 'Rate updated', message: 'Storefront will now use the new rate.', position: 'topRight', layout: 2 });
        return true;
      }
      toast.error({
        title: 'Error',
        message: (error?.value as any)?.data?.message || 'Could not save rate.',
        position: 'topRight',
        layout: 2,
      });
      return false;
    } finally {
      submitting.value = false;
    }
  };

  return { usdZwg, loading, submitting, fetch, save };
});
