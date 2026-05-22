<template>
  <section class="mx-auto max-w-3xl px-4 py-10">
    <div v-if="orderStore.loading && !orderStore.current" class="flex justify-center py-16">
      <span class="loading loading-spinner loading-lg"></span>
    </div>

    <div v-else-if="!orderStore.current" class="rounded-3xl border border-base-300 bg-base-100 p-8 text-center">
      <p>Order not found.</p>
      <NuxtLink to="/account/orders" class="btn btn-primary mt-4 rounded-full">My orders</NuxtLink>
    </div>

    <div v-else>
      <div class="rounded-3xl border border-success/40 bg-success/5 p-6">
        <div class="flex items-start gap-3">
          <Icon name="lucide:circle-check-big" class="h-6 w-6 text-success" />
          <div>
            <div class="text-lg font-bold">Order placed!</div>
            <div class="text-sm opacity-80">
              Order <span class="font-mono font-semibold">{{ orderStore.current.order_number }}</span>
              is awaiting payment confirmation.
            </div>
          </div>
        </div>
      </div>

      <div class="mt-6 rounded-3xl border border-base-300 bg-base-100 p-6">
        <h2 class="text-sm font-semibold uppercase tracking-wider opacity-70">Pay this amount via {{ orderStore.current.mobile_wallet?.name ?? 'mobile wallet' }}</h2>
        <div class="mt-3 text-4xl font-extrabold text-primary">
          {{ currency.format(orderStore.current.total_usd) }}
        </div>
        <div v-if="currency.code === 'USD' && currency.hasZwgRate" class="mt-1 text-sm opacity-70">
          ≈ ZWG {{ zwgEquivalent }}
        </div>

        <dl class="mt-6 grid gap-3 text-sm sm:grid-cols-2">
          <div>
            <dt class="font-semibold uppercase text-xs opacity-70">Payment reference</dt>
            <dd class="mt-1 flex items-center gap-2 font-mono text-base">
              {{ orderStore.current.payment_reference }}
              <button class="btn btn-ghost btn-xs rounded-full" @click="copy(orderStore.current.payment_reference)">
                <Icon name="lucide:copy" class="h-3.5 w-3.5" />
              </button>
            </dd>
          </div>
          <div>
            <dt class="font-semibold uppercase text-xs opacity-70">Wallet</dt>
            <dd class="mt-1">{{ orderStore.current.mobile_wallet?.name }} ({{ orderStore.current.mobile_wallet?.code }})</dd>
          </div>
        </dl>

        <div class="mt-6 rounded-2xl border border-base-300 bg-base-200/40 p-4 text-sm">
          <div class="font-semibold">How it works</div>
          <ol class="mt-2 list-decimal space-y-1 pl-5 opacity-80">
            <li>Open your {{ orderStore.current.mobile_wallet?.name }} app or USSD.</li>
            <li>Send <strong>{{ currency.format(orderStore.current.total_usd) }}</strong> to the Delivo merchant number (we'll share this in-app when wallet setup is finalised).</li>
            <li>Use the reference <strong class="font-mono">{{ orderStore.current.payment_reference }}</strong>.</li>
            <li>Delivo will confirm receipt and dispatch your order — you can track status under <NuxtLink to="/account/orders" class="link link-primary">My orders</NuxtLink>.</li>
          </ol>
        </div>
      </div>

      <div class="mt-6 rounded-3xl border border-base-300 bg-base-100 p-6">
        <h2 class="text-sm font-semibold uppercase tracking-wider opacity-70">Delivery to</h2>
        <div class="mt-2 text-sm">
          <div class="font-semibold">{{ orderStore.current.ship_recipient_name }}</div>
          <div class="opacity-80">{{ orderStore.current.ship_street }}, {{ orderStore.current.ship_suburb }}, {{ orderStore.current.ship_city }}</div>
          <div class="opacity-60">{{ orderStore.current.ship_recipient_phone }}</div>
          <div v-if="orderStore.current.ship_notes" class="mt-1 opacity-60">{{ orderStore.current.ship_notes }}</div>
        </div>
      </div>

      <div class="mt-6 flex flex-wrap justify-end gap-2">
        <NuxtLink to="/products" class="btn btn-ghost rounded-full">Keep shopping</NuxtLink>
        <NuxtLink to="/account/orders" class="btn btn-primary rounded-full">My orders →</NuxtLink>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
definePageMeta({ middleware: ['auth'] });

const route = useRoute();
const orderStore = useOrderStore();
const currency = useCurrencyStore();
const toast = useToast();

const orderNumber = computed(() => route.params.orderNumber as string);

onMounted(() => orderStore.fetchOne(orderNumber.value));

useHead({ title: () => `Order ${orderNumber.value} — Delivo` });

const zwgEquivalent = computed(() => {
  if (!currency.hasZwgRate || !orderStore.current) return '';
  const usd = Number(orderStore.current.total_usd);
  const zwg = usd * (currency.usdToZwgRate as unknown as number);
  return zwg.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
});

const copy = async (text: string) => {
  try {
    await navigator.clipboard.writeText(text);
    toast.success({ title: 'Copied', message: text, position: 'topRight', layout: 2 });
  } catch {
    toast.info({ title: 'Copy failed', message: 'Select and copy the reference manually.', position: 'topRight', layout: 2 });
  }
};
</script>
