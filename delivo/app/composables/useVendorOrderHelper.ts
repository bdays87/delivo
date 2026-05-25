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

  const listCarts = async () => {
    try {
      const data = await client('/api/v1/vendor/me/carts', { method: 'GET' });
      return { data: ref(data), error: ref(null) };
    } catch (err) {
      return { data: ref(null), error: ref(err) };
    }
  };

  const listDropoffs = async () => {
    try {
      const data = await client('/api/v1/vendor/me/dropoffs', { method: 'GET' });
      return { data: ref(data), error: ref(null) };
    } catch (err) {
      return { data: ref(null), error: ref(err) };
    }
  };

  const markDroppedOff = async (shipmentId: number) => {
    try {
      const data = await client(`/api/v1/vendor/me/shipments/${shipmentId}/dropoff`, { method: 'POST' });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  return { listOrders, ordersSummary, listCoupons, couponsSummary, listCarts, listDropoffs, markDroppedOff };
};
