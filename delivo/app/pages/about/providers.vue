<template>
  <section class="mx-auto max-w-5xl px-4 py-12">
    <div class="rounded-3xl border border-base-300 bg-base-100 p-8 md:p-12">
      <div class="flex flex-wrap items-center gap-3">
        <span class="badge badge-lg gap-2 border-base-300 bg-base-200/80 px-4 py-3 font-medium">
          <Icon name="lucide:truck" class="h-4 w-4 text-primary" />
          For fleet owners
        </span>
      </div>
      <h1 class="mt-5 text-3xl font-extrabold leading-tight tracking-tight md:text-5xl">
        Deliver on Delivo. <span class="text-primary">Grow your fleet.</span>
      </h1>
      <p class="mt-4 max-w-3xl text-base opacity-80 md:text-lg">
        Plug your fleet into Delivo's pipeline of multi-vendor orders. Pick the cities you cover,
        get matched to vendor pickups and customer drop-offs in real time, and get paid for every
        delivered shipment — no idle drivers, no chasing dispatchers.
      </p>

      <div class="mt-10 grid gap-4 md:grid-cols-3">
        <article class="rounded-2xl border border-base-300 bg-base-200/40 p-5">
          <span class="grid h-10 w-10 place-items-center rounded-xl bg-primary/15 text-primary">
            <Icon name="lucide:zap" class="h-5 w-5" />
          </span>
          <h3 class="mt-3 font-semibold">Real-time matching</h3>
          <p class="mt-1 text-sm opacity-70">
            The moment a customer places an order, Delivo's matcher finds an active fleet whose
            coverage spans both the vendor's pickup hub and the customer's city — and assigns
            the shipment automatically.
          </p>
        </article>
        <article class="rounded-2xl border border-base-300 bg-base-200/40 p-5">
          <span class="grid h-10 w-10 place-items-center rounded-xl bg-primary/15 text-primary">
            <Icon name="lucide:map-pinned" class="h-5 w-5" />
          </span>
          <h3 class="mt-3 font-semibold">Coverage on your terms</h3>
          <p class="mt-1 text-sm opacity-70">
            Pick from Delivo's coverage cities (Harare, Bulawayo, Mutare, Gweru, Masvingo, Vic
            Falls and more). Cover one city or all nine — you only get shipments your fleet
            can actually serve.
          </p>
        </article>
        <article class="rounded-2xl border border-base-300 bg-base-200/40 p-5">
          <span class="grid h-10 w-10 place-items-center rounded-xl bg-primary/15 text-primary">
            <Icon name="lucide:wallet" class="h-5 w-5" />
          </span>
          <h3 class="mt-3 font-semibold">Earn per shipment</h3>
          <p class="mt-1 text-sm opacity-70">
            Each shipment carries a published distance-based fee. Pickup, dispatch and mark
            delivered through the dashboard — your earnings are tallied per leg and paid out
            into your mobile wallet or bank.
          </p>
        </article>
      </div>

      <div class="mt-10 rounded-2xl border border-base-300 bg-base-200/40 p-6">
        <h2 class="text-lg font-bold">How it works</h2>
        <ol class="mt-3 list-decimal space-y-2 pl-5 text-sm opacity-80">
          <li>Sign up and submit your fleet's KYC: national ID, driver's licence, vehicle registration.</li>
          <li>An admin reviews and activates your account — usually within one working day.</li>
          <li>Pick the coverage cities your fleet can serve.</li>
          <li>Every time a customer order matches your coverage you'll see it on the provider dashboard with the pickup hub, drop-off address, distance and fee.</li>
          <li>Mark each shipment <strong>Picked up</strong>, <strong>Dispatched</strong>, then <strong>Delivered</strong> as you move it. Customers see your progress in real time on their order page.</li>
        </ol>
      </div>

      <div class="mt-10 grid gap-4 md:grid-cols-2">
        <div class="rounded-2xl border border-base-300 bg-base-200/40 p-5">
          <h3 class="font-semibold">Built for fleet owners, not gig drivers</h3>
          <p class="mt-1 text-sm opacity-70">
            One account per fleet — operators manage shipments centrally instead of a driver-per-app
            mess. Pick what your trucks, vans and bikes can handle, and Delivo routes accordingly.
          </p>
        </div>
        <div class="rounded-2xl border border-base-300 bg-base-200/40 p-5">
          <h3 class="font-semibold">Multi-vendor pickups</h3>
          <p class="mt-1 text-sm opacity-70">
            Carry shipments from several vendors at once — each leg is its own paid shipment with
            its own hub origin, distance and band fee.
          </p>
        </div>
      </div>

      <div class="mt-10 flex flex-wrap gap-3">
        <NuxtLink to="/providers/apply" class="btn btn-primary btn-lg rounded-full px-7">
          Become a partner
          <Icon name="lucide:arrow-right" class="h-4 w-4" />
        </NuxtLink>
        <NuxtLink to="/about/sellers" class="btn btn-outline btn-lg rounded-full px-7">
          How selling works
        </NuxtLink>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
useHead({ title: 'Deliver on Delivo — Delivo' });
</script>
