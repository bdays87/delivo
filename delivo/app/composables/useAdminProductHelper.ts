export const useAdminProductHelper = () => {
  const client = useSanctumClient();

  const listProducts = async (status?: string) => {
    try {
      const url = status ? `/api/v1/admin/products?status=${status}` : '/api/v1/admin/products';
      const data = await client(url, { method: 'GET' });
      return { data: ref(data), error: ref(null) };
    } catch (err) {
      return { data: ref(null), error: ref(err) };
    }
  };

  const getProduct = async (id: number) => {
    try {
      const data = await client(`/api/v1/admin/products/${id}`, { method: 'GET' });
      return { data: ref(data), error: ref(null) };
    } catch (err) {
      return { data: ref(null), error: ref(err) };
    }
  };

  const approveProduct = async (id: number) => {
    try {
      const data = await client(`/api/v1/admin/products/${id}/approve`, { method: 'POST' });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  const rejectProduct = async (id: number, reason: string) => {
    try {
      const data = await client(`/api/v1/admin/products/${id}/reject`, {
        method: 'POST',
        body: { reason },
      });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  const takedownProduct = async (id: number, reason: string) => {
    try {
      const data = await client(`/api/v1/admin/products/${id}/takedown`, {
        method: 'POST',
        body: { reason },
      });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  return { listProducts, getProduct, approveProduct, rejectProduct, takedownProduct };
};
