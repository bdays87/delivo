<template>
  <div>
    <div class="flex flex-wrap items-end justify-between gap-3">
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight">Categories</h1>
        <p class="mt-1 text-sm opacity-70">
          Marketplace catalogue taxonomy. Archived categories stay on existing products but
          are hidden from new listings and the storefront grid.
        </p>
      </div>
      <button class="btn btn-primary rounded-full" @click="openCreate">
        <Icon name="lucide:plus" class="h-4 w-4" />
        New category
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
            <th>Category</th>
            <th>Slug</th>
            <th>Parent</th>
            <th>Order</th>
            <th>Status</th>
            <th class="text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="!store.items.length">
            <td colspan="7" class="py-10 text-center text-base-content/60">No categories yet.</td>
          </tr>
          <tr v-for="(c, i) in store.items" :key="c.id">
            <td>{{ i + 1 }}</td>
            <td>
              <div class="flex items-center gap-2">
                <span
                  v-if="c.emoji"
                  class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-base-200 text-lg"
                >{{ c.emoji }}</span>
                <span v-else class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-primary/10 text-primary">
                  <Icon :name="c.icon || 'lucide:tag'" class="h-4 w-4" />
                </span>
                <div>
                  <div class="font-semibold">{{ c.name }}</div>
                  <div v-if="c.description" class="max-w-xs truncate text-xs opacity-60">{{ c.description }}</div>
                </div>
              </div>
            </td>
            <td class="font-mono text-xs">{{ c.slug }}</td>
            <td class="text-sm">{{ c.parent?.name ?? '—' }}</td>
            <td>{{ c.sort_order }}</td>
            <td>
              <span :class="['badge badge-sm', c.status === 'ACTIVE' ? 'badge-success' : 'badge-ghost']">{{ c.status }}</span>
            </td>
            <td class="text-right">
              <div class="flex justify-end gap-2">
                <button class="btn btn-xs btn-ghost rounded-full" @click="openEdit(c.id)">
                  <Icon name="lucide:pencil" class="h-3.5 w-3.5" />
                  Edit
                </button>
                <button
                  v-if="c.status === 'ACTIVE'"
                  class="btn btn-xs btn-ghost rounded-full text-warning"
                  @click="confirmArchive(c.id)"
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

    <dialog :open="modalOpen" class="modal" :class="{ 'modal-open': modalOpen }">
      <div class="modal-box max-w-lg">
        <h3 class="text-lg font-bold">{{ editingId ? 'Edit category' : 'New category' }}</h3>

        <form class="mt-4 flex flex-col gap-3" @submit.prevent="handleSubmit">
          <label class="fieldset">
            <span class="fieldset-legend">Name</span>
            <input
              v-model="form.name"
              type="text"
              placeholder="e.g. Groceries"
              :class="['input input-bordered w-full', errors.name ? 'input-error' : '']"
              @input="maybeAutoSlug"
            />
            <span v-if="errors.name" class="text-xs text-red-600">{{ errors.name }}</span>
          </label>

          <label class="fieldset">
            <span class="fieldset-legend">Slug</span>
            <input
              v-model="form.slug"
              type="text"
              placeholder="e.g. groceries"
              :class="['input input-bordered w-full font-mono', errors.slug ? 'input-error' : '']"
            />
            <span v-if="errors.slug" class="text-xs text-red-600">{{ errors.slug }}</span>
          </label>

          <label class="fieldset">
            <span class="fieldset-legend">Parent category</span>
            <select v-model="form.parent_id" class="select select-bordered w-full">
              <option :value="null">None (top-level)</option>
              <option
                v-for="p in parentOptions"
                :key="p.id"
                :value="p.id"
              >
                {{ p.name }}
              </option>
            </select>
            <span v-if="errors.parent_id" class="text-xs text-red-600">{{ errors.parent_id }}</span>
          </label>

          <div class="grid gap-3 sm:grid-cols-2">
            <label class="fieldset">
              <span class="fieldset-legend">Icon (Lucide)</span>
              <input
                v-model="form.icon"
                type="text"
                placeholder="lucide:tag"
                :class="['input input-bordered w-full font-mono text-sm', errors.icon ? 'input-error' : '']"
              />
            </label>
            <label class="fieldset">
              <span class="fieldset-legend">Emoji (storefront)</span>
              <input
                v-model="form.emoji"
                type="text"
                placeholder="🛒"
                maxlength="4"
                class="input input-bordered w-full"
              />
            </label>
          </div>

          <label class="fieldset">
            <span class="fieldset-legend">Tint classes (Tailwind gradient)</span>
            <input
              v-model="form.tint"
              type="text"
              placeholder="from-emerald-100 to-emerald-50"
              class="input input-bordered w-full font-mono text-sm"
            />
          </label>

          <label class="fieldset">
            <span class="fieldset-legend">Description</span>
            <textarea
              v-model="form.description"
              rows="2"
              class="textarea textarea-bordered w-full"
              placeholder="Optional short description"
            />
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
useHead({ title: 'Categories — Delivo Admin' });

