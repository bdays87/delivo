<template>
  <div>
    <div>
      <h1 class="text-2xl font-extrabold tracking-tight">Orders</h1>
      <p class="mt-1 text-sm opacity-70">
        Confirm payments received in mobile wallets, then the order moves to delivery. Once the
        customer enters their delivery code, the order completes and influencer earnings clear.
      </p>
    </div>

    <div class="mt-6 flex flex-wrap gap-2">
      <button
        v-for="tab in tabs"
        :key="tab.key"
        :class="['btn btn-sm rounded-full', activeKey === tab.key ? 'btn-primary' : 'btn-ghost']"
        @click="setFilter(tab.key)"
      >
        {{ tab.label }}
      </button>
    </div>

    <div v-if="loading" class="flex justify-center py-16">
      <span class="loading loading-spinner loading-lg"></span>
    </div>

    <div v-else-if="!rows.length" class="mt-6 rounded-3xl border border-dashed border-base-300 p-12 text-center text-sm opacity-70">
      Nothing in this queue.
    </div>

    <div v-else class="mt-6 overflow-hidden rounded-3xl border border-base-300 bg-base-100">
      <table class="table">
        <thead class="bg-base-200/50 text-xs uppercase tracking-wider opacity-70">
          <tr>
            <th>Order</th>
            <th>Customer</th>
            <th>Total</th>
            <th>Wallet</th>
            <th>Status</th>
            <th>Placed</th>
            <th class="text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="o in rows" :key="o.id">
            <td>
              <div class="font-mono text-xs font-semibold">{{ o.order_number }}</div>
              <div class="text-xs opacity-60">Ref {{ o.payment_reference }}</div>
            </td>
            <td>
              <div class="font-semibold">{{ o.user?.name ?? '—' }}</div>
              <div class="text-xs opacity-60">{{ o.user?.email }}</div>
            </td>
            <td class="font-semibold">${{ Number(o.total_usd).toFixed(2) }}</td>
            <td class="text-xs">{{ o.mobile_wallet?.name ?? '—' }}</td>
            <td>
              <span :class="['badge badge-sm', statusBadge(o.status)]">{{ statusLabel(o.status) }}</span>
              <div class="mt-1">
                <span :class="['badge badge-xs', deliveryBadge(o.delivery_status)]">{{ deliveryLabel(o.delivery_status) }}</span>
              </div>
            </td>
            <td class="text-xs opacity-60">{{ o.created_at?.slice(0, 10) }}</td>
            <td class="text-right">
              <div class="flex flex-col items-end gap-1">
                <button
                  v-if="o.status === 'PENDING_PAYMENT'"
                  class="btn btn-success btn-xs rounded-full"
                  :disabled="submitting === o.id"
                  @click="onConfirmPayment(o)"
                >
                  Confirm payment
                </button>
                <button
                  v-if="o.status === 'PAID' && o.delivery_status === 'DROPOFF_INITIATED'"
                  class="btn btn-warning btn-xs rounded-full"
                  :disabled="submitting === o.id"
                  @click="onMarkDroppedOff(o)"
                >
                  Confirm receipt at hub
                </button>
                <button
                  v-if="o.status === 'PAID' && o.delivery_status === 'DELIVERED'"
                  class="btn btn-primary btn-xs rounded-full"
                  :disabled="submitting === o.id"
                  @click="onMarkDelivered(o)"
                >
                  Mark delivered + close
                </button>
                <span v-if="o.customer_delivery_confirmed_at" class="text-xs text-success">
                  ✓ Customer confirmed
                </span>
                <span v-else-if="o.payment_confirmed_at && o.status !== 'PENDING_PAYMENT'" class="text-xs opacity-60">
                  Paid {{ o.payment_confirmed_at.slice(0, 10) }}
                </span>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ layout: 'admin', middleware: ['auth', 'admin'] });
useHead({ title: 'Orders — Delivo Admin' });

type OrderStatus =
  | 'PENDING_PAYMENT' | 'PAID' | 'PICKED_UP' | 'OUT_FOR_DELIVERY'
  | 'DELIVERED' | 'COMPLETED' | 'CANCELLED' | 'REFUNDED';

type DeliveryStatus = 'PENDING' | 'AWAITING_DROPOFF' | 'DROPOFF_INITIATED' | 'AWAITING_DISPATCH' | 'INROUTE' | 'DELIVERED';

interface OrderRow {
  id: number;
  order_number: string;
  payment_reference: string;
  user: { id: number; name: string; email: string } | null;
  mobile_wallet: { id: number; name: string; code: string } | null;
  total_usd: string;
  status: OrderStatus;
  delivery_status: DeliveryStatus;
  payment_confirmed_at: string | null;
  customer_delivery_confirmed_at: string | null;
  created_at: string;
}

const { listOrders, confirmPayment, markDroppedOff, markDelivered } = useAdminOrderHelper();
const toast = useToast();

interface Tab { label: string; status?: OrderStatus; delivery_status?: DeliveryStatus; key: string }

