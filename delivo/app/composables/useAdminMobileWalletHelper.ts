export const useAdminMobileWalletHelper = () => {
  const client = useSanctumClient();

  const listWallets = async () => {
    try {
      const data = await client('/api/v1/admin/mobile-wallets', { method: 'GET' });
      return { data: ref(data), error: ref(null) };
    } catch (err) {
      return { data: ref(null), error: ref(err) };
    }
  };

  const getWallet = async (id: number) => {
    try {
      const data = await client(`/api/v1/admin/mobile-wallets/${id}`, { method: 'GET' });
      return { data: ref(data), error: ref(null) };
    } catch (err) {
      return { data: ref(null), error: ref(err) };
    }
  };

  const createWallet = async (payload: Record<string, unknown>) => {
    try {
      const data = await client('/api/v1/admin/mobile-wallets', { method: 'POST', body: payload });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  const updateWallet = async (id: number, payload: Record<string, unknown>) => {
    try {
      const data = await client(`/api/v1/admin/mobile-wallets/${id}`, { method: 'PUT', body: payload });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  const archiveWallet = async (id: number) => {
    try {
      const data = await client(`/api/v1/admin/mobile-wallets/${id}`, { method: 'DELETE' });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  return { listWallets, getWallet, createWallet, updateWallet, archiveWallet };
};
