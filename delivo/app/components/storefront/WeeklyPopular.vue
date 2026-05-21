<template>
  <section class="bg-base-200 py-16">
    <div class="mx-auto max-w-7xl px-4">
      <div class="mb-8 flex items-end justify-between">
        <div>
          <p class="text-sm font-semibold uppercase tracking-wider text-primary">Weekly popular</p>
          <h2 class="mt-2 text-3xl font-bold md:text-4xl">What everyone's buying</h2>
        </div>
        <div class="hidden gap-1 md:flex">
          <button class="btn btn-sm rounded-full">All</button>
          <button class="btn btn-sm btn-ghost rounded-full">Home</button>
          <button class="btn btn-sm btn-ghost rounded-full">Kitchen</button>
          <button class="btn btn-sm btn-ghost rounded-full">Tech</button>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
        <article
          v-for="w in items"
          :key="w.name"
          class="group rounded-3xl bg-base-100 p-4 transition hover:shadow-xl"
        >
          <div class="relative aspect-square overflow-hidden rounded-2xl bg-base-200">
            <img :src="mockImage(w.seed)" :alt="w.name" class="h-full w-full object-cover transition group-hover:scale-105" />
            <span v-if="w.badge" class="badge badge-sm absolute left-2 top-2 bg-neutral text-neutral-content font-semibold">
              {{ w.badge }}
            </span>
            <button class="btn btn-circle btn-xs absolute right-2 top-2 border-none bg-base-100/90" aria-label="Save">
              <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.6a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" stroke-linecap="round" stroke-linejoin="round" /></svg>
            </button>
          </div>
          <div class="mt-3 flex gap-0.5 text-warning">
            <svg
              v-for="(filled, i) in starFlags(w.rating)"
              :key="i"
              class="h-3 w-3"
              :class="filled ? '' : 'opacity-30'"
              viewBox="0 0 24 24"
              fill="currentColor"
            >
              <path d="M12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z" />
            </svg>
          </div>
          <div class="mt-1 line-clamp-1 text-sm font-semibold">{{ w.name }}</div>
          <div class="mt-1 flex items-center justify-between">
            <span class="font-bold text-primary">${{ w.price.toFixed(2) }}</span>
            <button class="btn btn-xs btn-ghost rounded-full">+ Add</button>
          </div>
        </article>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
const items = useWeeklyPopular();
</script>
