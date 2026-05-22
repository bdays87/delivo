<template>
  <div>
    <div class="flex flex-wrap items-end justify-between gap-3">
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight">Roles</h1>
        <p class="mt-1 text-sm opacity-70">
          Manage roles and the permissions they grant. The system roles
          (<span class="font-mono">admin</span>, <span class="font-mono">vendor</span>,
          <span class="font-mono">customer</span>) cannot be deleted or renamed but can have
          their permissions edited.
        </p>
      </div>
      <button class="btn btn-primary rounded-full" @click="openCreate">
        <Icon name="lucide:plus" class="h-4 w-4" /> New role
      </button>
    </div>

    <div v-if="loading" class="flex justify-center py-16">
      <span class="loading loading-spinner loading-lg"></span>
    </div>

    <div v-else class="mt-6 overflow-hidden rounded-3xl border border-base-300 bg-base-100">
      <table class="table">
        <thead class="bg-base-200/50 text-xs uppercase tracking-wider opacity-70">
          <tr>
            <th>Name</th>
            <th>Guard</th>
            <th>Permissions</th>
            <th></th>
            <th class="text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="!roles.length">
            <td colspan="5" class="py-10 text-center text-base-content/60">No roles yet.</td>
          </tr>
          <tr v-for="r in roles" :key="r.id">
            <td class="font-mono font-semibold">{{ r.name }}</td>
            <td class="text-xs opacity-70">{{ r.guard_name }}</td>
            <td>{{ r.permissions_count }}</td>
            <td>
              <span v-if="r.is_protected" class="badge badge-sm badge-warning gap-1">
                <Icon name="lucide:lock" class="h-3 w-3" /> Protected
              </span>
            </td>
            <td class="text-right">
              <div class="flex justify-end gap-2">
                <NuxtLink :to="`/admin/roles/${r.id}`" class="btn btn-xs btn-ghost rounded-full">
                  <Icon name="lucide:settings-2" class="h-3.5 w-3.5" /> Edit permissions
                </NuxtLink>
                <button
                  v-if="!r.is_protected"
                  class="btn btn-xs btn-ghost rounded-full text-error"
                  @click="confirmDelete(r)"
                >
                  <Icon name="lucide:trash-2" class="h-3.5 w-3.5" /> Delete
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <dialog :open="modalOpen" class="modal" :class="{ 'modal-open': modalOpen }">
      <div class="modal-box max-w-md">
        <h3 class="text-lg font-bold">New role</h3>
        <p class="mt-2 text-sm opacity-70">
          Use lowercase letters, numbers, dots, underscores or hyphens.
          e.g. <span class="font-mono">shipper</span>, <span class="font-mono">finance.viewer</span>.
        </p>
        <form class="mt-4" @submit.prevent="onCreate">
          <label class="fieldset">
            <span class="fieldset-legend">Role name</span>
            <input
              v-model="newName"
              type="text"
              placeholder="e.g. shipper"
              :class="['input input-bordered w-full font-mono', errorMessage ? 'input-error' : '']"
            />
            <span v-if="errorMessage" class="text-xs text-red-600">{{ errorMessage }}</span>
          </label>
          <div class="modal-action">
            <button type="button" class="btn rounded-full" @click="modalOpen = false">Cancel</button>
            <button type="submit" class="btn btn-primary rounded-full" :disabled="submitting">
              <span v-if="submitting">Creating…</span>
              <span v-else>Create role</span>
            </button>
          </div>
        </form>
      </div>
    </dialog>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ layout: 'admin', middleware: ['auth', 'admin'] });
useHead({ title: 'Roles — Delivo Admin' });

interface RoleRow {
  id: number;
  name: string;
  guard_name: string;
  permissions_count: number;
  is_protected: boolean;
}

const { listRoles, createRole, deleteRole } = useAdminRoleHelper();
const toast = useToast();
const router = useRouter();

const roles = ref<RoleRow[]>([]);
const loading = ref(false);
const submitting = ref(false);
const modalOpen = ref(false);
const newName = ref('');
const errorMessage = ref('');

const fetchAll = async () => {
  loading.value = true;
  const { data, error } = await listRoles();
  if (!error.value) {
    roles.value = (data.value as any)?.data ?? [];
  }
  loading.value = false;
};

onMounted(fetchAll);

const openCreate = () => {
  newName.value = '';
  errorMessage.value = '';
  modalOpen.value = true;
};

const onCreate = async () => {
  errorMessage.value = '';
  const name = newName.value.trim();
  if (!/^[a-z0-9_.-]+$/.test(name)) {
    errorMessage.value = 'Use lowercase letters, numbers, dots, underscores or hyphens.';
    return;
  }
  submitting.value = true;
  const { data, status, error } = await createRole(name);
  if (status?.value) {
    const created = (data.value as any)?.data;
    modalOpen.value = false;
    toast.success({ title: 'Role created', message: name, position: 'topRight', layout: 2 });
    if (created?.id) router.push(`/admin/roles/${created.id}`);
    else await fetchAll();
  } else {
    const apiMsg = (error?.value as any)?.data?.errors?.name?.[0]
      || (error?.value as any)?.data?.message
      || 'Could not create role.';
    errorMessage.value = apiMsg;
  }
  submitting.value = false;
};

const confirmDelete = async (r: RoleRow) => {
  if (!window.confirm(`Delete role "${r.name}"? Users with this role will lose its permissions.`)) return;
  const { status, error } = await deleteRole(r.id);
  if (status?.value) {
    toast.success({ title: 'Role deleted', message: r.name, position: 'topRight', layout: 2 });
    await fetchAll();
  } else {
    toast.error({
      title: 'Error',
      message: (error?.value as any)?.data?.message || 'Could not delete role.',
      position: 'topRight',
      layout: 2,
    });
  }
};
</script>
