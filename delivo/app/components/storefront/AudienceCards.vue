<template>
  <section class="border-b border-base-300 bg-base-100" aria-label="Delivo services">
    <div class="mx-auto max-w-7xl px-4 py-8 md:py-10">
      <p class="text-center text-sm font-semibold uppercase tracking-wide text-base-content/50">
        What can you do on Delivo?
      </p>

      <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5 lg:gap-4">
        <article
          v-for="card in cards"
          :key="card.id"
          class="flex flex-col rounded-2xl border border-base-300 bg-base-200/30 p-4 transition hover:border-primary/30 hover:bg-base-100 hover:shadow-md lg:p-5"
        >
          <span
            class="grid h-9 w-9 shrink-0 place-items-center rounded-xl"
            :class="card.iconBg"
          >
            <Icon :name="card.icon" class="h-4 w-4" />
          </span>
          <h2 class="mt-3 text-sm font-bold leading-snug">{{ card.title }}</h2>
          <p class="mt-1.5 flex-1 text-xs leading-relaxed opacity-70">
            {{ card.summary }}
          </p>
          <NuxtLink
            :to="card.readMoreTo"
            class="btn btn-ghost btn-sm mt-4 h-8 min-h-8 rounded-full px-0 font-semibold text-primary hover:bg-primary/10"
          >
            Read more
            <Icon name="lucide:arrow-right" class="h-3.5 w-3.5" />
          </NuxtLink>
        </article>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
interface AudienceCard {
  id: string;
  title: string;
  summary: string;
  icon: string;
  iconBg: string;
  readMoreTo: string;
}

const cards: AudienceCard[] = [
  {
    id: 'shoppers',
    title: 'Shop & get delivered',
    summary:
      'Browse groceries, fashion, tech and more from local vendors — one cart, delivered anywhere in Zimbabwe.',
    icon: 'lucide:shopping-bag',
    iconBg: 'bg-primary/15 text-primary',
    readMoreTo: '/about/shoppers',
  },
  {
    id: 'sellers',
    title: 'Sell on Delivo',
    summary:
      'List products, manage orders and get paid. Reach customers nationwide without your own store or delivery fleet.',
    icon: 'lucide:store',
    iconBg: 'bg-primary/15 text-primary',
    readMoreTo: '/about/sellers',
  },
  {
    id: 'parcels',
    title: 'Send parcels',
    summary:
      'Book affordable courier runs for documents and packages — tracked from pickup to drop-off across major cities.',
    icon: 'lucide:package',
    iconBg: 'bg-info/15 text-info',
    readMoreTo: '/about/parcels',
  },
  {
    id: 'fleet',
    title: 'Deliver as a fleet',
    summary:
      'Plug your trucks, vans and bikes into Delivo orders. Pick your cities, get matched to shipments, earn per delivery.',
    icon: 'lucide:truck',
    iconBg: 'bg-success/15 text-success',
    readMoreTo: '/about/providers',
  },
  {
    id: 'influencers',
    title: 'Earn as an influencer',
    summary:
      'Promote products that pay commission. Share your code and earn when your audience buys through Delivo.',
    icon: 'lucide:megaphone',
    iconBg: 'bg-accent/30 text-accent-content',
    readMoreTo: '/about/influencers',
  },
];
</script>
