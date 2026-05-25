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
          Join Delivo.<br />
          <span class="text-primary">Shop anywhere in Zimbabwe.</span>
        </h1>
        <p class="mt-4 max-w-md text-base opacity-70">
          Create an account and we'll deliver groceries, fashion, electronics and more from local vendors —
          paid via your favourite mobile money wallet.
        </p>
      </div>

      <div class="rounded-3xl border border-base-300 bg-base-100 p-6 text-sm">
        <div class="font-semibold">Already a member?</div>
        <p class="mt-1 opacity-70">Sign in and pick up where you left off.</p>
        <NuxtLink :to="loginLink" class="mt-3 inline-flex text-primary font-semibold">
          Sign in →
        </NuxtLink>
      </div>
    </div>

    <div class="rounded-3xl border border-base-300 bg-base-100 p-8 shadow-sm md:p-10">
      <h2 class="text-2xl font-bold">Create an account</h2>
      <p class="mt-1 text-sm opacity-70">Takes less than a minute.</p>

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
          <span class="fieldset-legend">Full name</span>
          <input
            v-model="form.name"
            type="text"
            autocomplete="name"
            placeholder="Tendai Moyo"
            :class="['input input-bordered w-full', errors.name ? 'input-error' : '']"
          />
          <span v-if="errors.name" class="text-xs text-red-600">{{ errors.name }}</span>
        </label>

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
          <span class="fieldset-legend">Mobile number</span>
          <input
            v-model="form.phone"
            type="tel"
            autocomplete="tel"
            placeholder="0772 000 000"
            :class="['input input-bordered w-full', errors.phone ? 'input-error' : '']"
          />
          <span class="text-xs opacity-60">
            We'll use this for delivery updates and mobile money payments.
          </span>
          <span v-if="errors.phone" class="text-xs text-red-600">{{ errors.phone }}</span>
        </label>

        <label class="fieldset">
          <span class="fieldset-legend">Password</span>
          <input
            v-model="form.password"
            type="password"
            autocomplete="new-password"
            placeholder="At least 8 characters"
            :class="['input input-bordered w-full', errors.password ? 'input-error' : '']"
          />
          <span v-if="errors.password" class="text-xs text-red-600">{{ errors.password }}</span>
        </label>

        <label class="fieldset">
          <span class="fieldset-legend">Confirm password</span>
          <input
            v-model="form.password_confirmation"
            type="password"
            autocomplete="new-password"
            placeholder="Repeat your password"
            :class="['input input-bordered w-full', errors.password_confirmation ? 'input-error' : '']"
          />
          <span v-if="errors.password_confirmation" class="text-xs text-red-600">{{ errors.password_confirmation }}</span>
        </label>

        <button
          class="btn btn-primary btn-lg rounded-full"
          type="submit"
          :disabled="submitting"
        >
          <span v-if="submitting">Creating account…</span>
          <span v-else>Create account</span>
        </button>
      </form>

      <p class="mt-6 text-center text-sm opacity-70 lg:hidden">
        Already a member?
        <NuxtLink :to="loginLink" class="font-semibold text-primary">Sign in</NuxtLink>
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ layout: 'default' });
useHead({ title: 'Create account — Delivo' });

const auth = useAuthStore();
const route = useRoute();

const redirectTo = computed(() => {
  const q = route.query.redirect;
  return typeof q === 'string' ? q : null;
});

const loginLink = computed(() =>
  redirectTo.value
    ? { path: '/auth/login', query: { redirect: redirectTo.value } }
    : { path: '/auth/login' },
);

const form = reactive({
  name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
});
const errors = reactive({
  name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
});
const submitting = ref(false);
const serverError = ref<string | null>(null);

type FormKey = keyof typeof errors;

const clearErrors = () => {
  (Object.keys(errors) as FormKey[]).forEach((k) => { errors[k] = ''; });
  serverError.value = null;
};

const applyServerErrors = (payload: any) => {
  const fieldErrors = payload?.errors;
  if (fieldErrors && typeof fieldErrors === 'object') {
    (Object.keys(errors) as FormKey[]).forEach((k) => {
      if (Array.isArray(fieldErrors[k]) && fieldErrors[k][0]) errors[k] = fieldErrors[k][0];
    });
  }
  if (payload?.message) serverError.value = payload.message;
};

const handleSubmit = async () => {
  clearErrors();
  try {
    submitting.value = true;
    const valid = await RegisterSchema.validate(form, { abortEarly: false });
    await auth.register(valid, redirectTo.value);
    // auth.register() already navigates to the role-appropriate landing
    // (customers → /, vendors → /vendor, admins → /admin) — or to
    // `?redirect=` when set (e.g. from a "Become a vendor" CTA).
  } catch (err: any) {
    if (err?.inner?.length) {
      err.inner.forEach((e: any) => {
        if (e.path && Object.prototype.hasOwnProperty.call(errors, e.path)) {
          errors[e.path as FormKey] = e.message;
        }
      });
      return;
    }
    if (err?.response?._data) {
      applyServerErrors(err.response._data);
      return;
    }
    if (err?.data) {
      applyServerErrors(err.data);
      return;
    }
    if (err?.message) serverError.value = err.message;
  } finally {
    submitting.value = false;
  }
};
</script>