const tabs: Tab[] = [
  { key: 'pending_payment', label: 'Awaiting payment', status: 'PENDING_PAYMENT' },
  { key: 'awaiting_dropoff', label: 'Awaiting dropoff', delivery_status: 'AWAITING_DROPOFF' },
  { key: 'dropoff_initiated', label: 'Dropoff in progress', delivery_status: 'DROPOFF_INITIATED' },
  { key: 'awaiting_dispatch', label: 'Awaiting dispatch', delivery_status: 'AWAITING_DISPATCH' },
  { key: 'inroute', label: 'In route', delivery_status: 'INROUTE' },
  { key: 'delivered_pending_close', label: 'Delivered (to close)', delivery_status: 'DELIVERED' },
  { key: 'closed', label: 'Closed', status: 'DELIVERED' },
  { key: 'all', label: 'All' },
];

const loading = ref(false);
const submitting = ref<number | null>(null);
const activeKey = ref<string>('pending_payment');
const rows = ref<OrderRow[]>([]);

const fetchAll = async () => {
  loading.value = true;
  const tab = tabs.find((t) => t.key === activeKey.value);
  const { data, error } = await listOrders({
    status: tab?.status,
    delivery_status: tab?.delivery_status,
  });
  if (!error.value) {
    rows.value = ((data.value as any)?.data ?? []) as OrderRow[];
  } else {
    toast.error({
      title: 'Error',
      message: (error.value as any)?.data?.message || 'Failed to load orders.',
      position: 'topRight',
      layout: 2,
    });
  }
  loading.value = false;
};

onMounted(fetchAll);

const setFilter = async (key: string) => {
  activeKey.value = key;
  await fetchAll();
};

const onConfirmPayment = async (o: OrderRow) => {
  if (!window.confirm(`Confirm payment of $${Number(o.total_usd).toFixed(2)} received for ${o.order_number}?`)) return;
  submitting.value = o.id;
  const { status, error } = await confirmPayment(o.order_number);
  if (status?.value) {
    toast.success({ title: 'Payment confirmed', message: o.order_number, position: 'topRight', layout: 2 });
    await fetchAll();
  } else {
    toast.error({
      title: 'Could not confirm',
      message: (error?.value as any)?.data?.message || 'Try again.',
      position: 'topRight',
      layout: 2,
    });
  }
  submitting.value = null;
};

const statusLabel = (s: OrderStatus) => ({
  PENDING_PAYMENT: 'Awaiting payment',
  PAID: 'Paid',
  PICKED_UP: 'Picked up',
  OUT_FOR_DELIVERY: 'Out for delivery',
  DELIVERED: 'Delivered',
  COMPLETED: 'Completed',
  CANCELLED: 'Cancelled',
  REFUNDED: 'Refunded',
}[s] ?? s);

const statusBadge = (s: OrderStatus) => ({
  PENDING_PAYMENT: 'badge-warning',
  PAID: 'badge-info',
  PICKED_UP: 'badge-info',
  OUT_FOR_DELIVERY: 'badge-info',
  DELIVERED: 'badge-success',
  COMPLETED: 'badge-success',
  CANCELLED: 'badge-error',
  REFUNDED: 'badge-ghost',
}[s] ?? 'badge-ghost');

const deliveryLabel = (s: DeliveryStatus) => ({
  PENDING: 'Pending',
  AWAITING_DROPOFF: 'Awaiting dropoff',
  DROPOFF_INITIATED: 'Dropoff in progress',
  AWAITING_DISPATCH: 'Awaiting dispatch',
  INROUTE: 'In route',
  DELIVERED: 'Delivered',
}[s] ?? s);

const deliveryBadge = (s: DeliveryStatus) => ({
  PENDING: 'badge-ghost',
  AWAITING_DROPOFF: 'badge-warning',
  DROPOFF_INITIATED: 'badge-info',
  AWAITING_DISPATCH: 'badge-info',
  INROUTE: 'badge-info',
  DELIVERED: 'badge-success',
}[s] ?? 'badge-ghost');

const onMarkDroppedOff = async (o: OrderRow) => {
  if (!window.confirm(`Mark ${o.order_number} as dropped off at the hub? Rider will be able to collect.`)) return;
  submitting.value = o.id;
  const { status, error } = await markDroppedOff(o.order_number);
  if (status?.value) {
    toast.success({ title: 'Marked dropped off', message: o.order_number, position: 'topRight', layout: 2 });
    await fetchAll();
  } else {
    toast.error({
      title: 'Could not mark',
      message: (error?.value as any)?.data?.message || 'Try again.',
      position: 'topRight',
      layout: 2,
    });
  }
  submitting.value = null;
};

const onMarkDelivered = async (o: OrderRow) => {
  if (!window.confirm(`Mark ${o.order_number} as delivered? This releases influencer earnings.`)) return;
  submitting.value = o.id;
  const { status, error } = await markDelivered(o.order_number);
  if (status?.value) {
    toast.success({ title: 'Marked delivered', message: o.order_number, position: 'topRight', layout: 2 });
    await fetchAll();
  } else {
    toast.error({
      title: 'Could not mark delivered',
      message: (error?.value as any)?.data?.message || 'Try again.',
      position: 'topRight',
      layout: 2,
    });
  }
  submitting.value = null;
};
</script>
