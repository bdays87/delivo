<template>
  <section class="mx-auto max-w-7xl px-4 py-10">
    <h1 class="text-3xl font-extrabold tracking-tight md:text-4xl">Checkout</h1>
    <p class="mt-1 text-sm opacity-70">Confirm delivery, choose how you'll pay, then place your order.</p>

    <div v-if="loading" class="flex justify-center py-16">
      <span class="loading loading-spinner loading-lg"></span>
    </div>

    <div v-else-if="!cart.cart.items.length" class="mt-8 rounded-3xl border border-dashed border-base-300 p-12 text-center">
      <p class="text-sm opacity-70">Your cart is empty. Add something before checking out.</p>
      <NuxtLink to="/products" class="btn btn-primary mt-4 rounded-full">Shop products</NuxtLink>
    </div>

    <div v-else class="mt-8 grid gap-6 lg:grid-cols-[1fr_360px]">
      <div class="space-y-6">
        <section class="rounded-3xl border border-base-300 bg-base-100 p-6">
          <h2 class="text-lg font-bold">Delivery method</h2>
          <p class="mt-1 text-sm opacity-70">
            How would you like to receive your order?
          </p>
          <div class="mt-4 grid gap-3 sm:grid-cols-2">
            <label
              :class="[
                'flex cursor-pointer items-start gap-3 rounded-2xl border-2 p-4 transition',
                deliveryMethod === 'HOME_DELIVERY' ? 'border-primary bg-primary/5' : 'border-base-300 bg-base-100 hover:border-primary/50',
              ]"
            >
              <input v-model="deliveryMethod" type="radio" value="HOME_DELIVERY" class="radio radio-primary mt-1" />
              <div>
                <div class="font-semibold">Home delivery</div>
                <div class="text-xs opacity-70">A rider brings it to your door. Delivery fee calculated below.</div>
              </div>
            </label>
            <label
              :class="[
                'flex cursor-pointer items-start gap-3 rounded-2xl border-2 p-4 transition',
                deliveryMethod === 'SELF_PICKUP' ? 'border-primary bg-primary/5' : 'border-base-300 bg-base-100 hover:border-primary/50',
              ]"
            >
              <input v-model="deliveryMethod" type="radio" value="SELF_PICKUP" class="radio radio-primary mt-1" />
              <div>
                <div class="font-semibold">Collect from vendor</div>
                <div class="text-xs opacity-70">Pick up in person. No delivery fee.</div>
              </div>
            </label>
          </div>
        </section>

        <section class="rounded-3xl border border-base-300 bg-base-100 p-6">
          <div class="flex items-start justify-between gap-3">
            <h2 class="text-lg font-bold">{{ deliveryMethod === 'SELF_PICKUP' ? 'Your contact details' : 'Delivery address' }}</h2>
            <button class="btn btn-ghost btn-sm rounded-full" @click="showAddForm = !showAddForm">
              <Icon name="lucide:plus" class="h-4 w-4" />
              Add new
            </button>
          </div>

          <div v-if="!address.addresses.length && !showAddForm" class="mt-4 text-sm opacity-70">
            No saved addresses yet. <button class="link link-primary" @click="showAddForm = true">Add one →</button>
          </div>

          <div v-if="address.addresses.length" class="mt-4 grid gap-3 md:grid-cols-2">
            <label
              v-for="a in address.addresses"
              :key="a.id"
              :class="[
                'flex cursor-pointer items-start gap-3 rounded-2xl border-2 p-4 transition',
                selectedAddressId === a.id ? 'border-primary bg-primary/5' : 'border-base-300 bg-base-100 hover:border-primary/50',
              ]"
            >
              <input
                v-model="selectedAddressId"
                type="radio"
                :value="a.id"
                class="radio radio-primary mt-1"
              />
              <div class="flex-1">
                <div class="flex items-center gap-2">
                  <span class="font-semibold">{{ a.label || 'Address' }}</span>
                  <span v-if="a.is_default" class="badge badge-xs badge-primary">Default</span>
                </div>
                <div class="text-sm">{{ a.recipient_name }}</div>
                <div class="text-xs opacity-70">{{ a.street }}, {{ a.suburb }}, {{ a.city }}</div>
                <div class="text-xs opacity-60">{{ a.recipient_phone }}</div>
              </div>
            </label>
          </div>

          <form v-if="showAddForm" class="mt-4 grid gap-3 md:grid-cols-2" @submit.prevent="saveNewAddress">
            <label class="fieldset md:col-span-2">
              <span class="fieldset-legend">Label (optional)</span>
              <input v-model="form.label" type="text" placeholder="Home / Office" class="input input-bordered" />
            </label>
            <label class="fieldset">
              <span class="fieldset-legend">Recipient name</span>
              <input v-model="form.recipient_name" type="text" class="input input-bordered" required />
            </label>
            <label class="fieldset">
              <span class="fieldset-legend">Phone</span>
              <input v-model="form.recipient_phone" type="text" placeholder="+263 …" class="input input-bordered" required />
            </label>
            <label class="fieldset">
              <span class="fieldset-legend">City</span>
              <select v-model="form.city" class="select select-bordered" required>
                <option value="" disabled>Pick a covered city…</option>
                <option v-for="a in coverage.areas" :key="a.id" :value="a.city">{{ a.city }}</option>
              </select>
              <span class="text-xs opacity-60">
                Delivo only delivers to listed cities — pick one to continue.
              </span>
            </label>
            <label class="fieldset">
              <span class="fieldset-legend">Suburb</span>
              <input v-model="form.suburb" type="text" placeholder="Avondale" class="input input-bordered" required />
            </label>
            <label class="fieldset md:col-span-2">
              <span class="fieldset-legend">Street</span>
              <input v-model="form.street" type="text" placeholder="House number, road" class="input input-bordered" required />
            </label>
            <label class="fieldset md:col-span-2">
              <span class="fieldset-legend">Notes (optional)</span>
              <textarea v-model="form.notes" rows="2" class="textarea textarea-bordered" placeholder="Gate code, landmarks…" />
            </label>
            <div class="md:col-span-2 flex justify-end gap-2">
              <button type="button" class="btn rounded-full" @click="showAddForm = false">Cancel</button>
              <button type="submit" class="btn btn-primary rounded-full" :disabled="address.submitting">
                Save address
              </button>
            </div>
          </form>
        </section>

        <section class="rounded-3xl border border-base-300 bg-base-100 p-6">
          <h2 class="text-lg font-bold">Payment</h2>
          <p class="mt-1 text-sm opacity-70">
            We'll generate a payment instruction with the amount and reference. Send the money to the wallet
            below and our team will confirm receipt manually in this version.
          </p>

          <div class="mt-4 grid gap-3 sm:grid-cols-2">
            <label
              v-for="w in mobileWallets"
              :key="w.id"
              :class="[
                'flex cursor-pointer items-center gap-3 rounded-2xl border-2 p-4 transition',
                selectedWalletId === w.id ? 'border-primary bg-primary/5' : 'border-base-300 bg-base-100 hover:border-primary/50',
              ]"
            >
              <input v-model="selectedWalletId" type="radio" :value="w.id" class="radio radio-primary" />
              <div>
                <div class="font-semibold">{{ w.name }}</div>
                <div class="text-xs opacity-60">{{ w.code }}</div>
              </div>
            </label>
          </div>
        </section>
      </div>

      <aside class="h-fit rounded-3xl border border-base-300 bg-base-100 p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
          <h2 class="text-sm font-semibold uppercase tracking-wider opacity-70">Order summary</h2>
          <div class="join">
            <button
              type="button"
              :class="['btn btn-sm join-item rounded-l-full', currency.code === 'USD' ? 'btn-primary' : 'btn-ghost']"
              @click="currency.setCode('USD')"
            >USD</button>
            <button
              type="button"
              :class="['btn btn-sm join-item rounded-r-full', currency.code === 'ZWG' ? 'btn-primary' : 'btn-ghost']"
              :disabled="!currency.hasZwgRate"
              :title="currency.hasZwgRate ? '' : 'ZWG rate not set yet'"
              @click="currency.setCode('ZWG')"
            >ZWG</button>
          </div>
        </div>
        <p class="mt-2 text-xs opacity-60">
          Choose how you want amounts displayed. Vendor prices are stored in USD;
          <span v-if="currency.hasZwgRate">ZWG uses Delivo's published rate (1 USD = {{ currency.usdToZwgRate }} ZWG).</span>
          <span v-else>ZWG is unavailable until an exchange rate is published.</span>
        </p>
        <ul class="mt-3 space-y-2 text-sm">
          <li v-for="line in cart.cart.items" :key="line.id" class="flex justify-between gap-3">
            <div class="min-w-0">
              <div class="truncate font-medium">{{ line.product?.name ?? 'Item' }}</div>
              <div class="text-xs opacity-60">{{ line.quantity }} × {{ currency.format(line.unit_price_usd) }}</div>
            </div>
            <div class="text-right font-semibold">{{ currency.format(line.line_total_usd) }}</div>
          </li>
        </ul>

        <div v-if="cart.appliedCoupon" class="mt-4 flex items-center justify-between gap-3 rounded-2xl border border-success/40 bg-success/5 p-3 text-xs">
          <div>
            <div class="uppercase tracking-wider text-success">Code applied</div>
            <div class="font-mono font-semibold">{{ cart.appliedCoupon.code }}</div>
          </div>
          <div class="text-right">
            <div class="font-semibold text-success">{{ cart.appliedCoupon.buyer_discount_pct }}% off</div>
          </div>
        </div>

        <dl class="mt-4 space-y-2 border-t border-base-300 pt-4 text-sm">
          <div class="flex justify-between">
            <dt>{{ cart.totalDiscountUsd > 0 ? 'Subtotal (after code)' : 'Subtotal' }}</dt>
            <dd>{{ currency.format(cart.subtotalUsd) }}</dd>
          </div>
          <div v-if="cart.totalDiscountUsd > 0" class="flex justify-between text-success">
            <dt>You saved</dt>
            <dd>− {{ currency.format(cart.totalDiscountUsd) }}</dd>
          </div>
          <div class="flex justify-between">
            <dt>Service charge</dt>
            <dd>{{ currency.format(cart.serviceChargeUsd) }}</dd>
          </div>
          <div class="flex justify-between">
            <dt>Delivery</dt>
            <dd>
              <span v-if="quoteLoading" class="loading loading-spinner loading-xs"></span>
              <span v-else-if="quote && quote.shipping_usd !== null">{{ currency.format(quote.shipping_usd) }}</span>
              <span v-else-if="quote?.is_covered === false" class="text-xs text-error">Not covered</span>
              <span v-else class="text-xs opacity-60">Choose address</span>
            </dd>
          </div>
          <div v-if="quote?.shipments?.length" class="mt-2 space-y-1 rounded-2xl bg-base-200/40 px-3 py-2 text-xs opacity-80">
            <div v-for="s in quote.shipments" :key="s.vendor_id" class="flex items-baseline justify-between gap-3">
              <div class="min-w-0">
                <div class="truncate font-medium">{{ s.vendor_name }}</div>
                <div v-if="s.hub" class="truncate opacity-70">
                  from {{ s.hub.name ?? s.hub.city }}
                  <span v-if="s.distance_km !== null">· {{ s.distance_km }} km</span>
                </div>
                <div v-else-if="s.reason" class="text-error">{{ s.reason }}</div>
              </div>
              <div class="shrink-0 font-semibold">
                <span v-if="s.fee_usd !== null">{{ currency.format(s.fee_usd) }}</span>
                <span v-else class="text-error">—</span>
              </div>
            </div>
          </div>
        </dl>
        <div class="mt-4 flex items-baseline justify-between border-t border-base-300 pt-4">
          <span class="text-sm font-semibold">Total</span>
          <span class="text-2xl font-extrabold text-primary">
            <span v-if="quote && quote.total_usd !== null">{{ currency.format(quote.total_usd) }}</span>
            <span v-else class="text-base opacity-60">—</span>
          </span>
        </div>

        <div
          v-if="quote?.is_covered === false"
          class="mt-4 rounded-2xl border border-error/40 bg-error/5 p-3 text-xs text-error"
        >
          We don't yet deliver to this address's city. Choose a different address — or
          <NuxtLink to="/cart" class="link">go back to your cart</NuxtLink> while we expand.
        </div>
        <button
          class="btn btn-primary btn-lg mt-4 w-full rounded-full"
          :disabled="!canPlace || placing"
          @click="onPlaceOrder"
        >
          <span v-if="placing" class="loading loading-spinner loading-xs"></span>
          Place order
        </button>
        <p v-if="errorMessage" class="mt-3 text-xs text-error">{{ errorMessage }}</p>
        <NuxtLink to="/cart" class="link link-hover mt-3 block text-center text-xs">
          ← Back to cart
        </NuxtLink>
      </aside>
    </div>
  </section>
