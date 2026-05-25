<template>
  <section class="mx-auto max-w-4xl px-4 py-10">
    <div v-if="store.loading && !store.current" class="flex justify-center py-16">
      <span class="loading loading-spinner loading-lg"></span>
    </div>

    <div v-else-if="!store.current" class="rounded-3xl border border-base-300 bg-base-100 p-8 text-center">
      <p>Order not found.</p>
      <NuxtLink to="/account/orders" class="btn btn-primary mt-4 rounded-full">All orders</NuxtLink>
    </div>

    <div v-else class="space-y-6">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
          <NuxtLink to="/account/orders" class="link link-hover text-xs opacity-70">← All orders</NuxtLink>
          <h1 class="mt-1 text-3xl font-extrabold tracking-tight">{{ store.current.order_number }}</h1>
          <p class="text-sm opacity-70">Placed {{ store.current.created_at?.slice(0, 10) }}</p>
        </div>
        <div class="flex flex-col items-end gap-2">
          <span :class="['badge badge-lg', statusBadge]">{{ statusLabel }}</span>
          <span :class="['badge', deliveryBadge]">{{ deliveryLabel }}</span>
        </div>
      </div>

      <section v-if="store.current.status !== 'PENDING_PAYMENT' && store.current.status !== 'CANCELLED'" class="rounded-3xl border border-base-300 bg-base-100 p-6">
        <h2 class="text-sm font-semibold uppercase tracking-wider opacity-70">Delivery progress</h2>
        <ol class="mt-5 grid gap-3 md:grid-cols-5">
          <li
            v-for="(step, idx) in deliverySteps"
            :key="step.key"
            :class="[
              'rounded-2xl border-2 p-3 text-xs transition',
              step.state === 'done' ? 'border-success/40 bg-success/5' : '',
              step.state === 'active' ? 'border-primary bg-primary/5' : '',
              step.state === 'todo' ? 'border-base-300 opacity-50' : '',
            ]"
          >
            <div class="flex items-center gap-2">
              <span
                :class="[
                  'grid h-7 w-7 shrink-0 place-items-center rounded-full text-xs font-bold',
                  step.state === 'done' ? 'bg-success text-success-content' : '',
                  step.state === 'active' ? 'bg-primary text-primary-content' : '',
                  step.state === 'todo' ? 'bg-base-300 text-base-content/50' : '',
                ]"
              >
                <Icon v-if="step.state === 'done'" name="lucide:check" class="h-4 w-4" />
                <span v-else>{{ idx + 1 }}</span>
              </span>
              <span class="text-xs font-semibold leading-tight">{{ step.label }}</span>
            </div>
            <div v-if="step.at" class="mt-1 text-[10px] opacity-70">{{ formatStep(step.at) }}</div>
          </li>
        </ol>
      </section>

      <section
        v-if="store.current.status === 'PENDING_PAYMENT'"
        class="rounded-3xl border border-warning/40 bg-warning/5 p-5 text-sm"
      >
        <div class="flex items-start gap-3">
          <Icon name="lucide:clock" class="h-5 w-5 text-warning" />
          <div>
            <div class="font-semibold">Awaiting payment</div>
            <div class="opacity-80">
              Send {{ currency.format(store.current.total_usd) }} via
              {{ store.current.mobile_wallet?.name ?? 'mobile wallet' }} using reference
              <span class="font-mono font-semibold">{{ store.current.payment_reference }}</span>.
              <NuxtLink :to="`/checkout/instructions/${store.current.order_number}`" class="link link-primary">
                See full instructions →
              </NuxtLink>
            </div>
          </div>
        </div>
      </section>

      <section
        v-if="showHandoverCode"
        class="rounded-3xl border border-info/40 bg-info/5 p-5 text-sm"
      >
        <div class="flex items-start gap-3">
          <Icon name="lucide:key-round" class="h-5 w-5 text-info" />
          <div class="flex-1">
            <div class="font-semibold">{{ handoverHeading }}</div>
            <p class="mt-1 opacity-80">{{ handoverBlurb }}</p>
            <div v-if="store.current.delivery_code" class="mt-3 inline-flex items-center gap-3 rounded-2xl border-2 border-info bg-base-100 px-5 py-3">
              <span class="text-xs uppercase tracking-wider opacity-60">Your code</span>
              <span class="font-mono text-3xl font-extrabold tracking-widest">{{ store.current.delivery_code }}</span>
            </div>
            <p class="mt-3 text-xs opacity-60">
              Do not share this code online or with anyone else — only the
              {{ store.current.delivery_method === 'SELF_PICKUP' ? 'vendor handing your parcel over' : 'rider delivering to you' }}
              should hear it.
            </p>
          </div>
        </div>
      </section>

      <section class="rounded-3xl border border-base-300 bg-base-100 p-6">
        <h2 class="text-sm font-semibold uppercase tracking-wider opacity-70">Items</h2>
        <ul class="mt-3 divide-y divide-base-300">
          <li v-for="i in store.current.items ?? []" :key="i.id" class="flex items-center justify-between gap-3 py-3 text-sm">
            <div>
              <div class="font-semibold">{{ i.product_name_snapshot }}</div>
              <div class="text-xs opacity-60">
                {{ i.color_snapshot ? `${i.color_snapshot} · ` : '' }}{{ i.quantity }} × {{ currency.format(i.unit_price_usd_snapshot) }}
              </div>
            </div>
            <div class="font-semibold">{{ currency.format(i.line_total_usd_snapshot) }}</div>
          </li>
        </ul>
        <div
          v-if="store.current.applied_coupon_code"
          class="mt-4 flex items-center justify-between gap-3 rounded-2xl border border-success/40 bg-success/5 p-3 text-xs"
        >
          <div>
            <div class="uppercase tracking-wider text-success">Code applied</div>
            <div class="font-mono font-semibold">{{ store.current.applied_coupon_code }}</div>
          </div>
          <div v-if="Number(store.current.total_buyer_discount_usd) > 0" class="text-right font-semibold text-success">
            − {{ currency.format(store.current.total_buyer_discount_usd) }}
          </div>
        </div>

        <dl class="mt-4 space-y-2 border-t border-base-300 pt-4 text-sm">
          <div class="flex justify-between">
            <dt>{{ Number(store.current.total_buyer_discount_usd) > 0 ? 'Subtotal (after code)' : 'Subtotal' }}</dt>
            <dd>{{ currency.format(store.current.subtotal_usd) }}</dd>
          </div>
          <div v-if="Number(store.current.total_buyer_discount_usd) > 0" class="flex justify-between text-success">
            <dt>You saved</dt>
            <dd>− {{ currency.format(store.current.total_buyer_discount_usd) }}</dd>
          </div>
          <div class="flex justify-between"><dt>Service charge</dt><dd>{{ currency.format(store.current.service_charge_usd) }}</dd></div>
          <div class="flex justify-between"><dt>Delivery</dt><dd>{{ currency.format(store.current.shipping_usd) }}</dd></div>
          <div class="flex justify-between border-t border-base-300 pt-2 font-bold">
            <dt>Total</dt><dd class="text-primary">{{ currency.format(store.current.total_usd) }}</dd>
          </div>
        </dl>
      </section>

      <section class="rounded-3xl border border-base-300 bg-base-100 p-6">
        <h2 class="text-sm font-semibold uppercase tracking-wider opacity-70">Delivery</h2>
        <div class="mt-2 text-sm">
          <div class="font-semibold">{{ store.current.ship_recipient_name }}</div>
          <div class="opacity-80">{{ store.current.ship_street }}, {{ store.current.ship_suburb }}, {{ store.current.ship_city }}</div>
          <div class="opacity-60">{{ store.current.ship_recipient_phone }}</div>
          <div v-if="store.current.ship_notes" class="mt-1 opacity-60">{{ store.current.ship_notes }}</div>
        </div>
      </section>

      <section v-if="(store.current as any).shipments?.length" class="rounded-3xl border border-base-300 bg-base-100 p-6">
        <h2 class="text-sm font-semibold uppercase tracking-wider opacity-70">Shipments</h2>
        <ul class="mt-3 divide-y divide-base-300">
          <li v-for="s in (store.current as any).shipments" :key="s.id" class="flex items-center justify-between gap-3 py-3 text-sm">
            <div>
              <div class="font-semibold">{{ s.vendor?.business_name ?? 'Shipment' }}</div>
              <div class="text-xs opacity-60">
                <span v-if="s.hub_name_snapshot">From {{ s.hub_name_snapshot }} · </span>
                {{ s.distance_km !== null ? `${s.distance_km} km` : '' }}
                <span v-if="s.provider"> · Carrier: {{ s.provider.business_name }}</span>
              </div>
            </div>
            <span :class="['badge badge-sm', shipmentBadge(s.shipment_status)]">
              {{ shipmentLabel(s.shipment_status) }}
            </span>
          </li>
        </ul>
      </section>
    </div>
  </section>
