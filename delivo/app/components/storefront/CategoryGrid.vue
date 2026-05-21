<template>
  <section class="mx-auto max-w-7xl px-4 py-16">
    <div class="mb-8 flex items-end justify-between">
      <div>
        <p class="text-sm font-semibold uppercase tracking-wider text-primary">Shop by category</p>
        <h2 class="mt-2 text-3xl font-bold md:text-4xl">Find what you love</h2>
      </div>
      <NuxtLink to="/products" class="link link-hover hidden text-sm font-medium md:inline-flex">
        View all products →
      </NuxtLink>
    </div>

    <div v-if="categoryStore.loading && !categoryStore.categories.length" class="flex justify-center py-10">
      <span class="loading loading-spinner loading-md"></span>
    </div>

    <div v-else-if="!categoryStore.categories.length" class="rounded-3xl border border-dashed border-base-300 p-12 text-center text-sm opacity-70">
      No categories available yet.
    </div>

    <div v-else class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-8">
      <NuxtLink
        v-for="c in categoryStore.categories"
        :key="c.id"
        :to="`/products?category_id=${c.id}`"
        class="group rounded-3xl border border-base-300 bg-base-100 p-5 transition hover:-translate-y-1 hover:shadow-lg"
      >
        <div
          class="mb-3 grid aspect-square place-items-center rounded-2xl bg-gradient-to-br text-4xl"
          :class="c.tint ?? 'from-base-200 to-base-300'"
        >
          {{ c.emoji ?? '📦' }}
        </div>
        <div class="font-semibold">{{ c.name }}</div>
        <div class="text-xs opacity-60">
          {{ c.products_count ?? 0 }} item{{ (c.products_count ?? 0) === 1 ? '' : 's' }}
        </div>
      </NuxtLink>
    </div>
  </section>
</template>

<script setup lang="ts">
const categoryStore = useCategoryStore();

onMounted(() => categoryStore.fetchActive());
</script>
