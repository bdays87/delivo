<template>
  <div>
    <div class="flex flex-wrap items-end justify-between gap-3">
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight">Orders</h1>
        <p class="mt-1 text-sm opacity-70">
          Orders that contain your products. Customer name + delivery city are shown for fulfillment;
          phone and address details stay private.
        </p>
      </div>
      <span v-if="summary.pending_payment_count > 0" class="badge badge-lg badge-warning gap-2">
        <Icon name="lucide:clock" class="h-3.5 w-3.5" />
        {{ summary.pending_payment_count }} awaiting payment
      </span>
    </div>

    <div class="mt-6 grid gap-4 md:grid-cols-4">
      <div class="rounded-2xl bg-base-100 p-4">
        <div class="text-xs uppercase tracking-wider opacity-60">Confirmed orders</div>
        <div class="mt-1 text-xl font-bold">{{ summary.paid_count }}</div>
      </div>
      <div class="rounded-2xl bg-base-100 p-4">
        <div class="text-xs uppercase tracking-wider opacity-60">Delivered</div>
        <div class="mt-1 text-xl font-bold">{{ summary.delivered_count }}</div>
      </div>
      <div class="rounded-2xl bg-base-100 p-4">
        <div class="text-xs uppercase tracking-wider opacity-60">Gross revenue</div>
        <div class="mt-1 text-xl font-bold">${{ Number(summary.gross_revenue_usd).toFixed(2) }}</div>
      </div>
      <div class="rounded-2xl bg-success/5 p-4">
        <div class="text-xs uppercase tracking-wider text-success">Your net (after commission)</div>
        <div class="mt-1 text-xl font-bold text-success">${{ Number(summary.net_after_commission_usd).toFixed(2) }}</div>
      </div>
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
            <th>Product</th>
            <th>Qty</th>
            <th class="text-right">Gross</th>
            <th>Influencer</th>
            <th class="text-right">Your net</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="r in rows" :key="r.id">
            <td>
              <div class="font-mono text-xs font-semibold">{{ r.order?.order_number ?? '—' }}</div>
              <div class="text-xs opacity-60">{{ r.order?.created_at?.slice(0, 10) }}</div>
            </td>
            <td>
              <div class="text-sm">{{ r.order?.customer_name ?? '—' }}</div>
              <div class="text-xs opacity-60">{{ r.order?.ship_suburb }}, {{ r.order?.ship_city }}</div>
            </td>
            <td>
              <div class="text-sm font-medium">{{ r.product?.name ?? '—' }}</div>
              <div v-if="r.variant?.color" class="text-xs opacity-60">{{ r.variant.color }}</div>
              <div v-if="r.variant?.sku" class="text-xs opacity-60 font-mono">{{ r.variant.sku }}</div>
            </td>
            <td>{{ r.quantity }}</td>
            <td class="text-right">${{ Number(r.line_total_usd).toFixed(2) }}</td>
            <td>
              <div v-if="r.influencer" class="text-xs">
                <div class="font-semibold">{{ r.influencer.display_name }}</div>
                <div class="opacity-60">− ${{ Number(r.influencer_commission_usd).toFixed(2) }}</div>
              </div>
              <span v-else class="text-xs opacity-40">—</span>
            </td>
            <td class="text-right font-semibold text-success">${{ Number(r.vendor_net_usd).toFixed(2) }}</td>
            <td>
              <span :class="['badge badge-sm', statusBadge(r.order?.status)]">{{ statusLabel(r.order?.status) }}</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ layout: 'vendor', middleware: ['auth'] });
useHead({ title: 'Orders — Delivo Vendor' });

type OrderStatus = 'PENDING_PAYMENT' | 'PAID' | 'PICKED_UP' | 'OUT_FOR_DELIVERY' | 'DELIVERED' | 'COMPLETED' | 'CANCELLED' | 'REFUNDED';

interface OrderRow {
  id: number;
  order: {
    id: number;
    order_number: string;
    status: OrderStatus;
    ship_city: string;
    ship_suburb: string;
    customer_name: string | null;
    payment_confirmed_at: string | null;
    delivered_at: string | null;
    created_at: string;
  } | null;
  product: { id: number; name: string; slug: string } | null;
  variant: { id: number; color: string | null; sku: string | null } | null;
  quantity: number;
  unit_price_usd: string;
  line_total_usd: string;
  influencer: { id: number; display_name: string; slug: string } | null;
  influencer_commission_usd: string;
  vendor_net_usd: string;
}

interface SummaryRow {
  pending_payment_count: number;
  paid_count: number;
  delivered_count: number;
  gross_revenue_usd: string;
  influencer_commission_paid_usd: string;
  net_after_commission_usd: string;
}

const { listOrders, ordersSummary } = useVendorOrderHelper();
const toast = useToast();

const tabs: { label: string; value: '' | OrderStatus }[] = [
  { label: 'All confirmed', value: '' },
  { label: 'Paid', value: 'PAID' },
  { label: 'Out for delivery', value: 'OUT_FOR_DELIVERY' },
  { label: 'Delivered', value: 'DELIVERED' },
];

const loading = ref(false);
const filterStatus = ref<'' | OrderStatus>('');
const rows = ref<OrderRow[]>([]);
const summary = ref<SummaryRow>({
  pending_payment_count: 0,
  paid_count: 0,
  delivered_count: 0,
  gross_revenue_usd: '0.00',
  influencer_commission_paid_usd: '0.00',
  net_after_commission_usd: '0.00',
});

const fetchAll = async () => {
  loading.value = true;
  const [{ data: list, error }, { data: sum }] = await Promise.all([
    listOrders(filterStatus.value || undefined),
    ordersSummary(),
  ]);
  if (!error.value) {
    rows.value = ((list.value as any)?.data ?? []) as OrderRow[];
  } else {
    toast.error({ title: 'Error', message: (error.value as any)?.data?.message || 'Failed to load orders.', position: 'topRight', layout: 2 });
  }
  summary.value = ((sum.value as any)?.data ?? summary.value) as SummaryRow;
  loading.value = false;
};

onMounted(fetchAll);

const setFilter = async (v: '' | OrderStatus) => {
  filterStatus.value = v;
  await fetchAll();
};

const statusLabel = (s?: OrderStatus | null) => ({
  PAID: 'Paid',
  PICKED_UP: 'Picked up',
  OUT_FOR_DELIVERY: 'Out for delivery',
  DELIVERED: 'Delivered',
  COMPLETED: 'Completed',
  PENDING_PAYMENT: 'Awaiting payment',
  CANCELLED: 'Cancelled',
  REFUNDED: 'Refunded',
}[s as OrderStatus] ?? '—');

const statusBadge = (s?: OrderStatus | null) => ({
  PAID: 'badge-info',
  PICKED_UP: 'badge-info',
  OUT_FOR_DELIVERY: 'badge-info',
  DELIVERED: 'badge-success',
  COMPLETED: 'badge-success',
  PENDING_PAYMENT: 'badge-warning',
  CANCELLED: 'badge-error',
  REFUNDED: 'badge-ghost',
}[s as OrderStatus] ?? 'badge-ghost');
</script>
