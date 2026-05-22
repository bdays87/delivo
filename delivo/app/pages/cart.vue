<template>
  <section class="mx-auto max-w-7xl px-4 py-10">
    <h1 class="text-3xl font-extrabold tracking-tight md:text-4xl">Your cart</h1>
    <p class="mt-1 text-sm opacity-70">
      {{ cart.itemCount }} item{{ cart.itemCount === 1 ? '' : 's' }} · prices shown in {{ currency.code }}.
    </p>

    <div v-if="cart.loading" class="flex justify-center py-16">
      <span class="loading loading-spinner loading-lg"></span>
    </div>

    <div v-else-if="!cart.cart.items.length" class="mt-8 rounded-3xl border border-dashed border-base-300 p-12 text-center">
      <Icon name="lucide:shopping-cart" class="mx-auto h-10 w-10 opacity-30" />
      <p class="mt-3 text-sm opacity-70">Your cart is empty.</p>
      <NuxtLink to="/products" class="btn btn-primary mt-4 rounded-full">Start shopping</NuxtLink>
    </div>

    <div v-else class="mt-8 grid gap-6 lg:grid-cols-[1fr_360px]">
      <div class="rounded-3xl border border-base-300 bg-base-100">
        <ul class="divide-y divide-base-300">
          <li v-for="line in cart.cart.items" :key="line.id" class="flex gap-4 p-5">
            <div class="h-24 w-24 shrink-0 overflow-hidden rounded-2xl bg-base-200">
              <img v-if="imageUrl(line)" :src="imageUrl(line)!" :alt="line.product?.name ?? ''" class="h-full w-full object-cover" />
              <div v-else class="grid h-full place-items-center text-base-content/30">
                <Icon name="lucide:image-off" class="h-6 w-6" />
              </div>
            </div>
            <div class="flex-1">
              <div class="flex items-start justify-between gap-3">
                <div>
                  <NuxtLink
                    v-if="line.product"
                    :to="`/products/${line.product.slug}`"
                    class="font-semibold hover:underline"
                  >{{ line.product?.name ?? 'Product' }}</NuxtLink>
                  <span v-else class="font-semibold opacity-60">Product unavailable</span>
                  <div v-if="line.variant?.color" class="text-xs opacity-60">{{ line.variant.color }}</div>
                  <div v-if="line.product?.vendor" class="text-xs opacity-60">
                    {{ line.product.vendor.business_name }}
                  </div>
                </div>
                <button class="btn btn-ghost btn-xs rounded-full text-error" @click="onRemove(line.id)">
                  <Icon name="lucide:trash-2" class="h-3.5 w-3.5" />
                </button>
              </div>

              <div v-if="line.stock_warning" class="mt-2 text-xs text-warning">
                Only {{ line.variant?.stock_quantity ?? 0 }} in stock — reduce quantity before checkout.
              </div>

              <div class="mt-3 flex flex-wrap items-end justify-between gap-3">
                <div class="flex items-center gap-2 rounded-full border border-base-300 px-2 py-1">
                  <button
                    class="btn btn-circle btn-ghost btn-xs"
                    :disabled="line.quantity <= 1 || cart.submitting"
                    @click="changeQty(line.id, line.quantity - 1)"
                  >
                    <Icon name="lucide:minus" class="h-3 w-3" />
                  </button>
                  <span class="w-8 text-center text-sm font-semibold">{{ line.quantity }}</span>
                  <button
                    class="btn btn-circle btn-ghost btn-xs"
                    :disabled="cart.submitting"
                    @click="changeQty(line.id, line.quantity + 1)"
                  >
                    <Icon name="lucide:plus" class="h-3 w-3" />
                  </button>
                </div>
                <div class="text-right">
                  <div class="text-base font-bold text-primary">{{ currency.format(line.line_total_usd) }}</div>
                  <div class="text-xs opacity-60">
                    {{ currency.format(line.unit_price_usd) }} each
                  </div>
                </div>
              </div>
            </div>
          </li>
        </ul>
      </div>

      <aside class="h-fit rounded-3xl border border-base-300 bg-base-100 p-6">
        <h2 class="text-sm font-semibold uppercase tracking-wider opacity-70">Order summary</h2>
        <dl class="mt-4 space-y-2 text-sm">
          <div class="flex justify-between"><dt>Subtotal</dt><dd>{{ currency.format(cart.subtotalUsd) }}</dd></div>
          <div class="flex justify-between">
            <dt>Service charge</dt>
            <dd>{{ currency.format(cart.serviceChargeUsd) }}</dd>
          </div>
          <div class="flex justify-between text-xs opacity-70">
            <dt>Delivery</dt>
            <dd class="text-right">Calculated at checkout</dd>
          </div>
        </dl>
        <div class="mt-4 flex items-baseline justify-between border-t border-base-300 pt-4">
          <span class="text-sm font-semibold">Items total</span>
          <span class="text-2xl font-extrabold text-primary">{{ currency.format(cart.itemsTotalUsd) }}</span>
        </div>
        <p class="mt-1 text-xs opacity-60">Delivery fee depends on your delivery city.</p>
        <button
          class="btn btn-primary btn-lg mt-4 w-full rounded-full"
          :disabled="hasStockIssue || cart.submitting"
          @click="goCheckout"
        >
          Proceed to checkout
          <Icon name="lucide:arrow-right" class="h-4 w-4" />
        </button>
        <p v-if="hasStockIssue" class="mt-2 text-xs text-warning">
          Resolve stock issues above before checking out.
        </p>
        <NuxtLink to="/products" class="link link-hover mt-3 block text-center text-xs">
          ← Continue shopping
        </NuxtLink>
      </aside>
    </div>
  </section>
</template>

<script setup lang="ts">
import { cartLineImageUrl, type CartLine } from '~/stores/cart';

definePageMeta({ middleware: ['auth'] });
useHead({ title: 'Cart — Delivo' });

const cart = useCartStore();
const currency = useCurrencyStore();
const router = useRouter();

onMounted(() => cart.refresh());

const hasStockIssue = computed(() => cart.cart.items.some((l) => l.stock_warning));

const imageUrl = (line: CartLine) => cartLineImageUrl(line);

const changeQty = async (id: number, qty: number) => {
  if (qty < 1) return;
  await cart.updateQty(id, qty);
};

const onRemove = async (id: number) => {
  if (!window.confirm('Remove this item?')) return;
  await cart.remove(id);
};

const goCheckout = () => router.push('/checkout');
</script>
