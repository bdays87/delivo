interface ListParams {
  category_id?: number | null;
  q?: string | null;
  per_page?: number;
  page?: number;
}

export const useProductHelper = () => {
  const client = useSanctumClient();

  const listProducts = async (params: ListParams = {}) => {
    try {
      const query = new URLSearchParams();
      if (params.category_id) query.set('category_id', String(params.category_id));
      if (params.q) query.set('q', params.q);
      if (params.per_page) query.set('per_page', String(params.per_page));
      if (params.page) query.set('page', String(params.page));
      const suffix = query.toString() ? `?${query.toString()}` : '';
      const data = await client(`/api/v1/products${suffix}`, { method: 'GET' });
      return { data: ref(data), error: ref(null) };
    } catch (err) {
      return { data: ref(null), error: ref(err) };
    }
  };

  const getProductBySlug = async (slug: string) => {
    try {
      const data = await client(`/api/v1/products/${slug}`, { method: 'GET' });
      return { data: ref(data), error: ref(null) };
    } catch (err) {
      return { data: ref(null), error: ref(err) };
    }
  };

  return { listProducts, getProductBySlug };
};
