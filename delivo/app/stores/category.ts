import { defineStore } from 'pinia';

export interface CategoryParent {
  id: number;
  name: string;
  slug: string;
}

export interface Category {
  id: number;
  parent_id: number | null;
  name: string;
  slug: string;
  icon: string;
  emoji: string | null;
  tint: string | null;
  description: string | null;
  sort_order: number;
  status: 'ACTIVE' | 'ARCHIVED';
  parent?: CategoryParent | null;
  products_count?: number;
}

export const useCategoryStore = defineStore('category', () => {
  const categories = ref<Category[]>([]);
  const loading = ref(false);

  const { listActive } = useCategoryHelper();

  const fetchActive = async () => {
    if (categories.value.length) return;
    loading.value = true;
    const { data, error } = await listActive();
    if (!error.value) {
      categories.value = (data.value as any)?.data ?? [];
    }
    loading.value = false;
  };

  return { categories, loading, fetchActive };
});
