import { defineStore } from 'pinia';
import type { Product, ProductPriceTier, ProductVariant } from './product';

export interface VendorProductPayload {
  category_id: number;
  name: string;
  description?: string | null;
  sku?: string | null;
  weight_kg?: number | null;
  affiliate_influencer_pct?: number;
  affiliate_buyer_discount_pct?: number;
  price_tiers: ProductPriceTier[];
  variants: ProductVariant[];
}

export const useVendorProductStore = defineStore('vendorProduct', () => {
  const items = ref<Product[]>([]);
  const current = ref<Product | null>(null);
  const loading = ref(false);
  const submitting = ref(false);
  const filterStatus = ref<string | null>(null);

  const {
    listProducts, getProduct, createProduct, updateProduct, archiveProduct,
    resubmitProduct, uploadImage, setPrimaryImage, deleteImage,
  } = useVendorProductHelper();
  const toast = useToast();

  const counts = computed(() => {
    const acc = { PENDING: 0, ACTIVE: 0, REJECTED: 0, ARCHIVED: 0 } as Record<string, number>;
    for (const p of items.value) acc[p.status] = (acc[p.status] ?? 0) + 1;
    return acc;
  });

  const fetchAll = async (status?: string) => {
    loading.value = true;
    filterStatus.value = status ?? null;
    const { data, error } = await listProducts(status);
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

  const create = async (payload: VendorProductPayload): Promise<Product | null> => {
    submitting.value = true;
    try {
      const { data, status, error } = await createProduct(payload as Record<string, unknown>);
      if (status?.value) {
        toast.success({ title: 'Product submitted', message: 'Awaiting admin review.', position: 'topRight', layout: 2 });
        return ((data.value as any)?.data ?? null) as Product | null;
      }
      toast.error({
        title: 'Error',
        message: (error?.value as any)?.data?.message || 'Failed to create product.',
        position: 'topRight',
        layout: 2,
      });
      return null;
    } finally {
      submitting.value = false;
    }
  };

  const update = async (id: number, payload: VendorProductPayload): Promise<Product | null> => {
    submitting.value = true;
    try {
      const { data, status, error } = await updateProduct(id, payload as Record<string, unknown>);
      if (status?.value) {
        toast.success({ title: 'Product updated', message: 'Saved.', position: 'topRight', layout: 2 });
        return ((data.value as any)?.data ?? null) as Product | null;
      }
      toast.error({
        title: 'Error',
        message: (error?.value as any)?.data?.message || 'Failed to update product.',
        position: 'topRight',
        layout: 2,
      });
      return null;
    } finally {
      submitting.value = false;
    }
  };

  const archive = async (id: number): Promise<boolean> => {
    submitting.value = true;
    try {
      const { status, error } = await archiveProduct(id);
      if (status?.value) {
        toast.success({ title: 'Archived', message: 'Product archived.', position: 'topRight', layout: 2 });
        return true;
      }
      toast.error({
        title: 'Error',
        message: (error?.value as any)?.data?.message || 'Failed to archive.',
        position: 'topRight',
        layout: 2,
      });
      return false;
    } finally {
      submitting.value = false;
    }
  };

  const resubmit = async (id: number): Promise<boolean> => {
    submitting.value = true;
    try {
      const { status, error } = await resubmitProduct(id);
      if (status?.value) {
        toast.success({ title: 'Resubmitted', message: 'Awaiting admin review.', position: 'topRight', layout: 2 });
        return true;
      }
      toast.error({
        title: 'Error',
        message: (error?.value as any)?.data?.message || 'Failed to resubmit.',
        position: 'topRight',
        layout: 2,
      });
      return false;
    } finally {
      submitting.value = false;
    }
  };

  const uploadProductImage = async (id: number, file: File): Promise<boolean> => {
    submitting.value = true;
    try {
      const { status, error } = await uploadImage(id, file);
      if (status?.value) {
        toast.success({ title: 'Image uploaded', message: '', position: 'topRight', layout: 2 });
        return true;
      }
      toast.error({
        title: 'Error',
        message: (error?.value as any)?.data?.message || 'Upload failed.',
        position: 'topRight',
        layout: 2,
      });
      return false;
    } finally {
      submitting.value = false;
    }
  };

  const setPrimary = async (id: number, imageId: number): Promise<boolean> => {
    const { status, error } = await setPrimaryImage(id, imageId);
    if (status?.value) return true;
    toast.error({
      title: 'Error',
      message: (error?.value as any)?.data?.message || 'Could not set primary.',
      position: 'topRight',
      layout: 2,
    });
    return false;
  };

  const removeImage = async (id: number, imageId: number): Promise<boolean> => {
    const { status, error } = await deleteImage(id, imageId);
    if (status?.value) return true;
    toast.error({
      title: 'Error',
      message: (error?.value as any)?.data?.message || 'Delete failed.',
      position: 'topRight',
      layout: 2,
    });
    return false;
  };

  return {
    items, current, loading, submitting, filterStatus, counts,
    fetchAll, fetchOne, create, update, archive, resubmit,
    uploadProductImage, setPrimary, removeImage,
  };
});
