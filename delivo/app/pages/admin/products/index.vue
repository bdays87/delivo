<template>
  <div>
    <div class="flex flex-wrap items-end justify-between gap-3">
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight">Products moderation</h1>
        <p class="mt-1 text-sm opacity-70">
          Review vendor submissions. Pending and re-submitted products land here first.
        </p>
      </div>
    </div>

    <div class="mt-6 flex flex-wrap gap-2">
      <button
        v-for="tab in tabs"
        :key="tab.value"
        :class="['btn btn-sm rounded-full', store.filterStatus === tab.value ? 'btn-primary' : 'btn-ghost']"
        @click="store.fetchAll(tab.value)"
      >
        {{ tab.label }}
      </button>
    </div>

    <div v-if="store.loading" class="flex justify-center py-16">
      <span class="loading loading-spinner loading-lg"></span>
    </div>

    <div v-else-if="!store.items.length" class="mt-6 rounded-3xl border border-dashed border-base-300 p-12 text-center">
      <Icon name="lucide:package" class="mx-auto h-10 w-10 opacity-30" />
      <p class="mt-3 text-sm opacity-70">Nothing in this queue.</p>
    </div>

    <div v-else class="mt-6 overflow-hidden rounded-3xl border border-base-300 bg-base-100">
      <table class="table">
        <thead class="bg-base-200/50 text-xs uppercase tracking-wider opacity-70">
          <tr>
            <th></th>
            <th>Product</th>
            <th>Vendor</th>
            <th>Category</th>
            <th>Variants</th>
            <th>From</th>
            <th>Submitted</th>
            <th class="text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="p in store.items" :key="p.id">
            <td>
              <div class="h-12 w-12 overflow-hidden rounded-xl bg-base-200">
                <img v-if="primary(p)" :src="primary(p)!" class="h-full w-full object-cover" :alt="p.name" />
                <div v-else class="grid h-full place-items-center text-base-content/30">
                  <Icon name="lucide:image-off" class="h-4 w-4" />
                </div>
              </div>
            </td>
            <td>
              <div class="font-semibold">{{ p.name }}</div>
              <div class="text-xs opacity-60 font-mono">{{ p.slug }}</div>
            </td>
            <td class="text-sm">{{ (p.vendor as any)?.business_name ?? '—' }}</td>
            <td class="text-sm">{{ (p.category as any)?.name ?? '—' }}</td>
            <td>{{ p.variants?.length ?? 0 }}</td>
            <td>${{ baseTier(p) }}</td>
            <td class="text-xs opacity-70">{{ p.submitted_at?.slice(0, 10) ?? '—' }}</td>
            <td class="text-right">
              <NuxtLink :to="`/admin/products/${p.id}`" class="btn btn-xs btn-ghost rounded-full">
                Review →
              </NuxtLink>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
import { productImageUrl, productPrimaryImage, type Product } from '~/stores/product';

definePageMeta({
  layout: 'admin',
  middleware: ['auth', 'admin'],
});
useHead({ title: 'Products — Delivo Admin' });

const store = useAdminProductStore();

const tabs = [
  { label: 'Pending', value: 'PENDING' },
  { label: 'Active', value: 'ACTIVE' },
  { label: 'Rejected', value: 'REJECTED' },
  { label: 'Archived', value: 'ARCHIVED' },
];

onMounted(() => store.fetchAll('PENDING'));

const primary = (p: Product) => productImageUrl(productPrimaryImage(p));

const baseTier = (p: Product): string => {
  if (!p.price_tiers?.length) return '—';
  const lowest = [...p.price_tiers].sort((a, b) => Number(a.min_qty) - Number(b.min_qty))[0];
  return Number(lowest.unit_price).toFixed(2);
};
</script>
