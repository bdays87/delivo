<template>
  <div>
    <div>
      <h1 class="text-2xl font-extrabold tracking-tight">Platform settings</h1>
      <p class="mt-1 text-sm opacity-70">
        Service-charge formula and the default delivery fee for cities not in the zones table.
      </p>
    </div>

    <div v-if="loading" class="mt-6 flex justify-center py-16">
      <span class="loading loading-spinner loading-lg"></span>
    </div>

    <form v-else class="mt-6 grid gap-6" @submit.prevent="onSave">
      <section class="rounded-3xl border border-base-300 bg-base-100 p-6">
        <h2 class="text-lg font-bold">Service charge</h2>
        <p class="mt-1 text-sm opacity-70">
          Applied to every cart. Calculated as a percentage of subtotal, floored at the minimum.
        </p>
        <div class="mt-4 grid gap-3 md:grid-cols-2">
          <label class="fieldset">
            <span class="fieldset-legend">Percentage (%)</span>
            <input
              v-model.number="form.service_charge_pct"
              type="number"
              step="0.01"
              min="0"
              max="100"
              class="input input-bordered"
            />
          </label>
          <label class="fieldset">
            <span class="fieldset-legend">Minimum (USD)</span>
            <input
              v-model.number="form.service_charge_min_usd"
              type="number"
              step="0.01"
              min="0"
              class="input input-bordered"
            />
          </label>
        </div>
        <p class="mt-3 text-xs opacity-60">
          Example: 2.5% of a $20 cart = $0.50. Floor of $0.50 ensures very small carts still pay it.
        </p>
      </section>

      <section class="rounded-3xl border border-base-300 bg-base-200/40 p-5 text-sm">
        <div class="flex items-start gap-3">
          <Icon name="lucide:info" class="h-5 w-5 text-info" />
          <p class="opacity-80">
            Delivery fees are managed per-city in
            <NuxtLink to="/admin/delivery-zones" class="link link-primary">Coverage areas</NuxtLink>.
            Delivo only accepts vendors and orders in covered cities.
          </p>
        </div>
      </section>

      <div class="flex justify-end">
        <button type="submit" class="btn btn-primary rounded-full" :disabled="submitting">
          <span v-if="submitting" class="loading loading-spinner loading-xs"></span>
          Save changes
        </button>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'admin',
  middleware: ['auth', 'admin'],
});
useHead({ title: 'Platform settings — Delivo Admin' });

interface SettingsForm {
  service_charge_pct: number;
  service_charge_min_usd: number;
}

const { getSettings, updateSettings } = useAdminPlatformSettingsHelper();
const toast = useToast();

const loading = ref(false);
const submitting = ref(false);
const form = reactive<SettingsForm>({
  service_charge_pct: 2.5,
  service_charge_min_usd: 0.5,
});

onMounted(async () => {
  loading.value = true;
  const { data, error } = await getSettings();
  if (!error.value) {
    const s = (data.value as any)?.data;
    if (s) {
      form.service_charge_pct = Number(s.service_charge_pct);
      form.service_charge_min_usd = Number(s.service_charge_min_usd);
    }
  }
  loading.value = false;
});

const onSave = async () => {
  submitting.value = true;
  const { status, error } = await updateSettings(form as Record<string, unknown>);
  if (status?.value) {
    toast.success({ title: 'Settings saved', message: '', position: 'topRight', layout: 2 });
  } else {
    toast.error({
      title: 'Error',
      message: (error?.value as any)?.data?.message || 'Failed to save.',
      position: 'topRight',
      layout: 2,
    });
  }
  submitting.value = false;
};
</script>
