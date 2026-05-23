<template>
  <div class="mx-auto max-w-4xl px-4 py-12">
    <div class="breadcrumbs text-sm opacity-70">
      <ul>
        <li><NuxtLink to="/">Home</NuxtLink></li>
        <li>Deliver on Delivo</li>
      </ul>
    </div>

    <h1 class="mt-4 text-3xl font-extrabold tracking-tight md:text-4xl">Deliver on Delivo</h1>
    <p class="mt-3 max-w-2xl text-base opacity-70">
      Tell us about your fleet. After you submit, upload KYC documents, declare your routes or
      coverage, and our team will activate your account.
    </p>

    <!-- After application: dashboard sections -->
    <div v-if="store.provider" class="mt-8 space-y-6">
      <div class="rounded-3xl border border-base-300 bg-base-100 p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
          <div class="flex items-center gap-3">
            <span class="grid h-10 w-10 place-items-center rounded-2xl bg-primary/10 text-primary">
              <Icon name="lucide:truck" class="h-5 w-5" />
            </span>
            <div>
              <div class="font-semibold">{{ store.provider.business_name }}</div>
              <div class="text-sm opacity-70">
                {{ store.provider.route_type === 'INTRA_CITY' ? 'Intra-city' : 'Inter-city' }} ·
                Base {{ store.provider.base_city }} · {{ statusLabel }}
              </div>
            </div>
          </div>
          <span :class="['badge badge-lg', statusBadge]">{{ statusLabel }}</span>
        </div>
        <div v-if="store.provider.status === 'REJECTED' && store.provider.rejection_reason" class="mt-3 rounded-2xl border border-error/40 bg-error/5 p-3 text-sm">
          <div class="font-semibold">Rejection reason</div>
          <div class="opacity-80">{{ store.provider.rejection_reason }}</div>
        </div>
      </div>

      <!-- KYC -->
      <div class="rounded-3xl border border-base-300 bg-base-100 p-6">
        <h2 class="text-lg font-bold">KYC documents</h2>
        <p class="mt-1 text-sm opacity-70">
          Upload national ID, driver's licence and vehicle registration. PDF, JPG or PNG, max 5 MB each.
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

      <!-- Vehicle fleet (read-only after apply for now) -->
      <div class="rounded-3xl border border-base-300 bg-base-100 p-6">
        <h2 class="text-lg font-bold">Fleet vehicles</h2>
        <div v-if="!store.provider.vehicle_types?.length" class="mt-3 text-sm opacity-70">No vehicle types declared.</div>
        <div v-else class="mt-3 flex flex-wrap gap-2">
          <span v-for="v in store.provider.vehicle_types" :key="v.id" class="badge badge-ghost gap-1">
            <Icon :name="v.icon" class="h-3.5 w-3.5" /> {{ v.name }}
          </span>
        </div>
      </div>

      <!-- INTER_CITY: route editor -->
      <div v-if="store.provider.route_type === 'INTER_CITY'" class="rounded-3xl border border-base-300 bg-base-100 p-6">
        <div class="flex items-start justify-between gap-3">
          <div>
            <h2 class="text-lg font-bold">Routes you run</h2>
            <p class="mt-1 text-sm opacity-70">
              List every route with its origin, destination and intermediate stops. Customers along
              the route can be served as partial-leg deliveries.
            </p>
          </div>
          <button type="button" class="btn btn-sm btn-ghost rounded-full" @click="addRoute">
            <Icon name="lucide:plus" class="h-4 w-4" /> Add route
          </button>
        </div>

        <div class="mt-4 space-y-3">
          <div v-for="(r, idx) in routes" :key="idx" class="rounded-2xl border border-base-300 bg-base-200/40 p-4">
            <div class="grid grid-cols-1 gap-2 md:grid-cols-[1fr_1fr_auto]">
              <label class="fieldset">
                <span class="fieldset-legend">Origin city</span>
                <select v-model="r.origin_city" class="select select-bordered w-full">
                  <option value="" disabled>Choose…</option>
                  <option v-for="a in coverage.areas" :key="`o-${a.id}`" :value="a.city">{{ a.city }}</option>
                </select>
              </label>
              <label class="fieldset">
                <span class="fieldset-legend">Destination city</span>
                <select v-model="r.destination_city" class="select select-bordered w-full">
                  <option value="" disabled>Choose…</option>
                  <option v-for="a in coverage.areas" :key="`d-${a.id}`" :value="a.city">{{ a.city }}</option>
                </select>
              </label>
              <button type="button" class="btn btn-sm btn-ghost rounded-full text-error self-end mb-3" @click="removeRoute(idx)">
                <Icon name="lucide:trash-2" class="h-4 w-4" />
              </button>
            </div>
            <label class="fieldset mt-2">
              <span class="fieldset-legend">Waypoints (intermediate cities, in order)</span>
              <input
                :value="(r.waypoints ?? []).join(', ')"
                type="text"
                placeholder="e.g. Kadoma, Kwekwe, Gweru"
                class="input input-bordered w-full"
                @input="(e) => updateWaypoints(idx, (e.target as HTMLInputElement).value)"
              />
              <span class="text-xs opacity-60">Comma-separated. These should match Delivo coverage cities for matching to work.</span>
            </label>
          </div>
          <div v-if="!routes.length" class="rounded-2xl border border-dashed border-base-300 p-6 text-center text-sm opacity-70">
            No routes yet. Add at least one to receive shipments.
          </div>
        </div>

        <div class="mt-4 flex justify-end">
          <button class="btn btn-primary rounded-full" :disabled="store.submitting" @click="onSaveRoutes">
            <span v-if="store.submitting">Saving…</span><span v-else>Save routes</span>
          </button>
        </div>
      </div>

      <!-- Coverage (always for intra; conditional for inter offering intra) -->
      <div class="rounded-3xl border border-base-300 bg-base-100 p-6">
        <div class="flex items-start justify-between gap-3">
          <div>
            <h2 class="text-lg font-bold">
              {{ store.provider.route_type === 'INTRA_CITY' ? 'Coverage cities' : 'Intra-city services' }}
            </h2>
            <p class="mt-1 text-sm opacity-70">
              <template v-if="store.provider.route_type === 'INTRA_CITY'">
                Pick the cities your fleet can pick up from and deliver within.
              </template>
              <template v-else>
                Toggle on if you also handle within-city deliveries in any of these cities.
              </template>
            </p>
          </div>
          <label v-if="store.provider.route_type === 'INTER_CITY'" class="flex items-center gap-2 text-sm">
            <input
              type="checkbox"
              class="toggle toggle-primary toggle-sm"
              :checked="store.provider.offers_intra_city"
              @change="onToggleOffersIntra(($event.target as HTMLInputElement).checked)"
            />
            Offers intra-city too
          </label>
        </div>

        <div v-if="store.provider.route_type === 'INTRA_CITY' || store.provider.offers_intra_city" class="mt-4">
          <div v-if="coverage.loading && !coverage.areas.length" class="text-sm opacity-70">Loading…</div>
          <div v-else class="grid grid-cols-2 gap-2 sm:grid-cols-3 md:grid-cols-4">
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
              <span v-if="store.submitting">Saving…</span><span v-else>Save coverage</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Apply form -->
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
        </div>
      </section>

      <section class="rounded-3xl border border-base-300 bg-base-100 p-6 lg:col-span-2">
        <h2 class="text-lg font-bold">Route type</h2>
        <p class="mt-1 text-sm opacity-70">Pick how your fleet operates.</p>
        <div class="mt-4 grid gap-3 sm:grid-cols-2">
          <label :class="['flex cursor-pointer items-start gap-3 rounded-2xl border-2 p-4 transition', form.route_type === 'INTRA_CITY' ? 'border-primary bg-primary/5' : 'border-base-300 hover:border-primary/50']">
            <input v-model="form.route_type" type="radio" value="INTRA_CITY" class="radio radio-primary mt-1" />
            <div>
              <div class="font-semibold">Intra-city</div>
              <div class="text-xs opacity-70">Deliveries within one or more cities. You'll pick which cities after sign-up.</div>
            </div>
          </label>
          <label :class="['flex cursor-pointer items-start gap-3 rounded-2xl border-2 p-4 transition', form.route_type === 'INTER_CITY' ? 'border-primary bg-primary/5' : 'border-base-300 hover:border-primary/50']">
            <input v-model="form.route_type" type="radio" value="INTER_CITY" class="radio radio-primary mt-1" />
            <div>
              <div class="font-semibold">Inter-city</div>
              <div class="text-xs opacity-70">Long-haul routes between cities (e.g. Harare → Bulawayo via Kadoma, Kwekwe, Gweru). You'll add routes after sign-up.</div>
            </div>
          </label>
        </div>
        <span v-if="errors.route_type" class="mt-2 text-xs text-red-600">{{ errors.route_type }}</span>

        <label v-if="form.route_type === 'INTER_CITY'" class="mt-4 flex items-center gap-2 text-sm">
          <input v-model="form.offers_intra_city" type="checkbox" class="toggle toggle-primary toggle-sm" />
          We also offer intra-city services in some cities
        </label>
      </section>

      <section class="rounded-3xl border border-base-300 bg-base-100 p-6 lg:col-span-2">
        <h2 class="text-lg font-bold">Vehicles in your fleet</h2>
        <p class="mt-1 text-sm opacity-70">Pick every vehicle type you operate. Customers see what your fleet can handle.</p>
        <div class="mt-4 grid grid-cols-2 gap-2 sm:grid-cols-3 md:grid-cols-4">
          <label v-for="v in vehicleTypes" :key="v.id" class="flex cursor-pointer items-center gap-2 rounded-2xl border border-base-300 bg-base-200/40 p-3">
            <input
              type="checkbox"
              class="checkbox checkbox-sm checkbox-primary"
              :checked="form.vehicle_type_ids.includes(v.id)"
              @change="toggleVehicle(v.id)"
            />
            <Icon :name="v.icon || 'lucide:truck'" class="h-4 w-4 opacity-70" />
            <span class="text-sm">{{ v.name }}</span>
          </label>
        </div>
        <span v-if="errors.vehicle_type_ids" class="mt-2 text-xs text-red-600">{{ errors.vehicle_type_ids }}</span>
      </section>

      <div class="flex flex-wrap items-center gap-3 lg:col-span-2">
        <button class="btn btn-primary btn-lg rounded-full px-7" type="submit" :disabled="store.submitting">
          <span v-if="store.submitting">Submitting…</span><span v-else>Submit application</span>
        </button>
        <NuxtLink to="/" class="btn btn-ghost rounded-full">Cancel</NuxtLink>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { DeliveryProviderApplySchema } from '~/utils/DeliveryProviderSchemas';