const store = useAdminCategoryStore();

const modalOpen = ref(false);
const editingId = ref<number | null>(null);
const slugTouched = ref(false);

const blankForm = () => ({
  parent_id: null as number | null,
  name: '',
  slug: '',
  icon: 'lucide:tag',
  emoji: '' as string | null,
  tint: '' as string | null,
  description: '' as string | null,
  sort_order: 0,
  status: 'ACTIVE' as 'ACTIVE' | 'ARCHIVED',
});
const form = reactive(blankForm());
const errors = reactive<Record<string, string>>({});

const parentOptions = computed(() =>
  store.activeParents.filter((p) => p.id !== editingId.value),
);

onMounted(() => {
  store.fetchAll();
});

const clearErrors = () => {
  Object.keys(errors).forEach((k) => delete errors[k]);
};

const maybeAutoSlug = () => {
  if (!slugTouched.value && !editingId.value) {
    form.slug = slugifyCategoryName(form.name);
  }
};

const openCreate = () => {
  Object.assign(form, blankForm());
  editingId.value = null;
  slugTouched.value = false;
  clearErrors();
  modalOpen.value = true;
};

const openEdit = async (id: number) => {
  const category = await store.findOne(id);
  if (!category) return;
  Object.assign(form, {
    parent_id: category.parent_id,
    name: category.name,
    slug: category.slug,
    icon: category.icon,
    emoji: category.emoji ?? '',
    tint: category.tint ?? '',
    description: category.description ?? '',
    sort_order: category.sort_order,
    status: category.status,
  });
  editingId.value = id;
  slugTouched.value = true;
  clearErrors();
  modalOpen.value = true;
};

const closeModal = () => {
  modalOpen.value = false;
};

const applyServerErrors = (payload: any) => {
  const fields = payload?.errors ?? payload?.data;
  const bag = fields?.errors ?? fields;
  if (bag && typeof bag === 'object') {
    Object.keys(bag).forEach((k) => {
      if (Array.isArray(bag[k]) && bag[k][0]) errors[k] = bag[k][0];
    });
  }
};

const normalizePayload = (valid: Record<string, unknown>) => ({
  ...valid,
  parent_id: valid.parent_id ?? null,
  emoji: valid.emoji || null,
  tint: valid.tint || null,
  description: valid.description || null,
});

const handleSubmit = async () => {
  clearErrors();
  try {
    const valid = await CategorySchema.validate(
      { ...form, slug: form.slug.trim().toLowerCase() },
      { abortEarly: false },
    );
    const payload = normalizePayload(valid as Record<string, unknown>);
    const createPayload = {
      parent_id: payload.parent_id,
      name: payload.name,
      slug: payload.slug,
      icon: payload.icon,
      emoji: payload.emoji,
      tint: payload.tint,
      description: payload.description,
      sort_order: payload.sort_order,
    };
    const ok = editingId.value
      ? await store.update(editingId.value, payload)
      : await store.create(createPayload);
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
  if (!window.confirm('Archive this category? It will no longer appear on the storefront.')) return;
  await store.archive(id);
};
</script>
