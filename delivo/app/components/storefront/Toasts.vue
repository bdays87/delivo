<template>
  <Teleport to="body">
    <div class="pointer-events-none fixed right-4 top-4 z-[60] flex w-full max-w-sm flex-col gap-2">
      <transition-group name="toast" tag="div" class="flex flex-col gap-2">
        <div
          v-for="t in toast.queue.value"
          :key="t.id"
          :class="['pointer-events-auto rounded-2xl border bg-base-100 p-4 shadow-lg', borderFor(t.level)]"
          role="alert"
        >
          <div class="flex items-start gap-3">
            <span :class="['mt-0.5 grid h-6 w-6 place-items-center rounded-full', iconBgFor(t.level)]">
              <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                <path v-if="t.level === 'success'" d="M5 12l4 4 10-10" stroke-linecap="round" stroke-linejoin="round" />
                <path v-else-if="t.level === 'error'" d="M6 6l12 12M18 6L6 18" stroke-linecap="round" stroke-linejoin="round" />
                <path v-else d="M12 8v4M12 16h0" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </span>
            <div class="flex-1 text-sm">
              <div v-if="t.title" class="font-semibold">{{ t.title }}</div>
              <div class="opacity-80">{{ t.message }}</div>
            </div>
            <button class="opacity-60 hover:opacity-100" @click="toast.dismiss(t.id)" aria-label="Dismiss">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M6 6l12 12M18 6L6 18" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </button>
          </div>
        </div>
      </transition-group>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import type { Toast } from '~/composables/useToast';

const toast = useToast();

const borderFor = (level: Toast['level']) => {
  if (level === 'success') return 'border-success/40';
  if (level === 'error') return 'border-error/40';
  return 'border-base-300';
};

const iconBgFor = (level: Toast['level']) => {
  if (level === 'success') return 'bg-success/15 text-success';
  if (level === 'error') return 'bg-error/15 text-error';
  return 'bg-info/15 text-info';
};
</script>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: transform 0.25s ease, opacity 0.25s ease;
}
.toast-enter-from {
  transform: translateY(-8px);
  opacity: 0;
}
.toast-leave-to {
  transform: translateY(-8px);
  opacity: 0;
}
</style>
