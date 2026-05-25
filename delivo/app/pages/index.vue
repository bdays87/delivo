<template>
  <div>
    <section class="bg-primary text-primary-content">
      <div class="mx-auto flex max-w-7xl flex-col items-center gap-4 px-4 py-4 text-center sm:flex-row sm:justify-between sm:py-5 sm:text-left">
        <p class="max-w-xl text-sm opacity-90 sm:text-base">
          Describe what you need, or snap a photo of your shopping list — our assistant finds the right
          vendors on our platform.
        </p>
        <NuxtLink
          to="/ai-shopping-assistant"
          class="btn btn-lg shrink-0 rounded-full border-0 bg-base-100 px-8 text-base-content hover:bg-base-200"
        >
          <Icon name="lucide:sparkles" class="h-5 w-5 text-primary" />
          AI Shopping Assistant
        </NuxtLink>
      </div>
    </section>

    <StorefrontHero />
    <StorefrontAudienceCards />

    <StorefrontTopProducts />
    <StorefrontVendorGrid />
  </div>
</template>

<script setup lang="ts">
useHead({
  title: 'Delivo — multi-vendor shopping, delivered anywhere in Zimbabwe',
  meta: [
    {
      name: 'description',
      content:
        'Delivo is Zimbabwe\'s multi-vendor marketplace. Shop groceries, fashion, tech and more from local vendors — delivered to your door.',
    },
  ],
});
</script>
