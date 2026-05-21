<template>
  <div>
    <div v-if="store.loading && !store.current" class="flex justify-center py-16">
      <span class="loading loading-spinner loading-lg"></span>
    </div>

    <div v-else-if="!store.current" class="rounded-3xl border border-base-300 bg-base-100 p-8 text-center">
      <p>Product not found.</p>
      <NuxtLink to="/vendor/products" class="btn btn-primary mt-4 rounded-full">Back to products</NuxtLink>
    </div>

    <div v-else class="space-y-6">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
          <NuxtLink to="/vendor/products" class="btn btn-ghost btn-sm rounded-full">
            <Icon name="lucide:arrow-left" class="h-4 w-4" />
          </NuxtLink>
          <div>
            <h1 class="text-2xl font-extrabold tracking-tight">{{ store.current.name }}</h1>
            <p class="mt-1 text-xs opacity-60 font-mono">{{ store.current.slug }}</p>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <span :class="['badge badge-lg', statusBadge]">{{ statusLabel }}</span>
          <button
            v-if="store.current.status === 'REJECTED'"
            class="btn btn-sm btn-primary rounded-full"
            :disabled="store.submitting"
            @click="onResubmit"
          >
            Resubmit
          </button>
          <button
            v-if="store.current.status !== 'ARCHIVED'"
            class="btn btn-sm btn-ghost rounded-full text-error"
            :disabled="store.submitting"
            @click="confirmArchive"
          >
            <Icon name="lucide:archive" class="h-4 w-4" /> Archive
          </button>
        </div>
      </div>

      <div
        v-if="store.current.status === 'REJECTED' && store.current.rejection_reason"
        class="rounded-3xl border border-error/40 bg-error/5 p-4 text-sm"
      >
        <div class="font-semibold">Rejection reason</div>
        <div class="opacity-80">{{ store.current.rejection_reason }}</div>
      </div>

      <section class="rounded-3xl border border-base-300 bg-base-100 p-6">
        <div class="flex items-center justify-between gap-4">
          <h2 class="text-lg font-bold">Images</h2>
          <label class="btn btn-sm btn-ghost rounded-full">
            <Icon name="lucide:upload" class="h-4 w-4" /> Upload
            <input
              type="file"
              accept="image/jpeg,image/png,image/webp"
              class="hidden"
              @change="onUploadImage"
            />
          </label>
        </div>

        <div v-if="!store.current.images?.length" class="mt-3 text-sm opacity-70">
          No images yet — add at least one before submitting for review.
        </div>
        <div v-else class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
          <div
            v-for="img in store.current.images"
            :key="img.id"
            class="group relative overflow-hidden rounded-2xl border border-base-300"
          >
            <img :src="imageUrl(img)" :alt="store.current.name" class="aspect-square w-full object-cover" />
            <span v-if="img.is_primary" class="badge badge-primary absolute left-2 top-2">Primary</span>
            <div class="absolute inset-x-0 bottom-0 flex items-center justify-between gap-1 bg-base-100/90 p-2 opacity-0 transition group-hover:opacity-100">
              <button
                v-if="!img.is_primary"
                class="btn btn-xs btn-ghost"
                @click="onSetPrimary(img.id)"
              >
                Set primary
              </button>
              <button class="btn btn-xs btn-ghost text-error" @click="onDeleteImage(img.id)">
                <Icon name="lucide:trash-2" class="h-3.5 w-3.5" />
              </button>
            </div>
          </div>
        </div>
      </section>

      <section
        v-if="store.current.status === 'ACTIVE'"
        class="rounded-3xl border border-warning/40 bg-warning/5 p-4 text-sm"
      >
        <Icon name="lucide:info" class="mr-1 inline h-4 w-4" />
        This product is currently live. Saving any edit will move it back to <strong>Pending</strong> for re-approval.
      </section>

      <VendorProductForm
        :initial="store.current"
        :submitting="store.submitting"
        submit-label="Save changes"
        @submit="onUpdate"
        @cancel="back"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { productImageUrl, type ProductImage, type ProductStatus } from '~/stores/product';
import type { VendorProductPayload } from '~/stores/vendorProduct';

definePageMeta({
  layout: 'vendor',
  middleware: ['auth', 'vendor'],
});

const route = useRoute();
const router = useRouter();
const store = useVendorProductStore();

const productId = computed(() => Number(route.params.id));

onMounted(async () => {
  await store.fetchOne(productId.value);
});

useHead({ title: () => `${store.current?.name ?? 'Product'} — Delivo Vendor` });

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

const imageUrl = (img: ProductImage) => productImageUrl(img) ?? '';

const onUploadImage = async (event: Event) => {
  const file = (event.target as HTMLInputElement).files?.[0];
  if (!file) return;
  const ok = await store.uploadProductImage(productId.value, file);
  if (ok) await store.fetchOne(productId.value);
  (event.target as HTMLInputElement).value = '';
};

const onSetPrimary = async (imageId: number) => {
  const ok = await store.setPrimary(productId.value, imageId);
  if (ok) await store.fetchOne(productId.value);
};

const onDeleteImage = async (imageId: number) => {
  if (!window.confirm('Delete this image?')) return;
  const ok = await store.removeImage(productId.value, imageId);
  if (ok) await store.fetchOne(productId.value);
};

const onUpdate = async (payload: VendorProductPayload) => {
  const updated = await store.update(productId.value, payload);
  if (updated) await store.fetchOne(productId.value);
};

const onResubmit = async () => {
  const ok = await store.resubmit(productId.value);
  if (ok) await store.fetchOne(productId.value);
};

const confirmArchive = async () => {
  if (!window.confirm('Archive this product? It will no longer be listed.')) return;
  const ok = await store.archive(productId.value);
  if (ok) router.push('/vendor/products');
};

const back = () => router.push('/vendor/products');
</script>
