<template>
  <section
    class="relative overflow-hidden bg-base-200"
    aria-roledescription="carousel"
    aria-label="Delivo highlights"
    @mouseenter="pauseAutoplay"
    @mouseleave="resumeAutoplay"
  >
    <div class="pointer-events-none absolute -top-24 -right-20 h-80 w-80 rounded-full bg-primary/15 blur-3xl"></div>
    <div class="pointer-events-none absolute -bottom-24 -left-20 h-80 w-80 rounded-full bg-secondary/15 blur-3xl"></div>

    <div class="relative mx-auto max-w-7xl px-4 py-6 md:px-6 md:py-8 lg:px-8">
      <div class="grid gap-5 lg:grid-cols-3 lg:gap-6">
        <!-- Carousel: 2/3 -->
        <div class="lg:col-span-2">
          <div class="relative h-[min(58vh,460px)] min-h-[340px] w-full overflow-hidden rounded-3xl shadow-sm sm:min-h-[380px]">
            <div class="h-full w-full">
              <div
                class="flex h-full transition-transform duration-700 ease-out"
                :style="{ transform: `translateX(-${active * 100}%)` }"
              >
                <article
                  v-for="(slide, index) in slides"
                  :key="slide.id"
                  class="relative h-full min-w-full shrink-0 grow-0 basis-full"
                  :class="index === active ? 'z-10' : 'pointer-events-none'"
                  :aria-hidden="index !== active"
                >
                  <img
                    :src="slide.image"
                    :alt="slide.imageAlt"
                    class="absolute inset-0 h-full w-full object-cover"
                    :style="{ objectPosition: slide.imagePosition }"
                    :loading="index === 0 ? 'eager' : 'lazy'"
                  />
                  <div
                    class="pointer-events-none absolute inset-0 bg-gradient-to-r from-base-100/95 via-base-100/75 to-base-100/25 md:from-base-100/92 md:via-base-100/60 md:to-transparent"
                  ></div>

                  <div class="relative z-10 flex h-full items-center px-5 py-6 md:px-9 md:py-9">
                    <div class="max-w-md">
                      <div class="badge gap-2 border-base-300/80 bg-base-100/90 px-3 py-2.5 text-xs font-medium backdrop-blur-sm sm:badge-lg sm:text-sm">
                        <Icon :name="slide.badgeIcon" class="h-4 w-4 text-primary" />
                        {{ slide.badge }}
                      </div>
                      <h1 class="mt-3 text-2xl font-extrabold leading-[1.1] tracking-tight md:text-4xl lg:text-[2.5rem]">
                        {{ slide.title }}
                        <span v-if="slide.titleAccent" class="text-primary">{{ slide.titleAccent }}</span>
                      </h1>
                      <p class="mt-3 max-w-md text-sm opacity-80 md:text-base">
                        {{ slide.description }}
                      </p>
                      <div class="mt-5 flex flex-wrap gap-2.5">
                        <a
                          v-if="isHashLink(slide.ctaTo)"
                          :href="slide.ctaTo"
                          class="btn btn-primary rounded-full px-5 sm:btn-md md:px-6"
                          @click.prevent="followLink(slide.ctaTo)"
                        >
                          {{ slide.ctaLabel }}
                          <Icon name="lucide:arrow-right" class="h-4 w-4" />
                        </a>
                        <NuxtLink
                          v-else
                          :to="slide.ctaTo"
                          class="btn btn-primary rounded-full px-5 sm:btn-md md:px-6"
                        >
                          {{ slide.ctaLabel }}
                          <Icon name="lucide:arrow-right" class="h-4 w-4" />
                        </NuxtLink>

                        <template v-if="slide.secondaryCta">
                          <a
                            v-if="isHashLink(slide.secondaryCta.to)"
                            :href="slide.secondaryCta.to"
                            class="btn btn-outline rounded-full border-base-300 bg-base-100/80 px-5 backdrop-blur-sm sm:btn-md md:px-6"
                            @click.prevent="followLink(slide.secondaryCta.to)"
                          >
                            {{ slide.secondaryCta.label }}
                          </a>
                          <NuxtLink
                            v-else
                            :to="slide.secondaryCta.to"
                            class="btn btn-outline rounded-full border-base-300 bg-base-100/80 px-5 backdrop-blur-sm sm:btn-md md:px-6"
                          >
                            {{ slide.secondaryCta.label }}
                          </NuxtLink>
                        </template>
                      </div>
                    </div>
                  </div>
                </article>
              </div>
            </div>

          </div>

          <div class="mt-3 flex flex-wrap items-center justify-center gap-2">
            <button
              v-for="(slide, index) in slides"
              :key="`dot-${slide.id}`"
              type="button"
              class="group flex items-center gap-2 rounded-full px-2.5 py-1.5 transition"
              :class="index === active ? 'bg-primary/10' : 'hover:bg-base-300/60'"
              :aria-label="`Go to slide ${index + 1}: ${slide.badge}`"
              :aria-current="index === active ? 'true' : undefined"
              @click="goTo(index)"
            >
              <span
                class="h-2 rounded-full transition-all"
                :class="index === active ? 'w-6 bg-primary' : 'w-2 bg-base-content/25 group-hover:bg-base-content/40'"
              ></span>
              <span
                class="hidden text-[11px] font-semibold sm:inline"
                :class="index === active ? 'text-primary' : 'opacity-60'"
              >
                {{ slide.badge }}
              </span>
            </button>
          </div>
        </div>

        <!-- Quick-action side panel: 1/3 -->
        <aside class="lg:col-span-1" aria-label="Join Delivo">
          <p class="px-1 text-xs font-semibold uppercase tracking-wider text-base-content/50">
            Earn with Delivo
          </p>
          <div class="mt-3 grid grid-cols-1 gap-2.5 sm:grid-cols-2 lg:grid-cols-1">
            <NuxtLink
              v-for="link in quickLinks"
              :key="link.id"
              :to="link.to"
              class="group flex items-start gap-3 rounded-2xl border border-base-300 bg-base-100 p-3.5 transition hover:-translate-y-0.5 hover:border-primary/40 hover:shadow-md md:p-4"
            >
              <span
                class="grid h-10 w-10 shrink-0 place-items-center rounded-xl"
                :class="link.iconBg"
              >
                <Icon :name="link.icon" class="h-5 w-5" />
              </span>
              <div class="min-w-0 flex-1">
                <h3 class="text-sm font-bold leading-tight">{{ link.title }}</h3>
                <p class="mt-1 text-xs leading-snug opacity-65">{{ link.description }}</p>
              </div>
              <Icon
                name="lucide:arrow-right"
                class="mt-1 h-4 w-4 shrink-0 text-base-content/35 transition group-hover:translate-x-0.5 group-hover:text-primary"
              />
            </NuxtLink>
          </div>
        </aside>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
