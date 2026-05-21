<template>
  <div>
    <div class="flex flex-wrap items-end justify-between gap-3">
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight">Products</h1>
        <p class="mt-1 text-sm opacity-70">
          Manage your catalog. New and edited products are reviewed by admins before going live.
        </p>
      </div>
      <NuxtLink to="/vendor/products/new" class="btn btn-primary rounded-full">
        <Icon name="lucide:plus" class="h-4 w-4" />
        New product
      </NuxtLink>
    </div>

    <div class="mt-6 flex flex-wrap gap-2">
      <button
        v-for="tab in tabs"
        :key="tab.value ?? 'all'"
        :class="['btn btn-sm rounded-full', store.filterStatus === tab.value ? 'btn-primary' : 'btn-ghost']"
        @click="store.fetchAll(tab.value ?? undefined)"
      >
        {{ tab.label }}
        <span v-if="tab.value" class="badge badge-sm ml-1">{{ store.counts[tab.value] ?? 0 }}</span>
      </button>
    </div>

    <div v-if="store.loading" class="flex justify-center py-16">
      <span class="loading loading-spinner loading-lg"></span>
    </div>

    <div v-else-if="!store.items.length" class="mt-6 rounded-3xl border border-dashed border-base-300 p-12 text-center">
      <Icon name="lucide:package" class="mx-auto h-10 w-10 opacity-30" />
      <p class="mt-3 text-sm opacity-70">No products in this view yet.</p>
      <NuxtLink to="/vendor/products/new" class="btn btn-primary btn-sm mt-4 rounded-full">List your first product</NuxtLink>
    </div>

    <div v-else class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
      <NuxtLink
        v-for="p in store.items"
        :key="p.id"
        :to="`/vendor/products/${p.id}`"
        class="group rounded-3xl border border-base-300 bg-base-100 p-4 transition hover:-translate-y-1 hover:shadow-xl"
      >
        <div class="aspect-[4/3] overflow-hidden rounded-2xl bg-base-200">
          <img v-if="primary(p)" :src="primary(p)!" :alt="p.name" class="h-full w-full object-cover" />
          <div v-else class="grid h-full place-items-center text-base-content/30">
            <Icon name="lucide:image-off" class="h-8 w-8" />
          </div>
        </div>
        <div class="mt-3 flex items-start justify-between gap-2">
          <div class="min-w-0">
            <h3 class="truncate font-bold">{{ p.name }}</h3>
            <p class="truncate text-xs opacity-60">{{ (p.category as any)?.name ?? '—' }}</p>
          </div>
          <span :class="['badge badge-sm shrink-0', statusBadge(p.status)]">{{ statusLabel(p.status) }}</span>
        </div>
        <div class="mt-3 flex items-center justify-between text-xs opacity-70">
          <span>{{ p.variants?.length ?? 0 }} variant(s)</span>
          <span>From ${{ baseTier(p) }}</span>
        </div>
      </NuxtLink>
    </div>
  </div>
</template>

<script setup lang="ts">
import { productPrimaryImage, productImageUrl, type Product, type ProductStatus } from '~/stores/product';

definePageMeta({
  layout: 'vendor',
  middleware: ['auth', 'vendor'],
});
useHead({ title: 'Products — Delivo Vendor' });

const store = useVendorProductStore();

const tabs: { label: string; value: ProductStatus | null }[] = [
  { label: 'All', value: null },
  { label: 'Pending', value: 'PENDING' },
  { label: 'Active', value: 'ACTIVE' },
  { label: 'Rejected', value: 'REJECTED' },
  { label: 'Archived', value: 'ARCHIVED' },
];

onMounted(() => store.fetchAll());

const primary = (p: Product) => productImageUrl(productPrimaryImage(p));

const baseTier = (p: Product): string => {
  if (!p.price_tiers?.length) return '—';
  const lowest = [...p.price_tiers].sort((a, b) => Number(a.min_qty) - Number(b.min_qty))[0];
  return Number(lowest.unit_price).toFixed(2);
};

const statusLabel = (s: ProductStatus): string => ({
  PENDING: 'Pending',
  ACTIVE: 'Active',
  REJECTED: 'Rejected',
  ARCHIVED: 'Archived',
}[s]);

const statusBadge = (s: ProductStatus): string => ({
  PENDING: 'badge-warning',
  ACTIVE: 'badge-success',
  REJECTED: 'badge-error',
  ARCHIVED: 'badge-ghost',
}[s]);
</script>
