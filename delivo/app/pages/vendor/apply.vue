<template>
  <div class="mx-auto max-w-4xl px-4 py-12">
    <div class="breadcrumbs text-sm opacity-70">
      <ul>
        <li><NuxtLink to="/">Home</NuxtLink></li>
        <li>Become a seller</li>
      </ul>
    </div>

    <h1 class="mt-4 text-3xl font-extrabold tracking-tight md:text-4xl">
      Become a seller on Delivo
    </h1>
    <p class="mt-3 max-w-2xl text-base opacity-70">
      Tell us about your business. After you submit, upload your national ID and our team will
      review and approve your application — usually within one working day.
    </p>

    <StorefrontCreateAccountGate
      v-if="!auth.isAuthenticated"
      role-label="seller"
      icon="lucide:store"
      icon-bg="bg-primary/15 text-primary"
      benefits-title="What you get as a seller"
      :benefits="[
        'List products and reach customers across Zimbabwe',
        'Get paid via mobile money or bank transfer',
        'No need to run your own delivery or website',
      ]"
      :steps="[
        { title: 'Create a free customer account', detail: 'Used to sign in and manage your store.' },
        { title: 'Submit your seller application', detail: 'Business name, contact details and payout method.' },
        { title: 'Upload your national ID', detail: 'Quick admin review — usually under a working day.' },
      ]"
    />

    <div v-else-if="vendorStore.vendor" class="mt-8 space-y-6">
      <div class="rounded-3xl border border-base-300 bg-base-100 p-6">
        <div class="flex items-center gap-3">
          <span class="grid h-10 w-10 place-items-center rounded-2xl bg-primary/10 text-primary">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </span>
          <div>
            <div class="font-semibold">Application on file</div>
            <div class="text-sm opacity-70">{{ vendorStore.vendor.business_name }} · {{ statusLabel }}</div>
          </div>
        </div>
      </div>

      <div class="rounded-3xl border border-base-300 bg-base-100 p-6">
        <h2 class="text-lg font-bold">National ID upload</h2>
        <p class="mt-1 text-sm opacity-70">
          Upload a clear photo or scan of your national ID. PDF, JPG or PNG, max 5 MB.
        </p>

        <div v-if="hasUploadedId" class="mt-4 rounded-2xl border border-success/40 bg-success/5 p-4 text-sm">
          ✓ National ID uploaded. Awaiting admin review.
        </div>

        <form v-else class="mt-4 flex flex-col gap-3" @submit.prevent="handleKycSubmit">
          <input
            ref="fileInput"
            type="file"
            accept=".pdf,.jpg,.jpeg,.png"
            class="file-input file-input-bordered w-full"
            @change="onFileSelected"
          />
          <span v-if="kycError" class="text-xs text-red-600">{{ kycError }}</span>
          <button class="btn btn-primary btn-lg rounded-full self-start" :disabled="!selectedFile || vendorStore.submitting">
            <span v-if="vendorStore.submitting">Uploading…</span>
            <span v-else>Upload national ID</span>
          </button>
        </form>
      </div>

      <NuxtLink to="/vendor" class="btn btn-outline rounded-full">Go to vendor dashboard →</NuxtLink>
    </div>

    <form v-else class="mt-8 grid gap-8 lg:grid-cols-2" @submit.prevent="handleApply">
      <!-- Business info -->
      <section class="rounded-3xl border border-base-300 bg-base-100 p-6 lg:col-span-2">
        <h2 class="text-lg font-bold">Business information</h2>
        <p class="mt-1 text-sm opacity-70">Customers will see your business name and storefront URL.</p>

        <div class="mt-4 grid gap-4 md:grid-cols-2">
          <label class="fieldset">
            <span class="fieldset-legend">Business name *</span>
            <input v-model="form.business_name" type="text" placeholder="e.g. Fresh Goods"
                   :class="['input input-bordered w-full', errors.business_name ? 'input-error' : '']" />
            <span v-if="errors.business_name" class="text-xs text-red-600">{{ errors.business_name }}</span>
          </label>
          <label class="fieldset">
            <span class="fieldset-legend">Storefront URL *</span>
            <input v-model="form.slug" type="text" placeholder="fresh-goods"
                   :class="['input input-bordered w-full', errors.slug ? 'input-error' : '']" />
            <span class="text-xs opacity-60">delivo.co.zw/store/{{ form.slug || 'your-store' }}</span>
            <span v-if="errors.slug" class="text-xs text-red-600">{{ errors.slug }}</span>
          </label>
          <label class="fieldset">
            <span class="fieldset-legend">TIN (optional)</span>
            <input v-model="form.tin" type="text"
                   :class="['input input-bordered w-full', errors.tin ? 'input-error' : '']" />
          </label>
          <label class="fieldset">
            <span class="fieldset-legend">Registration number (optional)</span>
            <input v-model="form.registration_no" type="text"
                   :class="['input input-bordered w-full', errors.registration_no ? 'input-error' : '']" />
          </label>
        </div>
      </section>

      <!-- Contact -->
      <section class="rounded-3xl border border-base-300 bg-base-100 p-6">
        <h2 class="text-lg font-bold">Business contact</h2>
        <p class="mt-1 text-sm opacity-70">Customers and admins reach your store via these.</p>

        <div class="mt-4 grid gap-4">
          <label class="fieldset">
            <span class="fieldset-legend">Support email *</span>
            <input v-model="form.support_email" type="email" placeholder="hello@yourstore.co.zw"
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
            <span class="fieldset-legend">Operating city *</span>
            <select v-model="form.city"
                    :class="['select select-bordered w-full', errors.city ? 'select-error' : '']">
              <option value="" disabled>Select your city…</option>
              <option v-for="a in coverage.areas" :key="a.id" :value="a.city">{{ a.city }}</option>
            </select>
            <span class="text-xs opacity-60">
              Delivo only accepts vendors from cities we currently deliver to.
            </span>
            <span v-if="!coverage.areas.length && !coverage.loading" class="text-xs text-red-600">
              No coverage areas configured yet. Ask an admin to add cities.
            </span>
            <span v-if="errors.city" class="text-xs text-red-600">{{ errors.city }}</span>
          </label>
        </div>
      </section>

      <!-- Payouts -->
      <section class="rounded-3xl border border-base-300 bg-base-100 p-6">
        <h2 class="text-lg font-bold">Payout details</h2>
        <p class="mt-1 text-sm opacity-70">Where we send your share after each sale. You can update these later.</p>

        <div class="mt-4 flex gap-2">
          <button
            type="button"
            :class="['btn btn-sm rounded-full', form.payout_method === 'MOBILE_WALLET' ? 'btn-primary' : 'btn-ghost']"
            @click="setPayoutMethod('MOBILE_WALLET')"
          >
            <Icon name="lucide:smartphone" class="h-3.5 w-3.5" />
            Mobile wallet
          </button>
          <button
            type="button"
            :class="['btn btn-sm rounded-full', form.payout_method === 'BANK_TRANSFER' ? 'btn-primary' : 'btn-ghost']"
            @click="setPayoutMethod('BANK_TRANSFER')"
          >
            <Icon name="lucide:landmark" class="h-3.5 w-3.5" />
            Bank transfer
          </button>
        </div>
        <span v-if="errors.payout_method" class="mt-2 text-xs text-red-600">{{ errors.payout_method }}</span>

        <!-- Mobile wallet fields -->
        <div v-if="form.payout_method === 'MOBILE_WALLET'" class="mt-4 grid gap-4">
          <label class="fieldset">
            <span class="fieldset-legend">Mobile wallet *</span>
            <select v-model.number="form.mobile_wallet_id"
                    :class="['select select-bordered w-full', errors.mobile_wallet_id ? 'select-error' : '']">
              <option :value="null" disabled>Select a wallet…</option>
              <option v-for="w in walletStore.items" :key="w.id" :value="w.id">{{ w.name }}</option>
            </select>
            <span v-if="!walletStore.items.length && !walletStore.loading" class="text-xs opacity-60">
              No active wallets configured. Ask an admin to set them up.
            </span>
            <span v-if="errors.mobile_wallet_id" class="text-xs text-red-600">{{ errors.mobile_wallet_id }}</span>
          </label>
          <label class="fieldset">
            <span class="fieldset-legend">Wallet number *</span>
            <input v-model="form.mobile_wallet_msisdn" type="tel" placeholder="0772 000 000"
                   :class="['input input-bordered w-full', errors.mobile_wallet_msisdn ? 'input-error' : '']" />
            <span v-if="errors.mobile_wallet_msisdn" class="text-xs text-red-600">{{ errors.mobile_wallet_msisdn }}</span>
          </label>
        </div>

        <!-- Bank transfer fields -->
        <div v-else-if="form.payout_method === 'BANK_TRANSFER'" class="mt-4 grid gap-4">
          <label class="fieldset">
            <span class="fieldset-legend">Bank name *</span>
            <input v-model="form.bank_name" type="text" placeholder="e.g. CABS"
                   :class="['input input-bordered w-full', errors.bank_name ? 'input-error' : '']" />
            <span v-if="errors.bank_name" class="text-xs text-red-600">{{ errors.bank_name }}</span>
          </label>
          <label class="fieldset">
            <span class="fieldset-legend">Account holder name *</span>
            <input v-model="form.bank_account_name" type="text"
                   :class="['input input-bordered w-full', errors.bank_account_name ? 'input-error' : '']" />
            <span v-if="errors.bank_account_name" class="text-xs text-red-600">{{ errors.bank_account_name }}</span>
          </label>
          <label class="fieldset">
            <span class="fieldset-legend">Account number *</span>
            <input v-model="form.bank_account_number" type="text"
                   :class="['input input-bordered w-full', errors.bank_account_number ? 'input-error' : '']" />
            <span v-if="errors.bank_account_number" class="text-xs text-red-600">{{ errors.bank_account_number }}</span>
          </label>
          <label class="fieldset">
            <span class="fieldset-legend">Currency *</span>
            <select v-model="form.bank_currency"
                    :class="['select select-bordered w-full', errors.bank_currency ? 'select-error' : '']">
              <option :value="null" disabled>Select currency…</option>
              <option value="USD">USD — United States Dollar</option>
              <option value="ZWG">ZWG — Zimbabwe Gold</option>
            </select>
            <span v-if="errors.bank_currency" class="text-xs text-red-600">{{ errors.bank_currency }}</span>
          </label>
        </div>

        <p v-else class="mt-4 text-sm opacity-70">
          Pick a payout method above — you can change this later.
        </p>
      </section>

      <div class="flex flex-wrap items-center gap-3 lg:col-span-2">
        <button class="btn btn-primary btn-lg rounded-full px-7" type="submit" :disabled="vendorStore.submitting">
          <span v-if="vendorStore.submitting">Submitting…</span>
          <span v-else>Submit application</span>
        </button>
        <NuxtLink to="/" class="btn btn-ghost rounded-full">Cancel</NuxtLink>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import type { Vendor } from '~/stores/vendor';

