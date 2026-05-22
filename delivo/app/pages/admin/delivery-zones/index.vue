<template>
  <div>
    <div class="flex flex-wrap items-end justify-between gap-3">
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight">Coverage areas</h1>
        <p class="mt-1 text-sm opacity-70">
          Cities Delivo operates in, with the per-city delivery fee. Vendors and customers can only
          register or check out in cities listed here.
        </p>
      </div>
      <button class="btn btn-primary rounded-full" @click="openCreate">
        <Icon name="lucide:plus" class="h-4 w-4" /> New city
      </button>
    </div>

    <div v-if="loading" class="flex justify-center py-16">
      <span class="loading loading-spinner loading-lg"></span>
    </div>

    <div v-else class="mt-6 overflow-hidden rounded-3xl border border-base-300 bg-base-100">
      <table class="table">
        <thead class="bg-base-200/50 text-xs uppercase tracking-wider opacity-70">
          <tr>
            <th>City</th>
            <th>Hub</th>
            <th>Coords</th>
            <th>Order</th>
            <th>Status</th>
            <th class="text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="!zones.length">
            <td colspan="6" class="py-10 text-center text-base-content/60">No cities yet.</td>
          </tr>
          <tr v-for="z in zones" :key="z.id">
            <td class="font-semibold">{{ z.city }}</td>
            <td>
              <div class="font-medium">{{ z.hub_name || '—' }}</div>
              <div v-if="z.hub_address" class="text-xs opacity-60">{{ z.hub_address }}</div>
            </td>
            <td class="font-mono text-xs opacity-70">
              <span v-if="z.hub_latitude && z.hub_longitude">
                {{ Number(z.hub_latitude).toFixed(4) }}, {{ Number(z.hub_longitude).toFixed(4) }}
              </span>
              <span v-else class="opacity-50">—</span>
            </td>
            <td>{{ z.sort_order }}</td>
            <td>
              <span :class="['badge badge-sm', z.status === 'ACTIVE' ? 'badge-success' : 'badge-ghost']">
                {{ z.status }}
              </span>
            </td>
            <td class="text-right">
              <div class="flex justify-end gap-2">
                <button class="btn btn-xs btn-ghost rounded-full" @click="openEdit(z)">
                  <Icon name="lucide:pencil" class="h-3.5 w-3.5" /> Edit
                </button>
                <button
                  v-if="z.status === 'ACTIVE'"
                  class="btn btn-xs btn-ghost rounded-full text-warning"
                  @click="confirmArchive(z.id)"
                >
                  <Icon name="lucide:archive" class="h-3.5 w-3.5" /> Archive
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <dialog :open="modalOpen" class="modal" :class="{ 'modal-open': modalOpen }">
      <div class="modal-box max-w-md">
        <h3 class="text-lg font-bold">{{ editingId ? 'Edit coverage city' : 'New coverage city' }}</h3>

        <form class="mt-4 flex flex-col gap-3" @submit.prevent="onSubmit">
          <label class="fieldset">
            <span class="fieldset-legend">City</span>
            <input
              v-model="form.city"
              type="text"
              placeholder="e.g. Harare"
              :class="['input input-bordered w-full', errors.city ? 'input-error' : '']"
            />
            <span v-if="errors.city" class="text-xs text-red-600">{{ errors.city }}</span>
          </label>

          <label class="fieldset">
            <span class="fieldset-legend">Hub name</span>
            <input
              v-model="form.hub_name"
              type="text"
              placeholder="e.g. Delivo Harare Hub"
              :class="['input input-bordered w-full', errors.hub_name ? 'input-error' : '']"
            />
            <span v-if="errors.hub_name" class="text-xs text-red-600">{{ errors.hub_name }}</span>
          </label>

          <label class="fieldset">
            <span class="fieldset-legend">Hub address</span>
            <input
              v-model="form.hub_address"
              type="text"
              placeholder="Street, area, city"
              :class="['input input-bordered w-full', errors.hub_address ? 'input-error' : '']"
            />
            <span v-if="errors.hub_address" class="text-xs text-red-600">{{ errors.hub_address }}</span>
          </label>

          <div class="grid grid-cols-2 gap-2">
            <label class="fieldset">
              <span class="fieldset-legend">Latitude</span>
              <input
                v-model.number="form.hub_latitude"
                type="number"
                step="0.000001"
                min="-90"
                max="90"
                placeholder="-17.7945"
                :class="['input input-bordered w-full font-mono text-sm', errors.hub_latitude ? 'input-error' : '']"
              />
            </label>
            <label class="fieldset">
              <span class="fieldset-legend">Longitude</span>
              <input
                v-model.number="form.hub_longitude"
                type="number"
                step="0.000001"
                min="-180"
                max="180"
                placeholder="31.0335"
                :class="['input input-bordered w-full font-mono text-sm', errors.hub_longitude ? 'input-error' : '']"
              />
            </label>
          </div>
          <span class="text-xs opacity-60">
            Lat/long is used as the origin for the Google Distance Matrix call. If left blank, the
            hub address is used instead — less accurate.
          </span>

          <label class="fieldset">
            <span class="fieldset-legend">Sort order</span>
            <input v-model.number="form.sort_order" type="number" min="0" max="1000" class="input input-bordered w-full" />
          </label>

          <label v-if="editingId" class="fieldset">
            <span class="fieldset-legend">Status</span>
            <select v-model="form.status" class="select select-bordered w-full">
              <option value="ACTIVE">Active</option>
              <option value="ARCHIVED">Archived</option>
            </select>
          </label>

          <div class="modal-action">
            <button type="button" class="btn" @click="modalOpen = false">Cancel</button>
            <button type="submit" class="btn btn-primary" :disabled="submitting">
              <span v-if="submitting">Saving…</span>
              <span v-else>Save</span>
            </button>
          </div>
        </form>
      </div>
    </dialog>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'admin',
  middleware: ['auth', 'admin'],
});
useHead({ title: 'Coverage areas — Delivo Admin' });

