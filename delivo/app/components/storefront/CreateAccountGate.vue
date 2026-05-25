<template>
  <section class="mt-8 overflow-hidden rounded-3xl border border-base-300 bg-base-100">
    <div class="grid gap-0 md:grid-cols-[1.4fr_1fr]">
      <div class="p-6 md:p-8">
        <div class="badge gap-2 border-primary/20 bg-primary/10 text-primary">
          <Icon name="lucide:info" class="h-4 w-4" />
          Before you can apply
        </div>

        <h2 class="mt-4 text-2xl font-bold tracking-tight md:text-3xl">
          First, create a free customer account
        </h2>
        <p class="mt-2 text-sm opacity-70 md:text-base">
          Every Delivo seller, fleet partner and influencer starts as a customer. It's free,
          takes under a minute, and we'll bring you right back here to finish your
          <span class="font-semibold">{{ roleLabel }}</span> application.
        </p>

        <ol class="mt-6 space-y-3">
          <li
            v-for="(step, idx) in steps"
            :key="idx"
            class="flex items-start gap-3 text-sm"
          >
            <span
              class="grid h-7 w-7 shrink-0 place-items-center rounded-full text-xs font-bold"
              :class="idx === 0 ? 'bg-primary text-primary-content' : 'bg-base-200 text-base-content/70'"
            >
              {{ idx + 1 }}
            </span>
            <div class="pt-0.5">
              <div class="font-semibold leading-tight">{{ step.title }}</div>
              <div class="text-xs leading-snug opacity-65 md:text-sm">{{ step.detail }}</div>
            </div>
          </li>
        </ol>

        <div class="mt-7 flex flex-wrap gap-3">
          <NuxtLink :to="registerLink" class="btn btn-primary btn-lg rounded-full px-7">
            <Icon name="lucide:user-plus" class="h-4 w-4" />
            Create customer account
          </NuxtLink>
          <NuxtLink :to="loginLink" class="btn btn-outline btn-lg rounded-full border-base-300 px-7">
            I already have an account
          </NuxtLink>
        </div>

        <p class="mt-4 text-xs opacity-60">
          Already signed in but seeing this? Refresh the page.
        </p>
      </div>

      <div class="hidden flex-col justify-between gap-4 border-l border-base-300 bg-base-200/30 p-6 md:flex md:p-8">
        <div>
          <div class="grid h-12 w-12 place-items-center rounded-2xl" :class="iconBg">
            <Icon :name="icon" class="h-6 w-6" />
          </div>
          <h3 class="mt-4 text-lg font-bold">{{ benefitsTitle }}</h3>
          <ul class="mt-3 space-y-2 text-sm opacity-80">
            <li v-for="(benefit, idx) in benefits" :key="idx" class="flex items-start gap-2">
              <Icon name="lucide:check-circle-2" class="mt-0.5 h-4 w-4 shrink-0 text-success" />
              <span>{{ benefit }}</span>
            </li>
          </ul>
        </div>
        <p class="text-xs opacity-60">
          Your customer account also lets you shop, track parcels, and pay with mobile money — even
          if you never finish the {{ roleLabel }} application.
        </p>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
interface Step {
  title: string;
  detail: string;
}

const props = defineProps<{
  roleLabel: string;
  icon: string;
  iconBg?: string;
  benefitsTitle: string;
  benefits: string[];
  steps: Step[];
  /** Override the redirect target. Defaults to the current route path. */
  redirectTo?: string;
}>();

const route = useRoute();

const target = computed(() => props.redirectTo ?? route.fullPath);

const registerLink = computed(() => ({
  path: '/auth/register',
  query: { redirect: target.value },
}));

const loginLink = computed(() => ({
  path: '/auth/login',
  query: { redirect: target.value },
}));
</script>