import type { ProviderRoute } from '~/stores/provider';

definePageMeta({ layout: 'default', middleware: ['auth'] });
useHead({ title: 'Deliver on Delivo' });

interface VehicleType { id: number; name: string; icon: string; }

const store = useProviderStore();
const coverage = useCoverageStore();
const { listActive: listVehicleTypes } = useVehicleTypeHelper();

const form = reactive({
  business_name: '',
  slug: '',
  support_email: '',
  support_phone: '',
  base_city: '',
  route_type: 'INTRA_CITY' as 'INTRA_CITY' | 'INTER_CITY',
  offers_intra_city: false,
  vehicle_type_ids: [] as number[],
});
const errors = reactive<Record<string, string>>({});

const kycType = ref<'NATIONAL_ID' | 'DRIVERS_LICENSE' | 'VEHICLE_REGISTRATION'>('NATIONAL_ID');
const selectedFile = ref<File | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);

const selectedZoneIds = ref<Set<number>>(new Set());
const vehicleTypes = ref<VehicleType[]>([]);
const routes = ref<ProviderRoute[]>([]);

onMounted(async () => {
  await Promise.all([store.fetchCurrent(), coverage.ensureLoaded(), fetchVehicleTypes()]);
  if (store.provider?.coverage_areas) {
    selectedZoneIds.value = new Set(store.provider.coverage_areas.map((c) => c.id));
  }
  if (store.provider?.routes) {
    routes.value = store.provider.routes.map((r) => ({
      origin_city: r.origin_city,
      destination_city: r.destination_city,
      waypoints: Array.isArray(r.waypoints) ? r.waypoints : [],
    }));
  }
});

