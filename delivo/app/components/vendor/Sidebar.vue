<template>
  <aside class="flex h-full w-64 flex-col border-r border-base-300 bg-base-100">
    <div class="flex items-center gap-2 border-b border-base-300 px-5 py-4">
      <NuxtLink to="/vendor" class="flex items-center gap-2">
        <span class="grid h-9 w-9 place-items-center rounded-2xl bg-primary text-primary-content">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M3 7h11l2 4h5v6h-2a3 3 0 1 1-6 0H9a3 3 0 1 1-6 0V7z" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </span>
        <div class="flex flex-col leading-tight">
          <span class="text-lg font-extrabold tracking-tight">delivo<span class="text-primary">.</span></span>
          <span class="text-[10px] uppercase tracking-wider opacity-60">Vendor portal</span>
        </div>
      </NuxtLink>
    </div>

    <nav class="flex-1 overflow-y-auto p-3">
      <ul class="menu w-full gap-1 p-0">
        <li v-for="item in navItems" :key="item.to">
          <NuxtLink
            :to="item.to"
            :class="['gap-2 rounded-xl', isActive(item.to) ? 'menu-active bg-primary text-primary-content' : '']"
          >
            <Icon :name="item.icon" class="h-4 w-4" />
            <span>{{ item.label }}</span>
          </NuxtLink>
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
const auth = useAuthStore();
const route = useRoute();

const navItems = [
  { label: 'Dashboard', to: '/vendor', icon: 'lucide:layout-dashboard' },
  { label: 'Products', to: '/vendor/products', icon: 'lucide:package' },
  { label: 'Orders', to: '/vendor/orders', icon: 'lucide:shopping-cart' },
  { label: 'Active carts', to: '/vendor/carts', icon: 'lucide:shopping-bag' },
  { label: 'Coupons', to: '/vendor/coupons', icon: 'lucide:ticket' },
  { label: 'Earnings', to: '/vendor/earnings', icon: 'lucide:banknote' },
  { label: 'Store settings', to: '/vendor/settings', icon: 'lucide:settings' },
] as const;

const isActive = (url: string) => {
  if (url === '/vendor') return route.path === '/vendor';
  return route.path === url || route.path.startsWith(`${url}/`);
};
</script>
