<template>
  <div class="mx-auto max-w-7xl px-4 py-10">
    <div class="flex flex-wrap items-end justify-between gap-3">
      <div>
        <h1 class="text-3xl font-extrabold tracking-tight md:text-4xl">Provider dashboard</h1>
        <p class="mt-1 text-sm opacity-70">
          Shipments assigned to your delivery operation. Pick them up, dispatch and mark delivered as
          you complete each leg.
        </p>
      </div>
      <NuxtLink to="/providers/apply" class="btn btn-ghost rounded-full">
        <Icon name="lucide:settings-2" class="h-4 w-4" />
        Coverage + KYC
      </NuxtLink>
    </div>

    <div class="mt-6 flex flex-wrap gap-2">
      <button
        v-for="tab in tabs"
        :key="tab.value ?? 'all'"
        :class="['btn btn-sm rounded-full', store.filterStatus === tab.value ? 'btn-primary' : 'btn-ghost bg-base-100']"
        @click="store.fetchAll(tab.value ?? undefined)"
      >
        {{ tab.label }}
        <span v-if="tab.value && store.counts[tab.value]" class="badge badge-sm ml-1">
          {{ store.counts[tab.value] }}
        </span>
      </button>
    </div>

    <div v-if="store.loading" class="flex justify-center py-16">
      <span class="loading loading-spinner loading-lg"></span>
    </div>

    <div v-else-if="!store.shipments.length" class="mt-6 rounded-3xl border border-dashed border-base-300 p-12 text-center">
      <Icon name="lucide:truck" class="mx-auto h-10 w-10 opacity-30" />
      <p class="mt-3 text-sm opacity-70">Nothing in this queue.</p>
      <p class="mt-1 text-xs opacity-60">Orders are matched to providers whose coverage spans both the pickup and delivery cities.</p>
    </div>

    <div v-else class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
      <article
        v-for="s in store.shipments"
        :key="s.id"
        class="flex flex-col rounded-3xl border border-base-300 bg-base-100 p-5"
      >
        <div class="flex items-start justify-between gap-3">
          <div>
            <div class="text-xs font-medium uppercase tracking-wider text-primary/80">
              {{ s.order?.order_number ?? '—' }}
            </div>
            <h3 class="mt-1 font-bold">{{ s.vendor?.business_name ?? 'Vendor' }}</h3>
          </div>
          <span :class="['badge badge-sm', badgeFor(s.shipment_status)]">{{ labelFor(s.shipment_status) }}</span>
        </div>

        <dl class="mt-4 grid gap-1 text-sm">
          <div class="flex items-baseline justify-between gap-2">
            <dt class="opacity-60">Pickup</dt>
            <dd class="text-right">{{ s.hub?.hub_name ?? s.hub?.city ?? '—' }}</dd>
          </div>
          <div class="flex items-baseline justify-between gap-2">
            <dt class="opacity-60">Drop off</dt>
            <dd class="text-right">
              {{ s.order?.ship_city ?? '—' }}<span v-if="s.order?.ship_suburb">, {{ s.order?.ship_suburb }}</span>
            </dd>
          </div>
          <div class="flex items-baseline justify-between gap-2">
            <dt class="opacity-60">Distance</dt>
            <dd>{{ s.distance_km !== null ? `${s.distance_km} km` : '—' }}</dd>
          </div>
          <div class="flex items-baseline justify-between gap-2">
            <dt class="opacity-60">Fee</dt>
            <dd class="font-semibold text-primary">${{ Number(s.fee_usd).toFixed(2) }}</dd>
          </div>
        </dl>

        <div v-if="s.order?.ship_notes" class="mt-3 rounded-2xl bg-base-200/40 p-3 text-xs opacity-80">
          <Icon name="lucide:sticky-note" class="mr-1 inline h-3 w-3" />
          {{ s.order.ship_notes }}
        </div>

        <div class="mt-4 flex flex-wrap items-center justify-between gap-2">
          <div class="text-xs opacity-60">
            <span v-if="s.assigned_at">Assigned {{ s.assigned_at.slice(0, 10) }}</span>
          </div>
          <div class="flex gap-2">
            <button
              v-if="s.shipment_status === 'ASSIGNED'"
              class="btn btn-primary btn-sm rounded-full"
              :disabled="store.submitting"
              @click="store.doTransition(s.id, 'pickup')"
            >
              <Icon name="lucide:hand-coins" class="h-3.5 w-3.5" /> Pick up
            </button>
            <button
              v-if="s.shipment_status === 'PICKED_UP'"
              class="btn btn-primary btn-sm rounded-full"
              :disabled="store.submitting"
              @click="store.doTransition(s.id, 'dispatch')"
            >
              <Icon name="lucide:send" class="h-3.5 w-3.5" /> Dispatch
            </button>
            <button
              v-if="s.shipment_status === 'OUT_FOR_DELIVERY'"
              class="btn btn-success btn-sm rounded-full"
              :disabled="store.submitting"
              @click="onDeliver(s.id)"
            >
              <Icon name="lucide:check" class="h-3.5 w-3.5" /> Delivered
            </button>
            <span v-if="s.shipment_status === 'DELIVERED'" class="text-xs opacity-60">
              Delivered {{ s.delivered_at?.slice(0, 10) }}
            </span>
          </div>
        </div>
      </article>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { ShipmentStatus } from '~/stores/providerShipment';

definePageMeta({ middleware: ['auth'] });
useHead({ title: 'Provider dashboard — Delivo' });

const store = useProviderShipmentStore();

const tabs: { label: string; value: ShipmentStatus | null }[] = [
  { label: 'All', value: null },
  { label: 'Assigned', value: 'ASSIGNED' },
  { label: 'Picked up', value: 'PICKED_UP' },
  { label: 'Out for delivery', value: 'OUT_FOR_DELIVERY' },
  { label: 'Delivered', value: 'DELIVERED' },
];

onMounted(() => store.fetchAll());

const onDeliver = async (id: number) => {
  const code = window.prompt('Ask the customer to read their delivery code. Enter it exactly:');
  if (!code || !code.trim()) return;
  await store.doTransition(id, 'deliver', { code: code.trim() });
};

const labelFor = (s: ShipmentStatus): string => ({
  AWAITING_PROVIDER: 'Awaiting provider',
  ASSIGNED: 'Assigned',
  PICKED_UP: 'Picked up',
  OUT_FOR_DELIVERY: 'Out for delivery',
  DELIVERED: 'Delivered',
  CANCELLED: 'Cancelled',
}[s]);

const badgeFor = (s: ShipmentStatus): string => ({
  AWAITING_PROVIDER: 'badge-ghost',
  ASSIGNED: 'badge-warning',
  PICKED_UP: 'badge-info',
  OUT_FOR_DELIVERY: 'badge-info',
  DELIVERED: 'badge-success',
  CANCELLED: 'badge-error',
}[s]);
</script>