watch(() => store.provider?.coverage_areas, (next) => {
  selectedZoneIds.value = new Set((next ?? []).map((c) => c.id));
});
watch(() => store.provider?.routes, (next) => {
  routes.value = (next ?? []).map((r) => ({
    origin_city: r.origin_city,
    destination_city: r.destination_city,
    waypoints: Array.isArray(r.waypoints) ? r.waypoints : [],
  }));
});

const fetchVehicleTypes = async () => {
  const { data } = await listVehicleTypes();
  vehicleTypes.value = (data.value as any)?.data ?? [];
};

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

const toggleVehicle = (id: number) => {
  if (form.vehicle_type_ids.includes(id)) {
    form.vehicle_type_ids = form.vehicle_type_ids.filter((x) => x !== id);
  } else {
    form.vehicle_type_ids = [...form.vehicle_type_ids, id];
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

const addRoute = () => {
  routes.value = [...routes.value, { origin_city: '', destination_city: '', waypoints: [] }];
};
const removeRoute = (idx: number) => {
  routes.value = routes.value.filter((_, i) => i !== idx);
};
const updateWaypoints = (idx: number, raw: string) => {
  const list = raw.split(',').map((s) => s.trim()).filter(Boolean);
  routes.value[idx].waypoints = list;
};
const onSaveRoutes = async () => {
  await store.saveRoutes(routes.value);
};

const onToggleOffersIntra = async (on: boolean) => {
  await store.saveOffersIntraCity(on);
};
</script>
