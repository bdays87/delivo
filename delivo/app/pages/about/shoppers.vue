<template>
  <section class="mx-auto max-w-5xl px-4 py-12">
    <div class="rounded-3xl border border-base-300 bg-base-100 p-8 md:p-12">
      <div class="flex flex-wrap items-center gap-3">
        <span class="badge badge-lg gap-2 border-base-300 bg-base-200/80 px-4 py-3 font-medium">
          <Icon name="lucide:shopping-bag" class="h-4 w-4 text-primary" />
          For shoppers
        </span>
      </div>
      <h1 class="mt-5 text-3xl font-extrabold leading-tight tracking-tight md:text-5xl">
        You order. <span class="text-primary">We deliver.</span>
      </h1>
      <p class="mt-4 max-w-3xl text-base opacity-80 md:text-lg">
        Delivo is Zimbabwe's multi-vendor marketplace. Shop groceries, fashion, electronics and more
        from trusted local vendors — picked, packed and brought to your door anywhere in the country.
      </p>

      <div class="mt-10 grid gap-4 md:grid-cols-3">
        <article class="rounded-2xl border border-base-300 bg-base-200/40 p-5">
          <span class="grid h-10 w-10 place-items-center rounded-xl bg-primary/15 text-primary">
            <Icon name="lucide:search" class="h-5 w-5" />
          </span>
          <h3 class="mt-3 font-semibold">Find what you need</h3>
          <p class="mt-1 text-sm opacity-70">
            Browse by category, search by name, or shop from a specific local store. Prices are listed in
            USD with live ZWG conversion using the platform's published exchange rate.
          </p>
        </article>
        <article class="rounded-2xl border border-base-300 bg-base-200/40 p-5">
          <span class="grid h-10 w-10 place-items-center rounded-xl bg-primary/15 text-primary">
            <Icon name="lucide:layers" class="h-5 w-5" />
          </span>
          <h3 class="mt-3 font-semibold">Bulk-friendly pricing</h3>
          <p class="mt-1 text-sm opacity-70">
            Many products are tier-priced — the unit price drops as you buy more. The savings show up
            live in your cart and at checkout.
          </p>
        </article>
        <article class="rounded-2xl border border-base-300 bg-base-200/40 p-5">
          <span class="grid h-10 w-10 place-items-center rounded-xl bg-primary/15 text-primary">
            <Icon name="lucide:truck" class="h-5 w-5" />
          </span>
          <h3 class="mt-3 font-semibold">Delivered nationwide</h3>
          <p class="mt-1 text-sm opacity-70">
            Delivo picks up from the vendor and brings your order to your door. Delivery is calculated
            at checkout based on the city you choose.
          </p>
        </article>
      </div>

      <div class="mt-10 rounded-2xl border border-base-300 bg-base-200/40 p-6">
        <h2 class="text-lg font-bold">How it works</h2>
        <ol class="mt-3 list-decimal space-y-2 pl-5 text-sm opacity-80">
          <li>Create your free account and add a delivery address (you can save more than one).</li>
          <li>Add products to your cart — sign in is required so we can keep your basket safe across devices.</li>
          <li>Go to checkout, pick a saved address, and choose a mobile-wallet provider (Ecocash / NetOne / Innbucks / Omari).</li>
          <li>Pay using the order's reference number. Delivo confirms receipt and dispatches your order.</li>
          <li>Track every order under <NuxtLink to="/account/orders" class="link link-primary">My orders</NuxtLink>.</li>
        </ol>
      </div>

      <div class="mt-10 grid gap-4 md:grid-cols-2">
        <div class="rounded-2xl border border-base-300 bg-base-200/40 p-5">
          <h3 class="font-semibold">Payments</h3>
          <p class="mt-1 text-sm opacity-70">
            v1 supports Ecocash, NetOne, Innbucks and Omari — no card processor yet. You'll see a
            payment instruction with the wallet name and a unique reference at checkout.
          </p>
        </div>
        <div class="rounded-2xl border border-base-300 bg-base-200/40 p-5">
          <h3 class="font-semibold">Currency toggle</h3>
          <p class="mt-1 text-sm opacity-70">
            Switch between USD and ZWG at the top of the header. Vendor prices are stored in USD;
            ZWG totals use the rate published by Delivo's admin team.
          </p>
        </div>
      </div>

      <div class="mt-10 flex flex-wrap gap-3">
        <NuxtLink to="/products" class="btn btn-primary btn-lg rounded-full px-7">
          Start shopping
          <Icon name="lucide:arrow-right" class="h-4 w-4" />
        </NuxtLink>
        <NuxtLink to="/auth/register" class="btn btn-outline btn-lg rounded-full px-7">
          Create account
        </NuxtLink>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
useHead({ title: 'Shopping on Delivo — Delivo' });
</script>