</template>

<script setup lang="ts">
import type { AddressPayload } from '~/stores/address';

definePageMeta({ middleware: ['auth'] });
useHead({ title: 'Checkout — Delivo' });

interface MobileWallet { id: number; code: string; name: string; }

interface ShipmentRow {
  vendor_id: number;
  vendor_name: string;
  vendor_city: string;
  hub: { id: number; city: string; name: string | null; address: string | null } | null;
  distance_km: number | null;
  fee_usd: number | null;
  band_id: number | null;
  is_covered: boolean | null;
  reason: string | null;
}

interface Quote {
  subtotal_usd: number;
  service_charge_usd: number;
  shipping_usd: number | null;
  is_covered: boolean | null;
  items_total_usd: number;
  total_usd: number | null;
  shipments: ShipmentRow[];
}

const cart = useCartStore();
const address = useAddressStore();
const currency = useCurrencyStore();
const coverage = useCoverageStore();
const { quoteOrder, placeOrder } = useCheckoutHelper();
const { listProducts } = useProductHelper();
const router = useRouter();
const toast = useToast();
const client = useSanctumClient();

const loading = ref(false);
const placing = ref(false);
const showAddForm = ref(false);
const selectedAddressId = ref<number | null>(null);
const selectedWalletId = ref<number | null>(null);
const deliveryMethod = ref<'HOME_DELIVERY' | 'SELF_PICKUP'>('HOME_DELIVERY');
const mobileWallets = ref<MobileWallet[]>([]);
const errorMessage = ref('');
const quote = ref<Quote | null>(null);
const quoteLoading = ref(false);

