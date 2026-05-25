<template>
  <div class="mx-auto max-w-7xl px-4 py-10">
    <div class="flex flex-wrap items-end justify-between gap-3">
      <div>
        <h1 class="text-3xl font-extrabold tracking-tight md:text-4xl">Earnings</h1>
        <p class="mt-1 text-sm opacity-70">
          Pending shows commissions on paid orders awaiting delivery. Cleared is what you can
          withdraw — once a customer confirms delivery, your commission moves to Cleared.
        </p>
      </div>
      <div class="flex items-center gap-2">
        <NuxtLink to="/influencer" class="btn btn-ghost rounded-full">
          <Icon name="lucide:layout-dashboard" class="h-4 w-4" /> Dashboard
        </NuxtLink>
        <NuxtLink to="/influencer/payouts" class="btn btn-primary rounded-full">
          <Icon name="lucide:wallet" class="h-4 w-4" /> Payouts
        </NuxtLink>
      </div>
    </div>

    <div v-if="loading" class="flex justify-center py-16">
      <span class="loading loading-spinner loading-lg"></span>
    </div>

    <div v-else>
      <div class="mt-6 grid gap-4 md:grid-cols-3">
        <div class="rounded-3xl border border-base-300 bg-base-100 p-5">
          <div class="text-xs uppercase tracking-wider opacity-60">Pending</div>
          <div class="mt-2 text-2xl font-bold">{{ format(summary.pending_usd) }}</div>
          <div class="mt-1 text-xs opacity-60">Awaiting customer delivery confirmation.</div>
        </div>
        <div class="rounded-3xl border border-success/40 bg-success/5 p-5">
          <div class="text-xs uppercase tracking-wider text-success">Cleared</div>
          <div class="mt-2 text-2xl font-bold text-success">{{ format(summary.cleared_usd) }}</div>
          <div class="mt-1 text-xs opacity-70">
            Ready to withdraw — minimum {{ format(summary.min_payout_usd) }} per payout.
          </div>
        </div>
        <div class="rounded-3xl border border-base-300 bg-base-100 p-5">
          <div class="text-xs uppercase tracking-wider opacity-60">Paid out</div>
          <div class="mt-2 text-2xl font-bold">{{ format(summary.paid_usd) }}</div>
          <div class="mt-1 text-xs opacity-60">Lifetime payouts received.</div>
        </div>
      </div>

      <div class="mt-3 rounded-2xl bg-base-200/40 px-4 py-2 text-xs opacity-70">
        Delivo deducts a {{ summary.service_fee_pct }}% service fee from each payout.
      </div>

      <h2 class="mt-8 text-lg font-bold">Ledger</h2>
      <p class="mt-1 text-xs opacity-60">One row per order-item commission.</p>

      <div v-if="!entries.length" class="mt-4 rounded-3xl border border-dashed border-base-300 p-12 text-center text-sm opacity-70">
        No commissions yet. When customers redeem your code and pay, you'll see entries here.
      </div>

      <div v-else class="mt-4 overflow-hidden rounded-3xl border border-base-300 bg-base-100">
        <table class="table">
          <thead class="bg-base-200/50 text-xs uppercase tracking-wider opacity-70">
            <tr>
              <th>Order</th>
              <th>Item</th>
              <th class="text-right">Amount</th>
              <th>Status</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="e in entries" :key="e.id">
              <td>
                <NuxtLink v-if="e.order?.order_number" :to="`/account/orders/${e.order.order_number}`" class="link link-hover font-mono text-xs">
                  {{ e.order.order_number }}
                </NuxtLink>
                <span v-else class="font-mono text-xs opacity-60">—</span>
              </td>
              <td class="text-sm">
                {{ e.order_item?.product_name_snapshot ?? '—' }}
                <span v-if="e.order_item?.quantity" class="text-xs opacity-60"> × {{ e.order_item.quantity }}</span>
              </td>
              <td class="text-right font-semibold">{{ format(e.amount_usd) }}</td>
              <td>
                <span :class="['badge badge-sm', statusBadge(e.status)]">{{ e.status }}</span>
              </td>
              <td class="text-xs opacity-60">{{ e.created_at?.slice(0, 10) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ middleware: ['auth'] });
useHead({ title: 'Earnings — Delivo' });

interface SummaryRow {
  pending_usd: number;
  cleared_usd: number;
  paid_usd: number;
  min_payout_usd: number;
  service_fee_pct: number;
}

interface LedgerRow {
  id: number;
  amount_usd: string;
  status: 'PENDING' | 'CLEARED' | 'PAID';
  cleared_at: string | null;
  paid_at: string | null;
  created_at: string;
  order: { id: number; order_number: string; delivered_at: string | null } | null;
  order_item: { id: number; product_name_snapshot: string; quantity: number } | null;
}

const { earningsSummary, listEarnings } = useInfluencerHelper();
const toast = useToast();

const loading = ref(false);
const summary = ref<SummaryRow>({
  pending_usd: 0,
  cleared_usd: 0,
  paid_usd: 0,
  min_payout_usd: 10,
  service_fee_pct: 5,
});
const entries = ref<LedgerRow[]>([]);

const format = (v: string | number) => `$${Number(v).toFixed(2)}`;

const fetchAll = async () => {
  loading.value = true;
  const [{ data: s, error: se }, { data: l }] = await Promise.all([earningsSummary(), listEarnings()]);
  if (!se.value) {
    summary.value = ((s.value as any)?.data ?? summary.value) as SummaryRow;
  } else {
    const msg = (se.value as any)?.data?.message || 'Failed to load earnings.';
    toast.error({ title: 'Error', message: msg, position: 'topRight', layout: 2 });
  }
  entries.value = ((l.value as any)?.data ?? []) as LedgerRow[];
  loading.value = false;
};

onMounted(fetchAll);

const statusBadge = (s: string) => ({
  PENDING: 'badge-warning',
  CLEARED: 'badge-success',
  PAID: 'badge-info',
}[s] ?? 'badge-ghost');
</script>
