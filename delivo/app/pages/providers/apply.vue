<template>
  <div class="mx-auto max-w-4xl px-4 py-12">
    <div class="breadcrumbs text-sm opacity-70">
      <ul>
        <li><NuxtLink to="/">Home</NuxtLink></li>
        <li>Drive for Delivo</li>
      </ul>
    </div>

    <h1 class="mt-4 text-3xl font-extrabold tracking-tight md:text-4xl">
      Drive for Delivo
    </h1>
    <p class="mt-3 max-w-2xl text-base opacity-70">
      Tell us about your delivery operation. After you submit, upload your KYC documents and pick
      the cities you can serve — our team will review and activate your account, usually within
      one working day.
    </p>

    <div v-if="store.provider" class="mt-8 space-y-6">
      <div class="rounded-3xl border border-base-300 bg-base-100 p-6">
        <div class="flex items-center justify-between gap-3">
          <div class="flex items-center gap-3">
            <span class="grid h-10 w-10 place-items-center rounded-2xl bg-primary/10 text-primary">
              <Icon name="lucide:truck" class="h-5 w-5" />
            </span>
            <div>
              <div class="font-semibold">{{ store.provider.business_name }}</div>
              <div class="text-sm opacity-70">Base: {{ store.provider.base_city }} · {{ statusLabel }}</div>
            </div>
          </div>
          <span :class="['badge badge-lg', statusBadge]">{{ statusLabel }}</span>
        </div>
        <div v-if="store.provider.status === 'REJECTED' && store.provider.rejection_reason" class="mt-3 rounded-2xl border border-error/40 bg-error/5 p-3 text-sm">
          <div class="font-semibold">Rejection reason</div>
          <div class="opacity-80">{{ store.provider.rejection_reason }}</div>
        </div>
      </div>

      <div class="rounded-3xl border border-base-300 bg-base-100 p-6">
        <h2 class="text-lg font-bold">KYC documents</h2>
        <p class="mt-1 text-sm opacity-70">
          Upload your national ID, driver's licence and vehicle registration. PDF, JPG or PNG, max 5 MB each.
        </p>

        <ul v-if="store.provider.kyc_documents?.length" class="mt-3 divide-y divide-base-300">
          <li v-for="d in store.provider.kyc_documents" :key="d.id" class="flex items-center justify-between py-2 text-sm">
            <div>
              <div class="font-medium">{{ d.original_filename }}</div>
              <div class="text-xs opacity-60">{{ d.type }}</div>
            </div>
            <span :class="['badge badge-sm', d.status === 'APPROVED' ? 'badge-success' : d.status === 'REJECTED' ? 'badge-error' : 'badge-ghost']">
              {{ d.status }}
            </span>
          </li>
        </ul>

        <form class="mt-4 grid gap-3 sm:grid-cols-[1fr_1fr_auto]" @submit.prevent="onUploadKyc">
          <select v-model="kycType" class="select select-bordered">
            <option value="NATIONAL_ID">National ID</option>
            <option value="DRIVERS_LICENSE">Driver's licence</option>
            <option value="VEHICLE_REGISTRATION">Vehicle registration</option>
          </select>
          <input
            ref="fileInput"
            type="file"
            accept=".pdf,.jpg,.jpeg,.png"
            class="file-input file-input-bordered w-full"
            @change="onFileSelected"
          />
          <button class="btn btn-primary rounded-full" :disabled="!selectedFile || store.submitting">
            <span v-if="store.submitting">Uploading…</span>
            <span v-else>Upload</span>
          </button>
        </form>
      </div>

      <div class="rounded-3xl border border-base-300 bg-base-100 p-6">
        <h2 class="text-lg font-bold">Coverage areas</h2>
        <p class="mt-1 text-sm opacity-70">
          Pick the cities you can pick up from and deliver to. Orders are matched to providers whose
          coverage includes both the pickup hub and the customer's city.
        </p>

        <div v-if="coverage.loading && !coverage.areas.length" class="mt-3 text-sm opacity-70">Loading…</div>
        <div v-else class="mt-4 grid grid-cols-2 gap-2 sm:grid-cols-3 md:grid-cols-4">
          <label v-for="c in coverage.areas" :key="c.id" class="flex cursor-pointer items-center gap-2 rounded-2xl border border-base-300 bg-base-200/40 p-3">
            <input
              type="checkbox"
              class="checkbox checkbox-sm checkbox-primary"
              :checked="selectedZoneIds.has(c.id)"
              @change="toggleZone(c.id)"
            />
            <span class="text-sm">{{ c.city }}</span>
          </label>
        </div>

        <div class="mt-4 flex justify-end">
          <button class="btn btn-primary rounded-full" :disabled="store.submitting" @click="onSaveCoverage">
            <span v-if="store.submitting">Saving…</span>
            <span v-else>Save coverage</span>
          </button>
        </div>
      </div>
    </div>

    <form v-else class="mt-8 grid gap-6 lg:grid-cols-2" @submit.prevent="handleApply">
      <section class="rounded-3xl border border-base-300 bg-base-100 p-6 lg:col-span-2">
        <h2 class="text-lg font-bold">Business information</h2>
        <div class="mt-4 grid gap-4 md:grid-cols-2">
          <label class="fieldset">
            <span class="fieldset-legend">Business name *</span>
            <input v-model="form.business_name" type="text" placeholder="e.g. Harare Couriers"
                   :class="['input input-bordered w-full', errors.business_name ? 'input-error' : '']" />
            <span v-if="errors.business_name" class="text-xs text-red-600">{{ errors.business_name }}</span>
          </label>
          <label class="fieldset">
            <span class="fieldset-legend">URL slug *</span>
            <input v-model="form.slug" type="text" placeholder="harare-couriers"
                   :class="['input input-bordered w-full', errors.slug ? 'input-error' : '']" />
            <span v-if="errors.slug" class="text-xs text-red-600">{{ errors.slug }}</span>
          </label>
          <label class="fieldset">
            <span class="fieldset-legend">Support email *</span>
            <input v-model="form.support_email" type="email" placeholder="hi@yourcouriers.co.zw"
                   :class="['input input-bordered w-full', errors.support_email ? 'input-error' : '']" />
            <span v-if="errors.support_email" class="text-xs text-red-600">{{ errors.support_email }}</span>
          </label>
          <label class="fieldset">
            <span class="fieldset-legend">Support phone *</span>
            <input v-model="form.support_phone" type="tel" placeholder="0772 000 000"
                   :class="['input input-bordered w-full', errors.support_phone ? 'input-error' : '']" />
            <span v-if="errors.support_phone" class="text-xs text-red-600">{{ errors.support_phone }}</span>
          </label>
          <label class="fieldset">
            <span class="fieldset-legend">Base city *</span>
            <select v-model="form.base_city" :class="['select select-bordered w-full', errors.base_city ? 'select-error' : '']">
              <option value="" disabled>Select your base city…</option>
              <option v-for="a in coverage.areas" :key="a.id" :value="a.city">{{ a.city }}</option>
            </select>
            <span v-if="errors.base_city" class="text-xs text-red-600">{{ errors.base_city }}</span>
          </label>
          <label class="fieldset">
            <span class="fieldset-legend">Vehicle types</span>
            <input v-model="form.vehicle_types" type="text" placeholder="Bike, Van, Truck"
                   class="input input-bordered w-full" />
          </label>
        </div>
      </section>

      <div class="flex flex-wrap items-center gap-3 lg:col-span-2">
        <button class="btn btn-primary btn-lg rounded-full px-7" type="submit" :disabled="store.submitting">
          <span v-if="store.submitting">Submitting…</span>
          <span v-else>Submit application</span>
        </button>
        <NuxtLink to="/" class="btn btn-ghost rounded-full">Cancel</NuxtLink>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { DeliveryProviderApplySchema } from '~/utils/DeliveryProviderSchemas';

