<template>
  <div class="flex h-screen overflow-hidden bg-base-200 text-base-content">
    <!-- Desktop sidebar -->
    <div class="hidden lg:flex">
      <VendorSidebar />
    </div>

    <!-- Mobile sidebar drawer -->
    <div v-if="mobileSidebarOpen" class="fixed inset-0 z-40 lg:hidden">
      <button
        type="button"
        class="absolute inset-0 bg-black/50"
        aria-label="Close sidebar"
        @click="mobileSidebarOpen = false"
      ></button>
      <div class="relative h-full w-64">
        <VendorSidebar />
        <button
          type="button"
          class="btn btn-sm btn-circle absolute -right-12 top-4 text-white"
          @click="mobileSidebarOpen = false"
        >
          <Icon name="lucide:x" class="h-4 w-4" />
        </button>
      </div>
    </div>

    <div class="flex flex-1 flex-col overflow-hidden">
      <VendorTopnav @toggle-sidebar="mobileSidebarOpen = true" />
      <main class="flex-1 overflow-y-auto p-6">
        <slot />
      </main>
    </div>

    <StorefrontToasts />
  </div>
</template>

<script setup lang="ts">
const mobileSidebarOpen = ref(false);
const route = useRoute();

watch(() => route.fullPath, () => {
  mobileSidebarOpen.value = false;
});
</script>
