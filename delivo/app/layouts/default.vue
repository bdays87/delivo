<template>
  <div class="min-h-screen bg-base-100 text-base-content">
    <StorefrontPromoBar />
    <StorefrontHeader />
    <main>
      <slot />
    </main>
    <StorefrontFooter />
    <StorefrontToasts />
  </div>
</template>
