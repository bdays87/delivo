import { defineStore } from 'pinia';
import type { Vendor } from './vendor';

export const useAdminVendorStore = defineStore('adminVendor', () => {
  const items = ref<Vendor[]>([]);
  const current = ref<Vendor | null>(null);
  const loading = ref(false);
  const submitting = ref(false);

  const {
    listVendors,
    getVendor,
    approveVendor,
    rejectVendor,
    suspendVendor,
    kycDocumentBlobUrl,
  } = useAdminVendorHelper();
  const toast = useToast();

  const fetchList = async (status?: string) => {
    loading.value = true;
    const { data, error } = await listVendors(status);
    if (!error.value) {
      items.value = (data.value as any)?.data ?? [];
    } else {
      const msg = (error.value as any)?.data?.message || 'Failed to fetch vendors.';
      toast.error({ title: 'Error', message: msg, position: 'topRight', layout: 2 });
    }
    loading.value = false;
  };

  const fetchOne = async (id: number) => {
    loading.value = true;
    const { data, error } = await getVendor(id);
    if (!error.value) {
      current.value = (data.value as any)?.data ?? null;
    } else {
      current.value = null;
      const msg = (error.value as any)?.data?.message || 'Failed to fetch vendor.';
      toast.error({ title: 'Error', message: msg, position: 'topRight', layout: 2 });
    }
    loading.value = false;
  };

  const approve = async (id: number) => {
    submitting.value = true;
    try {
      const { status, error } = await approveVendor(id);
      if (status?.value) {
        toast.success({ title: 'Approved', message: 'Vendor is now active.', position: 'topRight', layout: 2 });
        await fetchOne(id);
        return true;
      }
      toast.error({
        title: 'Error',
        message: (error?.value as any)?.data?.message || 'Failed to approve vendor.',
        position: 'topRight',
        layout: 2,
      });
      return false;
    } finally {
      submitting.value = false;
    }
  };

  const reject = async (id: number, reason: string) => {
    submitting.value = true;
    try {
      const { status, error } = await rejectVendor(id, reason);
      if (status?.value) {
        toast.success({ title: 'Rejected', message: 'Vendor rejected.', position: 'topRight', layout: 2 });
        await fetchOne(id);
        return true;
      }
      toast.error({
        title: 'Error',
        message: (error?.value as any)?.data?.message || 'Failed to reject vendor.',
        position: 'topRight',
        layout: 2,
      });
      return false;
    } finally {
      submitting.value = false;
    }
  };

  const suspend = async (id: number, reason?: string) => {
    submitting.value = true;
    try {
      const { status, error } = await suspendVendor(id, reason);
      if (status?.value) {
        toast.success({ title: 'Suspended', message: 'Vendor suspended.', position: 'topRight', layout: 2 });
        await fetchOne(id);
        return true;
      }
      toast.error({
        title: 'Error',
        message: (error?.value as any)?.data?.message || 'Failed to suspend vendor.',
        position: 'topRight',
        layout: 2,
      });
      return false;
    } finally {
      submitting.value = false;
    }
  };

  return { items, current, loading, submitting, fetchList, fetchOne, approve, reject, suspend, kycDocumentBlobUrl };
});
