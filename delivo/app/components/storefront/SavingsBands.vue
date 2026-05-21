<template>
  <section class="mx-auto max-w-7xl px-4 py-16">
    <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
      <a
        v-for="s in bands"
        :key="s.tag"
        href="#"
        class="group relative overflow-hidden rounded-3xl border border-base-300 bg-base-100 p-5 transition hover:shadow-xl"
      >
        <div class="relative z-10">
          <span class="badge badge-primary mb-3 font-semibold">{{ s.amount }}</span>
          <div class="text-lg font-bold">Explore our</div>
          <div class="text-lg font-bold">{{ s.tag }} range</div>
          <span class="mt-3 inline-flex items-center gap-1 text-sm font-medium text-primary">
            Shop now
            <svg class="h-3.5 w-3.5 transition group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M5 12h14M13 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round" /></svg>
          </span>
        </div>
        <img :src="mockImage(s.seed, 400, 400)" :alt="s.tag" class="absolute -right-6 -bottom-6 h-32 w-32 rounded-full object-cover opacity-90 transition group-hover:scale-110" />
      </a>
    </div>
  </section>
</template>

<script setup lang="ts">
const bands = useSavingsBands();
</script>
