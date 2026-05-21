<template>
  <section id="parcel-delivery" class="mx-auto max-w-7xl scroll-mt-24 px-4 py-16">
    <div class="grid gap-5 md:grid-cols-3">
      <div class="rounded-3xl border border-base-300 bg-base-100 p-6">
        <div class="grid h-12 w-12 place-items-center rounded-2xl bg-primary/10 text-primary">
          <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M3 7h11l2 4h5v6h-2a3 3 0 1 1-6 0H9a3 3 0 1 1-6 0V7z" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </div>
        <h3 class="mt-5 text-xl font-bold">Door-to-door delivery</h3>
        <p class="mt-2 text-sm opacity-70">
          We deliver anywhere in Zimbabwe. Track your order from pickup to your doorstep.
        </p>
        <a href="#" class="mt-4 inline-flex text-sm font-semibold text-primary">How it works →</a>
      </div>

      <div class="rounded-3xl border border-base-300 bg-base-100 p-6">
        <div class="grid h-12 w-12 place-items-center rounded-2xl bg-success/10 text-success">
          <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <rect x="3" y="6" width="18" height="13" rx="2" />
            <path d="M3 10h18" stroke-linecap="round" />
          </svg>
        </div>
        <h3 class="mt-5 text-xl font-bold">Secure mobile money</h3>
        <p class="mt-2 text-sm opacity-70">
          Pay with Ecocash, NetOne, Innbucks or Omari. Every order is covered by buyer protection.
        </p>
        <a href="#" class="mt-4 inline-flex text-sm font-semibold text-primary">Payment options →</a>
      </div>

      <div class="rounded-3xl border border-base-300 bg-base-100 p-6">
        <div class="grid h-12 w-12 place-items-center rounded-2xl bg-secondary/10 text-secondary">
          <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <circle cx="12" cy="12" r="9" />
            <path d="M12 8v4l3 2" stroke-linecap="round" />
          </svg>
        </div>
        <h3 class="mt-5 text-xl font-bold">Real support</h3>
        <p class="mt-2 text-sm opacity-70">
          Real humans, ready to help — from checkout through returns and refunds.
        </p>
        <a href="#" class="mt-4 inline-flex text-sm font-semibold text-primary">Help centre →</a>
      </div>
    </div>

    <StorefrontPaymentStrip class="mt-12" />
  </section>
</template>
