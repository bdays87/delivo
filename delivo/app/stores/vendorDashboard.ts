import { defineStore } from 'pinia';
import type { VendorDashboardData } from '~/composables/useVendorDashboardHelper';

export const useVendorDashboardStore = defineStore('vendorDashboard', () => {
  const data = ref<VendorDashboardData | null>(null);
  const loading = ref(false);

  const { getDashboard } = useVendorDashboardHelper();
  const toast = useToast();

  const fetchDashboard = async () => {
    loading.value = true;
    const { data: response, error } = await getDashboard();
    if (!error.value) {
      data.value = (response.value as { data?: VendorDashboardData })?.data ?? null;
    } else {
      data.value = null;
      const msg = (error.value as { data?: { message?: string } })?.data?.message || 'Failed to load dashboard.';
      toast.error({ title: 'Error', message: msg, position: 'topRight', layout: 2 });
    }
    loading.value = false;
  };

  return { data, loading, fetchDashboard };
});
