<template>
  <section class="mx-auto max-w-7xl px-4 py-16">
    <div class="mb-8 flex items-end justify-between">
      <div>
        <p class="text-sm font-semibold uppercase tracking-wider text-primary">Save more in bulk</p>
        <h2 class="mt-2 text-3xl font-bold md:text-4xl">Best volume deals</h2>
        <p class="mt-1 text-sm opacity-70">Products with the biggest unit-price drop when you order more.</p>
      </div>
      <NuxtLink to="/products" class="link link-hover hidden text-sm font-medium md:inline-flex">
        Shop all →
      </NuxtLink>
    </div>

    <div v-if="loading" class="flex justify-center py-10">
      <span class="loading loading-spinner loading-md"></span>
    </div>

    <div v-else-if="!deals.length" class="rounded-3xl border border-dashed border-base-300 bg-base-100 p-12 text-center text-sm opacity-70">
      No bulk-discounted products yet.
    </div>

    <div v-else class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
      <NuxtLink
        v-for="d in deals"
        :key="d.product.id"
        :to="`/products/${d.product.slug}`"
        class="group flex flex-col overflow-hidden rounded-3xl border border-base-300 bg-base-100 transition hover:-translate-y-1 hover:shadow-xl"
      >
        <div class="relative aspect-[5/4] overflow-hidden bg-base-200">
          <img
            v-if="primaryUrlOf(d.product)"
            :src="primaryUrlOf(d.product)!"
            :alt="d.product.name"
            class="h-full w-full object-cover transition group-hover:scale-105"
            loading="lazy"
          />
          <div v-else class="grid h-full place-items-center text-base-content/30">
            <Icon name="lucide:image-off" class="h-10 w-10" />
          </div>
          <span class="badge badge-primary absolute left-3 top-3 font-semibold shadow">
            -{{ d.discountPct }}%
          </span>
        </div>
        <div class="flex flex-1 flex-col p-5">
          <div class="text-xs font-medium uppercase tracking-wider text-primary/80">
            {{ (d.product.category as any)?.name ?? '—' }}
          </div>
          <h3 class="mt-1 line-clamp-2 font-semibold">{{ d.product.name }}</h3>
          <div class="mt-1 text-xs opacity-60">{{ (d.product.vendor as any)?.business_name }}</div>
          <div class="mt-4 flex items-end justify-between">
            <div>
              <div class="text-xl font-bold text-primary">{{ currency.format(d.bestPrice) }}</div>
              <div class="text-xs opacity-60">when buying {{ d.minQtyForBest }}+</div>
            </div>
            <div class="text-right text-xs opacity-60">
              <div class="line-through">{{ currency.format(d.basePrice) }}</div>
              <div>single unit</div>
            </div>
          </div>
        </div>
      </NuxtLink>
    </div>
  </section>
</template>

<script setup lang="ts">
import type { Product, ProductImage, ProductPriceTier } from '~/stores/product';
import { productImageUrl, productPrimaryImage } from '~/stores/product';

interface Deal {
  product: Product;
  basePrice: number;
  bestPrice: number;
  discountPct: number;
  minQtyForBest: number;
}

const deals = ref<Deal[]>([]);
const loading = ref(false);

const { listProducts } = useProductHelper();
const currency = useCurrencyStore();

const computeDeal = (product: Product): Deal | null => {
  const tiers: ProductPriceTier[] = [...(product.price_tiers ?? [])].sort(
    (a, b) => Number(a.min_qty) - Number(b.min_qty),
  );
  if (tiers.length < 2) return null;

  const basePrice = Number(tiers[0].unit_price);
  const bestTier = tiers[tiers.length - 1];
  const bestPrice = Number(bestTier.unit_price);
  if (!basePrice || bestPrice >= basePrice) return null;

  const discountPct = Math.round(((basePrice - bestPrice) / basePrice) * 100);
  return {
    product,
    basePrice,
    bestPrice,
    discountPct,
    minQtyForBest: Number(bestTier.min_qty),
  };
};

const primaryUrlOf = (product: Product): string | null => {
  const image: ProductImage | null = productPrimaryImage(product);
  return productImageUrl(image);
};

onMounted(async () => {
  loading.value = true;
  const { data, error } = await listProducts({ per_page: 30 });
  if (!error.value) {
    const payload = (data.value as any)?.data ?? {};
    const items: Product[] = payload.items ?? [];
    currency.captureRateFromApi(payload.exchange_rate);
    const computed = items
      .map(computeDeal)
      .filter((d): d is Deal => d !== null)
      .sort((a, b) => b.discountPct - a.discountPct)
      .slice(0, 6);
    deals.value = computed;
  }
  loading.value = false;
});
</script>
