export const useVendorProductHelper = () => {
  const client = useSanctumClient();

  const listProducts = async (status?: string) => {
    try {
      const url = status ? `/api/v1/vendor/me/products?status=${status}` : '/api/v1/vendor/me/products';
      const data = await client(url, { method: 'GET' });
      return { data: ref(data), error: ref(null) };
    } catch (err) {
      return { data: ref(null), error: ref(err) };
    }
  };

  const getProduct = async (id: number) => {
    try {
      const data = await client(`/api/v1/vendor/me/products/${id}`, { method: 'GET' });
      return { data: ref(data), error: ref(null) };
    } catch (err) {
      return { data: ref(null), error: ref(err) };
    }
  };

  const createProduct = async (payload: Record<string, unknown>) => {
    try {
      const data = await client('/api/v1/vendor/me/products', { method: 'POST', body: payload });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  const updateProduct = async (id: number, payload: Record<string, unknown>) => {
    try {
      const data = await client(`/api/v1/vendor/me/products/${id}`, { method: 'PUT', body: payload });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  const archiveProduct = async (id: number) => {
    try {
      const data = await client(`/api/v1/vendor/me/products/${id}`, { method: 'DELETE' });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  const resubmitProduct = async (id: number) => {
    try {
      const data = await client(`/api/v1/vendor/me/products/${id}/resubmit`, { method: 'POST' });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  const uploadImage = async (id: number, file: File) => {
    try {
      const body = new FormData();
      body.append('image', file);
      const data = await client(`/api/v1/vendor/me/products/${id}/images`, { method: 'POST', body });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  const setPrimaryImage = async (id: number, imageId: number) => {
    try {
      const data = await client(`/api/v1/vendor/me/products/${id}/images/${imageId}/primary`, { method: 'POST' });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  const deleteImage = async (id: number, imageId: number) => {
    try {
      const data = await client(`/api/v1/vendor/me/products/${id}/images/${imageId}`, { method: 'DELETE' });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  return {
    listProducts,
    getProduct,
    createProduct,
    updateProduct,
    archiveProduct,
    resubmitProduct,
    uploadImage,
    setPrimaryImage,
    deleteImage,
  };
};
