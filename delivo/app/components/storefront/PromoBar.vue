<template>
  <div class="bg-neutral text-neutral-content text-xs">
    <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-2">
      <div class="hidden items-center gap-2 sm:flex">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
          <path d="M22 16.92V21a1 1 0 0 1-1.09 1A19.86 19.86 0 0 1 2 4.09 1 1 0 0 1 3 3h4.09a1 1 0 0 1 1 .75l1 4a1 1 0 0 1-.29 1L7.21 10.79a16 16 0 0 0 6 6l2.05-1.59a1 1 0 0 1 1-.29l4 1a1 1 0 0 1 .74 1.01Z" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <span class="opacity-80">+263 77 000 0000</span>
        <span class="mx-3 opacity-30">•</span>
        <span class="opacity-80">Nationwide delivery across Zimbabwe</span>
      </div>
      <p class="mx-auto sm:mx-0">
        🎉 Welcome to Delivo — multi-vendor shopping with
        <span class="font-semibold text-accent">door-to-door delivery</span>.
      </p>
      <div class="hidden items-center gap-4 md:flex">
        <div class="dropdown dropdown-end">
          <div tabindex="0" role="button" class="flex cursor-pointer items-center gap-1 opacity-80 hover:opacity-100">
            English
            <svg class="h-3 w-3" viewBox="0 0 24 24" fill="currentColor"><path d="M7 10l5 5 5-5z" /></svg>
          </div>
        </div>
        <div class="dropdown dropdown-end">
          <div tabindex="0" role="button" class="flex cursor-pointer items-center gap-1 opacity-80 hover:opacity-100">
            {{ currency }}
            <svg class="h-3 w-3" viewBox="0 0 24 24" fill="currentColor"><path d="M7 10l5 5 5-5z" /></svg>
          </div>
          <ul tabindex="0" class="dropdown-content menu bg-base-100 text-base-content rounded-box z-10 mt-2 w-28 p-2 shadow">
            <li><button :class="{ 'font-semibold text-primary': currency === 'USD' }" @click="currency = 'USD'">USD</button></li>
            <li><button :class="{ 'font-semibold text-primary': currency === 'ZWG' }" @click="currency = 'ZWG'">ZWG</button></li>
          </ul>
        </div>
        <div class="flex items-center gap-1 opacity-80">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" stroke-linecap="round" stroke-linejoin="round" />
            <circle cx="12" cy="10" r="3" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
          Harare, Zimbabwe
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
const currency = ref<'USD' | 'ZWG'>('USD');
</script>
