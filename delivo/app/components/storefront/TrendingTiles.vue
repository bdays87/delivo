<template>
  <section class="mx-auto max-w-7xl px-4 pb-16">
    <div class="grid gap-4 md:grid-cols-2">
      <a
        href="#"
        class="group relative aspect-[16/10] overflow-hidden rounded-[2rem] bg-base-200"
      >
        <img :src="mockImage('furniture', 1200, 800)" alt="Furniture" class="absolute inset-0 h-full w-full object-cover transition group-hover:scale-105" />
        <div class="absolute inset-0 bg-gradient-to-tr from-neutral/70 via-neutral/30 to-transparent"></div>
        <div class="relative flex h-full flex-col justify-end p-8 text-neutral-content">
          <span class="badge badge-primary mb-3 font-semibold">Delivered nationwide</span>
          <h3 class="text-3xl font-bold md:text-4xl">Furniture Village</h3>
          <p class="mt-2 max-w-sm opacity-90">Curated pieces from local artisan vendors.</p>
          <span class="mt-4 inline-flex items-center gap-2 text-sm font-semibold">
            Shop now
            <svg class="h-4 w-4 transition group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M5 12h14M13 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round" /></svg>
          </span>
        </div>
      </a>
      <a
        href="#"
        class="group relative aspect-[16/10] overflow-hidden rounded-[2rem] bg-base-200"
      >
        <img :src="mockImage('fashion', 1200, 800)" alt="Fashion" class="absolute inset-0 h-full w-full object-cover transition group-hover:scale-105" />
        <div class="absolute inset-0 bg-gradient-to-tr from-neutral/70 via-neutral/30 to-transparent"></div>
        <div class="relative flex h-full flex-col justify-end p-8 text-neutral-content">
          <span class="badge badge-primary mb-3 font-semibold">New season</span>
          <h3 class="text-3xl font-bold md:text-4xl">Fashion World</h3>
          <p class="mt-2 max-w-sm opacity-90">Fresh drops from designers across Zimbabwe.</p>
          <span class="mt-4 inline-flex items-center gap-2 text-sm font-semibold">
            Shop now
            <svg class="h-4 w-4 transition group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M5 12h14M13 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round" /></svg>
          </span>
        </div>
      </a>
    </div>
  </section>
</template>
