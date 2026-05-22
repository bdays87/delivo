<template>
  <header class="sticky top-0 z-40 border-b border-base-300 bg-base-100/80 backdrop-blur">
    <div class="mx-auto flex max-w-7xl items-center gap-4 px-4 py-4">
      <NuxtLink to="/" class="flex items-center gap-2">
        <span class="grid h-10 w-10 place-items-center rounded-2xl bg-primary text-primary-content shadow-sm">
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M3 7h11l2 4h5v6h-2a3 3 0 1 1-6 0H9a3 3 0 1 1-6 0V7z" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </span>
        <span class="text-2xl font-extrabold tracking-tight">delivo<span class="text-primary">.</span></span>
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

      <nav class="hidden items-center gap-1 lg:flex">
        <!-- Currency toggle -->
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
    </div>

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
</script>
