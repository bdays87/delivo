import { defineStore } from 'pinia';
import type { Product } from './product';

export const useAdminProductStore = defineStore('adminProduct', () => {
  const items = ref<Product[]>([]);
  const current = ref<Product | null>(null);
  const loading = ref(false);
  const submitting = ref(false);
  const filterStatus = ref<string>('PENDING');

  const { listProducts, getProduct, approveProduct, rejectProduct, takedownProduct } = useAdminProductHelper();
  const toast = useToast();

  const fetchAll = async (status?: string) => {
    loading.value = true;
    filterStatus.value = status ?? filterStatus.value;
    const { data, error } = await listProducts(filterStatus.value);
    if (!error.value) {
      items.value = (data.value as any)?.data ?? [];
    } else {
      const msg = (error.value as any)?.data?.message || 'Failed to fetch products.';
      toast.error({ title: 'Error', message: msg, position: 'topRight', layout: 2 });
    }
    loading.value = false;
  };

  const fetchOne = async (id: number) => {
    loading.value = true;
    const { data, error } = await getProduct(id);
    if (!error.value) {
      current.value = ((data.value as any)?.data ?? null) as Product | null;
    } else {
      current.value = null;
      const msg = (error.value as any)?.data?.message || 'Failed to fetch product.';
      toast.error({ title: 'Error', message: msg, position: 'topRight', layout: 2 });
    }
    loading.value = false;
    return current.value;
  };

  const approve = async (id: number): Promise<boolean> => {
    submitting.value = true;
    try {
      const { status, error } = await approveProduct(id);
      if (status?.value) {
        toast.success({ title: 'Approved', message: 'Product is now live.', position: 'topRight', layout: 2 });
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

  const reject = async (id: number, reason: string): Promise<boolean> => {
    submitting.value = true;
    try {
      const { status, error } = await rejectProduct(id, reason);
      if (status?.value) {
        toast.success({ title: 'Rejected', message: 'Vendor notified.', position: 'topRight', layout: 2 });
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

  const takedown = async (id: number, reason: string): Promise<boolean> => {
    submitting.value = true;
    try {
      const { status, error } = await takedownProduct(id, reason);
      if (status?.value) {
        toast.success({ title: 'Taken down', message: 'Product moved to rejected.', position: 'topRight', layout: 2 });
        return true;
      }
      toast.error({
        title: 'Error',
        message: (error?.value as any)?.data?.message || 'Failed to take down.',
        position: 'topRight',
        layout: 2,
      });
      return false;
    } finally {
      submitting.value = false;
    }
  };

  return { items, current, loading, submitting, filterStatus, fetchAll, fetchOne, approve, reject, takedown };
});
