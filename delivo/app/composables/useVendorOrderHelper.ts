export const useVendorOrderHelper = () => {
  const client = useSanctumClient();

  const listOrders = async (filter?: { status?: string; delivery_status?: string; delivery_method?: string }) => {
    try {
      const params = new URLSearchParams();
      if (filter?.status) params.set('status', filter.status);
      if (filter?.delivery_status) params.set('delivery_status', filter.delivery_status);
      if (filter?.delivery_method) params.set('delivery_method', filter.delivery_method);
      const qs = params.toString();
      const url = qs ? `/api/v1/vendor/me/orders?${qs}` : '/api/v1/vendor/me/orders';
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

  const listDropoffHubs = async () => {
    try {
      const data = await client('/api/v1/vendor/me/dropoff-hubs', { method: 'GET' });
      return { data: ref(data), error: ref(null) };
    } catch (err) {
      return { data: ref(null), error: ref(err) };
    }
  };

  const initiateDropoff = async (shipmentId: number, hubId: number) => {
    try {
      const data = await client(`/api/v1/vendor/me/shipments/${shipmentId}/initiate-dropoff`, {
        method: 'POST',
        body: { hub_id: hubId },
      });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  const confirmSelfPickup = async (orderNumber: string, code: string) => {
    try {
      const data = await client(`/api/v1/vendor/me/orders/${orderNumber}/confirm-pickup`, {
        method: 'POST',
        body: { code },
      });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  return { listOrders, ordersSummary, listCoupons, couponsSummary, listCarts, listDropoffHubs, initiateDropoff, confirmSelfPickup };
};
