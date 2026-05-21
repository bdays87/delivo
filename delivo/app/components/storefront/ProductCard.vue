<template>
  <NuxtLink
    :to="`/products/${product.slug}`"
    class="group flex flex-col overflow-hidden rounded-3xl border border-base-300 bg-base-100 transition hover:-translate-y-1 hover:shadow-xl"
  >
    <div class="relative aspect-[5/4] overflow-hidden bg-base-200">
      <img
        v-if="primary"
        :src="primary"
        :alt="product.name"
        class="h-full w-full object-cover transition group-hover:scale-105"
        loading="lazy"
      />
      <div v-else class="grid h-full place-items-center text-base-content/30">
        <Icon name="lucide:image-off" class="h-8 w-8" />
      </div>
      <span
        v-if="bulkDiscountPct"
        class="badge badge-primary absolute left-3 top-3 font-semibold shadow"
      >
        -{{ bulkDiscountPct }}% in bulk
      </span>
    </div>

    <div class="flex flex-1 flex-col p-4">
      <div class="text-xs font-medium uppercase tracking-wider text-primary/80">
        {{ (product.category as any)?.name ?? '—' }}
      </div>
      <h3 class="mt-1 line-clamp-2 text-sm font-semibold">{{ product.name }}</h3>
      <div class="mt-2 text-xs opacity-60">{{ (product.vendor as any)?.business_name }}</div>

      <div class="mt-3 flex items-end justify-between">
        <div>
          <div class="text-lg font-bold text-primary">{{ currency.format(baseUnitPrice) }}</div>
          <div v-if="bestUnitPrice && bestUnitPrice < baseUnitPrice" class="text-xs opacity-60">
            from {{ currency.format(bestUnitPrice) }} in bulk
          </div>
        </div>
        <span
          v-if="colorCount > 1"
          class="badge badge-ghost text-xs"
        >{{ colorCount }} colors</span>
      </div>
    </div>
  </NuxtLink>
</template>

<script setup lang="ts">
import { productImageUrl, productPrimaryImage, type Product } from '~/stores/product';

const props = defineProps<{ product: Product }>();

const currency = useCurrencyStore();

const primary = computed(() => productImageUrl(productPrimaryImage(props.product)));

const sortedTiers = computed(() => {
  const tiers = props.product.price_tiers ?? [];
  return [...tiers].sort((a, b) => Number(a.min_qty) - Number(b.min_qty));
});

const baseUnitPrice = computed(() => Number(sortedTiers.value[0]?.unit_price ?? 0));
const bestUnitPrice = computed(() => {
  const tiers = sortedTiers.value;
  return tiers.length ? Number(tiers[tiers.length - 1].unit_price) : 0;
});

const bulkDiscountPct = computed(() => {
  if (!baseUnitPrice.value || !bestUnitPrice.value || bestUnitPrice.value >= baseUnitPrice.value) {
    return 0;
  }
  return Math.round(((baseUnitPrice.value - bestUnitPrice.value) / baseUnitPrice.value) * 100);
});

const colorCount = computed(() => {
  const variants = props.product.variants ?? [];
  const colors = new Set(variants.map((v) => v.color).filter((c) => c && c.trim() !== ''));
  return colors.size;
});
</script>
