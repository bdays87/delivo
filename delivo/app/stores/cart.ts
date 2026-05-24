import { defineStore } from 'pinia';

export interface CartLineProduct {
  id: number;
  name: string;
  slug: string;
  status: string;
  vendor: { id: number; business_name: string } | null;
  primary_image: { disk: string; path: string } | null;
}

export interface CartLineVariant {
  id: number;
  color: string | null;
  stock_quantity: number;
  sku: string | null;
}

export interface CartLine {
  id: number;
  product_id: number;
  product_variant_id: number;
  product: CartLineProduct | null;
  variant: CartLineVariant | null;
  quantity: number;
  unit_price_usd: string;
  line_gross_usd?: string;
  line_discount_usd?: string;
  line_total_usd: string;
  coupon_applies_to_line?: boolean;
  stock_warning: boolean;
}

export interface AppliedCoupon {
  id: number;
  code: string;
  buyer_discount_pct: number;
  influencer_commission_pct: number;
  product_id: number | null;
  influencer_id: number | null;
}

export interface CartSnapshot {
  id: number | null;
  items: CartLine[];
  item_count: number;
  subtotal_usd: string;
  total_discount_usd: string;
  service_charge_usd: string;
  items_total_usd: string;
  applied_coupon: AppliedCoupon | null;
  shipping_note: string;
}

const emptyCart = (): CartSnapshot => ({
  id: null,
  items: [],
  item_count: 0,
  subtotal_usd: '0.00',
  total_discount_usd: '0.00',
  service_charge_usd: '0.00',
  items_total_usd: '0.00',
  applied_coupon: null,
  shipping_note: 'Delivery calculated at checkout based on delivery city.',
});

export const useCartStore = defineStore('cart', () => {
  const cart = ref<CartSnapshot>(emptyCart());
  const loading = ref(false);
  const submitting = ref(false);
  const loaded = ref(false);

  const { getCart, addItem, updateItem, removeItem, clearCart, applyCoupon, removeCoupon } = useCartHelper();
  const auth = useAuthStore();
  const toast = useToast();

  const itemCount = computed(() => cart.value.item_count);
  const subtotalUsd = computed(() => Number(cart.value.subtotal_usd));
  const totalDiscountUsd = computed(() => Number(cart.value.total_discount_usd));
  const serviceChargeUsd = computed(() => Number(cart.value.service_charge_usd));
  const itemsTotalUsd = computed(() => Number(cart.value.items_total_usd));
  const appliedCoupon = computed(() => cart.value.applied_coupon);

  const refresh = async (silent = false) => {
    if (!auth.isAuthenticated) {
      cart.value = emptyCart();
      loaded.value = true;
      return;
    }
    if (!silent) loading.value = true;
    const { data, error } = await getCart();
    if (!error.value) {
      cart.value = ((data.value as any)?.data ?? emptyCart()) as CartSnapshot;
    } else if (!silent) {
      const msg = (error.value as any)?.data?.message || 'Failed to load cart.';
      toast.error({ title: 'Error', message: msg, position: 'topRight', layout: 2 });
    }
    loaded.value = true;
    loading.value = false;
  };

  const ensureLoaded = async () => {
    if (loaded.value) return;
    await refresh(true);
  };

  const add = async (variantId: number, quantity: number): Promise<boolean> => {
    if (!auth.isAuthenticated) {
      toast.info({ title: 'Sign in required', message: 'Login to add items to your cart.', position: 'topRight', layout: 2 });
      return false;
    }
    submitting.value = true;
    try {
      const { data, status, error } = await addItem(variantId, quantity);
      if (status?.value) {
        cart.value = ((data.value as any)?.data ?? cart.value) as CartSnapshot;
        toast.success({ title: 'Added to cart', message: '', position: 'topRight', layout: 2 });
        return true;
      }
      toast.error({
        title: 'Error',
        message: (error?.value as any)?.data?.message || 'Failed to add item.',
        position: 'topRight',
        layout: 2,
      });
      return false;
    } finally {
      submitting.value = false;
    }
  };

  const updateQty = async (itemId: number, quantity: number): Promise<boolean> => {
    submitting.value = true;
    try {
      const { data, status, error } = await updateItem(itemId, quantity);
      if (status?.value) {
        cart.value = ((data.value as any)?.data ?? cart.value) as CartSnapshot;
        return true;
      }
      toast.error({
        title: 'Error',
        message: (error?.value as any)?.data?.message || 'Failed to update cart.',
        position: 'topRight',
        layout: 2,
      });
      return false;
    } finally {
      submitting.value = false;
    }
  };

  const remove = async (itemId: number): Promise<boolean> => {
    submitting.value = true;
    try {
      const { data, status, error } = await removeItem(itemId);
      if (status?.value) {
        cart.value = ((data.value as any)?.data ?? cart.value) as CartSnapshot;
        return true;
      }
      toast.error({
        title: 'Error',
        message: (error?.value as any)?.data?.message || 'Failed to remove item.',
        position: 'topRight',
        layout: 2,
      });
      return false;
    } finally {
      submitting.value = false;
    }
  };

  const clear = async (): Promise<boolean> => {
    submitting.value = true;
    try {
      const { data, status } = await clearCart();
      if (status?.value) {
        cart.value = ((data.value as any)?.data ?? emptyCart()) as CartSnapshot;
        return true;
      }
      return false;
    } finally {
      submitting.value = false;
    }
  };

  const reset = () => {
    cart.value = emptyCart();
    loaded.value = false;
  };

  const applyCode = async (code: string): Promise<boolean> => {
    submitting.value = true;
    try {
      const { data, status, error } = await applyCoupon(code);
      if (status?.value) {
        cart.value = ((data.value as any)?.data ?? cart.value) as CartSnapshot;
        toast.success({ title: 'Code applied', message: cart.value.applied_coupon?.code ?? '', position: 'topRight', layout: 2 });
        return true;
      }
      toast.error({
        title: 'Could not apply code',
        message: (error?.value as any)?.data?.message || 'Try again.',
        position: 'topRight',
        layout: 2,
      });
      return false;
    } finally {
      submitting.value = false;
    }
  };

  const removeCode = async (): Promise<boolean> => {
    submitting.value = true;
    try {
      const { data, status } = await removeCoupon();
      if (status?.value) {
        cart.value = ((data.value as any)?.data ?? cart.value) as CartSnapshot;
        return true;
      }
      return false;
    } finally {
      submitting.value = false;
    }
  };

  return {
    cart, loading, submitting, loaded,
    itemCount, subtotalUsd, totalDiscountUsd, serviceChargeUsd, itemsTotalUsd, appliedCoupon,
    refresh, ensureLoaded, add, updateQty, remove, clear, reset, applyCode, removeCode,
  };
});

// Build a public storefront URL from a cart line's stored image path.
export const cartLineImageUrl = (line: CartLine): string | null => {
  if (!line.product?.primary_image) return null;
  const cfg = useRuntimeConfig();
  const base = cfg.public.apiBase as string;
  return `${base}/storage/${line.product.primary_image.path}`;
};
