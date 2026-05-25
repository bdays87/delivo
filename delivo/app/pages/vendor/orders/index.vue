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
            <th>Product</th>
            <th>Qty</th>
            <th class="text-right">Gross</th>
            <th>Influencer</th>
            <th class="text-right">Your net</th>
            <th>Status</th>
            <th>Delivery</th>
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
            <td>
              <span :class="['badge badge-sm', deliveryBadge(r.order?.delivery_status)]">{{ deliveryLabel(r.order?.delivery_status) }}</span>
              <div v-if="canInitiate(r)" class="mt-1">
                <button
                  class="btn btn-warning btn-xs rounded-full"
                  @click="openDropoffModal(r)"
                >
                  Initiate dropoff
                </button>
              </div>
              <div v-else-if="r.shipment?.dropoff_initiated_at && !r.shipment?.dropped_off_at" class="mt-1 text-xs opacity-70">
                Awaiting hub receipt
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <dialog ref="dropoffDialog" class="modal">
      <div class="modal-box max-w-md">
        <h3 class="text-lg font-bold">Initiate dropoff</h3>
        <p class="mt-1 text-sm opacity-70">
          Select the hub where you're taking parcel
          <span class="font-mono">{{ activeRow?.order?.order_number }}</span>. Hub staff will inspect
          and confirm receipt on arrival.
        </p>

        <div v-if="!hubs.length" class="mt-4 rounded-2xl border border-warning/40 bg-warning/5 p-3 text-sm">
          No active hubs in your city yet. Contact Delivo support.
        </div>

        <div v-else class="mt-4 grid gap-2">
          <label
            v-for="h in hubs"
            :key="h.id"
            :class="[
              'flex cursor-pointer items-start gap-3 rounded-2xl border-2 p-3 transition',
              selectedHubId === h.id ? 'border-primary bg-primary/5' : 'border-base-300 hover:border-primary/50',
            ]"
          >
            <input v-model="selectedHubId" type="radio" :value="h.id" class="radio radio-primary mt-1" />
            <div class="min-w-0">
              <div class="font-semibold">{{ h.name ?? h.city }}</div>
              <div v-if="h.address" class="text-xs opacity-70">{{ h.address }}</div>
            </div>
          </label>
        </div>

        <div class="modal-action">
          <button class="btn rounded-full" @click="closeDropoffModal">Cancel</button>
          <button
            class="btn btn-primary rounded-full"
            :disabled="!selectedHubId || initiating"
            @click="submitDropoff"
          >
            <span v-if="initiating" class="loading loading-spinner loading-xs"></span>
            Confirm I'm dropping off
          </button>
        </div>
      </div>
      <form method="dialog" class="modal-backdrop"><button>close</button></form>
    </dialog>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ layout: 'vendor', middleware: ['auth'] });
useHead({ title: 'Orders — Delivo Vendor' });

type OrderStatus = 'PENDING_PAYMENT' | 'PAID' | 'PICKED_UP' | 'OUT_FOR_DELIVERY' | 'DELIVERED' | 'COMPLETED' | 'CANCELLED' | 'REFUNDED';
type DeliveryStatus = 'PENDING' | 'AWAITING_DROPOFF' | 'DROPOFF_INITIATED' | 'AWAITING_DISPATCH' | 'INROUTE' | 'DELIVERED';

