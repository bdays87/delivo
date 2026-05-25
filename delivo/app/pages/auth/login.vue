<template>
  <div class="mx-auto grid max-w-7xl gap-12 px-4 py-16 lg:grid-cols-2 lg:py-24">
    <div class="hidden flex-col justify-between lg:flex">
      <div>
        <NuxtLink to="/" class="flex items-center gap-2">
          <span class="grid h-10 w-10 place-items-center rounded-2xl bg-primary text-primary-content">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M3 7h11l2 4h5v6h-2a3 3 0 1 1-6 0H9a3 3 0 1 1-6 0V7z" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </span>
          <span class="text-2xl font-extrabold tracking-tight">delivo<span class="text-primary">.</span></span>
        </NuxtLink>
        <h1 class="mt-12 text-4xl font-extrabold leading-tight tracking-tight">
          Welcome back.<br />
          <span class="text-primary">Let's get shopping.</span>
        </h1>
        <p class="mt-4 max-w-md text-base opacity-70">
          Sign in to see your orders, track deliveries and pay with your favourite mobile money provider.
        </p>
      </div>

      <div class="rounded-3xl border border-base-300 bg-base-100 p-6 text-sm">
        <div class="font-semibold">New to Delivo?</div>
        <p class="mt-1 opacity-70">Create an account and we'll deliver from local vendors anywhere in Zimbabwe.</p>
        <NuxtLink :to="registerLink" class="mt-3 inline-flex text-primary font-semibold">
          Create an account →
        </NuxtLink>
      </div>
    </div>

    <div class="rounded-3xl border border-base-300 bg-base-100 p-8 shadow-sm md:p-10">
      <h2 class="text-2xl font-bold">Sign in</h2>
      <p class="mt-1 text-sm opacity-70">Use your email and password to continue.</p>

      <div v-if="serverError" role="alert" class="alert alert-error mt-6">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10" />
          <line x1="12" y1="8" x2="12" y2="12" stroke-linecap="round" />
          <line x1="12" y1="16" x2="12.01" y2="16" stroke-linecap="round" />
        </svg>
        <span>{{ serverError }}</span>
      </div>

      <form class="mt-6 flex flex-col gap-4" @submit.prevent="handleSubmit">
        <label class="fieldset">
          <span class="fieldset-legend">Email</span>
          <input
            v-model="form.email"
            type="email"
            autocomplete="email"
            placeholder="you@email.com"
            :class="['input input-bordered w-full', errors.email ? 'input-error' : '']"
          />
          <span v-if="errors.email" class="text-xs text-red-600">{{ errors.email }}</span>
        </label>

        <label class="fieldset">
          <span class="fieldset-legend">Password</span>
          <input
            v-model="form.password"
            type="password"
            autocomplete="current-password"
            placeholder="••••••••"
            :class="['input input-bordered w-full', errors.password ? 'input-error' : '']"
          />
          <span v-if="errors.password" class="text-xs text-red-600">{{ errors.password }}</span>
        </label>

        <button
          class="btn btn-primary btn-lg rounded-full"
          type="submit"
          :disabled="submitting"
        >
          <span v-if="submitting">Signing in…</span>
          <span v-else>Sign in</span>
        </button>
      </form>

      <p class="mt-6 text-center text-sm opacity-70 lg:hidden">
        New here?
        <NuxtLink :to="registerLink" class="font-semibold text-primary">Create an account</NuxtLink>
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ layout: 'default' });
useHead({ title: 'Sign in — Delivo' });

const auth = useAuthStore();
const route = useRoute();

const redirectTo = computed(() => {
  const q = route.query.redirect;
  return typeof q === 'string' ? q : null;
});

const registerLink = computed(() =>
  redirectTo.value
    ? { path: '/auth/register', query: { redirect: redirectTo.value } }
    : { path: '/auth/register' },
);

const form = reactive({ email: '', password: '' });
const errors = reactive({ email: '', password: '' });
const submitting = ref(false);
const serverError = ref<string | null>(null);

const clearErrors = () => {
  errors.email = '';
  errors.password = '';
  serverError.value = null;
};

const handleSubmit = async () => {
  clearErrors();
  try {
    submitting.value = true;
    const valid = await LoginSchema.validate(form, { abortEarly: false });
    await auth.login(valid, redirectTo.value);
  } catch (err: any) {
    if (err?.inner?.length) {
      err.inner.forEach((e: any) => {
        if (e.path && Object.prototype.hasOwnProperty.call(errors, e.path)) errors[e.path as 'email' | 'password'] = e.message;
      });
    } else if (err?.response?._data?.message) {
      serverError.value = err.response._data.message;
    } else if (err?.data?.message) {
      serverError.value = err.data.message;
    } else if (err?.message) {
      serverError.value = err.message;
    }
  } finally {
    submitting.value = false;
  }
};
</script>
