export const useCategoryHelper = () => {
  const client = useSanctumClient();

  const listActive = async () => {
    try {
      const data = await client('/api/v1/categories/list', { method: 'GET' });
      return { data: ref(data), error: ref(null) };
    } catch (err) {
      return { data: ref(null), error: ref(err) };
    }
  };

  return { listActive };
};
