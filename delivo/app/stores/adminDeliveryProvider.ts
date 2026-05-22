import { defineStore } from 'pinia';
import type { DeliveryProvider } from './provider';

export const useAdminDeliveryProviderStore = defineStore('adminDeliveryProvider', () => {
  const items = ref<DeliveryProvider[]>([]);
  const current = ref<DeliveryProvider | null>(null);
  const loading = ref(false);
  const submitting = ref(false);
  const filterStatus = ref<string>('PENDING');

  const { listProviders, getProvider, approve, reject, suspend, kycDocumentBlobUrl } = useAdminDeliveryProviderHelper();
  const toast = useToast();

  const fetchAll = async (status?: string) => {
    loading.value = true;
    filterStatus.value = status ?? filterStatus.value;
    const { data, error } = await listProviders(filterStatus.value);
    if (!error.value) {
      items.value = (data.value as any)?.data ?? [];
    }
    loading.value = false;
  };

  const fetchOne = async (id: number) => {
    loading.value = true;
    const { data, error } = await getProvider(id);
    if (!error.value) {
      current.value = ((data.value as any)?.data ?? null) as DeliveryProvider | null;
    } else {
      current.value = null;
    }
    loading.value = false;
    return current.value;
  };

  const onApprove = async (id: number): Promise<boolean> => {
    submitting.value = true;
    try {
      const { status, error } = await approve(id);
      if (status?.value) {
        toast.success({ title: 'Approved', message: 'Provider activated.', position: 'topRight', layout: 2 });
        return true;
      }
      toast.error({
        title: 'Error',
        message: (error?.value as any)?.data?.message || 'Failed to approve.',
        position: 'topRight',
        layout: 2,
      });
      return false;
    } finally {
      submitting.value = false;
    }
  };

  const onReject = async (id: number, reason: string): Promise<boolean> => {
    submitting.value = true;
    try {
      const { status, error } = await reject(id, reason);
      if (status?.value) {
        toast.success({ title: 'Rejected', message: 'Provider notified.', position: 'topRight', layout: 2 });
        return true;
      }
      toast.error({
        title: 'Error',
        message: (error?.value as any)?.data?.message || 'Failed to reject.',
        position: 'topRight',
        layout: 2,
      });
      return false;
    } finally {
      submitting.value = false;
    }
  };

  const onSuspend = async (id: number, reason?: string): Promise<boolean> => {
    submitting.value = true;
    try {
      const { status, error } = await suspend(id, reason);
      if (status?.value) {
        toast.success({ title: 'Suspended', message: '', position: 'topRight', layout: 2 });
        return true;
      }
      toast.error({
        title: 'Error',
        message: (error?.value as any)?.data?.message || 'Failed to suspend.',
        position: 'topRight',
        layout: 2,
      });
      return false;
    } finally {
      submitting.value = false;
    }
  };

  return {
    items, current, loading, submitting, filterStatus,
    fetchAll, fetchOne, onApprove, onReject, onSuspend, kycDocumentBlobUrl,
  };
});
