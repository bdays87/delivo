export const useAdminDeliveryZoneHelper = () => {
  const client = useSanctumClient();

  const listZones = async () => {
    try {
      const data = await client('/api/v1/admin/delivery-zones', { method: 'GET' });
      return { data: ref(data), error: ref(null) };
    } catch (err) {
      return { data: ref(null), error: ref(err) };
    }
  };

  const createZone = async (payload: Record<string, unknown>) => {
    try {
      const data = await client('/api/v1/admin/delivery-zones', { method: 'POST', body: payload });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  const updateZone = async (id: number, payload: Record<string, unknown>) => {
    try {
      const data = await client(`/api/v1/admin/delivery-zones/${id}`, { method: 'PUT', body: payload });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  const archiveZone = async (id: number) => {
    try {
      const data = await client(`/api/v1/admin/delivery-zones/${id}`, { method: 'DELETE' });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  return { listZones, createZone, updateZone, archiveZone };
};