</template>

<script setup lang="ts">
import type { OrderStatus } from '~/stores/order';

definePageMeta({ middleware: ['auth'] });

const route = useRoute();
const store = useOrderStore();
const currency = useCurrencyStore();

const orderNumber = computed(() => route.params.orderNumber as string);

// Show the handover code only between payment and customer-confirmed receipt.
const showHandoverCode = computed(() =>
  store.current
  && store.current.status === 'PAID'
  && store.current.customer_delivery_confirmed_at === null,
);

const handoverHeading = computed(() =>
  store.current?.delivery_method === 'SELF_PICKUP'
    ? 'Collecting from the vendor'
    : 'Your delivery code',
);

const handoverBlurb = computed(() =>
  store.current?.delivery_method === 'SELF_PICKUP'
    ? 'Read this code to the vendor when you collect your parcel — they will enter it to confirm handover.'
    : 'When your rider arrives with your parcel, read this code to them — they will enter it to confirm delivery.',
);

onMounted(() => store.fetchOne(orderNumber.value));

useHead({ title: () => `Order ${orderNumber.value} — Delivo` });

const statusLabel = computed(() => ({
  PENDING_PAYMENT: 'Awaiting payment',
  PAID: 'Paid',
  PICKED_UP: 'Picked up',
  OUT_FOR_DELIVERY: 'Out for delivery',
  DELIVERED: 'Delivered',
  COMPLETED: 'Completed',
  CANCELLED: 'Cancelled',
  REFUNDED: 'Refunded',
}[store.current?.status as OrderStatus] ?? ''));

