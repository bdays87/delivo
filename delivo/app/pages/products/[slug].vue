<template>
  <section class="mx-auto max-w-7xl px-4 py-10">
    <div v-if="store.loading && !store.current" class="flex justify-center py-16">
      <span class="loading loading-spinner loading-lg"></span>
    </div>

    <div v-else-if="!store.current" class="rounded-3xl border border-base-300 bg-base-100 p-8 text-center">
      <p>Product not found.</p>
      <NuxtLink to="/products" class="btn btn-primary mt-4 rounded-full">Back to shop</NuxtLink>
    </div>

    <div v-else>
      <div class="mb-4 flex items-center gap-2 text-xs opacity-60">
        <NuxtLink to="/" class="link link-hover">Home</NuxtLink>
        <Icon name="lucide:chevron-right" class="h-3 w-3" />
        <NuxtLink to="/products" class="link link-hover">Shop</NuxtLink>
        <Icon name="lucide:chevron-right" class="h-3 w-3" />
        <NuxtLink
          :to="`/products?category_id=${(store.current.category as any)?.id}`"
          class="link link-hover"
        >{{ (store.current.category as any)?.name }}</NuxtLink>
      </div>

      <div class="grid gap-8 lg:grid-cols-2">
        <div>
          <div class="aspect-square overflow-hidden rounded-3xl border border-base-300 bg-base-200">
            <img
              v-if="primaryUrl"
              :src="primaryUrl"
              :alt="store.current.name"
              class="h-full w-full object-cover"
            />
            <div v-else class="grid h-full place-items-center text-base-content/30">
              <Icon name="lucide:image-off" class="h-16 w-16" />
            </div>
          </div>
          <div v-if="store.current.images && store.current.images.length > 1" class="mt-3 grid grid-cols-5 gap-2">
            <button
              v-for="img in store.current.images"
              :key="img.id"
              :class="['overflow-hidden rounded-2xl border-2 transition', selectedImageId === img.id ? 'border-primary' : 'border-transparent hover:border-base-300']"
              @click="selectedImageId = img.id"
            >
              <img :src="urlFor(img)" :alt="store.current.name" class="aspect-square w-full object-cover" />
            </button>
          </div>
        </div>

        <div>
          <div class="text-xs font-semibold uppercase tracking-wider text-primary">
            {{ (store.current.category as any)?.name ?? '—' }}
          </div>
          <h1 class="mt-2 text-3xl font-extrabold tracking-tight md:text-4xl">{{ store.current.name }}</h1>
          <div class="mt-1 text-sm opacity-70">
            Sold by
            <span class="font-semibold">{{ (store.current.vendor as any)?.business_name }}</span>
          </div>

          <div class="mt-6 rounded-3xl border border-base-300 bg-base-100 p-5">
            <div class="flex flex-wrap items-end gap-3">
              <div>
                <div class="text-3xl font-extrabold text-primary">{{ currency.format(activeUnitPrice) }}</div>
                <div class="text-xs opacity-60">per unit · {{ currency.code }}</div>
              </div>
              <div v-if="currency.code === 'USD' && currency.hasZwgRate" class="text-sm opacity-70">
                ≈ {{ zwgEquivalent }}
              </div>
            </div>

            <div v-if="sortedTiers.length > 1" class="mt-4">
              <div class="text-xs font-semibold uppercase opacity-70">Bulk pricing</div>
              <div class="mt-2 grid grid-cols-2 gap-2 sm:grid-cols-3">
                <div
                  v-for="t in sortedTiers"
                  :key="t.id ?? `${t.min_qty}-${t.unit_price}`"
                  class="rounded-2xl border border-base-300 bg-base-200/40 p-3 text-center"
                >
                  <div class="text-xs opacity-60">{{ tierLabel(t.min_qty) }}</div>
                  <div class="mt-1 text-sm font-bold">{{ currency.format(t.unit_price) }}</div>
                </div>
              </div>
            </div>
          </div>

          <div v-if="colorVariants.length" class="mt-6">
            <div class="text-sm font-semibold">Color</div>
            <div class="mt-2 flex flex-wrap gap-2">
              <button
                v-for="v in colorVariants"
                :key="v.id ?? v.color"
                :class="[
                  'rounded-full border px-4 py-2 text-sm transition',
                  selectedVariant?.id === v.id
                    ? 'border-primary bg-primary text-primary-content'
                    : 'border-base-300 bg-base-100 hover:border-primary',
                  Number(v.stock_quantity) === 0 ? 'opacity-50' : '',
                ]"
                :disabled="Number(v.stock_quantity) === 0"
                @click="selectedVariant = v"
              >
                {{ v.color }}
                <span v-if="Number(v.stock_quantity) === 0" class="ml-1 text-xs">(sold out)</span>
              </button>
            </div>
          </div>

          <div class="mt-6 flex flex-wrap items-center gap-4">
            <div class="flex items-center gap-2 rounded-full border border-base-300 bg-base-100 px-2 py-1">
              <button
                class="btn btn-circle btn-ghost btn-xs"
                :disabled="qty <= 1"
                @click="qty = Math.max(1, qty - 1)"
              >
                <Icon name="lucide:minus" class="h-3 w-3" />
              </button>
              <input
                v-model.number="qty"
                type="number"
                min="1"
                class="w-14 bg-transparent text-center text-sm font-semibold"
              />
              <button
                class="btn btn-circle btn-ghost btn-xs"
                @click="qty = qty + 1"
              >
                <Icon name="lucide:plus" class="h-3 w-3" />
              </button>
            </div>

            <div class="text-sm opacity-70">
              {{ availableStock }} in stock
            </div>
          </div>

          <div class="mt-6 flex flex-wrap gap-3">
            <button class="btn btn-primary btn-lg rounded-full" :disabled="!canAddToCart" @click="onAddToCart">
              <Icon name="lucide:shopping-cart" class="h-4 w-4" />
              Add to cart
            </button>
            <NuxtLink to="/products" class="btn btn-outline btn-lg rounded-full">
              Continue browsing
            </NuxtLink>
          </div>

          <div v-if="store.current.description" class="mt-8 rounded-3xl border border-base-300 bg-base-100 p-5">
            <h2 class="text-sm font-semibold uppercase tracking-wider opacity-70">About this product</h2>
            <p class="mt-3 whitespace-pre-line text-sm opacity-80">{{ store.current.description }}</p>
            <dl class="mt-4 grid gap-2 text-xs opacity-70 sm:grid-cols-3">
              <div v-if="store.current.sku"><dt class="font-semibold uppercase opacity-70">SKU</dt><dd>{{ store.current.sku }}</dd></div>
              <div v-if="store.current.weight_kg"><dt class="font-semibold uppercase opacity-70">Weight</dt><dd>{{ store.current.weight_kg }} kg</dd></div>
            </dl>
          </div>

          <div class="mt-6 rounded-3xl border border-success/30 bg-success/5 p-4 text-sm">
            <div class="flex items-start gap-3">
              <Icon name="lucide:truck" class="h-5 w-5 text-success" />
              <div>
                <div class="font-semibold">Delivered by Delivo, anywhere in Zimbabwe</div>
                <div class="opacity-70">We pick up from the vendor and bring it to your door.</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { productImageUrl, type ProductImage, type ProductPriceTier, type ProductVariant } from '~/stores/product';

