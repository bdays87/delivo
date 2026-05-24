export const useCartHelper = () => {
  const client = useSanctumClient();

  const getCart = async () => {
    try {
      const data = await client('/api/v1/cart', { method: 'GET' });
      return { data: ref(data), error: ref(null) };
    } catch (err) {
      return { data: ref(null), error: ref(err) };
    }
  };

  const addItem = async (variantId: number, quantity: number) => {
    try {
      const data = await client('/api/v1/cart/items', {
        method: 'POST',
        body: { product_variant_id: variantId, quantity },
      });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  const updateItem = async (itemId: number, quantity: number) => {
    try {
      const data = await client(`/api/v1/cart/items/${itemId}`, {
        method: 'PUT',
        body: { quantity },
      });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  const removeItem = async (itemId: number) => {
    try {
      const data = await client(`/api/v1/cart/items/${itemId}`, { method: 'DELETE' });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  const clearCart = async () => {
    try {
      const data = await client('/api/v1/cart', { method: 'DELETE' });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  const applyCoupon = async (code: string) => {
    try {
      const data = await client('/api/v1/cart/coupon', { method: 'POST', body: { code } });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  const removeCoupon = async () => {
    try {
      const data = await client('/api/v1/cart/coupon', { method: 'DELETE' });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  return { getCart, addItem, updateItem, removeItem, clearCart, applyCoupon, removeCoupon };
};