interface SlideCta {
  label: string;
  to: string;
}

interface HeroSlide {
  id: string;
  badge: string;
  badgeIcon: string;
  title: string;
  titleAccent?: string;
  description: string;
  image: string;
  imageAlt: string;
  /** Keeps faces in frame when the viewport is wider than the artwork (16:9). */
  imagePosition: string;
  ctaLabel: string;
  ctaTo: string;
  secondaryCta?: SlideCta;
}

interface QuickLink {
  id: string;
  title: string;
  description: string;
  icon: string;
  iconBg: string;
  to: string;
}

const slides: HeroSlide[] = [
  {
    id: 'customers',
    badge: 'For shoppers',
    badgeIcon: 'lucide:shopping-bag',
    title: 'You order.',
    titleAccent: ' We deliver.',
    description:
      'Shop groceries, fashion, electronics and more from trusted local vendors — picked, packed and brought to your door anywhere in Zimbabwe.',
    image: '/images/hero/customers.png',
    imageAlt: 'Customer receiving a Delivo delivery at home',
    imagePosition: '74% 32%',
    ctaLabel: 'Start shopping',
    ctaTo: '#products',
    secondaryCta: { label: 'Read more', to: '/about/shoppers' },
  },
  {
    id: 'vendors',
    badge: 'For sellers',
    badgeIcon: 'lucide:store',
    title: 'Grow your store on ',
    titleAccent: 'Delivo.',
    description:
      'List products, manage orders and get paid — reach customers across the country without building your own website or delivery network.',
    image: '/images/hero/vendors.png',
    imageAlt: 'Vendor managing products in their shop',
    imagePosition: '72% 30%',
    ctaLabel: 'Become a seller',
    ctaTo: '/vendor/apply',
    secondaryCta: { label: 'Read more', to: '/about/sellers' },
  },
  {
    id: 'parcels',
    badge: 'Parcel delivery',
    badgeIcon: 'lucide:package',
    title: 'Send parcels ',
    titleAccent: 'door to door.',
    description:
      'Book affordable courier runs for documents, gifts and packages — tracked from pickup to drop-off in Harare, Bulawayo and beyond.',
    image: '/images/hero/parcels.png',
    imageAlt: 'Courier delivering a parcel to a customer',
    imagePosition: '68% 45%',
    ctaLabel: 'Send a parcel',
    ctaTo: '#parcel-delivery',
    secondaryCta: { label: 'Read more', to: '/about/parcels' },
  },
  {
    id: 'fleet',
    badge: 'For fleet owners',
    badgeIcon: 'lucide:truck',
    title: 'Deliver on Delivo. ',
    titleAccent: 'Grow your fleet.',
    description:
      "Plug your fleet into Delivo's order pipeline and we'll feed it work. Pick the cities you cover, get matched to vendor pickups + customer drop-offs in real time, and get paid for every delivered shipment.",
    image: '/images/hero/fleet.png',
    imageAlt: 'Fleet owner with Delivo-branded delivery trucks and bikes',
    imagePosition: '62% 38%',
    ctaLabel: 'Become a partner',
    ctaTo: '/providers/apply',
    secondaryCta: { label: 'Read more', to: '/about/providers' },
  },
  {
    id: 'influencers',
    badge: 'For influencers',
    badgeIcon: 'lucide:megaphone',
    title: 'Share products. ',
    titleAccent: 'Earn commission.',
    description:
      'Join Delivo as an approved influencer, pick products that pay affiliate commission, and share your unique code with your audience. When shoppers buy through your link, you earn a cut — vendors set the rate on each product.',
    image: '/images/hero/influencers.png',
    imageAlt: 'Influencer promoting Delivo products with affiliate commission',
    imagePosition: '70% 35%',
    ctaLabel: 'Become an influencer',
    ctaTo: '/influencers/apply',
    secondaryCta: { label: 'Read more', to: '/about/influencers' },
  },
];

