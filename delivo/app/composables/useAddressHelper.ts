export const useAddressHelper = () => {
  const client = useSanctumClient();

  const listAddresses = async () => {
    try {
      const data = await client('/api/v1/addresses', { method: 'GET' });
      return { data: ref(data), error: ref(null) };
    } catch (err) {
      return { data: ref(null), error: ref(err) };
    }
  };

  const createAddress = async (payload: Record<string, unknown>) => {
    try {
      const data = await client('/api/v1/addresses', { method: 'POST', body: payload });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  const updateAddress = async (id: number, payload: Record<string, unknown>) => {
    try {
      const data = await client(`/api/v1/addresses/${id}`, { method: 'PUT', body: payload });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  const deleteAddress = async (id: number) => {
    try {
      const data = await client(`/api/v1/addresses/${id}`, { method: 'DELETE' });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  const setDefaultAddress = async (id: number) => {
    try {
      const data = await client(`/api/v1/addresses/${id}/default`, { method: 'POST' });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  return { listAddresses, createAddress, updateAddress, deleteAddress, setDefaultAddress };
};
