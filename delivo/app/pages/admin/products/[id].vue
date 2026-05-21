<template>
  <div>
    <div v-if="store.loading && !store.current" class="flex justify-center py-16">
      <span class="loading loading-spinner loading-lg"></span>
    </div>

    <div v-else-if="!store.current" class="rounded-3xl border border-base-300 bg-base-100 p-8 text-center">
      <p>Product not found.</p>
      <NuxtLink to="/admin/products" class="btn btn-primary mt-4 rounded-full">Back to queue</NuxtLink>
    </div>

    <div v-else class="space-y-6">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
          <NuxtLink to="/admin/products" class="btn btn-ghost btn-sm rounded-full">
            <Icon name="lucide:arrow-left" class="h-4 w-4" />
          </NuxtLink>
          <div>
            <h1 class="text-2xl font-extrabold tracking-tight">{{ store.current.name }}</h1>
            <p class="mt-1 text-xs opacity-60 font-mono">{{ store.current.slug }}</p>
          </div>
        </div>
        <span :class="['badge badge-lg', statusBadge]">{{ statusLabel }}</span>
      </div>

      <div v-if="store.current.rejection_reason && store.current.status === 'REJECTED'"
        class="rounded-3xl border border-error/40 bg-error/5 p-4 text-sm">
        <div class="font-semibold">Rejection reason</div>
        <div class="opacity-80">{{ store.current.rejection_reason }}</div>
      </div>

      <div class="grid gap-6 lg:grid-cols-3">
        <section class="rounded-3xl border border-base-300 bg-base-100 p-6 lg:col-span-2">
          <h2 class="text-lg font-bold">Images</h2>
          <div v-if="!store.current.images?.length" class="mt-3 text-sm opacity-70">No images uploaded.</div>
          <div v-else class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
            <div
              v-for="img in store.current.images"
              :key="img.id"
              class="relative overflow-hidden rounded-2xl border border-base-300"
            >
              <img :src="imageUrl(img)" :alt="store.current.name" class="aspect-square w-full object-cover" />
              <span v-if="img.is_primary" class="badge badge-primary absolute left-2 top-2">Primary</span>
            </div>
          </div>
        </section>

        <section class="rounded-3xl border border-base-300 bg-base-100 p-6">
          <h2 class="text-lg font-bold">Vendor</h2>
          <div class="mt-3 text-sm">
            <div class="font-semibold">{{ (store.current.vendor as any)?.business_name ?? '—' }}</div>
            <div class="opacity-60 font-mono text-xs">{{ (store.current.vendor as any)?.slug }}</div>
          </div>
          <h2 class="mt-6 text-lg font-bold">Category</h2>
          <div class="mt-3 text-sm">{{ (store.current.category as any)?.name ?? '—' }}</div>

          <h2 class="mt-6 text-lg font-bold">Submitted</h2>
          <div class="mt-3 text-sm">{{ store.current.submitted_at?.slice(0, 10) ?? '—' }}</div>
        </section>
      </div>

      <section class="rounded-3xl border border-base-300 bg-base-100 p-6">
        <h2 class="text-lg font-bold">Description</h2>
        <p class="mt-3 whitespace-pre-line text-sm opacity-80">{{ store.current.description || '—' }}</p>
        <div class="mt-4 grid gap-3 text-sm md:grid-cols-3">
          <div><dt class="opacity-60">SKU</dt><dd>{{ store.current.sku || '—' }}</dd></div>
          <div><dt class="opacity-60">Weight (kg)</dt><dd>{{ store.current.weight_kg ?? '—' }}</dd></div>
        </div>
      </section>

      <section class="rounded-3xl border border-base-300 bg-base-100 p-6">
        <h2 class="text-lg font-bold">Price tiers (USD)</h2>
        <table class="table mt-3">
          <thead class="text-xs uppercase opacity-60">
            <tr><th>Min qty</th><th>Unit price</th></tr>
          </thead>
          <tbody>
            <tr v-for="t in tiersSorted" :key="t.id ?? `${t.min_qty}-${t.unit_price}`">
              <td>{{ t.min_qty }}</td>
              <td>${{ Number(t.unit_price).toFixed(2) }}</td>
            </tr>
          </tbody>
        </table>
      </section>

      <section class="rounded-3xl border border-base-300 bg-base-100 p-6">
        <h2 class="text-lg font-bold">Variants</h2>
        <table class="table mt-3">
          <thead class="text-xs uppercase opacity-60">
            <tr><th>Color</th><th>Stock</th><th>SKU</th></tr>
          </thead>
          <tbody>
            <tr v-for="v in store.current.variants" :key="v.id">
              <td>{{ v.color || '—' }}</td>
              <td>{{ v.stock_quantity }}</td>
              <td class="font-mono text-xs">{{ v.sku || '—' }}</td>
            </tr>
          </tbody>
        </table>
      </section>

      <div class="flex flex-wrap justify-end gap-2">
        <button
          v-if="store.current.status === 'ACTIVE'"
          class="btn btn-warning rounded-full"
          :disabled="store.submitting"
          @click="openTakedown"
        >
          <Icon name="lucide:hand" class="h-4 w-4" /> Take down
        </button>
        <button
          v-if="canReject"
          class="btn btn-error rounded-full"
          :disabled="store.submitting"
          @click="openReject"
        >
          <Icon name="lucide:x" class="h-4 w-4" /> Reject
        </button>
        <button
          v-if="canApprove"
          class="btn btn-primary rounded-full"
          :disabled="store.submitting"
          @click="onApprove"
        >
          <Icon name="lucide:check" class="h-4 w-4" /> Approve
        </button>
      </div>
    </div>

    <dialog :open="modalOpen" class="modal" :class="{ 'modal-open': modalOpen }">
      <div class="modal-box max-w-md">
        <h3 class="text-lg font-bold">{{ modalTitle }}</h3>
        <p class="mt-2 text-sm opacity-70">{{ modalCopy }}</p>
        <label class="fieldset mt-3">
          <span class="fieldset-legend">Reason (vendor will see this)</span>
          <textarea
            v-model="reason"
            rows="4"
            :class="['textarea textarea-bordered w-full', reasonError ? 'textarea-error' : '']"
            placeholder="e.g. Images are blurry; please reupload at higher resolution."
          />
          <span v-if="reasonError" class="text-xs text-red-600">{{ reasonError }}</span>
        </label>
        <div class="modal-action">
          <button type="button" class="btn rounded-full" @click="closeModal">Cancel</button>
          <button
            type="button"
            :class="['btn rounded-full', modalAction === 'reject' ? 'btn-error' : 'btn-warning']"
            :disabled="store.submitting"
            @click="onConfirmModal"
          >
            Confirm
          </button>
        </div>
      </div>
    </dialog>
  </div>
