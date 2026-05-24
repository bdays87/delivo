<template>
  <div class="mx-auto max-w-7xl px-4 py-10">
    <div class="flex flex-wrap items-end justify-between gap-3">
      <div>
        <h1 class="text-3xl font-extrabold tracking-tight md:text-4xl">Influencer dashboard</h1>
        <p class="mt-1 text-sm opacity-70">
          Browse products that pay an influencer commission, generate your unique code, and share
          it across your channels.
        </p>
      </div>
      <NuxtLink to="/influencers/apply" class="btn btn-ghost rounded-full">
        <Icon name="lucide:user-round-cog" class="h-4 w-4" /> Profile & handles
      </NuxtLink>
    </div>

    <div class="mt-6 flex flex-wrap gap-2">
      <button
        :class="['btn btn-sm rounded-full', tab === 'browse' ? 'btn-primary' : 'btn-ghost bg-base-100']"
        @click="tab = 'browse'"
      >
        Browse products
      </button>
      <button
        :class="['btn btn-sm rounded-full', tab === 'codes' ? 'btn-primary' : 'btn-ghost bg-base-100']"
        @click="tab = 'codes'"
      >
        My codes
        <span v-if="codes.length" class="badge badge-sm ml-1">{{ codes.length }}</span>
      </button>
    </div>

    <div v-if="loading" class="flex justify-center py-16">
      <span class="loading loading-spinner loading-lg"></span>
    </div>

    <!-- Browse -->
    <div v-else-if="tab === 'browse'">
      <div v-if="!products.length" class="mt-6 rounded-3xl border border-dashed border-base-300 p-12 text-center">
        <Icon name="lucide:package-search" class="mx-auto h-10 w-10 opacity-30" />
        <p class="mt-3 text-sm opacity-70">No products are paying influencer commission yet.</p>
      </div>
      <div v-else class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        <article v-for="p in products" :key="p.id" class="flex flex-col rounded-3xl border border-base-300 bg-base-100 p-5">
          <div class="flex items-start gap-3">
            <div class="h-16 w-16 shrink-0 overflow-hidden rounded-2xl bg-base-200">
              <img v-if="primaryUrl(p)" :src="primaryUrl(p)!" :alt="p.name" class="h-full w-full object-cover" />
              <div v-else class="grid h-full place-items-center text-base-content/30">
                <Icon name="lucide:image-off" class="h-5 w-5" />
              </div>
            </div>
            <div class="min-w-0">
              <h3 class="truncate font-bold">{{ p.name }}</h3>
              <p class="truncate text-xs opacity-60">{{ p.vendor?.business_name ?? '—' }}</p>
            </div>
          </div>

          <dl class="mt-3 grid gap-1 text-sm">
            <div class="flex justify-between"><dt class="opacity-60">Your commission</dt><dd class="font-semibold text-primary">{{ p.affiliate_influencer_pct }}%</dd></div>
            <div class="flex justify-between"><dt class="opacity-60">Buyer discount</dt><dd>{{ p.affiliate_buyer_discount_pct }}%</dd></div>
          </dl>

          <div class="mt-4 flex flex-wrap items-center justify-between gap-2">
            <NuxtLink :to="`/products/${p.slug}`" class="link link-hover text-xs opacity-70">View product →</NuxtLink>
            <div v-if="p.existing_code" class="flex items-center gap-2 rounded-2xl bg-base-200/60 px-3 py-1">
              <span class="font-mono text-xs">{{ p.existing_code }}</span>
              <button class="btn btn-circle btn-xs btn-ghost" @click="copy(p.existing_code!)">
                <Icon name="lucide:copy" class="h-3 w-3" />
              </button>
            </div>
            <button
              v-else
              class="btn btn-primary btn-sm rounded-full"
              :disabled="submitting === p.id"
              @click="onGenerate(p.id)"
            >
              <span v-if="submitting === p.id" class="loading loading-spinner loading-xs"></span>
              <Icon v-else name="lucide:plus" class="h-3.5 w-3.5" />
              Get my code
            </button>
          </div>
        </article>
      </div>
    </div>

    <!-- Codes -->
    <div v-else>
      <div v-if="!codes.length" class="mt-6 rounded-3xl border border-dashed border-base-300 p-12 text-center">
        <Icon name="lucide:ticket-percent" class="mx-auto h-10 w-10 opacity-30" />
        <p class="mt-3 text-sm opacity-70">No codes yet. Generate one from a product in the Browse tab.</p>
      </div>
      <div v-else class="mt-6 overflow-hidden rounded-3xl border border-base-300 bg-base-100">
        <table class="table">
          <thead class="bg-base-200/50 text-xs uppercase tracking-wider opacity-70">
            <tr>
              <th>Code</th>
              <th>Product</th>
              <th>Vendor</th>
              <th>Your %</th>
              <th>Buyer %</th>
              <th>Usage</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="c in codes" :key="c.id">
              <td>
                <div class="flex items-center gap-2">
                  <span class="font-mono font-semibold">{{ c.code }}</span>
                  <button class="btn btn-circle btn-xs btn-ghost" @click="copy(c.code)">
                    <Icon name="lucide:copy" class="h-3 w-3" />
                  </button>
                </div>
              </td>
              <td>{{ c.product?.name ?? '—' }}</td>
              <td>{{ c.product?.vendor?.business_name ?? '—' }}</td>
              <td>{{ Number(c.influencer_commission_pct).toFixed(2) }}%</td>
              <td>{{ Number(c.buyer_discount_pct).toFixed(2) }}%</td>
              <td>{{ c.usage_count }}<span v-if="c.usage_limit"> / {{ c.usage_limit }}</span></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ middleware: ['auth'] });
