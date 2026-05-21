<template>
  <div class="flex flex-wrap items-center justify-between gap-6 rounded-3xl bg-base-200 p-6">
    <div class="text-sm font-semibold uppercase tracking-wider opacity-70">We accept</div>
    <div class="flex flex-wrap items-center gap-3">
      <div
        v-for="m in providers"
        :key="m"
        class="rounded-xl bg-base-100 px-4 py-2 text-xs font-bold tracking-wide shadow-sm"
      >
        {{ m }}
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
const providers = ['Ecocash', 'NetOne', 'Innbucks', 'Omari'];
</script>