</template>

<script setup lang="ts">
import { productImageUrl, type ProductImage, type ProductStatus, type ProductPriceTier } from '~/stores/product';

definePageMeta({
  layout: 'admin',
  middleware: ['auth', 'admin'],
});

const route = useRoute();
const router = useRouter();
const store = useAdminProductStore();

const productId = computed(() => Number(route.params.id));

onMounted(() => store.fetchOne(productId.value));

useHead({ title: () => `${store.current?.name ?? 'Product'} — Delivo Admin` });

const statusLabel = computed(() => ({
  PENDING: 'Pending review',
  ACTIVE: 'Active',
  REJECTED: 'Rejected',
  ARCHIVED: 'Archived',
}[store.current?.status as ProductStatus] ?? ''));

const statusBadge = computed(() => ({
  PENDING: 'badge-warning',
  ACTIVE: 'badge-success',
  REJECTED: 'badge-error',
  ARCHIVED: 'badge-ghost',
}[store.current?.status as ProductStatus] ?? 'badge-ghost'));

const tiersSorted = computed<ProductPriceTier[]>(() => {
  const list = store.current?.price_tiers ?? [];
  return [...list].sort((a, b) => Number(a.min_qty) - Number(b.min_qty));
});

const canApprove = computed(() =>
  ['PENDING', 'REJECTED'].includes(store.current?.status ?? ''),
);
const canReject = computed(() => store.current?.status === 'PENDING');

const imageUrl = (img: ProductImage) => productImageUrl(img) ?? '';

const onApprove = async () => {
  const ok = await store.approve(productId.value);
  if (ok) router.push('/admin/products');
};

const modalOpen = ref(false);
const modalAction = ref<'reject' | 'takedown'>('reject');
const reason = ref('');
const reasonError = ref('');

const modalTitle = computed(() => modalAction.value === 'reject' ? 'Reject product' : 'Take product down');
const modalCopy = computed(() =>
  modalAction.value === 'reject'
    ? 'The vendor will be notified with the reason and can revise + resubmit.'
    : 'The product is live. Taking it down moves it back to Rejected — the vendor must fix and resubmit before it goes live again.',
);

const openReject = () => {
  modalAction.value = 'reject';
  reason.value = '';
  reasonError.value = '';
  modalOpen.value = true;
};
const openTakedown = () => {
  modalAction.value = 'takedown';
  reason.value = '';
  reasonError.value = '';
  modalOpen.value = true;
};
const closeModal = () => { modalOpen.value = false; };

const onConfirmModal = async () => {
  reasonError.value = '';
  if (reason.value.trim().length < 5) {
    reasonError.value = 'Give the vendor a clear reason (5+ characters).';
    return;
  }
  const ok = modalAction.value === 'reject'
    ? await store.reject(productId.value, reason.value.trim())
    : await store.takedown(productId.value, reason.value.trim());
  if (ok) {
    modalOpen.value = false;
    router.push('/admin/products');
  }
};
</script>
