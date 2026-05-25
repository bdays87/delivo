<template>
  <header class="sticky top-0 z-40 border-b border-base-300 bg-base-100/80 backdrop-blur">
    <!-- Top bar: logo, search, utilities -->
    <div class="mx-auto flex max-w-7xl items-center gap-3 px-4 py-3 md:gap-4">
      <NuxtLink to="/" class="flex shrink-0 items-center gap-2">
        <span class="grid h-9 w-9 place-items-center rounded-2xl bg-primary text-primary-content shadow-sm md:h-10 md:w-10">
          <svg class="h-4 w-4 md:h-5 md:w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M3 7h11l2 4h5v6h-2a3 3 0 1 1-6 0H9a3 3 0 1 1-6 0V7z" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </span>
        <span class="text-xl font-extrabold tracking-tight md:text-2xl">delivo<span class="text-primary">.</span></span>
      </NuxtLink>

      <label class="input input-bordered hidden min-w-0 flex-1 items-center gap-2 rounded-full bg-base-200 md:flex">
        <svg class="h-4 w-4 opacity-50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="11" cy="11" r="7" /><path d="m20 20-3.5-3.5" stroke-linecap="round" />
        </svg>
        <input
          v-model="searchInput"
          type="text"
          placeholder="Search for products, vendors, brands…"
          class="grow bg-transparent"
          @keyup.enter="submitSearch"
        />
      </label>

      <div class="ml-auto flex items-center gap-1">
        <div v-if="auth.isAuthenticated" class="dropdown dropdown-end hidden lg:block">
          <button tabindex="0" class="btn btn-ghost btn-sm gap-2 rounded-full">
            <span class="grid h-7 w-7 place-items-center rounded-full bg-primary/10 text-xs font-bold text-primary">
              {{ initials }}
            </span>
            <span class="hidden max-w-[8rem] truncate text-sm xl:inline">{{ auth.user?.name }}</span>
          </button>
          <ul tabindex="0" class="dropdown-content menu rounded-box z-10 mt-2 w-52 bg-base-100 p-2 shadow">
            <li class="menu-title">{{ auth.user?.email }}</li>
            <li><NuxtLink to="/account">My account</NuxtLink></li>
            <li><NuxtLink to="/account/orders">My orders</NuxtLink></li>
            <li><button @click="handleLogout">Sign out</button></li>
          </ul>
        </div>

        <NuxtLink v-else to="/auth/login" class="btn btn-ghost btn-sm hidden rounded-full lg:flex">
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" stroke-linecap="round" stroke-linejoin="round" />
            <circle cx="12" cy="7" r="4" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
          <span class="hidden xl:inline">Sign in</span>
        </NuxtLink>

        <NuxtLink :to="auth.isAuthenticated ? '/cart' : '/auth/login'" class="btn btn-primary btn-sm gap-2 rounded-full">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="9" cy="21" r="1" /><circle cx="20" cy="21" r="1" />
            <path d="M1 1h4l2.7 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
          <span class="hidden sm:inline">Cart</span>
          <span class="badge badge-sm bg-primary-content text-primary">{{ cartCount }}</span>
        </NuxtLink>

        <NuxtLink :to="trackOrdersTo" class="btn btn-outline btn-sm shrink-0 rounded-full px-3">
          <span class="hidden md:inline">Track Parcel/Order</span>
          <span class="md:hidden">Track</span>
        </NuxtLink>

        <button
          type="button"
          class="btn btn-ghost btn-sm rounded-full lg:hidden"
          aria-label="Open menu"
          @click="mobileOpen = !mobileOpen"
        >
          <svg v-if="!mobileOpen" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M3 6h18M3 12h18M3 18h18" stroke-linecap="round" />
          </svg>
          <svg v-else class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M6 6l12 12M18 6L6 18" stroke-linecap="round" />
          </svg>
        </button>
      </div>
    </div>

    <!-- Desktop secondary nav: category filter + site links -->
    <div class="hidden border-t border-base-300 lg:block">
      <div class="mx-auto flex max-w-7xl items-center gap-2 px-4 py-2">
        <div class="dropdown dropdown-bottom shrink-0">
          <button
            type="button"
            tabindex="0"
            class="btn btn-ghost btn-sm gap-2 rounded-full"
            :class="activeCategoryId || isAllActive ? 'bg-primary/10 text-primary' : ''"
          >
            <Icon name="lucide:sliders-horizontal" class="h-4 w-4" />
            {{ categoryFilterLabel }}
            <Icon name="lucide:chevron-down" class="h-3.5 w-3.5 opacity-60" />
          </button>
          <ul
            tabindex="0"
            class="dropdown-content menu z-50 mt-1 max-h-80 w-56 overflow-y-auto rounded-box bg-base-100 p-2 shadow-lg"
          >
            <li>
              <NuxtLink
                to="/products"
                :class="isAllActive ? 'active font-semibold' : ''"
              >
                All categories
              </NuxtLink>
            </li>
            <li v-for="c in categories" :key="c.id">
              <NuxtLink
                :to="`/products?category_id=${c.id}`"
                :class="activeCategoryId === c.id ? 'active font-semibold' : ''"
              >
                <span v-if="c.emoji" class="mr-1">{{ c.emoji }}</span>
                {{ c.name }}
              </NuxtLink>
            </li>
          </ul>
        </div>

        <div class="mx-1 h-6 w-px shrink-0 bg-base-300" aria-hidden="true" />

        <nav class="flex min-w-0 flex-1 items-center gap-0.5 overflow-x-auto" aria-label="Main">
          <NuxtLink
            v-for="link in navLinks"
            :key="link.to"
            :to="link.to"
            class="btn btn-ghost btn-sm shrink-0 whitespace-nowrap rounded-full px-3"
          >
            {{ link.label }}
          </NuxtLink>
        </nav>
      </div>
    </div>

    <!-- Mobile drawer -->
    <div v-if="mobileOpen" class="border-t border-base-300 bg-base-100 lg:hidden">
      <div class="mx-auto max-w-7xl space-y-4 px-4 py-4">
        <label class="input input-bordered flex items-center gap-2 rounded-full bg-base-200">
          <svg class="h-4 w-4 opacity-50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="7" /><path d="m20 20-3.5-3.5" stroke-linecap="round" />
          </svg>
          <input
            v-model="searchInput"
            type="text"
            placeholder="Search products"
            class="grow bg-transparent"
            @keyup.enter="onMobileSearch"
          />
        </label>

        <div class="dropdown w-full">
          <button
            type="button"
            tabindex="0"
            class="btn btn-outline btn-sm w-full justify-between rounded-xl"
          >
            <span class="flex items-center gap-2">
              <Icon name="lucide:sliders-horizontal" class="h-4 w-4" />
              {{ categoryFilterLabel }}
            </span>
            <Icon name="lucide:chevron-down" class="h-4 w-4 opacity-60" />
          </button>
          <ul
            tabindex="0"
            class="dropdown-content menu z-50 mt-1 max-h-64 w-full overflow-y-auto rounded-box bg-base-100 p-2 shadow-lg"
          >
            <li>
              <NuxtLink to="/products" @click="closeMenu">All categories</NuxtLink>
            </li>
            <li v-for="c in categories" :key="`m-cat-${c.id}`">
              <NuxtLink :to="`/products?category_id=${c.id}`" @click="closeMenu">
                <span v-if="c.emoji" class="mr-1">{{ c.emoji }}</span>
                {{ c.name }}
              </NuxtLink>
            </li>
          </ul>
        </div>

        <nav class="flex flex-col gap-1" aria-label="Main mobile">
          <NuxtLink
            v-for="link in navLinks"
            :key="`mobile-${link.to}`"
            :to="link.to"
            class="btn btn-ghost btn-sm justify-start rounded-xl"
            @click="closeMenu"
          >
            {{ link.label }}
          </NuxtLink>
        </nav>

        <div class="flex flex-wrap items-center gap-2">
          <NuxtLink
            v-if="!auth.isAuthenticated"
            to="/auth/login"
            class="btn btn-sm btn-ghost rounded-full bg-base-200"
            @click="closeMenu"
          >Sign in</NuxtLink>
          <NuxtLink
            v-if="!auth.isAuthenticated"
            to="/auth/register"
            class="btn btn-sm btn-primary rounded-full"
            @click="closeMenu"
          >Sign up</NuxtLink>
        </div>

        <div v-if="auth.isAuthenticated" class="rounded-2xl bg-base-200/40 p-3 text-sm">
          <div class="font-semibold">{{ auth.user?.name }}</div>
          <div class="truncate text-xs opacity-60">{{ auth.user?.email }}</div>
          <div class="mt-3 flex flex-wrap gap-2">
            <NuxtLink to="/account" class="btn btn-xs btn-ghost rounded-full bg-base-100" @click="closeMenu">My account</NuxtLink>
            <NuxtLink to="/account/orders" class="btn btn-xs btn-ghost rounded-full bg-base-100" @click="closeMenu">My orders</NuxtLink>
            <button class="btn btn-xs btn-ghost rounded-full bg-base-100 text-error" @click="handleLogoutAndClose">Sign out</button>
          </div>
        </div>

        <NuxtLink :to="trackOrdersTo" class="btn btn-outline btn-block rounded-full" @click="closeMenu">
          Track Parcel/Order
        </NuxtLink>
      </div>
    </div>
  </header>
