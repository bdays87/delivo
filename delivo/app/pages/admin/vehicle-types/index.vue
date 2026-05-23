<template>
  <div>
    <div class="flex flex-wrap items-end justify-between gap-3">
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight">Vehicle types</h1>
        <p class="mt-1 text-sm opacity-70">
          The list of vehicles fleets can declare when registering on Delivo. Archiving a type
          hides it from the apply form but doesn't remove it from existing fleet records.
        </p>
      </div>
      <button class="btn btn-primary rounded-full" @click="openCreate">
        <Icon name="lucide:plus" class="h-4 w-4" /> New vehicle type
      </button>
    </div>

    <div v-if="loading" class="flex justify-center py-16">
      <span class="loading loading-spinner loading-lg"></span>
    </div>

    <div v-else class="mt-6 overflow-hidden rounded-3xl border border-base-300 bg-base-100">
      <table class="table">
        <thead class="bg-base-200/50 text-xs uppercase tracking-wider opacity-70">
          <tr>
            <th></th>
            <th>Name</th>
            <th>Icon</th>
            <th>Order</th>
            <th>Status</th>
            <th class="text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="!items.length">
            <td colspan="6" class="py-10 text-center text-base-content/60">No vehicle types yet.</td>
          </tr>
          <tr v-for="v in items" :key="v.id">
            <td>
              <span class="grid h-9 w-9 place-items-center rounded-xl bg-primary/10 text-primary">
                <Icon :name="v.icon || 'lucide:truck'" class="h-4 w-4" />
              </span>
            </td>
            <td class="font-semibold">{{ v.name }}</td>
            <td class="font-mono text-xs opacity-70">{{ v.icon }}</td>
            <td>{{ v.sort_order }}</td>
            <td>
              <span :class="['badge badge-sm', v.status === 'ACTIVE' ? 'badge-success' : 'badge-ghost']">{{ v.status }}</span>
            </td>
            <td class="text-right">
              <div class="flex justify-end gap-2">
                <button class="btn btn-xs btn-ghost rounded-full" @click="openEdit(v)">
                  <Icon name="lucide:pencil" class="h-3.5 w-3.5" /> Edit
                </button>
                <button
                  v-if="v.status === 'ACTIVE'"
                  class="btn btn-xs btn-ghost rounded-full text-warning"
                  @click="confirmArchive(v.id)"
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
        <h3 class="text-lg font-bold">{{ editingId ? 'Edit vehicle type' : 'New vehicle type' }}</h3>
        <form class="mt-4 flex flex-col gap-3" @submit.prevent="onSubmit">
          <label class="fieldset">
            <span class="fieldset-legend">Name</span>
            <input
              v-model="form.name"
              type="text"
              placeholder="e.g. Pickup"
              :class="['input input-bordered w-full', errors.name ? 'input-error' : '']"
            />
            <span v-if="errors.name" class="text-xs text-red-600">{{ errors.name }}</span>
          </label>
          <label class="fieldset">
            <span class="fieldset-legend">Icon (Lucide)</span>
            <input
              v-model="form.icon"
              type="text"
              placeholder="lucide:truck"
              class="input input-bordered w-full font-mono text-sm"
            />
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
              <span v-if="submitting">Saving…</span><span v-else>Save</span>
            </button>
          </div>
        </form>
      </div>
    </dialog>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ layout: 'admin', middleware: ['auth', 'admin'] });
useHead({ title: 'Vehicle types — Delivo Admin' });

interface VehicleType {
  id: number;
  name: string;
  icon: string;
  sort_order: number;
  status: 'ACTIVE' | 'ARCHIVED';
}

const { listAll, create, update, archive } = useAdminVehicleTypeHelper();
const toast = useToast();

const items = ref<VehicleType[]>([]);
const loading = ref(false);
const submitting = ref(false);
const modalOpen = ref(false);
const editingId = ref<number | null>(null);

const blankForm = () => ({ name: '', icon: 'lucide:truck', sort_order: 0, status: 'ACTIVE' as 'ACTIVE' | 'ARCHIVED' });
const form = reactive(blankForm());
const errors = reactive<Record<string, string>>({});

const fetchAll = async () => {
  loading.value = true;
  const { data, error } = await listAll();
  if (!error.value) items.value = (data.value as any)?.data ?? [];
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
const openEdit = (v: VehicleType) => {
  form.name = v.name; form.icon = v.icon; form.sort_order = v.sort_order; form.status = v.status;
  editingId.value = v.id;
  clearErrors();
  modalOpen.value = true;
};

const onSubmit = async () => {
  clearErrors();
  if (!form.name.trim()) { errors.name = 'Name is required.'; return; }
  submitting.value = true;
  const payload = { name: form.name.trim(), icon: form.icon, sort_order: form.sort_order, ...(editingId.value ? { status: form.status } : {}) };
  const { status, error } = editingId.value
    ? await update(editingId.value, payload as Record<string, unknown>)
    : await create(payload as Record<string, unknown>);
  if (status?.value) {
    toast.success({ title: 'Saved', message: '', position: 'topRight', layout: 2 });
    modalOpen.value = false;
    await fetchAll();
  } else {
    const bag = (error?.value as any)?.data?.errors ?? {};
    Object.keys(bag).forEach((k) => { if (bag[k][0]) errors[k] = bag[k][0]; });
    toast.error({ title: 'Error', message: (error?.value as any)?.data?.message || 'Failed to save.', position: 'topRight', layout: 2 });
  }
  submitting.value = false;
};

const confirmArchive = async (id: number) => {
  if (!window.confirm('Archive this vehicle type?')) return;
  const { status } = await archive(id);
  if (status?.value) {
    toast.success({ title: 'Archived', message: '', position: 'topRight', layout: 2 });
    await fetchAll();
  }
};
</script>
