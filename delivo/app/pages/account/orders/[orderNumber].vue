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
        <span :class="['badge badge-lg', statusBadge]">{{ statusLabel }}</span>
      </div>

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
        <dl class="mt-4 space-y-2 border-t border-base-300 pt-4 text-sm">
          <div class="flex justify-between"><dt>Subtotal</dt><dd>{{ currency.format(store.current.subtotal_usd) }}</dd></div>
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
</script>
