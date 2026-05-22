<template>
  <section class="mx-auto max-w-5xl px-4 py-12">
    <div class="rounded-3xl border border-base-300 bg-base-100 p-8 md:p-12">
      <div class="flex flex-wrap items-center gap-3">
        <span class="badge badge-lg gap-2 border-base-300 bg-base-200/80 px-4 py-3 font-medium">
          <Icon name="lucide:package" class="h-4 w-4 text-primary" />
          Parcel delivery
        </span>
        <span class="badge badge-warning gap-1 font-medium">
          <Icon name="lucide:clock" class="h-3 w-3" /> Coming soon
        </span>
      </div>
      <h1 class="mt-5 text-3xl font-extrabold leading-tight tracking-tight md:text-5xl">
        Send parcels <span class="text-primary">door to door.</span>
      </h1>
      <p class="mt-4 max-w-3xl text-base opacity-80 md:text-lg">
        Book affordable courier runs for documents, gifts and packages — tracked from pickup to
        drop-off in Harare, Bulawayo and beyond. Parcel delivery is a follow-on to the marketplace
        — we'll open it up once the core shopping flow is live nationwide.
      </p>

      <div class="mt-10 grid gap-4 md:grid-cols-3">
        <article class="rounded-2xl border border-base-300 bg-base-200/40 p-5">
          <span class="grid h-10 w-10 place-items-center rounded-xl bg-primary/15 text-primary">
            <Icon name="lucide:map-pin" class="h-5 w-5" />
          </span>
          <h3 class="mt-3 font-semibold">City-aware pricing</h3>
          <p class="mt-1 text-sm opacity-70">
            Same per-city fee system that powers marketplace delivery. You'll see the parcel cost
            up-front before you book.
          </p>
        </article>
        <article class="rounded-2xl border border-base-300 bg-base-200/40 p-5">
          <span class="grid h-10 w-10 place-items-center rounded-xl bg-primary/15 text-primary">
            <Icon name="lucide:scan-search" class="h-5 w-5" />
          </span>
          <h3 class="mt-3 font-semibold">Live tracking</h3>
          <p class="mt-1 text-sm opacity-70">
            From pickup to drop-off — get notifications as your parcel moves through each stage of
            the Delivo network.
          </p>
        </article>
        <article class="rounded-2xl border border-base-300 bg-base-200/40 p-5">
          <span class="grid h-10 w-10 place-items-center rounded-xl bg-primary/15 text-primary">
            <Icon name="lucide:wallet" class="h-5 w-5" />
          </span>
          <h3 class="mt-3 font-semibold">Mobile-wallet payments</h3>
          <p class="mt-1 text-sm opacity-70">
            Pay with Ecocash, NetOne, Innbucks or Omari — the same wallets we already use for
            marketplace orders.
          </p>
        </article>
      </div>

      <div class="mt-10 rounded-2xl border border-warning/40 bg-warning/5 p-6 text-sm">
        <div class="flex items-start gap-3">
          <Icon name="lucide:info" class="h-5 w-5 text-warning" />
          <div>
            <div class="font-semibold">Not yet open for bookings.</div>
            <div class="opacity-80">
              We're focused on getting marketplace shopping rock-solid first. Parcel delivery joins the
              platform as a separate slice on the roadmap. Drop your email below if you'd like to be
              notified when it's live.
            </div>
          </div>
        </div>
      </div>

      <div class="mt-10 flex flex-wrap gap-3">
        <NuxtLink to="/" class="btn btn-primary btn-lg rounded-full px-7">
          Back to storefront
          <Icon name="lucide:arrow-right" class="h-4 w-4" />
        </NuxtLink>
        <NuxtLink to="/about/shoppers" class="btn btn-outline btn-lg rounded-full px-7">
          How shopping works
        </NuxtLink>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
useHead({ title: 'Parcel delivery — Delivo' });
</script>
