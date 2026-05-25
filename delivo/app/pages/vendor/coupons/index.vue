<template>
  <div>
    <div class="flex flex-wrap items-end justify-between gap-3">
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight">Coupons & influencers</h1>
        <p class="mt-1 text-sm opacity-70">
          Codes attached to your products and the influencers pushing them. Each row shows lifetime
          impact — units moved, revenue, and commission you've paid out.
        </p>
      </div>
    </div>

    <div class="mt-6 grid gap-4 md:grid-cols-5">
      <div class="rounded-2xl bg-base-100 p-4">
        <div class="text-xs uppercase tracking-wider opacity-60">Active codes</div>
        <div class="mt-1 text-xl font-bold">{{ summary.coupon_count }}</div>
      </div>
      <div class="rounded-2xl bg-base-100 p-4">
        <div class="text-xs uppercase tracking-wider opacity-60">Influencers</div>
        <div class="mt-1 text-xl font-bold">{{ summary.influencer_count }}</div>
      </div>
      <div class="rounded-2xl bg-base-100 p-4">
        <div class="text-xs uppercase tracking-wider opacity-60">Redemptions</div>
        <div class="mt-1 text-xl font-bold">{{ summary.total_redemptions }}</div>
      </div>
      <div class="rounded-2xl bg-base-100 p-4">
        <div class="text-xs uppercase tracking-wider opacity-60">Revenue via codes</div>
        <div class="mt-1 text-xl font-bold">${{ Number(summary.revenue_via_codes_usd).toFixed(2) }}</div>
      </div>
      <div class="rounded-2xl bg-base-100 p-4">
        <div class="text-xs uppercase tracking-wider opacity-60">Commission paid</div>
        <div class="mt-1 text-xl font-bold text-error">− ${{ Number(summary.commission_paid_usd).toFixed(2) }}</div>
      </div>
    </div>

    <div v-if="loading" class="flex justify-center py-16">
      <span class="loading loading-spinner loading-lg"></span>
    </div>

    <div v-else-if="!rows.length" class="mt-6 rounded-3xl border border-dashed border-base-300 p-12 text-center text-sm opacity-70">
      No influencer codes yet. Once an approved influencer generates a code for one of your products
      with a non-zero commission, you'll see it here.
    </div>

    <div v-else class="mt-6 overflow-hidden rounded-3xl border border-base-300 bg-base-100">
      <table class="table">
        <thead class="bg-base-200/50 text-xs uppercase tracking-wider opacity-70">
          <tr>
            <th>Code</th>
            <th>Product</th>
            <th>Influencer</th>
            <th>Split</th>
            <th class="text-right">Units</th>
            <th class="text-right">Revenue</th>
            <th class="text-right">Commission paid</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="r in rows" :key="r.id">
            <td>
              <div class="font-mono text-sm font-semibold">{{ r.code }}</div>
              <div class="text-xs opacity-60">{{ r.usage_count }} use{{ r.usage_count === 1 ? '' : 's' }}<span v-if="r.usage_limit"> / {{ r.usage_limit }}</span></div>
            </td>
            <td>{{ r.product?.name ?? '—' }}</td>
            <td>
              <div v-if="r.influencer" class="text-sm">
                <div class="font-semibold">{{ r.influencer.display_name }}</div>
                <div class="text-xs opacity-60">{{ r.influencer.contact_email }}</div>
              </div>
              <span v-else class="text-xs opacity-40">—</span>
            </td>
            <td class="text-xs">
              <div>{{ Number(r.buyer_discount_pct).toFixed(2) }}% buyer</div>
              <div class="opacity-60">{{ Number(r.influencer_commission_pct).toFixed(2) }}% influencer</div>
            </td>
            <td class="text-right">{{ r.units_sold }}</td>
            <td class="text-right font-semibold">${{ Number(r.revenue_usd).toFixed(2) }}</td>
            <td class="text-right text-error">− ${{ Number(r.commission_paid_usd).toFixed(2) }}</td>
            <td>
              <span :class="['badge badge-sm', r.status === 'ACTIVE' ? 'badge-success' : 'badge-ghost']">
                {{ r.status }}
              </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ layout: 'vendor', middleware: ['auth'] });
useHead({ title: 'Coupons — Delivo Vendor' });

interface CouponRow {
  id: number;
  code: string;
  status: 'ACTIVE' | 'ARCHIVED';
  buyer_discount_pct: string;
  influencer_commission_pct: string;
  usage_count: number;
  usage_limit: number | null;
  product: { id: number; name: string; slug: string } | null;
  influencer: { id: number; display_name: string; slug: string; contact_email: string } | null;
  units_sold: number;
  revenue_usd: string;
  commission_paid_usd: string;
}

interface SummaryRow {
  coupon_count: number;
  influencer_count: number;
  total_redemptions: number;
  revenue_via_codes_usd: string;
  commission_paid_usd: string;
}

const { listCoupons, couponsSummary } = useVendorOrderHelper();
const toast = useToast();

const loading = ref(false);
const rows = ref<CouponRow[]>([]);
const summary = ref<SummaryRow>({
  coupon_count: 0,
  influencer_count: 0,
  total_redemptions: 0,
  revenue_via_codes_usd: '0.00',
  commission_paid_usd: '0.00',
});

onMounted(async () => {
  loading.value = true;
  const [{ data: list, error }, { data: sum }] = await Promise.all([listCoupons(), couponsSummary()]);
  if (!error.value) {
    rows.value = ((list.value as any)?.data ?? []) as CouponRow[];
  } else {
    toast.error({
      title: 'Error',
      message: (error.value as any)?.data?.message || 'Failed to load coupons.',
      position: 'topRight',
      layout: 2,
    });
  }
  summary.value = ((sum.value as any)?.data ?? summary.value) as SummaryRow;
  loading.value = false;
});
</script>
