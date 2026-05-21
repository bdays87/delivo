<template>
  <header class="sticky top-0 z-30 flex items-center justify-between border-b border-base-300 bg-base-100/80 px-6 py-3 backdrop-blur">
    <div class="flex items-center gap-3">
      <button class="btn btn-ghost btn-sm rounded-full lg:hidden" aria-label="Toggle sidebar" @click="$emit('toggle-sidebar')">
        <Icon name="lucide:menu" class="h-5 w-5" />
      </button>
      <div class="flex flex-col leading-tight">
        <span class="text-sm font-semibold">{{ pageTitle }}</span>
        <span class="text-xs opacity-60">{{ breadcrumb }}</span>
      </div>
    </div>

    <div class="flex items-center gap-2">
      <NuxtLink to="/" class="btn btn-ghost btn-sm rounded-full">
        <Icon name="lucide:external-link" class="h-4 w-4" />
        <span class="hidden md:inline">View storefront</span>
      </NuxtLink>

      <button class="btn btn-ghost btn-sm rounded-full" aria-label="Notifications">
        <Icon name="lucide:bell" class="h-5 w-5" />
      </button>

      <div class="dropdown dropdown-end">
        <button tabindex="0" class="btn btn-ghost btn-sm gap-2 rounded-full">
          <span class="grid h-7 w-7 place-items-center rounded-full bg-primary/10 text-xs font-bold text-primary">
            {{ initials }}
          </span>
          <span class="hidden text-sm md:inline">{{ auth.user?.name }}</span>
          <Icon name="lucide:chevron-down" class="h-3 w-3 opacity-60" />
        </button>
        <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-10 mt-2 w-56 p-2 shadow">
          <li class="menu-title">{{ auth.user?.email }}</li>
          <li><NuxtLink to="/account">My account</NuxtLink></li>
          <li><button @click="handleLogout">Sign out</button></li>
        </ul>
      </div>
    </div>
  </header>
</template>

<script setup lang="ts">
defineEmits(['toggle-sidebar']);

const auth = useAuthStore();
const route = useRoute();

const initials = computed(() => {
  const name = auth.user?.name ?? '';
  return name
    .split(/\s+/)
    .filter(Boolean)
    .slice(0, 2)
    .map((s) => s[0]?.toUpperCase() ?? '')
    .join('');
});

const pageTitle = computed(() => {
  const segments = route.path.split('/').filter(Boolean);
  const last = segments[segments.length - 1] ?? '';
  if (last === 'admin' || !last) return 'Dashboard';
  return last.replace(/-/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
});

const breadcrumb = computed(() => route.path);

const handleLogout = async () => {
  try {
    await auth.logout();
  } catch (err) {
    console.error(err);
  }
};
</script>
