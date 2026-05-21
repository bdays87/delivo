<template>
  <header class="sticky top-0 z-30 flex items-center justify-between border-b border-base-300 bg-base-100/80 px-6 py-3 backdrop-blur">
    <div class="flex items-center gap-3">
      <button
        type="button"
        class="btn btn-ghost btn-sm rounded-full lg:hidden"
        aria-label="Toggle sidebar"
        @click="$emit('toggle-sidebar')"
      >
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

      <div class="dropdown dropdown-end">
        <button
          type="button"
          tabindex="0"
          class="btn btn-ghost btn-sm relative rounded-full"
          aria-label="Notifications"
        >
          <Icon name="lucide:bell" class="h-5 w-5" />
          <span
            v-if="unreadCount"
            class="badge badge-primary badge-xs absolute -right-0.5 -top-0.5"
          >{{ unreadCount }}</span>
        </button>
        <div
          tabindex="0"
          class="dropdown-content z-20 mt-2 w-80 rounded-box border border-base-300 bg-base-100 p-0 shadow-lg"
        >
          <div class="border-b border-base-300 px-4 py-3">
            <p class="text-sm font-semibold">Notifications</p>
            <p class="text-xs opacity-60">{{ unreadCount }} unread</p>
          </div>
          <ul class="max-h-72 overflow-y-auto">
            <li
              v-for="n in notifications"
              :key="n.id"
              class="border-b border-base-200 px-4 py-3 last:border-0 hover:bg-base-200/60"
              :class="n.unread ? 'bg-primary/5' : ''"
            >
              <p class="text-sm font-medium">{{ n.title }}</p>
              <p class="mt-0.5 text-xs opacity-70">{{ n.message }}</p>
              <p class="mt-1 text-xs opacity-50">{{ n.time }}</p>
            </li>
          </ul>
          <div class="border-t border-base-300 px-4 py-2 text-center">
            <button type="button" class="btn btn-ghost btn-xs rounded-full">Mark all as read</button>
          </div>
        </div>
      </div>

      <div class="dropdown dropdown-end">
        <button tabindex="0" type="button" class="btn btn-ghost btn-sm gap-2 rounded-full">
          <span class="grid h-7 w-7 place-items-center rounded-full bg-primary/10 text-xs font-bold text-primary">
            {{ initials }}
          </span>
          <span class="hidden text-sm md:inline">{{ displayName }}</span>
          <Icon name="lucide:chevron-down" class="h-3 w-3 opacity-60" />
        </button>
        <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-10 mt-2 w-56 p-2 shadow">
          <li class="menu-title">{{ auth.user?.email }}</li>
          <li>
            <NuxtLink to="/vendor/settings" class="gap-2">
              <Icon name="lucide:user" class="h-4 w-4" />
              Profile
            </NuxtLink>
          </li>
          <li>
            <button type="button" class="gap-2" @click="handleLogout">
              <Icon name="lucide:log-out" class="h-4 w-4" />
              Sign out
            </button>
          </li>
        </ul>
      </div>
    </div>
  </header>
</template>

<script setup lang="ts">
defineEmits(['toggle-sidebar']);

const auth = useAuthStore();
const route = useRoute();

const displayName = computed(() => auth.user?.name?.trim() || 'Vendor');

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
  if (last === 'vendor' || !last) return 'Dashboard';
  return last.replace(/-/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
});

const breadcrumb = computed(() => route.path);

const notifications = [
  {
    id: 1,
    title: 'New order received',
    message: 'Order #1042 is waiting for your confirmation.',
    time: '5 min ago',
    unread: true,
  },
  {
    id: 2,
    title: 'Low stock alert',
    message: 'Organic maize meal is down to 3 units.',
    time: '1 hr ago',
    unread: true,
  },
  {
    id: 3,
    title: 'Payout processed',
    message: 'Your weekly earnings of $248.50 have been sent.',
    time: 'Yesterday',
    unread: false,
  },
  {
    id: 4,
    title: 'Coupon expiring soon',
    message: 'SUMMER10 expires in 2 days.',
    time: '2 days ago',
    unread: false,
  },
];

const unreadCount = computed(() => notifications.filter((n) => n.unread).length);

const handleLogout = async () => {
  try {
    await auth.logout();
  } catch (err) {
    console.error(err);
  }
};
</script>