</template>

<script setup lang="ts">
const auth = useAuthStore();
const categoryStore = useCategoryStore();
const cart = useCartStore();
const route = useRoute();
const router = useRouter();

const navLinks = [
  { label: 'Home', to: '/' },
  { label: 'Browse products', to: '/products' },
  { label: 'Browse vendors', to: '/#vendors' },
  { label: 'Become a Customer', to: '/auth/register' },
  { label: 'Become a Vender', to: '/vendor/apply' },
  { label: 'Fleet owners', to: '/providers/apply' },
  { label: 'Social Media Influencers', to: '/influencers/apply' },
];

const cartCount = computed(() => cart.itemCount);
const mobileOpen = ref(false);

const trackOrdersTo = computed(() =>
  auth.isAuthenticated ? '/account/orders' : '/auth/login',
);

const categories = computed(() => categoryStore.categories);

const activeCategoryId = computed(() =>
  route.query.category_id ? Number(route.query.category_id) : null,
);
const isAllActive = computed(() =>
  route.path === '/products' && !route.query.category_id,
);

const categoryFilterLabel = computed(() => {
  if (isAllActive.value) return 'Filter by category';
  const match = categories.value.find((c) => c.id === activeCategoryId.value);
  return match?.name ?? 'Filter by category';
});

