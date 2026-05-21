export const useAdminExchangeRateHelper = () => {
  const client = useSanctumClient();

  const getUsdZwgRate = async () => {
    try {
      const data = await client('/api/v1/admin/exchange-rates/usd-zwg', { method: 'GET' });
      return { data: ref(data), error: ref(null) };
    } catch (err) {
      return { data: ref(null), error: ref(err) };
    }
  };

  const updateUsdZwgRate = async (rate: number) => {
    try {
      const data = await client('/api/v1/admin/exchange-rates/usd-zwg', {
        method: 'PUT',
        body: { rate },
      });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  return { getUsdZwgRate, updateUsdZwgRate };
};
