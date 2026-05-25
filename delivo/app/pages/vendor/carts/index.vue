<template>
  <div>
    <div>
      <h1 class="text-2xl font-extrabold tracking-tight">Active carts</h1>
      <p class="mt-1 text-sm opacity-70">
        Customers with your products in their cart who haven't checked out yet. Reach out to
        convert them — oldest carts (most likely to abandon) are shown first.
      </p>
    </div>

    <div class="mt-4 rounded-2xl border border-info/40 bg-info/5 px-4 py-2 text-xs">
      <Icon name="lucide:info" class="mr-1 inline h-3.5 w-3.5" />
      Customer contact details are shared with you so you can follow up on abandoned carts. Use
      this for legitimate sales outreach only — repeated unwanted contact may result in your
      vendor account being suspended.
    </div>

    <div v-if="loading" class="flex justify-center py-16">
      <span class="loading loading-spinner loading-lg"></span>
    </div>

    <div v-else-if="!rows.length" class="mt-6 rounded-3xl border border-dashed border-base-300 p-12 text-center text-sm opacity-70">
      No active carts right now.
    </div>

    <div v-else class="mt-6 grid gap-4">
      <article v-for="r in rows" :key="r.cart_id" class="rounded-3xl border border-base-300 bg-base-100 p-5">
        <header class="flex flex-wrap items-start justify-between gap-3">
          <div>
            <div class="text-lg font-bold">{{ r.customer.name }}</div>
            <div class="mt-1 flex flex-wrap items-center gap-3 text-xs">
              <a v-if="r.customer.phone" :href="`tel:${r.customer.phone}`" class="link link-primary">
                <Icon name="lucide:phone" class="mr-1 inline h-3 w-3" />{{ r.customer.phone }}
              </a>
              <a v-if="r.customer.phone" :href="whatsappLink(r.customer.phone, r)" target="_blank" class="link link-success">
                <Icon name="lucide:message-circle" class="mr-1 inline h-3 w-3" />WhatsApp
              </a>
              <a v-if="r.customer.email" :href="`mailto:${r.customer.email}`" class="link">
                <Icon name="lucide:mail" class="mr-1 inline h-3 w-3" />{{ r.customer.email }}
              </a>
            </div>
          </div>
          <div class="text-right">
            <div class="text-xs uppercase tracking-wider opacity-60">Your cart value</div>
            <div class="text-2xl font-extrabold text-primary">${{ Number(r.vendor_total_usd).toFixed(2) }}</div>
            <div class="text-xs opacity-60">{{ r.item_count }} item{{ r.item_count === 1 ? '' : 's' }} · sitting {{ relativeTime(r.oldest_item_at) }}</div>
          </div>
        </header>

        <ul class="mt-4 divide-y divide-base-300 text-sm">
          <li v-for="i in r.items" :key="i.id" class="flex items-center justify-between gap-3 py-2">
            <div>
              <div class="font-medium">{{ i.product?.name ?? '—' }}</div>
              <div class="text-xs opacity-60">
                {{ i.variant?.color ? `${i.variant.color} · ` : '' }}{{ i.quantity }} × ${{ Number(i.unit_price_usd).toFixed(2) }}
                <span v-if="i.variant && i.quantity > i.variant.stock_quantity" class="ml-2 text-warning">
                  ⚠ exceeds stock ({{ i.variant.stock_quantity }} left)
                </span>
              </div>
            </div>
            <div class="font-semibold">${{ Number(i.line_total_usd).toFixed(2) }}</div>
          </li>
        </ul>
      </article>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ layout: 'vendor', middleware: ['auth'] });
useHead({ title: 'Active carts — Delivo Vendor' });

interface CartRow {
  cart_id: number;
  customer: { id: number; name: string; email: string | null; phone: string | null };
  items: Array<{
    id: number;
    product: { id: number; name: string; slug: string } | null;
    variant: { id: number; color: string | null; sku: string | null; stock_quantity: number } | null;
    quantity: number;
    unit_price_usd: string;
    line_total_usd: string;
    updated_at: string | null;
  }>;
  vendor_total_usd: string;
  item_count: number;
  oldest_item_at: string | null;
  cart_updated_at: string | null;
}

const { listCarts } = useVendorOrderHelper();
const toast = useToast();

const loading = ref(false);
const rows = ref<CartRow[]>([]);

onMounted(async () => {
  loading.value = true;
  const { data, error } = await listCarts();
  if (!error.value) {
    rows.value = ((data.value as any)?.data ?? []) as CartRow[];
  } else {
    toast.error({
      title: 'Error',
      message: (error.value as any)?.data?.message || 'Failed to load carts.',
      position: 'topRight',
      layout: 2,
    });
  }
  loading.value = false;
});

const relativeTime = (iso: string | null): string => {
  if (!iso) return 'a while';
  const ms = Date.now() - new Date(iso).getTime();
  const mins = Math.floor(ms / 60000);
  if (mins < 60) return `${mins}m`;
  const hrs = Math.floor(mins / 60);
  if (hrs < 24) return `${hrs}h`;
  const days = Math.floor(hrs / 24);
  return `${days}d`;
};

const whatsappLink = (phone: string, r: CartRow): string => {
  const cleaned = phone.replace(/[^\d+]/g, '').replace(/^\+/, '');
  const itemNames = r.items.map((i) => i.product?.name).filter(Boolean).slice(0, 3).join(', ');
  const text = `Hi ${r.customer.name}, I noticed you have ${itemNames} in your Delivo cart — happy to help if you have any questions.`;
  return `https://wa.me/${cleaned}?text=${encodeURIComponent(text)}`;
};
</script>