const route = useRoute();
const store = usePublicProductStore();
const currency = useCurrencyStore();
const toast = useToast();

const slug = computed(() => route.params.slug as string);

const selectedImageId = ref<number | null>(null);
const selectedVariant = ref<ProductVariant | null>(null);
const qty = ref(1);

onMounted(async () => {
  await store.fetchOne(slug.value);
  hydrateSelections();
});

watch(slug, async () => {
  await store.fetchOne(slug.value);
  hydrateSelections();
});

useHead({ title: () => `${store.current?.name ?? 'Product'} — Delivo` });

const hydrateSelections = () => {
  const images = store.current?.images ?? [];
  const primary = images.find((i) => i.is_primary) ?? images[0];
  selectedImageId.value = primary?.id ?? null;

  const variants = store.current?.variants ?? [];
  selectedVariant.value = variants.find((v) => Number(v.stock_quantity) > 0) ?? variants[0] ?? null;
  qty.value = 1;
};

const sortedTiers = computed<ProductPriceTier[]>(() => {
  const tiers = store.current?.price_tiers ?? [];
  return [...tiers].sort((a, b) => Number(a.min_qty) - Number(b.min_qty));
});

// Resolve the unit price for the current qty against the tier table.
const activeUnitPrice = computed(() => {
  if (!sortedTiers.value.length) return 0;
  let price = Number(sortedTiers.value[0].unit_price);
  for (const t of sortedTiers.value) {
    if (qty.value >= Number(t.min_qty)) price = Number(t.unit_price);
  }
  return price;
});

const zwgEquivalent = computed(() => {
  if (!currency.hasZwgRate) return '';
  const usd = activeUnitPrice.value;
  const zwg = usd * (currency.usdToZwgRate as unknown as number);
  return `ZWG ${zwg.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
});

const tierLabel = (minQty: number | string): string => {
  const tiers = sortedTiers.value;
  const min = Number(minQty);
  const next = tiers.find((t) => Number(t.min_qty) > min);
  if (!next) return `${min}+`;
  return `${min}–${Number(next.min_qty) - 1}`;
};

const colorVariants = computed<ProductVariant[]>(() => {
  const variants = store.current?.variants ?? [];
  // Only show the color picker when at least one variant has a non-null color.
  return variants.some((v) => v.color && v.color.trim() !== '') ? variants : [];
});

const availableStock = computed(() => {
  if (selectedVariant.value) return Number(selectedVariant.value.stock_quantity);
  const variants = store.current?.variants ?? [];
  return variants.reduce((sum, v) => sum + Number(v.stock_quantity), 0);
});

const canAddToCart = computed(() => availableStock.value > 0);

const urlFor = (img: ProductImage) => productImageUrl(img) ?? '';

const primaryUrl = computed(() => {
  const images = store.current?.images ?? [];
  const chosen = images.find((i) => i.id === selectedImageId.value) ?? images[0];
  return chosen ? urlFor(chosen) : null;
});

const auth = useAuthStore();
const cart = useCartStore();
const router = useRouter();

const onAddToCart = async () => {
  if (!auth.isAuthenticated) {
    toast.info({
      title: 'Sign in to continue',
      message: 'Login or create an account to add to cart.',
      position: 'topRight',
      layout: 2,
    });
    router.push('/auth/login');
    return;
  }
  if (!selectedVariant.value) return;
  await cart.add(selectedVariant.value.id, qty.value);
};
</script>