definePageMeta({
  layout: 'default',
});
useHead({ title: 'Become a seller — Delivo' });

const auth = useAuthStore();
const vendorStore = useVendorStore();
const walletStore = useMobileWalletStore();
const coverage = useCoverageStore();

type PayoutMethod = 'MOBILE_WALLET' | 'BANK_TRANSFER' | null;

const form = reactive({
  business_name: '',
  slug: '',
  support_email: '',
  support_phone: '',
  city: '',
  tin: '' as string | null,
  registration_no: '' as string | null,
  payout_method: null as PayoutMethod,
  mobile_wallet_id: null as number | null,
  mobile_wallet_msisdn: '' as string | null,
  bank_name: '' as string | null,
  bank_account_name: '' as string | null,
  bank_account_number: '' as string | null,
  bank_currency: null as 'USD' | 'ZWG' | null,
});

const errors = reactive<Record<string, string>>({});

const selectedFile = ref<File | null>(null);
const kycError = ref<string | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);

onMounted(() => {
  if (auth.isAuthenticated) {
    vendorStore.fetchCurrent();
    walletStore.fetchActive();
  }
  coverage.ensureLoaded();
});

const prevBusinessName = ref('');
const slugify = (s: string) =>
  s.toLowerCase().trim().replace(/[^a-z0-9-]+/g, '-').replace(/^-+|-+$/g, '');

