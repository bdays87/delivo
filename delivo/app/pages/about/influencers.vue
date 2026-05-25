<template>
  <section class="mx-auto max-w-5xl px-4 py-12">
    <div class="rounded-3xl border border-base-300 bg-base-100 p-8 md:p-12">
      <div class="flex flex-wrap items-center gap-3">
        <span class="badge badge-lg gap-2 border-base-300 bg-base-200/80 px-4 py-3 font-medium">
          <Icon name="lucide:megaphone" class="h-4 w-4 text-primary" />
          For influencers
        </span>
      </div>
      <h1 class="mt-5 text-3xl font-extrabold leading-tight tracking-tight md:text-5xl">
        Share products. <span class="text-primary">Earn commission.</span>
      </h1>
      <p class="mt-4 max-w-3xl text-base opacity-80 md:text-lg">
        Delivo's affiliate program lets creators promote vendor products that offer influencer
        commission. Browse the catalog, generate your code, share it on Instagram, TikTok, X or
        WhatsApp — and get paid when your audience checks out with your link.
      </p>

      <div class="mt-10 grid gap-4 md:grid-cols-3">
        <article class="rounded-2xl border border-base-300 bg-base-200/40 p-5">
          <span class="grid h-10 w-10 place-items-center rounded-xl bg-primary/15 text-primary">
            <Icon name="lucide:package-search" class="h-5 w-5" />
          </span>
          <h3 class="mt-3 font-semibold">Products that pay</h3>
          <p class="mt-1 text-sm opacity-70">
            Only products with a commission rate appear in your dashboard. Vendors choose how much
            to share — you see the influencer % and any buyer discount before you promote.
          </p>
        </article>
        <article class="rounded-2xl border border-base-300 bg-base-200/40 p-5">
          <span class="grid h-10 w-10 place-items-center rounded-xl bg-primary/15 text-primary">
            <Icon name="lucide:link-2" class="h-5 w-5" />
          </span>
          <h3 class="mt-3 font-semibold">Your unique code</h3>
          <p class="mt-1 text-sm opacity-70">
            Generate a tracking code per product. Share it in posts, stories or DMs — when a
            shopper applies it at checkout, the sale is attributed to you.
          </p>
        </article>
        <article class="rounded-2xl border border-base-300 bg-base-200/40 p-5">
          <span class="grid h-10 w-10 place-items-center rounded-xl bg-primary/15 text-primary">
            <Icon name="lucide:wallet" class="h-5 w-5" />
          </span>
          <h3 class="mt-3 font-semibold">Tracked earnings</h3>
          <p class="mt-1 text-sm opacity-70">
            View commissions on each attributed order in your influencer dashboard. Request payouts
            when your balance is ready — paid to your mobile wallet or bank on file.
          </p>
        </article>
      </div>

      <div class="mt-10 rounded-2xl border border-base-300 bg-base-200/40 p-6">
        <h2 class="text-lg font-bold">How affiliate marketing on Delivo works</h2>
        <ol class="mt-3 list-decimal space-y-2 pl-5 text-sm opacity-80">
          <li>Apply with your display name, niche and social handles — our team reviews and approves your account.</li>
          <li>Once approved, open the influencer dashboard and browse products that offer commission.</li>
          <li>Generate a code for any product you want to promote and share it with your audience.</li>
          <li>When someone buys using your code, you earn the influencer commission % set by that vendor.</li>
          <li>Track sales and earnings in your dashboard; request a payout when you are ready to cash out.</li>
        </ol>
      </div>

      <div class="mt-10 grid gap-4 md:grid-cols-2">
        <div class="rounded-2xl border border-base-300 bg-base-200/40 p-5">
          <h3 class="font-semibold">Win-win with vendors</h3>
          <p class="mt-1 text-sm opacity-70">
            Sellers opt in per product — they set your commission and can offer a buyer discount so
            your audience has an incentive to use your code. You promote; they grow sales.
          </p>
        </div>
        <div class="rounded-2xl border border-base-300 bg-base-200/40 p-5">
          <h3 class="font-semibold">Built for Zimbabwe creators</h3>
          <p class="mt-1 text-sm opacity-70">
            Promote local brands on Delivo's multi-vendor marketplace — fashion, electronics,
            groceries and more — without negotiating one-off brand deals for every product.
          </p>
        </div>
      </div>

      <div class="mt-10 flex flex-wrap gap-3">
        <NuxtLink to="/influencers/apply" class="btn btn-primary btn-lg rounded-full px-7">
          Become an influencer
          <Icon name="lucide:arrow-right" class="h-4 w-4" />
        </NuxtLink>
        <NuxtLink to="/influencer" class="btn btn-outline btn-lg rounded-full px-7">
          Influencer dashboard
        </NuxtLink>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
useHead({ title: 'Affiliate marketing on Delivo — Delivo' });
</script>
