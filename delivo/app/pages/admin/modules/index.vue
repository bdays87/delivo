<template>
  <div>
    <div class="flex flex-wrap items-end justify-between gap-3">
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight">System modules</h1>
        <p class="mt-1 text-sm opacity-70">
          Read-only view of the module / submodule / permission tree. Modules are managed via
          database seeders — use this page to inspect what's in the system and which permissions
          each submodule controls.
        </p>
      </div>
    </div>

    <div v-if="loading" class="flex justify-center py-16">
      <span class="loading loading-spinner loading-lg"></span>
    </div>

    <div v-else-if="!modules.length" class="mt-6 rounded-3xl border border-dashed border-base-300 p-12 text-center text-sm opacity-70">
      No modules seeded yet.
    </div>

    <div v-else class="mt-6 overflow-hidden rounded-3xl border border-base-300 bg-base-100">
      <table class="table">
        <thead class="bg-base-200/50 text-xs uppercase tracking-wider opacity-70">
          <tr>
            <th></th>
            <th>Module</th>
            <th>Default permission</th>
            <th>Submodules</th>
            <th>Sort</th>
            <th>Status</th>
            <th class="text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="m in modules" :key="m.id">
            <td>
              <span class="grid h-9 w-9 place-items-center rounded-xl bg-primary/10 text-primary">
                <Icon :name="m.icon || 'lucide:layout-grid'" class="h-4 w-4" />
              </span>
            </td>
            <td class="font-semibold">{{ m.name }}</td>
            <td class="font-mono text-xs opacity-70">{{ m.default_permission }}</td>
            <td>{{ m.submodules_count ?? 0 }}</td>
            <td>{{ m.sort_order }}</td>
            <td>
              <span :class="['badge badge-sm', m.status === 'active' ? 'badge-success' : 'badge-ghost']">
                {{ m.status }}
              </span>
            </td>
            <td class="text-right">
              <NuxtLink :to="`/admin/modules/${m.id}`" class="btn btn-xs btn-ghost rounded-full">
                View →
              </NuxtLink>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ layout: 'admin', middleware: ['auth', 'admin'] });
useHead({ title: 'System modules — Delivo Admin' });

interface ModuleRow {
  id: number;
  name: string;
  icon: string | null;
  default_permission: string;
  status: string;
  sort_order: number;
  submodules_count?: number;
}

const { listModules } = useAdminModuleHelper();
const modules = ref<ModuleRow[]>([]);
const loading = ref(false);

onMounted(async () => {
  loading.value = true;
  const { data, error } = await listModules();
  if (!error.value) {
    modules.value = (data.value as any)?.data ?? [];
  }
  loading.value = false;
});
</script>
