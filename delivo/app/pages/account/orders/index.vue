<template>
  <section class="mx-auto max-w-7xl px-4 py-10">
    <h1 class="text-3xl font-extrabold tracking-tight md:text-4xl">My orders</h1>
    <p class="mt-1 text-sm opacity-70">Your full order history with Delivo.</p>

    <div v-if="store.loading" class="flex justify-center py-16">
      <span class="loading loading-spinner loading-lg"></span>
    </div>

    <div v-else-if="!store.orders.length" class="mt-8 rounded-3xl border border-dashed border-base-300 p-12 text-center">
      <p class="text-sm opacity-70">You haven't placed any orders yet.</p>
      <NuxtLink to="/products" class="btn btn-primary mt-4 rounded-full">Start shopping</NuxtLink>
    </div>

    <div v-else class="mt-8 overflow-hidden rounded-3xl border border-base-300 bg-base-100">
      <table class="table">
        <thead class="bg-base-200/50 text-xs uppercase tracking-wider opacity-70">
          <tr>
            <th>Order #</th>
            <th>Placed</th>
            <th>Items</th>
            <th>Total</th>
            <th>Status</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="o in store.orders" :key="o.id">
            <td class="font-mono font-semibold">{{ o.order_number }}</td>
            <td class="text-sm opacity-70">{{ o.created_at?.slice(0, 10) }}</td>
            <td>{{ (o.items ?? []).reduce((s, i) => s + i.quantity, 0) }}</td>
            <td class="font-semibold">{{ currency.format(o.total_usd) }}</td>
            <td><span :class="['badge badge-sm', statusBadge(o.status)]">{{ statusLabel(o.status) }}</span></td>
            <td class="text-right">
              <NuxtLink :to="`/account/orders/${o.order_number}`" class="btn btn-ghost btn-xs rounded-full">
                View →
              </NuxtLink>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </section>
</template>

<script setup lang="ts">
import type { OrderStatus } from '~/stores/order';

definePageMeta({ middleware: ['auth'] });
useHead({ title: 'My orders — Delivo' });

const store = useOrderStore();
const currency = useCurrencyStore();

onMounted(() => store.fetchAll());

const statusLabel = (s: OrderStatus): string => ({
  PENDING_PAYMENT: 'Awaiting payment',
  PAID: 'Paid',
  PICKED_UP: 'Picked up',
  OUT_FOR_DELIVERY: 'Out for delivery',
  DELIVERED: 'Delivered',
  COMPLETED: 'Completed',
  CANCELLED: 'Cancelled',
  REFUNDED: 'Refunded',
}[s]);

const statusBadge = (s: OrderStatus): string => ({
  PENDING_PAYMENT: 'badge-warning',
  PAID: 'badge-info',
  PICKED_UP: 'badge-info',
  OUT_FOR_DELIVERY: 'badge-info',
  DELIVERED: 'badge-success',
  COMPLETED: 'badge-success',
  CANCELLED: 'badge-error',
  REFUNDED: 'badge-ghost',
}[s]);
</script>
