<template>
  <div>
    <div class="mx-auto max-w-3xl px-4 pt-8">
      <div class="breadcrumbs text-sm opacity-70">
        <ul>
          <li><NuxtLink to="/">Home</NuxtLink></li>
          <li>AI Shopping Assistant</li>
        </ul>
      </div>
    </div>
    <StorefrontAiSearch />
  </div>
</template>

<script setup lang="ts">
useHead({
  title: 'AI Shopping Assistant — Delivo',
  meta: [
    {
      name: 'description',
      content:
        'Type what you need or photograph your shopping list — Delivo reads the items and suggests vendors and stores on our platform.',
    },
  ],
});
</script>
