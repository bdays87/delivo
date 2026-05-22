export const useCheckoutHelper = () => {
  const client = useSanctumClient();

  const quoteOrder = async (addressId: number) => {
    try {
      const data = await client('/api/v1/checkout/quote', {
        method: 'POST',
        body: { address_id: addressId },
      });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  const placeOrder = async (addressId: number, mobileWalletId: number) => {
    try {
      const data = await client('/api/v1/checkout', {
        method: 'POST',
        body: { address_id: addressId, mobile_wallet_id: mobileWalletId },
      });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  return { quoteOrder, placeOrder };
};
