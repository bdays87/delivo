export const useMobileWalletHelper = () => {
  const client = useSanctumClient();

  /** Active wallets for use as a lookup (e.g. vendor apply form). */
  const getActiveWallets = async () => {
    try {
      const data = await client('/api/v1/mobile-wallets/list', { method: 'GET' });
      return { data: ref(data), error: ref(null) };
    } catch (err) {
      return { data: ref(null), error: ref(err) };
    }
  };

  return { getActiveWallets };
};
