export const useVendorPayoutAccountHelper = () => {
  const client = useSanctumClient();

  const list = async () => {
    try {
      const data = await client('/api/v1/vendor/me/payout-accounts', { method: 'GET' });
      return { data: ref(data), error: ref(null) };
    } catch (err) {
      return { data: ref(null), error: ref(err) };
    }
  };

  const create = async (payload: Record<string, unknown>) => {
    try {
      const data = await client('/api/v1/vendor/me/payout-accounts', { method: 'POST', body: payload });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  const update = async (id: number, payload: Record<string, unknown>) => {
    try {
      const data = await client(`/api/v1/vendor/me/payout-accounts/${id}`, { method: 'PUT', body: payload });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  const archive = async (id: number) => {
    try {
      const data = await client(`/api/v1/vendor/me/payout-accounts/${id}`, { method: 'DELETE' });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  const setPrimary = async (id: number) => {
    try {
      const data = await client(`/api/v1/vendor/me/payout-accounts/${id}/primary`, { method: 'POST' });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  return { list, create, update, archive, setPrimary };
};