interface OrderRow {
  id: number;
  order: {
    id: number;
    order_number: string;
    status: OrderStatus;
    delivery_status: DeliveryStatus;
    ship_city: string;
    ship_suburb: string;
    customer_name: string | null;
    payment_confirmed_at: string | null;
    delivered_at: string | null;
    created_at: string;
  } | null;
  shipment: {
    id: number;
    hub_id: number | null;
    hub_name: string | null;
    hub_address: string | null;
    dropoff_initiated_at: string | null;
    dropped_off_at: string | null;
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

const { listOrders, ordersSummary, listDropoffHubs, initiateDropoff } = useVendorOrderHelper();
const toast = useToast();

interface DropoffHub { id: number; city: string; name: string | null; address: string | null }

const dropoffDialog = ref<HTMLDialogElement | null>(null);
const activeRow = ref<OrderRow | null>(null);
const hubs = ref<DropoffHub[]>([]);
const selectedHubId = ref<number | null>(null);
const initiating = ref(false);

const canInitiate = (r: OrderRow) =>
  r.order?.status === 'PAID'
  && r.order?.delivery_status === 'AWAITING_DROPOFF'
  && r.shipment !== null
  && r.shipment.dropoff_initiated_at === null;

const openDropoffModal = async (r: OrderRow) => {
  activeRow.value = r;
  selectedHubId.value = r.shipment?.hub_id ?? null;
  if (!hubs.value.length) {
    const { data, error } = await listDropoffHubs();
    if (!error.value) {
      hubs.value = ((data.value as any)?.data ?? []) as DropoffHub[];
    }
  }
  dropoffDialog.value?.showModal();
};

const closeDropoffModal = () => {
  dropoffDialog.value?.close();
  activeRow.value = null;
  selectedHubId.value = null;
};

const submitDropoff = async () => {
  if (!activeRow.value?.shipment || !selectedHubId.value) return;
  initiating.value = true;
  const { status, error } = await initiateDropoff(activeRow.value.shipment.id, selectedHubId.value);
  if (status?.value) {
    toast.success({ title: 'Dropoff initiated', message: 'Hub staff will confirm receipt.', position: 'topRight', layout: 2 });
    closeDropoffModal();
    await fetchAll();
  } else {
    toast.error({
      title: 'Could not initiate',
      message: (error?.value as any)?.data?.message || 'Try again.',
      position: 'topRight',
      layout: 2,
    });
  }
  initiating.value = false;
};

interface Tab { key: string; label: string; status?: OrderStatus; delivery_status?: DeliveryStatus }
const tabs: Tab[] = [
  { key: 'all', label: 'All' },
  { key: 'awaiting_payment', label: 'Awaiting payment', status: 'PENDING_PAYMENT' },
  { key: 'awaiting_dropoff', label: 'Awaiting dropoff', delivery_status: 'AWAITING_DROPOFF' },
  { key: 'dropoff_initiated', label: 'Dropoff in progress', delivery_status: 'DROPOFF_INITIATED' },
  { key: 'awaiting_dispatch', label: 'Awaiting dispatch', delivery_status: 'AWAITING_DISPATCH' },
  { key: 'inroute', label: 'In route', delivery_status: 'INROUTE' },
  { key: 'delivered', label: 'Delivered', delivery_status: 'DELIVERED' },
];

const loading = ref(false);
const activeKey = ref<string>('all');
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
  const tab = tabs.find((t) => t.key === activeKey.value);
  const [{ data: list, error }, { data: sum }] = await Promise.all([
    listOrders({ status: tab?.status, delivery_status: tab?.delivery_status }),
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

const setFilter = async (key: string) => {
  activeKey.value = key;
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

const deliveryLabel = (s?: DeliveryStatus | null) => ({
  PENDING: 'Pending',
  AWAITING_DROPOFF: 'Awaiting dropoff',
  DROPOFF_INITIATED: 'Dropoff in progress',
  AWAITING_DISPATCH: 'Awaiting dispatch',
  INROUTE: 'In route',
  DELIVERED: 'Delivered',
}[s as DeliveryStatus] ?? '—');

const deliveryBadge = (s?: DeliveryStatus | null) => ({
  PENDING: 'badge-ghost',
  AWAITING_DROPOFF: 'badge-warning',
  DROPOFF_INITIATED: 'badge-info',
  AWAITING_DISPATCH: 'badge-info',
  INROUTE: 'badge-info',
  DELIVERED: 'badge-success',
}[s as DeliveryStatus] ?? 'badge-ghost');
</script>
