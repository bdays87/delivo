export const useAdminVehicleTypeHelper = () => {
  const client = useSanctumClient();

  const listAll = async () => {
    try {
      const data = await client('/api/v1/admin/vehicle-types', { method: 'GET' });
      return { data: ref(data), error: ref(null) };
    } catch (err) {
      return { data: ref(null), error: ref(err) };
    }
  };

  const create = async (payload: Record<string, unknown>) => {
    try {
      const data = await client('/api/v1/admin/vehicle-types', { method: 'POST', body: payload });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  const update = async (id: number, payload: Record<string, unknown>) => {
    try {
      const data = await client(`/api/v1/admin/vehicle-types/${id}`, { method: 'PUT', body: payload });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  const archive = async (id: number) => {
    try {
      const data = await client(`/api/v1/admin/vehicle-types/${id}`, { method: 'DELETE' });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  return { listAll, create, update, archive };
};

export const useVehicleTypeHelper = () => {
  const client = useSanctumClient();
  const listActive = async () => {
    try {
      const data = await client('/api/v1/vehicle-types/list', { method: 'GET' });
      return { data: ref(data), error: ref(null) };
    } catch (err) {
      return { data: ref(null), error: ref(err) };
    }
  };

  return { listActive };
};
