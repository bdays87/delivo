<template>
  <section id="vendors" class="bg-base-200 py-16">
    <div class="mx-auto max-w-7xl px-4">
      <div class="mb-8 flex items-end justify-between">
        <div>
          <p class="text-sm font-semibold uppercase tracking-wider text-primary">Local vendors</p>
          <h2 class="mt-2 text-3xl font-bold md:text-4xl">Shop by store</h2>
        </div>
        <NuxtLink to="/products" class="link link-hover hidden text-sm font-medium md:inline-flex">
          Browse all products →
        </NuxtLink>
      </div>

      <div v-if="vendorStore.loading && !vendorStore.vendors.length" class="flex justify-center py-10">
        <span class="loading loading-spinner loading-md"></span>
      </div>

      <div
        v-else-if="!vendorStore.vendors.length"
        class="rounded-3xl border border-dashed border-base-300 bg-base-100 p-12 text-center text-sm opacity-70"
      >
        We're approving the first batch of vendors. <NuxtLink to="/vendor/apply" class="link link-primary">Apply to sell</NuxtLink>.
      </div>

      <div v-else class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4">
        <NuxtLink
          v-for="v in vendorStore.vendors"
          :key="v.id"
          :to="`/products?vendor_slug=${v.slug}`"
          class="group rounded-3xl bg-base-100 p-6 transition hover:-translate-y-1 hover:shadow-lg"
        >
          <div class="grid aspect-[3/2] place-items-center rounded-2xl bg-base-200">
            <span class="px-4 text-center text-xl font-extrabold tracking-tight opacity-80">{{ v.business_name }}</span>
          </div>
          <div class="mt-4 flex items-center gap-2">
            <span class="h-2 w-2 rounded-full bg-success"></span>
            <span class="text-xs font-medium text-success">Delivered nationwide</span>
          </div>
          <div class="mt-1 text-sm opacity-70">
            {{ v.products_count }} product{{ v.products_count === 1 ? '' : 's' }} listed
          </div>
        </NuxtLink>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
const vendorStore = usePublicVendorStore();

onMounted(() => vendorStore.fetchActive());
</script>