interface Zone {
  id: number;
  city: string;
  hub_name: string | null;
  hub_address: string | null;
  hub_latitude: string | number | null;
  hub_longitude: string | number | null;
  sort_order: number;
  status: 'ACTIVE' | 'ARCHIVED';
}

const { listZones, createZone, updateZone, archiveZone } = useAdminDeliveryZoneHelper();
const toast = useToast();

const zones = ref<Zone[]>([]);
const loading = ref(false);
const submitting = ref(false);
const modalOpen = ref(false);
const editingId = ref<number | null>(null);

const blankForm = () => ({
  city: '',
  hub_name: '',
  hub_address: '',
  hub_latitude: null as number | null,
  hub_longitude: null as number | null,
  sort_order: 0 as number,
  status: 'ACTIVE' as 'ACTIVE' | 'ARCHIVED',
});
const form = reactive(blankForm());
const errors = reactive<Record<string, string>>({});

const fetchAll = async () => {
  loading.value = true;
  const { data, error } = await listZones();
  if (!error.value) {
    zones.value = (data.value as any)?.data ?? [];
  } else {
    toast.error({ title: 'Error', message: 'Failed to load zones.', position: 'topRight', layout: 2 });
  }
  loading.value = false;
};

onMounted(fetchAll);

const clearErrors = () => Object.keys(errors).forEach((k) => delete errors[k]);

const openCreate = () => {
  Object.assign(form, blankForm());
  editingId.value = null;
  clearErrors();
  modalOpen.value = true;
};

const openEdit = (z: Zone) => {
  form.city = z.city;
  form.hub_name = z.hub_name ?? '';
  form.hub_address = z.hub_address ?? '';
  form.hub_latitude = z.hub_latitude !== null ? Number(z.hub_latitude) : null;
  form.hub_longitude = z.hub_longitude !== null ? Number(z.hub_longitude) : null;
  form.sort_order = z.sort_order;
  form.status = z.status;
  editingId.value = z.id;
  clearErrors();
  modalOpen.value = true;
};

const applyServerErrors = (payload: any) => {
  const bag = payload?.errors ?? payload?.data?.errors ?? payload?.data;
  if (bag && typeof bag === 'object') {
    Object.keys(bag).forEach((k) => {
      if (Array.isArray(bag[k]) && bag[k][0]) errors[k] = bag[k][0];
    });
  }
};

const onSubmit = async () => {
  clearErrors();
  if (!form.city.trim()) { errors.city = 'City is required.'; return; }
  if (!form.hub_name.trim()) { errors.hub_name = 'Hub name is required.'; return; }
  if (!form.hub_address.trim()) { errors.hub_address = 'Hub address is required.'; return; }

  submitting.value = true;
  const payload = {
    city: form.city.trim(),
    hub_name: form.hub_name.trim(),
    hub_address: form.hub_address.trim(),
    hub_latitude: form.hub_latitude,
    hub_longitude: form.hub_longitude,
    sort_order: form.sort_order,
    ...(editingId.value ? { status: form.status } : {}),
  };
  const { status, error } = editingId.value
    ? await updateZone(editingId.value, payload as Record<string, unknown>)
    : await createZone(payload as Record<string, unknown>);

  if (status?.value) {
    toast.success({ title: 'Saved', message: '', position: 'topRight', layout: 2 });
    modalOpen.value = false;
    await fetchAll();
  } else {
    applyServerErrors((error?.value as any)?.data);
    toast.error({
      title: 'Error',
      message: (error?.value as any)?.data?.message || 'Failed to save zone.',
      position: 'topRight',
      layout: 2,
    });
  }
  submitting.value = false;
};

const confirmArchive = async (id: number) => {
  if (!window.confirm('Archive this city? Vendors and customers will no longer be able to register or check out there.')) return;
  const { status } = await archiveZone(id);
  if (status?.value) {
    toast.success({ title: 'Archived', message: '', position: 'topRight', layout: 2 });
    await fetchAll();
  }
};
</script>