watch(() => form.business_name, (v) => {
  if (!errors.slug && (form.slug === '' || form.slug === slugify(prevBusinessName.value))) {
    form.slug = slugify(v);
  }
  prevBusinessName.value = v;
});

const hasUploadedId = computed(() =>
  (vendorStore.vendor?.kyc_documents ?? []).some(
    (d) => d.type === 'NATIONAL_ID' && d.status !== 'REJECTED',
  ),
);

const statusLabel = computed(() => {
  const s = vendorStore.vendor?.status;
  if (s === 'PENDING') return 'Pending review';
  if (s === 'ACTIVE') return 'Active';
  if (s === 'REJECTED') return 'Rejected';
  if (s === 'SUSPENDED') return 'Suspended';
  return '';
});

const setPayoutMethod = (method: PayoutMethod) => {
  form.payout_method = method;
  if (method === 'MOBILE_WALLET') {
    form.bank_name = null;
    form.bank_account_name = null;
    form.bank_account_number = null;
    form.bank_currency = null;
  } else if (method === 'BANK_TRANSFER') {
    form.mobile_wallet_id = null;
    form.mobile_wallet_msisdn = null;
  }
};

const clearErrors = () => {
  Object.keys(errors).forEach((k) => delete errors[k]);
};

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
    const valid = await VendorApplySchema.validate(form, { abortEarly: false });
    const result: Vendor | null = await vendorStore.apply(valid as any);
    if (!result) return;
  } catch (err: any) {
    if (err?.inner?.length) {
      err.inner.forEach((e: any) => {
        if (e.path) errors[e.path] = e.message;
      });
      return;
    }
    if (err?.response?._data) applyServerErrors(err.response._data);
  }
};

const onFileSelected = (e: Event) => {
  const f = (e.target as HTMLInputElement).files?.[0] ?? null;
  selectedFile.value = f;
  kycError.value = null;
};

const handleKycSubmit = async () => {
  kycError.value = null;
  if (!selectedFile.value) {
    kycError.value = 'Pick a file first.';
    return;
  }
  const ok = await vendorStore.uploadKyc('NATIONAL_ID', selectedFile.value);
  if (ok) {
    selectedFile.value = null;
    if (fileInput.value) fileInput.value.value = '';
  }
};
</script>