const blankForm = (): AddressPayload => ({
  label: '',
  recipient_name: '',
  recipient_phone: '',
  city: '',
  suburb: '',
  street: '',
  notes: '',
});
const form = reactive(blankForm());

onMounted(async () => {
  loading.value = true;
  await Promise.all([
    cart.refresh(true),
    address.fetchAll(),
    fetchWallets(),
    coverage.ensureLoaded(),
    ensureExchangeRate(),
  ]);
  selectedAddressId.value = address.defaultAddress?.id ?? null;
  if (!address.addresses.length) showAddForm.value = true;
  loading.value = false;
  if (selectedAddressId.value) await refreshQuote();
});

watch(selectedAddressId, async (next) => {
  if (next) await refreshQuote();
  else quote.value = null;
});

watch(deliveryMethod, async () => {
  if (selectedAddressId.value) await refreshQuote();
});

const refreshQuote = async () => {
  if (!selectedAddressId.value) return;
  quoteLoading.value = true;
  const { data, status } = await quoteOrder(selectedAddressId.value, deliveryMethod.value);
  if (status?.value) {
    const payload = (data.value as any)?.data;
    quote.value = payload ? {
      subtotal_usd: Number(payload.subtotal_usd),
      service_charge_usd: Number(payload.service_charge_usd),
      shipping_usd: payload.shipping_usd !== null ? Number(payload.shipping_usd) : null,
      is_covered: payload.is_covered,
      items_total_usd: Number(payload.items_total_usd),
      total_usd: payload.total_usd !== null ? Number(payload.total_usd) : null,
      shipments: payload.shipments ?? [],
    } : null;
  }
  quoteLoading.value = false;
};

