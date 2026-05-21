<template>
  <div>
    <div v-if="vendorStore.loading && !vendorStore.vendor" class="flex justify-center py-20">
      <span class="loading loading-spinner loading-lg"></span>
    </div>

    <div v-else-if="!vendorStore.vendor" class="rounded-3xl border border-base-300 bg-base-100 p-8 text-center">
      <h1 class="text-2xl font-bold">No vendor application yet</h1>
      <p class="mt-2 text-sm opacity-70">Start by submitting your business details.</p>
      <NuxtLink to="/vendor/apply" class="btn btn-primary mt-4 rounded-full">Apply to sell</NuxtLink>
    </div>

    <div v-else class="space-y-6">
      <!-- Header -->
      <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
          <h1 class="text-2xl font-extrabold tracking-tight">{{ vendorStore.vendor.business_name }}</h1>
          <p class="mt-1 text-sm opacity-70">delivo.co.zw/store/{{ vendorStore.vendor.slug }}</p>
        </div>
        <span :class="['badge badge-lg', statusBadge]">{{ statusLabel }}</span>
      </div>

      <!-- Status alerts -->
      <div v-if="vendorStore.vendor.status === 'PENDING'" class="rounded-2xl border border-warning/40 bg-warning/5 p-4 text-sm">
        Your application is awaiting admin review.
        <span v-if="!hasUploadedId" class="mt-2 block font-semibold">
          Action needed: upload your national ID.
          <NuxtLink to="/vendor/apply" class="link link-primary">Upload now →</NuxtLink>
        </span>
      </div>
      <div v-else-if="vendorStore.vendor.status === 'REJECTED'" class="rounded-2xl border border-error/40 bg-error/5 p-4 text-sm">
        <div class="font-semibold">Application rejected.</div>
        <div v-if="vendorStore.vendor.rejection_reason" class="opacity-80">{{ vendorStore.vendor.rejection_reason }}</div>
      </div>
      <div v-else-if="vendorStore.vendor.status === 'SUSPENDED'" class="rounded-2xl border border-error/40 bg-error/5 p-4 text-sm">
        <div class="font-semibold">Account suspended.</div>
        <div v-if="vendorStore.vendor.rejection_reason" class="opacity-80">{{ vendorStore.vendor.rejection_reason }}</div>
      </div>

      <div v-if="dashboardStore.loading" class="flex justify-center py-16">
        <span class="loading loading-spinner loading-lg"></span>
      </div>

      <template v-else-if="dashboardStore.data">
        <!-- KPI cards -->
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
          <div
            v-for="kpi in kpiCards"
            :key="kpi.label"
            class="rounded-3xl border border-base-300 bg-base-100 p-5"
          >
            <div class="flex items-start justify-between">
              <div>
                <div class="text-xs uppercase tracking-wider opacity-60">{{ kpi.label }}</div>
                <div class="mt-2 text-2xl font-bold">{{ kpi.value }}</div>
              </div>
              <span class="grid h-10 w-10 place-items-center rounded-2xl" :class="kpi.iconBg">
                <Icon :name="kpi.icon" class="h-5 w-5" />
              </span>
            </div>
            <p class="mt-3 text-xs opacity-60">{{ kpi.hint }}</p>
          </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-3">
          <!-- Top moving + search -->
          <div class="rounded-3xl border border-base-300 bg-base-100 p-6 xl:col-span-2">
            <div class="flex flex-wrap items-end justify-between gap-3">
              <div>
                <h2 class="text-lg font-bold">Highest moving products</h2>
                <p class="mt-1 text-sm opacity-70">
                  Ranked by stock pressure until order history is connected.
                </p>
              </div>
              <NuxtLink to="/vendor/products" class="btn btn-ghost btn-sm rounded-full">
                All products
                <Icon name="lucide:arrow-right" class="h-4 w-4" />
              </NuxtLink>
            </div>

            <label class="input input-bordered mt-4 flex w-full items-center gap-2 rounded-full bg-base-200/50">
              <Icon name="lucide:search" class="h-4 w-4 opacity-50" />
              <input
                v-model="productSearch"
                type="search"
                placeholder="Search products by name or category…"
                class="grow bg-transparent"
              />
            </label>

            <div v-if="!filteredTopMoving.length" class="mt-6 py-10 text-center text-sm opacity-60">
              <Icon name="lucide:package-search" class="mx-auto h-8 w-8 opacity-40" />
              <p class="mt-2">No products match your search.</p>
              <NuxtLink to="/vendor/products/new" class="btn btn-primary btn-sm mt-3 rounded-full">
                Add a product
              </NuxtLink>
            </div>

            <div v-else class="mt-4 overflow-x-auto">
              <table class="table table-sm">
                <thead class="text-xs uppercase tracking-wider opacity-60">
                  <tr>
                    <th class="w-14">#</th>
                    <th class="w-16"></th>
                    <th>Product</th>
                    <th>Category</th>
                    <th class="text-right">Units sold</th>
                    <th class="text-right">Stock left</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="p in filteredTopMoving" :key="p.id" class="hover:bg-base-200/50">
                    <td class="font-mono text-xs opacity-60">{{ p.movement_rank }}</td>
                    <td>
                      <div class="h-12 w-12 overflow-hidden rounded-xl bg-base-200">
                        <img
                          v-if="topMovingImageUrl(p)"
                          :src="topMovingImageUrl(p)!"
                          :alt="p.name"
                          class="h-full w-full object-cover"
                        />
                        <div v-else class="grid h-full w-full place-items-center text-base-content/25">
                          <Icon name="lucide:image-off" class="h-5 w-5" />
                        </div>
                      </div>
                    </td>
                    <td>
                      <div class="font-semibold">{{ p.name }}</div>
                      <span :class="['badge badge-xs mt-1', productStatusBadge(p.status)]">{{ p.status }}</span>
                    </td>
                    <td class="text-sm opacity-70">{{ p.category_name ?? '—' }}</td>
                    <td class="text-right font-medium">{{ p.units_sold }}</td>
                    <td class="text-right">
                      <span :class="p.stock_remaining <= 5 ? 'font-semibold text-warning' : ''">
                        {{ p.stock_remaining }}
                      </span>
                    </td>
                    <td class="text-right">
                      <NuxtLink :to="`/vendor/products/${p.id}`" class="btn btn-ghost btn-xs rounded-full">
                        View
                      </NuxtLink>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Stock summary -->
          <div class="rounded-3xl border border-base-300 bg-base-100 p-6">
            <h2 class="text-lg font-bold">Stock summary</h2>
            <p class="mt-1 text-sm opacity-70">Across all variants in your catalog.</p>

            <div class="mt-6 space-y-4">
              <div class="rounded-2xl bg-base-200/60 p-4">
                <div class="flex items-center justify-between text-sm">
                  <span class="opacity-70">Total units on hand</span>
                  <span class="text-xl font-bold">{{ dashboardStore.data.stock.total_units }}</span>
                </div>
                <div class="mt-1 text-xs opacity-50">{{ dashboardStore.data.stock.variant_count }} SKU variants</div>
              </div>

              <div
                v-for="row in stockRows"
                :key="row.label"
                class="flex items-center gap-3"
              >
                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl" :class="row.iconBg">
                  <Icon :name="row.icon" class="h-4 w-4" />
                </span>
                <div class="min-w-0 flex-1">
                  <div class="flex justify-between text-sm">
                    <span>{{ row.label }}</span>
                    <span class="font-semibold">{{ row.count }}</span>
                  </div>
                  <progress
                    class="progress mt-1 h-2 w-full"
                    :class="row.progressClass"
                    :value="row.count"
                    :max="stockProgressMax"
                  ></progress>
                </div>
              </div>
            </div>

            <div v-if="filteredStockAlerts.length" class="mt-6 border-t border-base-300 pt-4">
              <p class="text-xs font-semibold uppercase tracking-wider opacity-60">Needs attention</p>
              <ul class="mt-2 space-y-2">
                <li
                  v-for="item in filteredStockAlerts"
                  :key="item.id"
                  class="flex items-center justify-between rounded-xl bg-warning/10 px-3 py-2 text-sm"
                >
                  <span class="truncate font-medium">{{ item.name }}</span>
                  <span class="shrink-0 text-xs text-warning">{{ item.total_stock }} left</span>
                </li>
              </ul>
            </div>
          </div>
        </div>

        <p
          v-if="!dashboardStore.data.summary.orders_available"
          class="text-center text-xs opacity-50"
        >
          Order and sales totals will update automatically when the orders module is enabled.
        </p>
      </template>

      <!-- Collapsible store info -->
      <details class="rounded-3xl border border-base-300 bg-base-100">
        <summary class="cursor-pointer px-6 py-4 font-bold">Store & KYC details</summary>
        <div class="space-y-6 border-t border-base-300 px-6 py-4">
          <dl class="grid gap-3 text-sm md:grid-cols-2">
            <div><dt class="opacity-60">Support email</dt><dd>{{ vendorStore.vendor.support_email }}</dd></div>
            <div><dt class="opacity-60">Support phone</dt><dd>{{ vendorStore.vendor.support_phone }}</dd></div>
            <div v-if="vendorStore.vendor.tin"><dt class="opacity-60">TIN</dt><dd>{{ vendorStore.vendor.tin }}</dd></div>
            <div v-if="vendorStore.vendor.registration_no"><dt class="opacity-60">Reg. number</dt><dd>{{ vendorStore.vendor.registration_no }}</dd></div>
          </dl>
          <div>
            <h3 class="font-semibold">KYC documents</h3>
            <div v-if="!(vendorStore.vendor.kyc_documents?.length)" class="mt-2 text-sm opacity-70">
              No documents uploaded.
              <NuxtLink to="/vendor/apply" class="link link-primary">Upload ID</NuxtLink>
            </div>
            <ul v-else class="mt-2 divide-y divide-base-300">
              <li
                v-for="d in vendorStore.vendor.kyc_documents"
                :key="d.id"
                class="flex items-center justify-between py-2 text-sm"
              >
                <span>{{ d.original_filename }}</span>
                <span class="badge badge-sm">{{ d.status }}</span>
              </li>
            </ul>
          </div>
        </div>
      </details>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'vendor',
  middleware: ['auth', 'vendor'],
});
useHead({ title: 'Dashboard — Delivo Vendor' });

