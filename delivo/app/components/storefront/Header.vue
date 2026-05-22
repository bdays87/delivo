<template>
  <header class="sticky top-0 z-40 border-b border-base-300 bg-base-100/80 backdrop-blur">
    <div class="mx-auto flex max-w-7xl items-center gap-3 px-4 py-3 md:gap-4 md:py-4">
      <NuxtLink to="/" class="flex shrink-0 items-center gap-2">
        <span class="grid h-9 w-9 place-items-center rounded-2xl bg-primary text-primary-content shadow-sm md:h-10 md:w-10">
          <svg class="h-4 w-4 md:h-5 md:w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M3 7h11l2 4h5v6h-2a3 3 0 1 1-6 0H9a3 3 0 1 1-6 0V7z" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </span>
        <span class="text-xl font-extrabold tracking-tight md:text-2xl">delivo<span class="text-primary">.</span></span>
      </NuxtLink>

      <label class="input input-bordered hidden flex-1 items-center gap-2 rounded-full bg-base-200 md:flex">
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

      <!-- Desktop nav (lg and up) -->
      <nav class="ml-auto hidden items-center gap-1 lg:flex">
        <div class="join">
          <button
            :class="['btn btn-sm join-item rounded-l-full', currency.code === 'USD' ? 'btn-primary' : 'btn-ghost']"
            @click="currency.setCode('USD')"
          >USD</button>
          <button
            :class="['btn btn-sm join-item rounded-r-full', currency.code === 'ZWG' ? 'btn-primary' : 'btn-ghost']"
            :disabled="!currency.hasZwgRate"
            :title="currency.hasZwgRate ? '' : 'ZWG rate not set yet'"
            @click="currency.setCode('ZWG')"
          >ZWG</button>
        </div>

        <a class="btn btn-ghost btn-sm rounded-full" aria-label="Wishlist">
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M20.84 4.6a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </a>

        <div v-if="auth.isAuthenticated" class="dropdown dropdown-end">
          <button tabindex="0" class="btn btn-ghost btn-sm gap-2 rounded-full">
            <span class="grid h-7 w-7 place-items-center rounded-full bg-primary/10 text-primary text-xs font-bold">
              {{ initials }}
            </span>
            <span class="hidden text-sm md:inline">{{ auth.user?.name }}</span>
          </button>
          <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-10 mt-2 w-52 p-2 shadow">
            <li class="menu-title">{{ auth.user?.email }}</li>
            <li><NuxtLink to="/account">My account</NuxtLink></li>
            <li><NuxtLink to="/account/orders">My orders</NuxtLink></li>
            <li><button @click="handleLogout">Sign out</button></li>
          </ul>
        </div>

        <NuxtLink v-else to="/auth/login" class="btn btn-ghost btn-sm rounded-full">
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" stroke-linecap="round" stroke-linejoin="round" />
            <circle cx="12" cy="7" r="4" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
          <span class="hidden md:inline">Sign in</span>
        </NuxtLink>

        <NuxtLink :to="auth.isAuthenticated ? '/cart' : '/auth/login'" class="btn btn-primary btn-sm gap-2 rounded-full">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="9" cy="21" r="1" /><circle cx="20" cy="21" r="1" />
            <path d="M1 1h4l2.7 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
          Cart
          <span class="badge badge-sm bg-primary-content text-primary">{{ cartCount }}</span>
        </NuxtLink>
      </nav>

      <!-- Mobile actions (always visible on small + medium screens) -->
      <div class="ml-auto flex items-center gap-1 lg:hidden">
        <NuxtLink
          :to="auth.isAuthenticated ? '/cart' : '/auth/login'"
          class="btn btn-ghost btn-sm relative rounded-full"
          aria-label="Cart"
        >
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="9" cy="21" r="1" /><circle cx="20" cy="21" r="1" />
            <path d="M1 1h4l2.7 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
          <span
            v-if="cartCount > 0"
            class="badge badge-primary badge-xs absolute -right-1 -top-1"
          >{{ cartCount }}</span>
        </NuxtLink>

        <button
          type="button"
          class="btn btn-ghost btn-sm rounded-full"
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

    <!-- Mobile drawer panel -->
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

        <div class="flex flex-wrap items-center gap-2">
          <div class="join">
            <button
              :class="['btn btn-sm join-item rounded-l-full', currency.code === 'USD' ? 'btn-primary' : 'btn-ghost bg-base-200']"
              @click="currency.setCode('USD')"
            >USD</button>
            <button
              :class="['btn btn-sm join-item rounded-r-full', currency.code === 'ZWG' ? 'btn-primary' : 'btn-ghost bg-base-200']"
              :disabled="!currency.hasZwgRate"
              @click="currency.setCode('ZWG')"
            >ZWG</button>
          </div>
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

        <div class="rounded-2xl bg-base-200/40 p-3">
          <div class="px-2 text-xs font-semibold uppercase tracking-wider opacity-60">Shop</div>
          <div class="mt-2 flex flex-wrap gap-1">
            <NuxtLink to="/products" class="btn btn-xs btn-ghost rounded-full bg-base-100" @click="closeMenu">All</NuxtLink>
            <NuxtLink
              v-for="c in topCategories"
              :key="c.id"
              :to="`/products?category_id=${c.id}`"
              class="btn btn-xs btn-ghost rounded-full bg-base-100"
              @click="closeMenu"
            >{{ c.name }}</NuxtLink>
          </div>
        </div>

        <div class="flex items-center gap-2 text-xs opacity-70">
          <svg class="h-4 w-4 text-success" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
          Delivered anywhere in Zimbabwe
        </div>
      </div>
    </div>

    <!-- Desktop category strip -->
    <div class="hidden border-t border-base-300 lg:block">
      <div class="mx-auto flex max-w-7xl items-center gap-1 overflow-x-auto px-4 py-2 text-sm">
        <NuxtLink
          to="/products"
          :class="['btn btn-ghost btn-sm whitespace-nowrap rounded-full', isAllActive ? 'btn-primary' : '']"
        >All Categories</NuxtLink>
        <NuxtLink
          v-for="c in topCategories"
          :key="c.id"
          :to="`/products?category_id=${c.id}`"
          :class="['btn btn-ghost btn-sm whitespace-nowrap rounded-full', activeCategoryId === c.id ? 'btn-primary' : '']"
        >{{ c.name }}</NuxtLink>
        <div class="ml-auto flex items-center gap-2 text-sm opacity-70">
          <svg class="h-4 w-4 text-success" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
          Delivered anywhere in Zimbabwe
        </div>
      </div>
    </div>
  </header>
</template>

<script setup lang="ts">
const auth = useAuthStore();
const currency = useCurrencyStore();
const categoryStore = useCategoryStore();
const cart = useCartStore();
const route = useRoute();
const router = useRouter();

const cartCount = computed(() => cart.itemCount);
const mobileOpen = ref(false);

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

// Close the mobile drawer whenever the route changes.
watch(() => route.fullPath, () => {
  mobileOpen.value = false;
});

const topCategories = computed(() => categoryStore.categories.slice(0, 8));

const activeCategoryId = computed(() =>
  route.query.category_id ? Number(route.query.category_id) : null,
);
const isAllActive = computed(() =>
  route.path === '/products' && !route.query.category_id,
);

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
