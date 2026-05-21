import { defineStore } from 'pinia';
import type { Product } from './product';

interface PaginationMeta {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}

export const usePublicProductStore = defineStore('publicProduct', () => {
  const items = ref<Product[]>([]);
  const meta = ref<PaginationMeta>({ current_page: 1, last_page: 1, per_page: 24, total: 0 });
  const current = ref<Product | null>(null);
  const loading = ref(false);

  // Browse filters
  const categoryId = ref<number | null>(null);
  const search = ref<string>('');

  const { listProducts, getProductBySlug } = useProductHelper();
  const currency = useCurrencyStore();
  const toast = useToast();

  const fetchList = async (page = 1, perPage = 24) => {
    loading.value = true;
    const { data, error } = await listProducts({
      category_id: categoryId.value,
      q: search.value || null,
      page,
      per_page: perPage,
    });
    if (!error.value) {
      const payload = (data.value as any)?.data ?? {};
      items.value = payload.items ?? [];
      meta.value = payload.meta ?? meta.value;
      currency.captureRateFromApi(payload.exchange_rate);
    } else {
      const msg = (error.value as any)?.data?.message || 'Failed to load products.';
      toast.error({ title: 'Error', message: msg, position: 'topRight', layout: 2 });
    }
    loading.value = false;
  };

  const fetchOne = async (slug: string) => {
    loading.value = true;
    const { data, error } = await getProductBySlug(slug);
    if (!error.value) {
      const payload = (data.value as any)?.data ?? {};
      current.value = payload.product ?? null;
      currency.captureRateFromApi(payload.exchange_rate);
    } else {
      current.value = null;
      const msg = (error.value as any)?.data?.message || 'Failed to load product.';
      toast.error({ title: 'Error', message: msg, position: 'topRight', layout: 2 });
    }
    loading.value = false;
    return current.value;
  };

  const setCategory = (id: number | null) => {
    categoryId.value = id;
  };
  const setSearch = (q: string) => {
    search.value = q;
  };

  return {
    items, meta, current, loading,
    categoryId, search,
    fetchList, fetchOne, setCategory, setSearch,
  };
});
