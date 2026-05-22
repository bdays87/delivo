<template>
  <div>
    <div v-if="loading" class="flex justify-center py-16">
      <span class="loading loading-spinner loading-lg"></span>
    </div>

    <div v-else-if="!role" class="rounded-3xl border border-base-300 bg-base-100 p-8 text-center">
      <p>Role not found.</p>
      <NuxtLink to="/admin/roles" class="btn btn-primary mt-4 rounded-full">Back to roles</NuxtLink>
    </div>

    <div v-else>
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
          <NuxtLink to="/admin/roles" class="btn btn-ghost btn-sm rounded-full">
            <Icon name="lucide:arrow-left" class="h-4 w-4" />
          </NuxtLink>
          <div>
            <h1 class="text-2xl font-extrabold tracking-tight font-mono">{{ role.name }}</h1>
            <p class="mt-1 text-xs opacity-60">guard: {{ role.guard_name }}</p>
          </div>
          <span v-if="role.is_protected" class="badge badge-warning gap-1">
            <Icon name="lucide:lock" class="h-3 w-3" /> Protected
          </span>
        </div>
        <div class="flex items-center gap-3 text-sm opacity-70">
          <span class="font-semibold">{{ checkedIds.size }}</span> of
          <span>{{ totalPermissionCount }}</span> permissions selected
        </div>
      </div>

      <div
        v-if="role.is_protected && role.name === 'admin'"
        class="mt-4 rounded-3xl border border-warning/40 bg-warning/5 p-4 text-sm"
      >
        <Icon name="lucide:info" class="mr-1 inline h-4 w-4" />
        Editing the <span class="font-mono">admin</span> role can lock administrators out of pages. Proceed
        carefully — at minimum keep <span class="font-mono">can.access.roles</span> selected so you can
        come back here.
      </div>

      <div v-if="!tree" class="mt-6 text-sm opacity-70">No permissions seeded yet.</div>

      <div v-else class="mt-6 space-y-6">
        <section
          v-for="m in tree.modules"
          :key="m.id"
          class="rounded-3xl border border-base-300 bg-base-100 p-6"
        >
          <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
              <span class="grid h-9 w-9 place-items-center rounded-xl bg-primary/10 text-primary">
                <Icon :name="m.icon || 'lucide:layout-grid'" class="h-4 w-4" />
              </span>
              <h2 class="text-lg font-bold">{{ m.name }}</h2>
            </div>
            <div class="flex gap-2 text-xs">
              <button class="btn btn-ghost btn-xs rounded-full" @click="selectAllInModule(m, true)">
                Select all
              </button>
              <button class="btn btn-ghost btn-xs rounded-full" @click="selectAllInModule(m, false)">
                Clear
              </button>
            </div>
          </div>

          <div class="mt-4 space-y-4">
            <div v-for="s in m.submodules" :key="s.id" class="rounded-2xl border border-base-300 bg-base-200/40 p-4">
              <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                  <Icon :name="s.icon || 'lucide:layers'" class="h-4 w-4 opacity-70" />
                  <span class="font-semibold">{{ s.name }}</span>
                  <span class="font-mono text-xs opacity-60">{{ s.url }}</span>
                </div>
                <button class="btn btn-ghost btn-xs rounded-full" @click="selectAllInSubmodule(s, !allCheckedInSubmodule(s))">
                  {{ allCheckedInSubmodule(s) ? 'Clear' : 'Select all' }}
                </button>
              </div>
              <ul class="mt-3 grid gap-2 md:grid-cols-2">
                <li v-for="p in s.permissions" :key="p.id" class="flex items-center gap-2">
                  <input
                    :id="`perm-${p.id}`"
                    type="checkbox"
                    class="checkbox checkbox-sm checkbox-primary"
                    :checked="checkedIds.has(p.id)"
                    @change="toggle(p.id)"
                  />
                  <label :for="`perm-${p.id}`" class="cursor-pointer font-mono text-xs">
                    {{ p.name }}
                  </label>
                </li>
                <li v-if="!s.permissions.length" class="text-xs opacity-60 md:col-span-2">
                  No permissions yet.
                </li>
              </ul>
            </div>
          </div>
        </section>

        <section
          v-if="tree.orphan_permissions.length"
          class="rounded-3xl border border-base-300 bg-base-100 p-6"
        >
          <h2 class="text-lg font-bold">Other permissions</h2>
          <p class="mt-1 text-sm opacity-70">
            Permissions not yet attached to a submodule. Useful for ad-hoc system permissions.
          </p>
          <ul class="mt-3 grid gap-2 md:grid-cols-2 lg:grid-cols-3">
            <li v-for="p in tree.orphan_permissions" :key="p.id" class="flex items-center gap-2">
              <input
                :id="`orphan-${p.id}`"
                type="checkbox"
                class="checkbox checkbox-sm checkbox-primary"
                :checked="checkedIds.has(p.id)"
                @change="toggle(p.id)"
              />
              <label :for="`orphan-${p.id}`" class="cursor-pointer font-mono text-xs">{{ p.name }}</label>
            </li>
          </ul>
        </section>

        <div class="sticky bottom-4 flex justify-end">
          <button class="btn btn-primary btn-lg rounded-full shadow-lg" :disabled="saving" @click="onSave">
            <span v-if="saving" class="loading loading-spinner loading-xs"></span>
            Save permissions
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ layout: 'admin', middleware: ['auth', 'admin'] });

