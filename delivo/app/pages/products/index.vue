<template>
  <section class="mx-auto max-w-7xl px-4 py-10">
    <div class="flex flex-wrap items-end justify-between gap-4">
      <div>
        <p class="text-sm font-semibold uppercase tracking-wider text-primary">Shop</p>
        <h1 class="mt-2 text-3xl font-extrabold tracking-tight md:text-4xl">All products</h1>
        <p class="mt-1 text-sm opacity-70">
          Browse the full catalogue. Filter by category or search by name.
        </p>
      </div>
      <label class="input input-bordered flex w-full items-center gap-2 rounded-full bg-base-100 md:w-80">
        <Icon name="lucide:search" class="h-4 w-4 opacity-50" />
        <input
          v-model="searchInput"
          type="text"
          placeholder="Search products"
          class="grow bg-transparent"
          @keyup.enter="applySearch"
        />
        <button v-if="searchInput" class="opacity-60" @click="clearSearch">
          <Icon name="lucide:x" class="h-4 w-4" />
        </button>
      </label>
    </div>

    <div class="mt-6 flex flex-wrap gap-2">
      <button
        :class="['btn btn-sm rounded-full', store.categoryId === null ? 'btn-primary' : 'btn-ghost bg-base-100']"
        @click="filterCategory(null)"
      >
        All
        <span v-if="totalCount" class="badge badge-sm ml-1">{{ totalCount }}</span>
      </button>
      <button
        v-for="c in categories"
        :key="c.id"
        :class="['btn btn-sm rounded-full', store.categoryId === c.id ? 'btn-primary' : 'btn-ghost bg-base-100']"
        @click="filterCategory(c.id)"
      >
        <span v-if="c.emoji" class="mr-1">{{ c.emoji }}</span>
        {{ c.name }}
        <span v-if="c.products_count" class="badge badge-sm ml-1">{{ c.products_count }}</span>
      </button>
    </div>

    <div v-if="store.loading" class="flex justify-center py-16">
      <span class="loading loading-spinner loading-lg"></span>
    </div>

    <div v-else-if="!store.items.length" class="mt-8 rounded-3xl border border-dashed border-base-300 p-12 text-center">
      <Icon name="lucide:package-search" class="mx-auto h-10 w-10 opacity-30" />
      <p class="mt-3 text-sm opacity-70">No products match those filters yet.</p>
    </div>

    <div v-else class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
      <StorefrontProductCard v-for="p in store.items" :key="p.id" :product="p" />
    </div>

    <div v-if="store.meta.last_page > 1" class="mt-10 flex items-center justify-center gap-3">
      <button
        class="btn btn-sm btn-ghost rounded-full"
        :disabled="store.meta.current_page <= 1"
        @click="changePage(store.meta.current_page - 1)"
      >
        <Icon name="lucide:chevron-left" class="h-4 w-4" /> Prev
      </button>
      <span class="text-sm opacity-70">
        Page {{ store.meta.current_page }} of {{ store.meta.last_page }}
      </span>
      <button
        class="btn btn-sm btn-ghost rounded-full"
        :disabled="store.meta.current_page >= store.meta.last_page"
        @click="changePage(store.meta.current_page + 1)"
      >
        Next <Icon name="lucide:chevron-right" class="h-4 w-4" />
      </button>
    </div>
  </section>
</template>

<script setup lang="ts">
useHead({ title: 'Shop all — Delivo' });

const store = usePublicProductStore();
const categoryStore = useCategoryStore();
const route = useRoute();
const router = useRouter();

const categories = computed(() => categoryStore.categories);
const totalCount = computed(() =>
  categories.value.reduce((sum, c) => sum + (c.products_count ?? 0), 0),
);

const searchInput = ref('');

const syncFromRoute = () => {
  const cat = route.query.category_id;
  store.setCategory(cat ? Number(cat) : null);
  const q = (route.query.q as string) ?? '';
  store.setSearch(q);
  searchInput.value = q;
  const page = route.query.page ? Number(route.query.page) : 1;
  store.fetchList(page);
};

onMounted(async () => {
  await categoryStore.fetchActive();
  syncFromRoute();
});

watch(() => route.query, () => syncFromRoute());

const pushQuery = (next: Record<string, string | number | null | undefined>) => {
  const merged: Record<string, string> = {};
  const current = { ...route.query, ...next };
  Object.entries(current).forEach(([k, v]) => {
    if (v === null || v === undefined || v === '') return;
    merged[k] = String(v);
  });
  router.push({ path: '/products', query: merged });
};

const filterCategory = (id: number | null) => {
  pushQuery({ category_id: id, page: 1 });
};

const applySearch = () => {
  pushQuery({ q: searchInput.value || null, page: 1 });
};

const clearSearch = () => {
  searchInput.value = '';
  pushQuery({ q: null, page: 1 });
};

const changePage = (page: number) => {
  pushQuery({ page });
  if (import.meta.client) window.scrollTo({ top: 0, behavior: 'smooth' });
};
</script>
