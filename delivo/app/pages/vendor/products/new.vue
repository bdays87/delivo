<template>
  <div>
    <div class="flex items-center gap-3">
      <NuxtLink to="/vendor/products" class="btn btn-ghost btn-sm rounded-full">
        <Icon name="lucide:arrow-left" class="h-4 w-4" />
      </NuxtLink>
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight">New product</h1>
        <p class="mt-1 text-sm opacity-70">It will be submitted for admin review.</p>
      </div>
    </div>

    <div class="mt-6">
      <VendorProductForm
        :initial="null"
        :submitting="store.submitting"
        submit-label="Submit for review"
        @submit="onSubmit"
        @cancel="cancel"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import type { VendorProductPayload } from '~/stores/vendorProduct';

definePageMeta({
  layout: 'vendor',
  middleware: ['auth', 'vendor'],
});
useHead({ title: 'New product — Delivo Vendor' });

const store = useVendorProductStore();
const router = useRouter();

const onSubmit = async (payload: VendorProductPayload) => {
  const created = await store.create(payload);
  if (created) router.push(`/vendor/products/${created.id}`);
};

const cancel = () => router.push('/vendor/products');
</script>
