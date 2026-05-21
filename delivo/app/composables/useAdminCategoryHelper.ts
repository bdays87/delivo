export const useAdminCategoryHelper = () => {
  const client = useSanctumClient();

  const listCategories = async () => {
    try {
      const data = await client('/api/v1/admin/categories', { method: 'GET' });
      return { data: ref(data), error: ref(null) };
    } catch (err) {
      return { data: ref(null), error: ref(err) };
    }
  };

  const getCategory = async (id: number) => {
    try {
      const data = await client(`/api/v1/admin/categories/${id}`, { method: 'GET' });
      return { data: ref(data), error: ref(null) };
    } catch (err) {
      return { data: ref(null), error: ref(err) };
    }
  };

  const createCategory = async (payload: Record<string, unknown>) => {
    try {
      const data = await client('/api/v1/admin/categories', { method: 'POST', body: payload });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  const updateCategory = async (id: number, payload: Record<string, unknown>) => {
    try {
      const data = await client(`/api/v1/admin/categories/${id}`, { method: 'PUT', body: payload });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  const archiveCategory = async (id: number) => {
    try {
      const data = await client(`/api/v1/admin/categories/${id}`, { method: 'DELETE' });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  return { listCategories, getCategory, createCategory, updateCategory, archiveCategory };
};