const quickLinks: QuickLink[] = [
  {
    id: 'become-vendor',
    title: 'Become a vendor',
    description: 'List your products and reach customers across Zimbabwe.',
    icon: 'lucide:store',
    iconBg: 'bg-primary/15 text-primary',
    to: '/vendor/apply',
  },
  {
    id: 'become-deliverer',
    title: 'Become a deliverer',
    description: 'Plug your fleet into Delivo orders and earn per delivery.',
    icon: 'lucide:truck',
    iconBg: 'bg-success/15 text-success',
    to: '/providers/apply',
  },
  {
    id: 'become-influencer',
    title: 'Become an influencer',
    description: 'Apply to share products and earn affiliate commission.',
    icon: 'lucide:megaphone',
    iconBg: 'bg-accent/30 text-accent-content',
    to: '/influencers/apply',
  },
  {
    id: 'promote-commission',
    title: 'Promote commission products',
    description: 'Browse products paying commission and share your code.',
    icon: 'lucide:percent',
    iconBg: 'bg-info/15 text-info',
    to: '/influencer',
  },
];

const isHashLink = (to: string) => to.startsWith('#');

const followLink = async (to: string) => {
  if (isHashLink(to)) {
    const id = to.slice(1);
    const el = document.getElementById(id);
    if (el) {
      el.scrollIntoView({ behavior: 'smooth', block: 'start' });
      history.replaceState(null, '', to);
    }
    return;
  }
  await navigateTo(to);
};

const AUTOPLAY_MS = 7000;

const active = ref(0);
let timer: ReturnType<typeof setInterval> | null = null;

const goTo = (index: number) => {
  active.value = (index + slides.length) % slides.length;
};

const next = () => goTo(active.value + 1);

const startAutoplay = () => {
  stopAutoplay();
  timer = setInterval(next, AUTOPLAY_MS);
};

const stopAutoplay = () => {
  if (timer) {
    clearInterval(timer);
    timer = null;
  }
};

const pauseAutoplay = () => stopAutoplay();
const resumeAutoplay = () => startAutoplay();

onMounted(() => {
  startAutoplay();
});

onUnmounted(() => {
  stopAutoplay();
});
</script>