const statusBadge = computed(() => ({
  PENDING_PAYMENT: 'badge-warning',
  PAID: 'badge-info',
  PICKED_UP: 'badge-info',
  OUT_FOR_DELIVERY: 'badge-info',
  DELIVERED: 'badge-success',
  COMPLETED: 'badge-success',
  CANCELLED: 'badge-error',
  REFUNDED: 'badge-ghost',
}[store.current?.status as OrderStatus] ?? 'badge-ghost'));

const deliveryLabel = computed(() => ({
  PENDING: 'Delivery: pending',
  AWAITING_DROPOFF: 'Delivery: awaiting vendor dropoff',
  DROPOFF_INITIATED: 'Delivery: parcel on its way to hub',
  AWAITING_DISPATCH: 'Delivery: at hub, awaiting dispatch',
  INROUTE: 'Delivery: in route',
  READY_FOR_PICKUP: 'Ready to collect from vendor',
  DELIVERED: 'Delivery: delivered',
}[store.current?.delivery_status as string] ?? ''));

interface DeliveryStep { key: string; label: string; state: 'done' | 'active' | 'todo'; at: string | null }

const minTimestamp = (key: keyof import('~/stores/order').OrderShipment): string | null => {
  const stamps = (store.current?.shipments ?? [])
    .map((s) => s[key])
    .filter((v): v is string => typeof v === 'string' && v.length > 0);
  if (!stamps.length) return null;
  return stamps.reduce((a, b) => (a < b ? a : b));
};

