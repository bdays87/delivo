import { defineStore } from 'pinia';

export interface OrderItem {
  id: number;
  vendor_id: number;
  product_id: number | null;
  product_variant_id: number | null;
  product_name_snapshot: string;
  color_snapshot: string | null;
  quantity: number;
  unit_price_usd_snapshot: string;
  line_total_usd_snapshot: string;
}

export interface OrderMobileWallet {
  id: number;
  name: string;
  code: string;
}

export type OrderStatus =
  | 'PENDING_PAYMENT' | 'PAID' | 'PICKED_UP' | 'OUT_FOR_DELIVERY'
  | 'DELIVERED' | 'COMPLETED' | 'CANCELLED' | 'REFUNDED';

export interface Order {
  id: number;
  order_number: string;
  user_id: number;
  address_id: number | null;
  mobile_wallet_id: number | null;
  ship_recipient_name: string;
  ship_recipient_phone: string;
  ship_city: string;
  ship_suburb: string;
  ship_street: string;
  ship_notes: string | null;
  status: OrderStatus;
  delivery_status: 'PENDING' | 'AWAITING_DROPOFF' | 'DROPOFF_INITIATED' | 'AWAITING_DISPATCH' | 'INROUTE' | 'DELIVERED';
  subtotal_usd: string;
  total_buyer_discount_usd: string | null;
  total_influencer_commission_usd: string | null;
  service_charge_usd: string;
  shipping_usd: string;
  total_usd: string;
  applied_coupon_id: number | null;
  applied_coupon_code: string | null;
  usd_to_zwg_rate: string | null;
  payment_reference: string;
  delivery_code: string | null;
  payment_confirmed_at: string | null;
  delivered_at: string | null;
  cancellation_reason: string | null;
  created_at: string;
  items?: OrderItem[];
  mobile_wallet?: OrderMobileWallet | null;
}

export const useOrderStore = defineStore('order', () => {
  const orders = ref<Order[]>([]);
  const current = ref<Order | null>(null);
  const loading = ref(false);

  const { listOrders, getOrder } = useOrderHelper();
  const toast = useToast();

  const fetchAll = async () => {
    loading.value = true;
    const { data, error } = await listOrders();
    if (!error.value) {
      orders.value = (data.value as any)?.data ?? [];
    } else {
      const msg = (error.value as any)?.data?.message || 'Failed to load orders.';
      toast.error({ title: 'Error', message: msg, position: 'topRight', layout: 2 });
    }
    loading.value = false;
  };

  const fetchOne = async (orderNumber: string) => {
    loading.value = true;
    const { data, error } = await getOrder(orderNumber);
    if (!error.value) {
      current.value = ((data.value as any)?.data ?? null) as Order | null;
    } else {
      current.value = null;
      const msg = (error.value as any)?.data?.message || 'Failed to load order.';
      toast.error({ title: 'Error', message: msg, position: 'topRight', layout: 2 });
    }
    loading.value = false;
    return current.value;
  };

  return { orders, current, loading, fetchAll, fetchOne };
});
