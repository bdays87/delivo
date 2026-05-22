<template>
  <div>
    <div v-if="loading" class="flex justify-center py-16">
      <span class="loading loading-spinner loading-lg"></span>
    </div>

    <div v-else-if="!module" class="rounded-3xl border border-base-300 bg-base-100 p-8 text-center">
      <p>Module not found.</p>
      <NuxtLink to="/admin/modules" class="btn btn-primary mt-4 rounded-full">Back to modules</NuxtLink>
    </div>

    <div v-else>
      <div class="flex items-center gap-3">
        <NuxtLink to="/admin/modules" class="btn btn-ghost btn-sm rounded-full">
          <Icon name="lucide:arrow-left" class="h-4 w-4" />
        </NuxtLink>
        <span class="grid h-12 w-12 place-items-center rounded-2xl bg-primary/10 text-primary">
          <Icon :name="module.icon || 'lucide:layout-grid'" class="h-5 w-5" />
        </span>
        <div>
          <h1 class="text-2xl font-extrabold tracking-tight">{{ module.name }}</h1>
          <p class="mt-1 font-mono text-xs opacity-70">{{ module.default_permission }}</p>
        </div>
      </div>

      <div class="mt-6 rounded-3xl border border-base-300 bg-base-100 p-6">
        <h2 class="text-lg font-bold">Submodules</h2>
        <p class="mt-1 text-sm opacity-70">
          Each submodule controls a section of the admin sidebar. Click View to see the permissions
          associated with it.
        </p>

        <div v-if="!module.submodules?.length" class="mt-4 text-sm opacity-70">No submodules.</div>

        <table v-else class="table mt-4">
          <thead class="text-xs uppercase opacity-60">
            <tr>
              <th></th>
              <th>Name</th>
              <th>URL</th>
              <th>Default permission</th>
              <th>Status</th>
              <th class="text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="s in module.submodules" :key="s.id">
              <td>
                <span class="grid h-9 w-9 place-items-center rounded-xl bg-base-200">
                  <Icon :name="s.icon || 'lucide:layers'" class="h-4 w-4" />
                </span>
              </td>
              <td class="font-semibold">{{ s.name }}</td>
              <td class="font-mono text-xs opacity-70">{{ s.url }}</td>
              <td class="font-mono text-xs opacity-70">{{ s.default_permission }}</td>
              <td>
                <span :class="['badge badge-sm', s.status === 'active' ? 'badge-success' : 'badge-ghost']">
                  {{ s.status }}
                </span>
              </td>
              <td class="text-right">
                <NuxtLink
                  :to="`/admin/modules/${module.id}/submodules/${s.id}`"
                  class="btn btn-xs btn-ghost rounded-full"
                >
                  View permissions →
                </NuxtLink>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ layout: 'admin', middleware: ['auth', 'admin'] });

interface ModuleDetail {
  id: number;
  name: string;
  icon: string | null;
  default_permission: string;
  status: string;
  submodules: {
    id: number;
    name: string;
    icon: string | null;
    url: string;
    default_permission: string;
    status: string;
  }[];
}

const route = useRoute();
const { getModule } = useAdminModuleHelper();

const moduleId = computed(() => Number(route.params.id));
const module = ref<ModuleDetail | null>(null);
const loading = ref(false);

onMounted(async () => {
  loading.value = true;
  const { data, error } = await getModule(moduleId.value);
  if (!error.value) {
    module.value = (data.value as any)?.data ?? null;
  }
  loading.value = false;
});

useHead({ title: () => `${module.value?.name ?? 'Module'} — Delivo Admin` });
</script>
