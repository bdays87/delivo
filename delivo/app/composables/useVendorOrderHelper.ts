export const useVendorOrderHelper = () => {
  const client = useSanctumClient();

  const listOrders = async (status?: string) => {
    try {
      const url = status ? `/api/v1/vendor/me/orders?status=${status}` : '/api/v1/vendor/me/orders';
      const data = await client(url, { method: 'GET' });
      return { data: ref(data), error: ref(null) };
    } catch (err) {
      return { data: ref(null), error: ref(err) };
    }
  };

  const ordersSummary = async () => {
    try {
      const data = await client('/api/v1/vendor/me/orders/summary', { method: 'GET' });
      return { data: ref(data), error: ref(null) };
    } catch (err) {
      return { data: ref(null), error: ref(err) };
    }
  };

  const listCoupons = async () => {
    try {
      const data = await client('/api/v1/vendor/me/coupons', { method: 'GET' });
      return { data: ref(data), error: ref(null) };
    } catch (err) {
      return { data: ref(null), error: ref(err) };
    }
  };

  const couponsSummary = async () => {
    try {
      const data = await client('/api/v1/vendor/me/coupons/summary', { method: 'GET' });
      return { data: ref(data), error: ref(null) };
    } catch (err) {
      return { data: ref(null), error: ref(err) };
    }
  };

  return { listOrders, ordersSummary, listCoupons, couponsSummary };
};
