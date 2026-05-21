export const useVendorPublicHelper = () => {
  const client = useSanctumClient();

  const listActiveVendors = async () => {
    try {
      const data = await client('/api/v1/vendors/list', { method: 'GET' });
      return { data: ref(data), error: ref(null) };
    } catch (err) {
      return { data: ref(null), error: ref(err) };
    }
  };

  return { listActiveVendors };
};
