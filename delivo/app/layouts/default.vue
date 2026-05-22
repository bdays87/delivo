<template>
  <div class="flex min-h-screen flex-col bg-base-100 text-base-content">
    <StorefrontPromoBar />
    <StorefrontHeader />
    <main class="flex-1">
      <slot />
    </main>
    <StorefrontFooter />
    <StorefrontToasts />
  </div>
</template>
