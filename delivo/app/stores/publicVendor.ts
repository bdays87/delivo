import { defineStore } from 'pinia';

export interface PublicVendor {
  id: number;
  business_name: string;
  slug: string;
  support_email: string | null;
  approved_at: string | null;
  products_count: number;
}

export const usePublicVendorStore = defineStore('publicVendor', () => {
  const vendors = ref<PublicVendor[]>([]);
  const loading = ref(false);

  const { listActiveVendors } = useVendorPublicHelper();
  const toast = useToast();

  const fetchActive = async (force = false) => {
    if (vendors.value.length && !force) return;
    loading.value = true;
    const { data, error } = await listActiveVendors();
    if (!error.value) {
      vendors.value = (data.value as any)?.data ?? [];
    } else {
      const msg = (error.value as any)?.data?.message || 'Failed to load vendors.';
      toast.error({ title: 'Error', message: msg, position: 'topRight', layout: 2 });
    }
    loading.value = false;
  };

  return { vendors, loading, fetchActive };
});