const HOME_DELIVERY_ORDER = ['PENDING', 'AWAITING_DROPOFF', 'DROPOFF_INITIATED', 'AWAITING_DISPATCH', 'INROUTE', 'DELIVERED'] as const;
const SELF_PICKUP_ORDER = ['PENDING', 'READY_FOR_PICKUP', 'DELIVERED'] as const;

const deliverySteps = computed<DeliveryStep[]>(() => {
  const ds = store.current?.delivery_status ?? 'PENDING';

  if (store.current?.delivery_method === 'SELF_PICKUP') {
    const idx = SELF_PICKUP_ORDER.indexOf(ds as typeof SELF_PICKUP_ORDER[number]);
    const rows: { key: typeof SELF_PICKUP_ORDER[number]; label: string; at: string | null }[] = [
      { key: 'READY_FOR_PICKUP', label: 'Order paid', at: store.current?.payment_confirmed_at ?? null },
      { key: 'DELIVERED', label: 'Picked up by you', at: store.current?.customer_delivery_confirmed_at ?? null },
    ];
    return rows.map((r) => {
      const stepIdx = SELF_PICKUP_ORDER.indexOf(r.key);
      let state: DeliveryStep['state'];
      if (idx > stepIdx) state = 'done';
      else if (idx === stepIdx) state = 'active';
      else state = 'todo';
      return { ...r, state };
    });
  }

  const idx = HOME_DELIVERY_ORDER.indexOf(ds as typeof HOME_DELIVERY_ORDER[number]);
  const rows: { key: typeof HOME_DELIVERY_ORDER[number]; label: string; at: string | null }[] = [
    { key: 'AWAITING_DROPOFF', label: 'Order paid', at: store.current?.payment_confirmed_at ?? null },
    { key: 'DROPOFF_INITIATED', label: 'Vendor heading to hub', at: minTimestamp('dropoff_initiated_at') },
    { key: 'AWAITING_DISPATCH', label: 'Received at hub', at: minTimestamp('dropped_off_at') },
    { key: 'INROUTE', label: 'Out for delivery', at: minTimestamp('out_for_delivery_at') },
    { key: 'DELIVERED', label: 'Delivered', at: minTimestamp('delivered_at') },
  ];

  return rows.map((r) => {
    const stepIdx = HOME_DELIVERY_ORDER.indexOf(r.key);
    let state: DeliveryStep['state'];
    if (idx > stepIdx) state = 'done';
    else if (idx === stepIdx) state = 'active';
    else state = 'todo';
    return { ...r, state };
  });
});

const formatStep = (iso: string): string => {
  const d = new Date(iso);
  return d.toLocaleString(undefined, { dateStyle: 'short', timeStyle: 'short' });
};

const deliveryBadge = computed(() => ({
  PENDING: 'badge-ghost',
  AWAITING_DROPOFF: 'badge-warning',
  DROPOFF_INITIATED: 'badge-info',
  AWAITING_DISPATCH: 'badge-info',
  INROUTE: 'badge-info',
  READY_FOR_PICKUP: 'badge-warning',
  DELIVERED: 'badge-success',
}[store.current?.delivery_status as string] ?? 'badge-ghost'));

type ShipmentStatusKey = 'AWAITING_PROVIDER' | 'ASSIGNED' | 'PICKED_UP' | 'OUT_FOR_DELIVERY' | 'DELIVERED' | 'CANCELLED';

const shipmentLabel = (s: string): string => ({
  AWAITING_PROVIDER: 'Awaiting provider',
  ASSIGNED: 'Assigned',
  PICKED_UP: 'Picked up',
  OUT_FOR_DELIVERY: 'Out for delivery',
  DELIVERED: 'Delivered',
  CANCELLED: 'Cancelled',
}[s as ShipmentStatusKey] ?? s);

const shipmentBadge = (s: string): string => ({
  AWAITING_PROVIDER: 'badge-ghost',
  ASSIGNED: 'badge-warning',
  PICKED_UP: 'badge-info',
  OUT_FOR_DELIVERY: 'badge-info',
  DELIVERED: 'badge-success',
  CANCELLED: 'badge-error',
}[s as ShipmentStatusKey] ?? 'badge-ghost');
</script>
