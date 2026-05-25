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
        :key="tab.value"
        :class="['btn btn-sm rounded-full', filterStatus === tab.value ? 'btn-primary' : 'btn-ghost']"
        @click="setFilter(tab.value)"
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
            <td><span :class="['badge badge-sm', statusBadge(o.status)]">{{ statusLabel(o.status) }}</span></td>
            <td class="text-xs opacity-60">{{ o.created_at?.slice(0, 10) }}</td>
            <td class="text-right">
              <button
                v-if="o.status === 'PENDING_PAYMENT'"
                class="btn btn-success btn-xs rounded-full"
                :disabled="submitting === o.id"
                @click="onConfirmPayment(o)"
              >
                Confirm payment
              </button>
              <span v-else-if="o.payment_confirmed_at" class="text-xs opacity-60">
                Paid {{ o.payment_confirmed_at.slice(0, 10) }}
              </span>
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

interface OrderRow {
  id: number;
  order_number: string;
  payment_reference: string;
  user: { id: number; name: string; email: string } | null;
  mobile_wallet: { id: number; name: string; code: string } | null;
  total_usd: string;
  status: OrderStatus;
  payment_confirmed_at: string | null;
  created_at: string;
}

const { listOrders, confirmPayment } = useAdminOrderHelper();
const toast = useToast();

const tabs: { label: string; value: '' | OrderStatus }[] = [
  { label: 'Awaiting payment', value: 'PENDING_PAYMENT' },
  { label: 'Paid', value: 'PAID' },
  { label: 'Out for delivery', value: 'OUT_FOR_DELIVERY' },
  { label: 'Delivered', value: 'DELIVERED' },
  { label: 'All', value: '' },
];

const loading = ref(false);
const submitting = ref<number | null>(null);
const filterStatus = ref<'' | OrderStatus>('PENDING_PAYMENT');
const rows = ref<OrderRow[]>([]);

const fetchAll = async () => {
  loading.value = true;
  const { data, error } = await listOrders(filterStatus.value || undefined);
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

const setFilter = async (v: '' | OrderStatus) => {
  filterStatus.value = v;
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
</script>
