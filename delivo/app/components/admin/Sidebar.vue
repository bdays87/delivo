<template>
  <aside class="flex h-full w-64 flex-col border-r border-base-300 bg-base-100">
    <div class="flex items-center gap-2 border-b border-base-300 px-5 py-4">
      <NuxtLink to="/admin" class="flex items-center gap-2">
        <span class="grid h-9 w-9 place-items-center rounded-2xl bg-primary text-primary-content">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M3 7h11l2 4h5v6h-2a3 3 0 1 1-6 0H9a3 3 0 1 1-6 0V7z" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </span>
        <div class="flex flex-col leading-tight">
          <span class="text-lg font-extrabold tracking-tight">delivo<span class="text-primary">.</span></span>
          <span class="text-[10px] uppercase tracking-wider opacity-60">Platform admin</span>
        </div>
      </NuxtLink>
    </div>

    <nav class="flex-1 overflow-y-auto p-3">
      <ul class="menu w-full gap-1 p-0">
        <li>
          <NuxtLink
            to="/admin"
            :class="['gap-2 rounded-xl', isActive('/admin') ? 'menu-active bg-primary text-primary-content' : '']"
          >
            <Icon name="lucide:layout-dashboard" class="h-4 w-4" />
            <span>Dashboard</span>
          </NuxtLink>
        </li>

        <li v-for="m in modules" :key="m.uuid" class="mt-2">
          <details :open="hasActiveChild(m)">
            <summary class="gap-2 rounded-xl font-semibold">
              <Icon :name="m.icon" class="h-4 w-4" />
              <span>{{ m.name }}</span>
            </summary>
            <ul class="mt-1">
              <li v-for="s in m.submodules" :key="s.uuid">
                <NuxtLink
                  :to="s.url"
                  :class="['gap-2 rounded-xl text-sm', isActive(s.url) ? 'menu-active bg-primary text-primary-content' : '']"
                >
                  <Icon :name="s.icon" class="h-4 w-4" />
                  <span>{{ s.name }}</span>
                </NuxtLink>
              </li>
            </ul>
          </details>
        </li>

        <li v-if="!modules.length" class="px-3 py-4 text-xs opacity-60">
          No modules available for your role.
        </li>
      </ul>
    </nav>

    <div class="border-t border-base-300 p-4 text-xs opacity-60">
      Signed in as
      <div class="mt-1 truncate font-semibold text-base-content">{{ auth.user?.email }}</div>
    </div>
  </aside>
</template>

<script setup lang="ts">
import type { ModuleTreeNode } from '~/stores/auth';

const auth = useAuthStore();
const route = useRoute();

const modules = computed<ModuleTreeNode[]>(() => auth.user?.modules ?? []);

const isActive = (url: string) => {
  if (url === '/admin') return route.path === '/admin';
  return route.path === url || route.path.startsWith(`${url}/`);
};

const hasActiveChild = (m: ModuleTreeNode) => m.submodules.some((s) => isActive(s.url));
</script>
