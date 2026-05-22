<template>
  <section class="mx-auto max-w-5xl px-4 py-12">
    <div class="rounded-3xl border border-base-300 bg-base-100 p-8 md:p-12">
      <div class="flex flex-wrap items-center gap-3">
        <span class="badge badge-lg gap-2 border-base-300 bg-base-200/80 px-4 py-3 font-medium">
          <Icon name="lucide:store" class="h-4 w-4 text-primary" />
          For sellers
        </span>
      </div>
      <h1 class="mt-5 text-3xl font-extrabold leading-tight tracking-tight md:text-5xl">
        Grow your store on <span class="text-primary">Delivo.</span>
      </h1>
      <p class="mt-4 max-w-3xl text-base opacity-80 md:text-lg">
        List products, manage orders, get paid — and reach customers across Zimbabwe without
        building your own website, payment integration or delivery fleet.
      </p>

      <div class="mt-10 grid gap-4 md:grid-cols-3">
        <article class="rounded-2xl border border-base-300 bg-base-200/40 p-5">
          <span class="grid h-10 w-10 place-items-center rounded-xl bg-primary/15 text-primary">
            <Icon name="lucide:badge-check" class="h-5 w-5" />
          </span>
          <h3 class="mt-3 font-semibold">Free to onboard</h3>
          <p class="mt-1 text-sm opacity-70">
            Apply with your business details and a national-ID upload. An admin reviews and approves
            your store; you only pay commission on each sale.
          </p>
        </article>
        <article class="rounded-2xl border border-base-300 bg-base-200/40 p-5">
          <span class="grid h-10 w-10 place-items-center rounded-xl bg-primary/15 text-primary">
            <Icon name="lucide:layout-dashboard" class="h-5 w-5" />
          </span>
          <h3 class="mt-3 font-semibold">Vendor dashboard</h3>
          <p class="mt-1 text-sm opacity-70">
            Add products with color variants, tiered USD pricing, multiple images and a category.
            Edit any time — edits on live products auto-resubmit for moderation.
          </p>
        </article>
        <article class="rounded-2xl border border-base-300 bg-base-200/40 p-5">
          <span class="grid h-10 w-10 place-items-center rounded-xl bg-primary/15 text-primary">
            <Icon name="lucide:truck" class="h-5 w-5" />
          </span>
          <h3 class="mt-3 font-semibold">We handle delivery</h3>
          <p class="mt-1 text-sm opacity-70">
            Delivo picks up from your store and delivers nationwide. You ship to one place; we
            handle the rest. No self-fulfilment headaches.
          </p>
        </article>
      </div>

      <div class="mt-10 rounded-2xl border border-base-300 bg-base-200/40 p-6">
        <h2 class="text-lg font-bold">How selling on Delivo works</h2>
        <ol class="mt-3 list-decimal space-y-2 pl-5 text-sm opacity-80">
          <li>Sign up for an account and submit a vendor application with your business and KYC details.</li>
          <li>An admin reviews your application — typically same-day during weekdays.</li>
          <li>Once approved, add products with tiered pricing in USD, color variants and product images.</li>
          <li>Products go through admin moderation before they appear on the storefront.</li>
          <li>When customers buy, Delivo collects the payment, deducts commission and pays you out via your saved mobile-wallet or bank payout account.</li>
        </ol>
      </div>

      <div class="mt-10 grid gap-4 md:grid-cols-2">
        <div class="rounded-2xl border border-base-300 bg-base-200/40 p-5">
          <h3 class="font-semibold">Tiered pricing</h3>
          <p class="mt-1 text-sm opacity-70">
            Set quantity tiers per product (e.g. 1–9 at $5, 10–49 at $4.50, 50+ at $4). The
            storefront resolves the right unit price live as the customer changes quantity.
          </p>
        </div>
        <div class="rounded-2xl border border-base-300 bg-base-200/40 p-5">
          <h3 class="font-semibold">Color variants</h3>
          <p class="mt-1 text-sm opacity-70">
            One product can carry multiple colors — each variant tracks its own stock and SKU.
            Customers pick a color at the product page; we keep stock in sync.
          </p>
        </div>
      </div>

      <div class="mt-10 flex flex-wrap gap-3">
        <NuxtLink to="/vendor/apply" class="btn btn-primary btn-lg rounded-full px-7">
          Become a seller
          <Icon name="lucide:arrow-right" class="h-4 w-4" />
        </NuxtLink>
        <NuxtLink to="/vendor" class="btn btn-outline btn-lg rounded-full px-7">
          Vendor dashboard
        </NuxtLink>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
useHead({ title: 'Selling on Delivo — Delivo' });
</script>