const vendorStore = useVendorStore();
const dashboardStore = useVendorDashboardStore();

const productSearch = ref('');

onMounted(async () => {
  await vendorStore.fetchCurrent();
  if (vendorStore.vendor) {
    await dashboardStore.fetchDashboard();
  }
});

const statusLabel = computed(() => {
  switch (vendorStore.vendor?.status) {
    case 'PENDING': return 'Pending review';
    case 'ACTIVE': return 'Active';
    case 'REJECTED': return 'Rejected';
    case 'SUSPENDED': return 'Suspended';
    default: return '';
  }
});

const statusBadge = computed(() => {
  switch (vendorStore.vendor?.status) {
    case 'PENDING': return 'badge-warning';
    case 'ACTIVE': return 'badge-success';
    case 'REJECTED': return 'badge-error';
    case 'SUSPENDED': return 'badge-error';
    default: return 'badge-ghost';
  }
});

const hasUploadedId = computed(() =>
  (vendorStore.vendor?.kyc_documents ?? []).some(
    (d) => d.type === 'NATIONAL_ID' && d.status !== 'REJECTED',
  ),
);

const kpiCards = computed(() => {
  const s = dashboardStore.data?.summary;
  if (!s) return [];
  return [
    {
      label: 'Total products',
      value: String(s.total_products),
      hint: `${s.active_products} live · ${s.pending_products} pending review`,
      icon: 'lucide:package',
      iconBg: 'bg-primary/10 text-primary',
    },
    {
      label: 'Orders',
      value: String(s.total_orders),
      hint: s.orders_available ? 'All time' : 'Order tracking coming soon',
      icon: 'lucide:shopping-cart',
      iconBg: 'bg-secondary/10 text-secondary',
    },
    {
      label: 'Sales (USD)',
      value: `$${s.total_sales_usd}`,
      hint: 'Gross sales before payouts',
      icon: 'lucide:banknote',
      iconBg: 'bg-success/10 text-success',
    },
    {
      label: 'Units in stock',
      value: String(dashboardStore.data?.stock.total_units ?? 0),
      hint: `${dashboardStore.data?.stock.low_stock_count ?? 0} variants low · ${dashboardStore.data?.stock.out_of_stock_count ?? 0} out`,
      icon: 'lucide:boxes',
      iconBg: 'bg-warning/10 text-warning',
    },
  ];
});

