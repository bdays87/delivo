import { defineStore } from 'pinia';
import type { Category } from './category';

interface UpsertPayload {
  parent_id?: number | null;
  name?: string;
  slug?: string;
  icon?: string;
  emoji?: string | null;
  tint?: string | null;
  description?: string | null;
  sort_order?: number;
  status?: Category['status'];
}

export const useAdminCategoryStore = defineStore('adminCategory', () => {
  const items = ref<Category[]>([]);
  const loading = ref(false);
  const submitting = ref(false);

  const { listCategories, getCategory, createCategory, updateCategory, archiveCategory } = useAdminCategoryHelper();
  const toast = useToast();

  const fetchAll = async () => {
    loading.value = true;
    const { data, error } = await listCategories();
    if (!error.value) {
      items.value = (data.value as any)?.data ?? [];
    } else {
      const msg = (error.value as any)?.data?.message || 'Failed to fetch categories.';
      toast.error({ title: 'Error', message: msg, position: 'topRight', layout: 2 });
    }
    loading.value = false;
  };

  const findOne = async (id: number): Promise<Category | null> => {
    const { data, error } = await getCategory(id);
    if (!error.value) return ((data.value as any)?.data ?? null) as Category | null;
    const msg = (error.value as any)?.data?.message || 'Failed to fetch category.';
    toast.error({ title: 'Error', message: msg, position: 'topRight', layout: 2 });
    return null;
  };

  const create = async (payload: UpsertPayload): Promise<boolean> => {
    submitting.value = true;
    try {
      const { status, error } = await createCategory(payload as Record<string, unknown>);
      if (status?.value) {
        toast.success({ title: 'Category created', message: 'Saved.', position: 'topRight', layout: 2 });
        await fetchAll();
        return true;
      }
      toast.error({
        title: 'Error',
        message: (error?.value as any)?.data?.message || 'Failed to create category.',
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
      const { status, error } = await updateCategory(id, payload as Record<string, unknown>);
      if (status?.value) {
        toast.success({ title: 'Category updated', message: 'Saved.', position: 'topRight', layout: 2 });
        await fetchAll();
        return true;
      }
      toast.error({
        title: 'Error',
        message: (error?.value as any)?.data?.message || 'Failed to update category.',
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
      const { status, error } = await archiveCategory(id);
      if (status?.value) {
        toast.success({ title: 'Category archived', message: 'Saved.', position: 'topRight', layout: 2 });
        await fetchAll();
        return true;
      }
      toast.error({
        title: 'Error',
        message: (error?.value as any)?.data?.message || 'Failed to archive category.',
        position: 'topRight',
        layout: 2,
      });
      return false;
    } finally {
      submitting.value = false;
    }
  };

  const activeParents = computed(() =>
    items.value.filter((c) => c.status === 'ACTIVE' && !c.parent_id),
  );

  return { items, loading, submitting, fetchAll, findOne, create, update, archive, activeParents };
});
