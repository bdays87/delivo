<template>
  <section id="products" class="scroll-mt-24 bg-base-200 py-16">
    <div class="mx-auto max-w-7xl px-4">
      <div class="mb-8 flex items-end justify-between">
        <div>
          <p class="text-sm font-semibold uppercase tracking-wider text-primary">Fresh on Delivo</p>
          <h2 class="mt-2 text-3xl font-bold md:text-4xl">Latest from our vendors</h2>
        </div>
        <NuxtLink to="/products" class="link link-hover hidden text-sm font-medium md:inline-flex">
          See all →
        </NuxtLink>
      </div>

      <div v-if="loading" class="flex justify-center py-10">
        <span class="loading loading-spinner loading-md"></span>
      </div>

      <div v-else-if="!products.length" class="rounded-3xl border border-dashed border-base-300 bg-base-100 p-12 text-center text-sm opacity-70">
        Vendors are getting set up. Check back soon.
      </div>

      <div v-else class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-5">
        <StorefrontProductCard v-for="p in products" :key="p.id" :product="p" />
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import type { Product } from '~/stores/product';

const products = ref<Product[]>([]);
const loading = ref(false);

const { listProducts } = useProductHelper();
const currency = useCurrencyStore();

onMounted(async () => {
  loading.value = true;
  const { data, error } = await listProducts({ per_page: 10 });
  if (!error.value) {
    const payload = (data.value as any)?.data ?? {};
    products.value = payload.items ?? [];
    currency.captureRateFromApi(payload.exchange_rate);
  }
  loading.value = false;
});
</script>