useHead({ title: 'Influencer dashboard — Delivo' });

interface ProductRow {
  id: number;
  name: string;
  slug: string;
  vendor: { id: number; business_name: string } | null;
  category: { id: number; name: string } | null;
  affiliate_influencer_pct: number;
  affiliate_buyer_discount_pct: number;
  primary_image: { disk: string; path: string } | null;
  existing_code: string | null;
}

interface CodeRow {
  id: number;
  code: string;
  buyer_discount_pct: string | number;
  influencer_commission_pct: string | number;
  usage_count: number;
  usage_limit: number | null;
  product?: { id: number; name: string; slug: string; vendor?: { id: number; business_name: string } | null } | null;
}

const { listProducts, generateCode, listCodes } = useInfluencerHelper();
const toast = useToast();
const cfg = useRuntimeConfig();

const tab = ref<'browse' | 'codes'>('browse');
const loading = ref(false);
const submitting = ref<number | null>(null);
const products = ref<ProductRow[]>([]);
const codes = ref<CodeRow[]>([]);

const fetchAll = async () => {
  loading.value = true;
  const [{ data: pr }, { data: cd }] = await Promise.all([listProducts(), listCodes()]);
  products.value = ((pr.value as any)?.data ?? []) as ProductRow[];
  codes.value = ((cd.value as any)?.data ?? []) as CodeRow[];
  loading.value = false;
};

onMounted(fetchAll);

const primaryUrl = (p: ProductRow): string | null => {
  if (!p.primary_image) return null;
  return `${cfg.public.apiBase}/storage/${p.primary_image.path}`;
};

const onGenerate = async (productId: number) => {
  submitting.value = productId;
  const { data, status, error } = await generateCode(productId);
  if (status?.value) {
    const c = (data.value as any)?.data;
    toast.success({ title: 'Code ready', message: c?.code ?? '', position: 'topRight', layout: 2 });
    await fetchAll();
  } else {
    toast.error({
      title: 'Error',
      message: (error?.value as any)?.data?.message || 'Could not generate code.',
      position: 'topRight',
      layout: 2,
    });
  }
  submitting.value = null;
};

const copy = async (text: string) => {
  try {
    await navigator.clipboard.writeText(text);
    toast.success({ title: 'Copied', message: text, position: 'topRight', layout: 2 });
  } catch {
    toast.info({ title: 'Copy failed', message: 'Select and copy the code manually.', position: 'topRight', layout: 2 });
  }
};
</script>