const filteredTopMoving = computed(() => {
  const list = dashboardStore.data?.top_moving ?? [];
  const q = productSearch.value.trim().toLowerCase();
  if (!q) return list;
  return list.filter(
    (p) =>
      p.name.toLowerCase().includes(q)
      || (p.category_name?.toLowerCase().includes(q) ?? false)
      || p.slug.toLowerCase().includes(q),
  );
});

const stockProgressMax = computed(() => {
  const st = dashboardStore.data?.stock;
  if (!st) return 1;
  return Math.max(st.variant_count, 1);
});

const stockRows = computed(() => {
  const st = dashboardStore.data?.stock;
  if (!st) return [];
  return [
    {
      label: 'Healthy stock',
      count: st.healthy_count,
      icon: 'lucide:check-circle',
      iconBg: 'bg-success/15 text-success',
      progressClass: 'progress-success',
    },
    {
      label: 'Low stock (≤5)',
      count: st.low_stock_count,
      icon: 'lucide:alert-triangle',
      iconBg: 'bg-warning/15 text-warning',
      progressClass: 'progress-warning',
    },
    {
      label: 'Out of stock',
      count: st.out_of_stock_count,
      icon: 'lucide:package-x',
      iconBg: 'bg-error/15 text-error',
      progressClass: 'progress-error',
    },
  ];
});

const filteredStockAlerts = computed(() => {
  const q = productSearch.value.trim().toLowerCase();
  const items = (dashboardStore.data?.stock_by_product ?? []).filter(
    (p) => p.low_stock_variants > 0 || p.out_of_stock_variants > 0 || p.total_stock <= 5,
  );
  const sorted = [...items].sort((a, b) => a.total_stock - b.total_stock);
  if (!q) return sorted.slice(0, 5);
  return sorted.filter((p) => p.name.toLowerCase().includes(q)).slice(0, 5);
});

const topMovingImageUrl = (p: { image_path: string | null }) => {
  if (!p.image_path) return null;
  const cfg = useRuntimeConfig();
  return `${cfg.public.apiBase}/storage/${p.image_path}`;
};

const productStatusBadge = (status: string) => {
  if (status === 'ACTIVE') return 'badge-success';
  if (status === 'PENDING') return 'badge-warning';
  if (status === 'REJECTED') return 'badge-error';
  return 'badge-ghost';
};
</script>