interface PermissionRow { id: number; name: string; guard_name: string; }
interface SubmoduleNode {
  id: number; name: string; icon: string | null; url: string; permissions: PermissionRow[];
}
interface ModuleNode {
  id: number; name: string; icon: string | null; submodules: SubmoduleNode[];
}
interface TreePayload { modules: ModuleNode[]; orphan_permissions: PermissionRow[]; }
interface RoleDetail {
  id: number;
  name: string;
  guard_name: string;
  is_protected: boolean;
  permissions: PermissionRow[];
}

const route = useRoute();
const toast = useToast();
const { getRole, syncPermissions } = useAdminRoleHelper();
const { getModuleTree } = useAdminModuleHelper();

const roleId = computed(() => Number(route.params.id));
const role = ref<RoleDetail | null>(null);
const tree = ref<TreePayload | null>(null);
const loading = ref(false);
const saving = ref(false);
const checkedIds = ref(new Set<number>());

useHead({ title: () => `${role.value?.name ?? 'Role'} — Delivo Admin` });

const totalPermissionCount = computed(() => {
  if (!tree.value) return 0;
  const inSubs = tree.value.modules.reduce(
    (sum, m) => sum + m.submodules.reduce((s, sub) => s + sub.permissions.length, 0),
    0,
  );
  return inSubs + tree.value.orphan_permissions.length;
});

onMounted(async () => {
  loading.value = true;
  const [{ data: roleRes }, { data: treeRes }] = await Promise.all([
    getRole(roleId.value),
    getModuleTree(),
  ]);
  role.value = ((roleRes.value as any)?.data ?? null) as RoleDetail | null;
  tree.value = ((treeRes.value as any)?.data ?? null) as TreePayload | null;
  if (role.value) {
    checkedIds.value = new Set(role.value.permissions.map((p) => p.id));
  }
  loading.value = false;
});

const toggle = (permissionId: number) => {
  const next = new Set(checkedIds.value);
  if (next.has(permissionId)) next.delete(permissionId);
  else next.add(permissionId);
  checkedIds.value = next;
};

const selectAllInSubmodule = (s: SubmoduleNode, on: boolean) => {
  const next = new Set(checkedIds.value);
  for (const p of s.permissions) {
    if (on) next.add(p.id);
    else next.delete(p.id);
  }
  checkedIds.value = next;
};

const allCheckedInSubmodule = (s: SubmoduleNode): boolean =>
  s.permissions.length > 0 && s.permissions.every((p) => checkedIds.value.has(p.id));

const selectAllInModule = (m: ModuleNode, on: boolean) => {
  for (const s of m.submodules) selectAllInSubmodule(s, on);
};

const onSave = async () => {
  if (!role.value) return;
  saving.value = true;
  const ids = Array.from(checkedIds.value);
  const { status, error } = await syncPermissions(role.value.id, ids);
  if (status?.value) {
    toast.success({
      title: 'Permissions saved',
      message: `${ids.length} permissions on ${role.value.name}`,
      position: 'topRight',
      layout: 2,
    });
  } else {
    toast.error({
      title: 'Error',
      message: (error?.value as any)?.data?.message || 'Could not save permissions.',
      position: 'topRight',
      layout: 2,
    });
  }
  saving.value = false;
};
</script>
