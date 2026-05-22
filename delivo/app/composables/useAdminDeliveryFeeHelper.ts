export const useAdminDeliveryFeeHelper = () => {
  const client = useSanctumClient();

  const listFees = async () => {
    try {
      const data = await client('/api/v1/admin/delivery-fees', { method: 'GET' });
      return { data: ref(data), error: ref(null) };
    } catch (err) {
      return { data: ref(null), error: ref(err) };
    }
  };

  const createFee = async (payload: Record<string, unknown>) => {
    try {
      const data = await client('/api/v1/admin/delivery-fees', { method: 'POST', body: payload });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  const updateFee = async (id: number, payload: Record<string, unknown>) => {
    try {
      const data = await client(`/api/v1/admin/delivery-fees/${id}`, { method: 'PUT', body: payload });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  const archiveFee = async (id: number) => {
    try {
      const data = await client(`/api/v1/admin/delivery-fees/${id}`, { method: 'DELETE' });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  return { listFees, createFee, updateFee, archiveFee };
};
