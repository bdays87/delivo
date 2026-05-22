<template>
  <div>
    <div class="flex flex-wrap items-end justify-between gap-3">
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight">Delivery fees</h1>
        <p class="mt-1 text-sm opacity-70">
          Distance-banded delivery fees. At checkout, Delivo calls Google Distance Matrix from the
          vendor's hub (in <NuxtLink to="/admin/delivery-zones" class="link link-primary">Coverage areas</NuxtLink>)
          to the customer's address, then picks the band that covers the resulting km.
        </p>
      </div>
      <button class="btn btn-primary rounded-full" @click="openCreate">
        <Icon name="lucide:plus" class="h-4 w-4" /> New band
      </button>
    </div>

    <div v-if="loading" class="flex justify-center py-16">
      <span class="loading loading-spinner loading-lg"></span>
    </div>

    <div v-else class="mt-6 overflow-hidden rounded-3xl border border-base-300 bg-base-100">
      <table class="table">
        <thead class="bg-base-200/50 text-xs uppercase tracking-wider opacity-70">
          <tr>
            <th>Distance band</th>
            <th>Fee (USD)</th>
            <th>Order</th>
            <th>Status</th>
            <th class="text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="!fees.length">
            <td colspan="5" class="py-10 text-center text-base-content/60">No bands yet — add at least one.</td>
          </tr>
          <tr v-for="f in fees" :key="f.id">
            <td class="font-mono text-sm">
              {{ f.min_km }} – {{ f.max_km !== null ? f.max_km : '∞' }} km
            </td>
            <td>${{ Number(f.fee_usd).toFixed(2) }}</td>
            <td>{{ f.sort_order }}</td>
            <td>
              <span :class="['badge badge-sm', f.status === 'ACTIVE' ? 'badge-success' : 'badge-ghost']">
                {{ f.status }}
              </span>
            </td>
            <td class="text-right">
              <div class="flex justify-end gap-2">
                <button class="btn btn-xs btn-ghost rounded-full" @click="openEdit(f)">
                  <Icon name="lucide:pencil" class="h-3.5 w-3.5" /> Edit
                </button>
                <button
                  v-if="f.status === 'ACTIVE'"
                  class="btn btn-xs btn-ghost rounded-full text-warning"
                  @click="confirmArchive(f.id)"
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
        <h3 class="text-lg font-bold">{{ editingId ? 'Edit delivery band' : 'New delivery band' }}</h3>

        <form class="mt-4 flex flex-col gap-3" @submit.prevent="onSubmit">
          <div class="grid grid-cols-2 gap-2">
            <label class="fieldset">
              <span class="fieldset-legend">Min km</span>
              <input
                v-model.number="form.min_km"
                type="number"
                min="0"
                :class="['input input-bordered w-full', errors.min_km ? 'input-error' : '']"
              />
              <span v-if="errors.min_km" class="text-xs text-red-600">{{ errors.min_km }}</span>
            </label>
            <label class="fieldset">
              <span class="fieldset-legend">Max km (blank = and up)</span>
              <input
                v-model.number="form.max_km"
                type="number"
                min="0"
                :class="['input input-bordered w-full', errors.max_km ? 'input-error' : '']"
              />
              <span v-if="errors.max_km" class="text-xs text-red-600">{{ errors.max_km }}</span>
            </label>
          </div>

          <label class="fieldset">
            <span class="fieldset-legend">Fee (USD)</span>
            <input
              v-model.number="form.fee_usd"
              type="number"
              step="0.01"
              min="0"
              :class="['input input-bordered w-full', errors.fee_usd ? 'input-error' : '']"
            />
            <span v-if="errors.fee_usd" class="text-xs text-red-600">{{ errors.fee_usd }}</span>
          </label>

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
            <button type="button" class="btn rounded-full" @click="modalOpen = false">Cancel</button>
            <button type="submit" class="btn btn-primary rounded-full" :disabled="submitting">
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
definePageMeta({ layout: 'admin', middleware: ['auth', 'admin'] });
useHead({ title: 'Delivery fees — Delivo Admin' });

interface Band {
  id: number;
  min_km: number;
  max_km: number | null;
  fee_usd: string | number;
  sort_order: number;
  status: 'ACTIVE' | 'ARCHIVED';
}

const { listFees, createFee, updateFee, archiveFee } = useAdminDeliveryFeeHelper();
const toast = useToast();

const fees = ref<Band[]>([]);
const loading = ref(false);
const submitting = ref(false);
const modalOpen = ref(false);
const editingId = ref<number | null>(null);

const blankForm = () => ({
  min_km: 0 as number,
  max_km: null as number | null,
  fee_usd: 0 as number,
  sort_order: 0 as number,
  status: 'ACTIVE' as 'ACTIVE' | 'ARCHIVED',
});
const form = reactive(blankForm());
const errors = reactive<Record<string, string>>({});

const fetchAll = async () => {
  loading.value = true;
  const { data, error } = await listFees();
  if (!error.value) {
    fees.value = (data.value as any)?.data ?? [];
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

const openEdit = (f: Band) => {
  form.min_km = f.min_km;
  form.max_km = f.max_km;
  form.fee_usd = Number(f.fee_usd);
  form.sort_order = f.sort_order;
  form.status = f.status;
  editingId.value = f.id;
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
  if (form.min_km < 0) { errors.min_km = 'Min km must be ≥ 0.'; return; }
  if (form.max_km !== null && form.max_km < form.min_km) { errors.max_km = 'Max km must be ≥ min km.'; return; }
  if (form.fee_usd < 0) { errors.fee_usd = 'Fee must be ≥ 0.'; return; }

  submitting.value = true;
  const payload = {
    min_km: form.min_km,
    max_km: form.max_km,
    fee_usd: form.fee_usd,
    sort_order: form.sort_order,
    ...(editingId.value ? { status: form.status } : {}),
  };
  const { status, error } = editingId.value
    ? await updateFee(editingId.value, payload as Record<string, unknown>)
    : await createFee(payload as Record<string, unknown>);

  if (status?.value) {
    toast.success({ title: 'Saved', message: '', position: 'topRight', layout: 2 });
    modalOpen.value = false;
    await fetchAll();
  } else {
    applyServerErrors((error?.value as any)?.data);
    toast.error({
      title: 'Error',
      message: (error?.value as any)?.data?.message || 'Failed to save.',
      position: 'topRight',
      layout: 2,
    });
  }
  submitting.value = false;
};

const confirmArchive = async (id: number) => {
  if (!window.confirm('Archive this delivery fee band? Distances in this range will fail until covered by another band.')) return;
  const { status } = await archiveFee(id);
  if (status?.value) {
    toast.success({ title: 'Archived', message: '', position: 'topRight', layout: 2 });
    await fetchAll();
  }
};
</script>