onMounted(() => {
  categoryStore.fetchActive();
  if (auth.isAuthenticated) cart.ensureLoaded();
});

watch(() => auth.isAuthenticated, (loggedIn) => {
  if (loggedIn) {
    cart.refresh(true);
  } else {
    cart.reset();
  }
});

watch(() => route.fullPath, () => {
  mobileOpen.value = false;
});

const searchInput = ref(typeof route.query.q === 'string' ? route.query.q : '');

watch(() => route.query.q, (next) => {
  searchInput.value = typeof next === 'string' ? next : '';
});

const submitSearch = () => {
  router.push({
    path: '/products',
    query: searchInput.value ? { q: searchInput.value } : {},
  });
};

const onMobileSearch = () => {
  submitSearch();
  mobileOpen.value = false;
};

const closeMenu = () => {
  mobileOpen.value = false;
};

const initials = computed(() => {
  const name = auth.user?.name ?? '';
  return name
    .split(/\s+/)
    .filter(Boolean)
    .slice(0, 2)
    .map((s) => s[0]?.toUpperCase() ?? '')
    .join('');
});

const handleLogout = async () => {
  try {
    await auth.logout();
  } catch (err) {
    console.error(err);
  }
};

const handleLogoutAndClose = async () => {
  await handleLogout();
  mobileOpen.value = false;
};
</script>