definePageMeta({ layout: 'default', middleware: ['auth'] });
useHead({ title: 'Drive for Delivo' });

const store = useProviderStore();
const coverage = useCoverageStore();

const form = reactive({
  business_name: '',
  slug: '',
  support_email: '',
  support_phone: '',
  base_city: '',
  vehicle_types: '' as string | null,
});
const errors = reactive<Record<string, string>>({});

const kycType = ref<'NATIONAL_ID' | 'DRIVERS_LICENSE' | 'VEHICLE_REGISTRATION'>('NATIONAL_ID');
const selectedFile = ref<File | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);

const selectedZoneIds = ref<Set<number>>(new Set());

onMounted(async () => {
  await Promise.all([store.fetchCurrent(), coverage.ensureLoaded()]);
  if (store.provider?.coverage_areas) {
    selectedZoneIds.value = new Set(store.provider.coverage_areas.map((c) => c.id));
  }
});

watch(() => store.provider?.coverage_areas, (next) => {
  selectedZoneIds.value = new Set((next ?? []).map((c) => c.id));
});

const slugify = (s: string) => s.toLowerCase().trim().replace(/[^a-z0-9-]+/g, '-').replace(/^-+|-+$/g, '');
const prevName = ref('');
watch(() => form.business_name, (v) => {
  if (form.slug === '' || form.slug === slugify(prevName.value)) form.slug = slugify(v);
  prevName.value = v;
});

const statusLabel = computed(() => {
  const s = store.provider?.status;
  if (s === 'PENDING') return 'Pending review';
  if (s === 'ACTIVE') return 'Active';
  if (s === 'REJECTED') return 'Rejected';
  if (s === 'SUSPENDED') return 'Suspended';
  return '';
});
const statusBadge = computed(() => {
  const s = store.provider?.status;
  if (s === 'ACTIVE') return 'badge-success';
  if (s === 'REJECTED' || s === 'SUSPENDED') return 'badge-error';
  return 'badge-warning';
});

const clearErrors = () => Object.keys(errors).forEach((k) => delete errors[k]);

const applyServerErrors = (payload: any) => {
  const fields = payload?.errors;
  if (fields && typeof fields === 'object') {
    Object.keys(fields).forEach((k) => {
      if (Array.isArray(fields[k]) && fields[k][0]) errors[k] = fields[k][0];
    });
  }
};

const handleApply = async () => {
  clearErrors();
  try {
    const valid = await DeliveryProviderApplySchema.validate(form, { abortEarly: false });
    await store.applyAsProvider(valid as any);
  } catch (err: any) {
    if (err?.inner?.length) {
      err.inner.forEach((e: any) => { if (e.path) errors[e.path] = e.message; });
      return;
    }
    if (err?.response?._data) applyServerErrors(err.response._data);
  }
};

const onFileSelected = (e: Event) => {
  const f = (e.target as HTMLInputElement).files?.[0] ?? null;
  selectedFile.value = f;
};

const onUploadKyc = async () => {
  if (!selectedFile.value) return;
  const ok = await store.uploadKycDocument(kycType.value, selectedFile.value);
  if (ok) {
    selectedFile.value = null;
    if (fileInput.value) fileInput.value.value = '';
  }
};

const toggleZone = (id: number) => {
  const next = new Set(selectedZoneIds.value);
  if (next.has(id)) next.delete(id);
  else next.add(id);
  selectedZoneIds.value = next;
};

const onSaveCoverage = async () => {
  await store.saveCoverage(Array.from(selectedZoneIds.value));
};
</script>
