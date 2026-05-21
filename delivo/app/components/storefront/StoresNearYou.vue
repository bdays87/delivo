<template>
  <section class="bg-base-200 py-16">
    <div class="mx-auto max-w-7xl px-4">
      <div class="mb-8 flex items-end justify-between">
        <div>
          <p class="text-sm font-semibold uppercase tracking-wider text-primary">Local heroes</p>
          <h2 class="mt-2 text-3xl font-bold md:text-4xl">Best-selling stores near you</h2>
        </div>
      </div>

      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <article
          v-for="s in stores"
          :key="s.name"
          class="rounded-3xl bg-base-100 p-6 transition hover:shadow-xl"
        >
          <div class="flex items-center gap-3">
            <div class="grid h-12 w-12 place-items-center rounded-2xl bg-primary/10 text-lg font-bold text-primary">
              {{ s.name[0] }}
            </div>
            <div>
              <div class="font-semibold">{{ s.name }}</div>
              <div class="flex items-center gap-1 text-xs opacity-60">
                <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" /><circle cx="12" cy="10" r="3" />
                </svg>
                {{ s.loc }}
              </div>
            </div>
          </div>

          <div class="mt-4 flex flex-wrap gap-1.5">
            <span v-for="c in s.cats" :key="c" class="badge badge-sm badge-ghost bg-base-200">{{ c }}</span>
          </div>

          <div class="mt-5 flex items-center justify-between border-t border-base-300 pt-4 text-sm">
            <span class="inline-flex items-center gap-1 font-medium text-success">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 2" stroke-linecap="round" /></svg>
              {{ s.delivery }}
            </span>
            <button class="btn btn-xs btn-primary rounded-full">Visit store</button>
          </div>
        </article>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
const stores = useFeaturedStores();
</script>
