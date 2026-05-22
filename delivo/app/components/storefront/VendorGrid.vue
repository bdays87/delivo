<template>
  <section id="vendors" class="scroll-mt-24 border-t border-base-300 bg-base-100 py-16">
    <div class="mx-auto w-full max-w-[1600px] px-4 md:px-6 lg:px-8">
      <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
          <p class="text-sm font-semibold uppercase tracking-wider text-primary">Marketplace</p>
          <h2 class="mt-2 text-3xl font-bold md:text-4xl">All vendors</h2>
          <p class="mt-2 text-sm opacity-70">
            Browse every active store on Delivo. Search by business name or store slug.
          </p>
        </div>
        <NuxtLink to="/products" class="link link-hover hidden text-sm font-medium md:inline-flex">
          Browse all products →
        </NuxtLink>
      </div>

      <label class="input input-bordered mt-6 flex w-full max-w-xl items-center gap-2 rounded-full bg-base-200/60">
        <Icon name="lucide:search" class="h-4 w-4 opacity-50" />
        <input
          v-model="searchQuery"
          type="search"
          placeholder="Search by vendor name or slug…"
          class="grow bg-transparent"
          autocomplete="off"
        />
        <button
          v-if="searchQuery"
          type="button"
          class="btn btn-ghost btn-xs btn-circle"
          aria-label="Clear search"
          @click="searchQuery = ''"
        >
          <Icon name="lucide:x" class="h-3.5 w-3.5" />
        </button>
      </label>

      <p v-if="!vendorStore.loading && vendorStore.vendors.length" class="mt-3 text-xs opacity-60">
        <template v-if="searchQuery.trim()">
          {{ filteredVendors.length }} of {{ vendorStore.vendors.length }} vendors match
        </template>
        <template v-else>
          {{ vendorStore.vendors.length }} vendor{{ vendorStore.vendors.length === 1 ? '' : 's' }} on Delivo
        </template>
      </p>

      <div v-if="vendorStore.loading" class="flex justify-center py-16">
        <span class="loading loading-spinner loading-lg"></span>
      </div>

      <div
        v-else-if="!vendorStore.vendors.length"
        class="mt-6 rounded-3xl border border-dashed border-base-300 bg-base-100 p-12 text-center text-sm opacity-70"
      >
        We're approving the first batch of vendors.
        <NuxtLink to="/vendor/apply" class="link link-primary">Apply to sell</NuxtLink>.
      </div>

      <div
        v-else-if="!filteredVendors.length"
        class="mt-6 rounded-3xl border border-dashed border-base-300 bg-base-100 p-12 text-center"
      >
        <Icon name="lucide:store" class="mx-auto h-10 w-10 opacity-30" />
        <p class="mt-3 font-semibold">No vendors match "{{ searchQuery }}"</p>
        <p class="mt-1 text-sm opacity-70">Try another name or slug, or clear the search.</p>
        <button type="button" class="btn btn-ghost btn-sm mt-4 rounded-full" @click="searchQuery = ''">
          Clear search
        </button>
      </div>

      <div v-else class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
        <NuxtLink
          v-for="v in filteredVendors"
          :key="v.id"
          :to="`/products?vendor_slug=${v.slug}`"
          class="group rounded-3xl border border-base-300 bg-base-200/40 p-5 shadow-sm transition hover:-translate-y-1 hover:bg-base-200 hover:shadow-lg"
        >
          <div class="grid aspect-[3/2] place-items-center rounded-2xl bg-gradient-to-br from-primary/10 to-base-200 px-4">
            <span class="text-center text-lg font-extrabold tracking-tight md:text-xl">{{ v.business_name }}</span>
          </div>
          <div class="mt-4 flex items-center gap-2">
            <span class="h-2 w-2 shrink-0 rounded-full bg-success"></span>
            <span class="text-xs font-medium text-success">Delivered nationwide</span>
          </div>
          <div class="mt-2 font-mono text-xs opacity-60">delivo.co.zw/store/{{ v.slug }}</div>
          <div class="mt-1 text-sm opacity-70">
            {{ v.products_count }} product{{ v.products_count === 1 ? '' : 's' }} listed
          </div>
        </NuxtLink>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import type { PublicVendor } from '~/stores/publicVendor';

const vendorStore = usePublicVendorStore();
const searchQuery = ref('');

onMounted(() => vendorStore.fetchActive());

const filteredVendors = computed(() => {
  const q = searchQuery.value.trim().toLowerCase();
  const list = vendorStore.vendors as PublicVendor[];
  if (!q) return list;
  return list.filter(
    (v) =>
      v.business_name.toLowerCase().includes(q)
      || v.slug.toLowerCase().includes(q)
      || v.slug.toLowerCase().replace(/-/g, ' ').includes(q),
  );
});
</script>
