<template>
  <div>
    <div class="flex flex-wrap items-end justify-between gap-3">
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight">Mobile wallets</h1>
        <p class="mt-1 text-sm opacity-70">
          The list shown to vendors when they choose mobile-wallet payout. Archived wallets stay
          on existing vendor records but disappear from new selections.
        </p>
      </div>
      <button class="btn btn-primary rounded-full" @click="openCreate">
        <Icon name="lucide:plus" class="h-4 w-4" />
        New mobile wallet
      </button>
    </div>

    <div v-if="store.loading" class="flex justify-center py-16">
      <span class="loading loading-spinner loading-lg"></span>
    </div>

    <div v-else class="mt-6 overflow-hidden rounded-3xl border border-base-300 bg-base-100">
      <table class="table">
        <thead class="bg-base-200/50 text-xs uppercase tracking-wider opacity-70">
          <tr>
            <th>#</th>
            <th>Code</th>
            <th>Name</th>
            <th>Order</th>
            <th>Status</th>
            <th class="text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="!store.items.length">
            <td colspan="6" class="py-10 text-center text-base-content/60">No mobile wallets yet.</td>
          </tr>
          <tr v-for="(w, i) in store.items" :key="w.id">
            <td>{{ i + 1 }}</td>
            <td class="font-mono text-xs">{{ w.code }}</td>
            <td>{{ w.name }}</td>
            <td>{{ w.sort_order }}</td>
            <td>
              <span :class="['badge badge-sm', w.status === 'ACTIVE' ? 'badge-success' : 'badge-ghost']">{{ w.status }}</span>
            </td>
            <td class="text-right">
              <div class="flex justify-end gap-2">
                <button class="btn btn-xs btn-ghost rounded-full" @click="openEdit(w.id)">
                  <Icon name="lucide:pencil" class="h-3.5 w-3.5" />
                  Edit
                </button>
                <button
                  v-if="w.status === 'ACTIVE'"
                  class="btn btn-xs btn-ghost rounded-full text-warning"
                  @click="confirmArchive(w.id)"
                >
                  <Icon name="lucide:archive" class="h-3.5 w-3.5" />
                  Archive
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Upsert modal -->
    <dialog :open="modalOpen" class="modal" :class="{ 'modal-open': modalOpen }">
      <div class="modal-box">
        <h3 class="text-lg font-bold">{{ editingId ? 'Edit mobile wallet' : 'New mobile wallet' }}</h3>

        <form class="mt-4 flex flex-col gap-3" @submit.prevent="handleSubmit">
          <label class="fieldset">
            <span class="fieldset-legend">Code</span>
            <input
              v-model="form.code"
              type="text"
              placeholder="e.g. ECOCASH"
              :class="['input input-bordered w-full font-mono uppercase', errors.code ? 'input-error' : '']"
            />
            <span v-if="errors.code" class="text-xs text-red-600">{{ errors.code }}</span>
          </label>
          <label class="fieldset">
            <span class="fieldset-legend">Name</span>
            <input
              v-model="form.name"
              type="text"
              placeholder="e.g. Ecocash"
              :class="['input input-bordered w-full', errors.name ? 'input-error' : '']"
            />
            <span v-if="errors.name" class="text-xs text-red-600">{{ errors.name }}</span>
          </label>
          <label class="fieldset">
            <span class="fieldset-legend">Sort order</span>
            <input
              v-model.number="form.sort_order"
              type="number"
              min="0"
              max="1000"
              :class="['input input-bordered w-full', errors.sort_order ? 'input-error' : '']"
            />
          </label>
          <label v-if="editingId" class="fieldset">
            <span class="fieldset-legend">Status</span>
            <select v-model="form.status" class="select select-bordered w-full">
              <option value="ACTIVE">Active</option>
              <option value="ARCHIVED">Archived</option>
            </select>
          </label>

          <div class="modal-action">
            <button type="button" class="btn" @click="closeModal">Cancel</button>
            <button type="submit" class="btn btn-primary" :disabled="store.submitting">
              <span v-if="store.submitting">Saving…</span>
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
useHead({ title: 'Mobile wallets — Delivo Admin' });

const store = useAdminMobileWalletStore();

const modalOpen = ref(false);
const editingId = ref<number | null>(null);

const blankForm = () => ({ code: '', name: '', sort_order: 0, status: 'ACTIVE' as 'ACTIVE' | 'ARCHIVED' });
const form = reactive(blankForm());
const errors = reactive<Record<string, string>>({});

onMounted(() => {
  store.fetchAll();
});

const clearErrors = () => {
  Object.keys(errors).forEach((k) => delete errors[k]);
};

const openCreate = () => {
  Object.assign(form, blankForm());
  editingId.value = null;
  clearErrors();
  modalOpen.value = true;
};

const openEdit = async (id: number) => {
  const wallet = await store.findOne(id);
  if (!wallet) return;
  Object.assign(form, {
    code: wallet.code,
    name: wallet.name,
    sort_order: wallet.sort_order,
    status: wallet.status,
  });
  editingId.value = id;
  clearErrors();
  modalOpen.value = true;
};

const closeModal = () => {
  modalOpen.value = false;
};

const applyServerErrors = (payload: any) => {
  const fields = payload?.errors;
  if (fields && typeof fields === 'object') {
    Object.keys(fields).forEach((k) => {
      if (Array.isArray(fields[k]) && fields[k][0]) errors[k] = fields[k][0];
    });
  }
};

const handleSubmit = async () => {
  clearErrors();
  try {
    const valid = await MobileWalletSchema.validate({
      ...form,
      code: form.code.toUpperCase(),
    }, { abortEarly: false });
    const payload = editingId.value
      ? valid
      : { code: valid.code, name: valid.name, sort_order: valid.sort_order };
    const ok = editingId.value
      ? await store.update(editingId.value, payload)
      : await store.create(payload);
    if (ok) closeModal();
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

const confirmArchive = async (id: number) => {
  if (!window.confirm('Archive this wallet? Vendors will no longer be able to pick it.')) return;
  await store.archive(id);
};
</script>
