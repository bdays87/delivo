<template>
  <div>
    <StorefrontHero />
    <StorefrontAiSearch />
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