const ensureExchangeRate = async () => {
  if (currency.hasZwgRate) return;
  const { data, error } = await listProducts({ per_page: 1 });
  if (!error.value) {
    const payload = (data.value as any)?.data ?? {};
    currency.captureRateFromApi(payload.exchange_rate);
  }
};

const fetchWallets = async () => {
  try {
    const res = await client('/api/v1/mobile-wallets/list', { method: 'GET' });
    mobileWallets.value = (res as any).data ?? [];
    selectedWalletId.value = mobileWallets.value[0]?.id ?? null;
  } catch (err) {
    console.error(err);
  }
};

const canPlace = computed(() => {
  if (!selectedAddressId.value || !selectedWalletId.value) return false;
  if (!cart.cart.items.length) return false;
  if (cart.cart.items.some((l) => l.stock_warning)) return false;
  if (deliveryMethod.value === 'SELF_PICKUP') return true;
  return quote.value?.is_covered === true;
});

const saveNewAddress = async () => {
  if (!form.recipient_name || !form.recipient_phone) return;
  const created = await address.create({
    label: form.label || null,
    recipient_name: form.recipient_name,
    recipient_phone: form.recipient_phone,
    city: form.city,
    suburb: form.suburb,
    street: form.street,
    notes: form.notes || null,
    is_default: address.addresses.length === 0,
  });
  if (created) {
    selectedAddressId.value = created.id;
    Object.assign(form, blankForm());
    showAddForm.value = false;
    await refreshQuote();
  }
};

const onPlaceOrder = async () => {
  if (!selectedAddressId.value || !selectedWalletId.value) return;
  errorMessage.value = '';
  placing.value = true;
  try {
    const { data, status, error } = await placeOrder(selectedAddressId.value, selectedWalletId.value, deliveryMethod.value);
    if (status?.value) {
      const order = (data.value as any)?.data;
      cart.reset();
      if (order?.order_number) {
        router.push(`/checkout/instructions/${order.order_number}`);
      }
      return;
    }
    const errResp = (error?.value as any)?.data;
    const lines = errResp?.data?.lines;
    if (Array.isArray(lines) && lines.length) {
      errorMessage.value = lines
        .map((l: any) => l.product_name
          ? `${l.product_name}: only ${l.available} in stock (you have ${l.requested})`
          : (l.reason ?? 'Item unavailable'))
        .join(' · ');
    } else {
      errorMessage.value = errResp?.message || 'Could not place the order.';
    }
    toast.error({ title: 'Checkout failed', message: errorMessage.value, position: 'topRight', layout: 2 });
  } finally {
    placing.value = false;
  }
};
</script>
