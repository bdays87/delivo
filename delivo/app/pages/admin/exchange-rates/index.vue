<template>
  <div>
    <div>
      <h1 class="text-2xl font-extrabold tracking-tight">Exchange rates</h1>
      <p class="mt-1 text-sm opacity-70">
        Vendors price products in USD. The storefront converts to ZWG using the rate below.
        Update whenever the parallel-market rate moves materially.
      </p>
    </div>

    <div v-if="store.loading" class="mt-6 flex justify-center py-16">
      <span class="loading loading-spinner loading-lg"></span>
    </div>

    <div v-else class="mt-6 rounded-3xl border border-base-300 bg-base-100 p-6">
      <div class="flex flex-wrap items-end gap-6">
        <div>
          <div class="text-xs uppercase opacity-60">Current rate</div>
          <div class="mt-1 text-3xl font-extrabold">
            1 USD = <span class="text-primary">{{ currentRateDisplay }}</span> ZWG
          </div>
          <div v-if="store.usdZwg?.updated_at" class="mt-1 text-xs opacity-60">
            Last updated {{ store.usdZwg.updated_at?.slice(0, 16).replace('T', ' ') }} UTC
          </div>
        </div>
      </div>

      <form class="mt-6 flex flex-wrap items-end gap-3" @submit.prevent="onSave">
        <label class="fieldset">
          <span class="fieldset-legend">New rate (ZWG per USD)</span>
          <input
            v-model.number="newRate"
            type="number"
            step="0.000001"
            min="0.000001"
            :class="['input input-bordered w-56', error ? 'input-error' : '']"
            placeholder="e.g. 36.5"
          />
          <span v-if="error" class="text-xs text-red-600">{{ error }}</span>
        </label>
        <button type="submit" class="btn btn-primary rounded-full" :disabled="store.submitting">
          <span v-if="store.submitting" class="loading loading-spinner loading-xs"></span>
          Save
        </button>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'admin',
  middleware: ['auth', 'admin'],
});
useHead({ title: 'Exchange rates — Delivo Admin' });

const store = useAdminExchangeRateStore();
const newRate = ref<number | null>(null);
const error = ref('');

onMounted(async () => {
  await store.fetch();
  newRate.value = store.usdZwg?.rate !== null && store.usdZwg?.rate !== undefined
    ? Number(store.usdZwg.rate)
    : null;
});

const currentRateDisplay = computed(() =>
  store.usdZwg?.rate !== null && store.usdZwg?.rate !== undefined
    ? Number(store.usdZwg.rate).toFixed(2)
    : '— (not set)',
);

const onSave = async () => {
  error.value = '';
  if (!newRate.value || newRate.value <= 0) {
    error.value = 'Enter a positive rate.';
    return;
  }
  await store.save(Number(newRate.value));
};
</script>
