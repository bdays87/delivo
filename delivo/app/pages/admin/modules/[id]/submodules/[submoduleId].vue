<template>
  <div>
    <div v-if="loading" class="flex justify-center py-16">
      <span class="loading loading-spinner loading-lg"></span>
    </div>

    <div v-else-if="!data" class="rounded-3xl border border-base-300 bg-base-100 p-8 text-center">
      <p>Submodule not found.</p>
      <NuxtLink to="/admin/modules" class="btn btn-primary mt-4 rounded-full">Back to modules</NuxtLink>
    </div>

    <div v-else>
      <div class="flex items-center gap-3">
        <NuxtLink :to="`/admin/modules/${data.module.id}`" class="btn btn-ghost btn-sm rounded-full">
          <Icon name="lucide:arrow-left" class="h-4 w-4" />
        </NuxtLink>
        <div>
          <div class="text-xs uppercase tracking-wider opacity-60">{{ data.module.name }}</div>
          <h1 class="text-2xl font-extrabold tracking-tight">{{ data.submodule.name }}</h1>
          <p class="mt-1 font-mono text-xs opacity-70">{{ data.submodule.url }}</p>
        </div>
      </div>

      <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="rounded-3xl border border-base-300 bg-base-100 p-6 lg:col-span-2">
          <h2 class="text-lg font-bold">Permissions</h2>
          <p class="mt-1 text-sm opacity-70">
            Spatie permissions associated with this submodule. The submodule's own default
            permission and its parent module's default permission are auto-attached on read and
            cannot be removed — they're marked "Required".
          </p>

          <ul class="mt-4 divide-y divide-base-300">
            <li v-for="p in data.permissions" :key="p.id" class="flex items-center justify-between gap-3 py-3 text-sm">
              <div>
                <div class="font-mono">{{ p.name }}</div>
                <div class="text-xs opacity-60">guard: {{ p.guard_name }}</div>
              </div>
              <span v-if="p.is_default" class="badge badge-primary badge-sm">Required</span>
              <span v-else class="badge badge-ghost badge-sm">Additional</span>
            </li>
          </ul>
        </div>

        <aside class="rounded-3xl border border-base-300 bg-base-100 p-6 text-sm">
          <h2 class="font-bold">About permissions</h2>
          <p class="mt-2 opacity-70">
            Permissions are managed via seeders — to add or remove them, edit
            <span class="font-mono text-xs">ModulesSeeder.php</span> and re-run
            <span class="font-mono text-xs">db:seed</span>. Use the
            <NuxtLink to="/admin/roles" class="link link-primary">Roles</NuxtLink>
            page to grant these permissions to roles.
          </p>

          <h3 class="mt-4 font-semibold">Defaults</h3>
          <ul class="mt-2 space-y-1 text-xs opacity-70">
            <li>Module: <span class="font-mono">{{ data.module.default_permission }}</span></li>
            <li>Submodule: <span class="font-mono">{{ data.submodule.default_permission }}</span></li>
          </ul>
        </aside>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ layout: 'admin', middleware: ['auth', 'admin'] });

interface PermissionRow {
  id: number;
  name: string;
  guard_name: string;
  is_default: boolean;
}

interface SubmodulePermissionPayload {
  module: { id: number; name: string; default_permission: string };
  submodule: { id: number; name: string; url: string; default_permission: string };
  permissions: PermissionRow[];
}

const route = useRoute();
const { getSubmodulePermissions } = useAdminModuleHelper();

const moduleId = computed(() => Number(route.params.id));
const submoduleId = computed(() => Number(route.params.submoduleId));
const data = ref<SubmodulePermissionPayload | null>(null);
const loading = ref(false);

onMounted(async () => {
  loading.value = true;
  const { data: res, error } = await getSubmodulePermissions(moduleId.value, submoduleId.value);
  if (!error.value) {
    data.value = (res.value as any)?.data ?? null;
  }
  loading.value = false;
});

useHead({ title: () => `${data.value?.submodule.name ?? 'Submodule'} permissions — Delivo Admin` });
</script>
