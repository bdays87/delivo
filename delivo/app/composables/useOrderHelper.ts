export const useOrderHelper = () => {
  const client = useSanctumClient();

  const listOrders = async () => {
    try {
      const data = await client('/api/v1/orders', { method: 'GET' });
      return { data: ref(data), error: ref(null) };
    } catch (err) {
      return { data: ref(null), error: ref(err) };
    }
  };

  const getOrder = async (orderNumber: string) => {
    try {
      const data = await client(`/api/v1/orders/${orderNumber}`, { method: 'GET' });
      return { data: ref(data), error: ref(null) };
    } catch (err) {
      return { data: ref(null), error: ref(err) };
    }
  };

  return { listOrders, getOrder };
};

export const useAdminOrderHelper = () => {
  const client = useSanctumClient();

  const listOrders = async (filter?: { status?: string; delivery_status?: string }) => {
    try {
      const params = new URLSearchParams();
      if (filter?.status) params.set('status', filter.status);
      if (filter?.delivery_status) params.set('delivery_status', filter.delivery_status);
      const qs = params.toString();
      const url = qs ? `/api/v1/admin/orders?${qs}` : '/api/v1/admin/orders';
      const data = await client(url, { method: 'GET' });
      return { data: ref(data), error: ref(null) };
    } catch (err) {
      return { data: ref(null), error: ref(err) };
    }
  };

  const getOrder = async (orderNumber: string) => {
    try {
      const data = await client(`/api/v1/admin/orders/${orderNumber}`, { method: 'GET' });
      return { data: ref(data), error: ref(null) };
    } catch (err) {
      return { data: ref(null), error: ref(err) };
    }
  };

  const confirmPayment = async (orderNumber: string) => {
    try {
      const data = await client(`/api/v1/admin/orders/${orderNumber}/confirm-payment`, { method: 'POST' });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  const markDroppedOff = async (orderNumber: string) => {
    try {
      const data = await client(`/api/v1/admin/orders/${orderNumber}/mark-dropped-off`, { method: 'POST' });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  const markDelivered = async (orderNumber: string) => {
    try {
      const data = await client(`/api/v1/admin/orders/${orderNumber}/mark-delivered`, { method: 'POST' });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  return { listOrders, getOrder, confirmPayment, markDroppedOff, markDelivered };
};

export const useAdminPayoutHelper = () => {
  const client = useSanctumClient();

  const list = async (status?: string) => {
    try {
      const url = status ? `/api/v1/admin/payouts?status=${status}` : '/api/v1/admin/payouts';
      const data = await client(url, { method: 'GET' });
      return { data: ref(data), error: ref(null) };
    } catch (err) {
      return { data: ref(null), error: ref(err) };
    }
  };

  const get = async (id: number) => {
    try {
      const data = await client(`/api/v1/admin/payouts/${id}`, { method: 'GET' });
      return { data: ref(data), error: ref(null) };
    } catch (err) {
      return { data: ref(null), error: ref(err) };
    }
  };

  const approve = async (id: number, notes?: string) => {
    try {
      const data = await client(`/api/v1/admin/payouts/${id}/approve`, {
        method: 'POST',
        body: { notes: notes ?? null },
      });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  const markPaid = async (id: number, notes?: string) => {
    try {
      const data = await client(`/api/v1/admin/payouts/${id}/mark-paid`, {
        method: 'POST',
        body: { notes: notes ?? null },
      });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  const reject = async (id: number, reason: string) => {
    try {
      const data = await client(`/api/v1/admin/payouts/${id}/reject`, {
        method: 'POST',
        body: { reason },
      });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  return { list, get, approve, markPaid, reject };
};
