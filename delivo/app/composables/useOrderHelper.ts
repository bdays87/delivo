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
