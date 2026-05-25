export const useProviderShipmentHelper = () => {
  const client = useSanctumClient();

  const listShipments = async (status?: string) => {
    try {
      const url = status
        ? `/api/v1/provider/me/shipments?status=${status}`
        : '/api/v1/provider/me/shipments';
      const data = await client(url, { method: 'GET' });
      return { data: ref(data), error: ref(null) };
    } catch (err) {
      return { data: ref(null), error: ref(err) };
    }
  };

  const transition = async (id: number, action: 'pickup' | 'dispatch' | 'deliver', body?: Record<string, unknown>) => {
    try {
      const data = await client(`/api/v1/provider/me/shipments/${id}/${action}`, {
        method: 'POST',
        body: body ?? undefined,
      });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  return { listShipments, transition };
};
