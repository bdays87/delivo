<template>
  <section
    class="relative overflow-hidden bg-base-200"
    aria-roledescription="carousel"
    aria-label="Delivo highlights"
    @mouseenter="pauseAutoplay"
    @mouseleave="resumeAutoplay"
  >
    <div class="absolute -top-24 -right-20 h-80 w-80 rounded-full bg-primary/15 blur-3xl"></div>
    <div class="absolute -bottom-24 -left-20 h-80 w-80 rounded-full bg-secondary/15 blur-3xl"></div>

    <div class="relative mx-auto max-w-7xl px-4 py-10 md:py-14">
      <div class="relative overflow-hidden rounded-[2.5rem] border border-base-300 bg-base-100 shadow-xl">
        <!-- Slides -->
        <div
          class="flex transition-transform duration-700 ease-out"
          :style="{ transform: `translateX(-${active * 100}%)` }"
        >
          <article
            v-for="(slide, index) in slides"
            :key="slide.id"
            class="relative grid w-full shrink-0 grid-cols-1 md:grid-cols-2"
            :class="index === active ? 'z-10' : 'pointer-events-none'"
            :aria-hidden="index !== active"
          >
            <!-- Copy -->
            <div class="relative z-10 flex flex-col justify-center px-6 py-10 md:px-10 md:py-14 lg:px-14">
              <div class="badge badge-lg gap-2 border-base-300 bg-base-200/80 px-4 py-3 font-medium">
                <Icon :name="slide.badgeIcon" class="h-4 w-4 text-primary" />
                {{ slide.badge }}
              </div>
              <h1 class="mt-5 text-3xl font-extrabold leading-[1.08] tracking-tight md:text-5xl lg:text-6xl">
                {{ slide.title }}
                <span v-if="slide.titleAccent" class="text-primary">{{ slide.titleAccent }}</span>
              </h1>
              <p class="mt-4 max-w-lg text-base opacity-80 md:text-lg">
                {{ slide.description }}
              </p>
              <div class="mt-8 flex flex-wrap gap-3">
                <a
                  v-if="isHashLink(slide.ctaTo)"
                  :href="slide.ctaTo"
                  class="btn btn-primary btn-lg rounded-full px-7"
                  @click.prevent="followLink(slide.ctaTo)"
                >
                  {{ slide.ctaLabel }}
                  <Icon name="lucide:arrow-right" class="h-4 w-4" />
                </a>
                <NuxtLink
                  v-else
                  :to="slide.ctaTo"
                  class="btn btn-primary btn-lg rounded-full px-7"
                >
                  {{ slide.ctaLabel }}
                  <Icon name="lucide:arrow-right" class="h-4 w-4" />
                </NuxtLink>

                <template v-if="slide.secondaryCta">
                  <a
                    v-if="isHashLink(slide.secondaryCta.to)"
                    :href="slide.secondaryCta.to"
                    class="btn btn-outline btn-lg rounded-full px-7"
                    @click.prevent="followLink(slide.secondaryCta.to)"
                  >
                    {{ slide.secondaryCta.label }}
                  </a>
                  <NuxtLink
                    v-else
                    :to="slide.secondaryCta.to"
                    class="btn btn-outline btn-lg rounded-full px-7"
                  >
                    {{ slide.secondaryCta.label }}
                  </NuxtLink>
                </template>
              </div>
            </div>

            <!-- Image -->
            <div class="relative min-h-[280px] md:min-h-[420px]">
              <img
                :src="slide.image"
                :alt="slide.imageAlt"
                class="h-full w-full object-cover"
                :loading="index === 0 ? 'eager' : 'lazy'"
              />
              <div
                class="pointer-events-none absolute inset-0 bg-gradient-to-r from-base-100/90 via-base-100/40 to-transparent md:from-base-100/80 md:via-base-100/20"
              ></div>
              <div
                v-if="slide.floating"
                class="absolute bottom-6 right-6 hidden max-w-[220px] rounded-2xl bg-base-100/95 p-4 shadow-xl backdrop-blur md:block"
              >
                <div class="flex items-center gap-3">
                  <span
                    class="grid h-10 w-10 place-items-center rounded-xl"
                    :class="slide.floating.iconBg"
                  >
                    <Icon :name="slide.floating.icon" class="h-5 w-5" />
                  </span>
                  <div>
                    <div class="text-sm font-semibold">{{ slide.floating.title }}</div>
                    <div class="text-xs opacity-60">{{ slide.floating.subtitle }}</div>
                  </div>
                </div>
              </div>
            </div>
          </article>
        </div>

        <!-- Prev / next -->
        <button
          type="button"
          class="btn btn-circle btn-ghost absolute left-3 top-1/2 z-10 -translate-y-1/2 bg-base-100/80 backdrop-blur md:left-5"
          aria-label="Previous slide"
          @click="prev"
        >
          <Icon name="lucide:chevron-left" class="h-6 w-6" />
        </button>
        <button
          type="button"
          class="btn btn-circle btn-ghost absolute right-3 top-1/2 z-10 -translate-y-1/2 bg-base-100/80 backdrop-blur md:right-5"
          aria-label="Next slide"
          @click="next"
        >
          <Icon name="lucide:chevron-right" class="h-6 w-6" />
        </button>
      </div>

      <!-- Dots + progress -->
      <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
        <button
          v-for="(slide, index) in slides"
          :key="`dot-${slide.id}`"
          type="button"
          class="group flex items-center gap-2 rounded-full px-3 py-2 transition"
          :class="index === active ? 'bg-primary/10' : 'hover:bg-base-300/60'"
          :aria-label="`Go to slide ${index + 1}: ${slide.badge}`"
          :aria-current="index === active ? 'true' : undefined"
          @click="goTo(index)"
        >
          <span
            class="h-2.5 rounded-full transition-all"
            :class="index === active ? 'w-8 bg-primary' : 'w-2.5 bg-base-content/25 group-hover:bg-base-content/40'"
          ></span>
          <span
            class="hidden text-xs font-semibold sm:inline"
            :class="index === active ? 'text-primary' : 'opacity-60'"
          >
            {{ slide.badge }}
          </span>
        </button>
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
  ctaLabel: string;
  ctaTo: string;
  secondaryCta?: SlideCta;
  floating?: {
    icon: string;
    iconBg: string;
    title: string;
    subtitle: string;
  };
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
    ctaLabel: 'Start shopping',
    ctaTo: '#products',
    secondaryCta: { label: 'Create account', to: '/auth/register' },
    floating: {
      icon: 'lucide:truck',
      iconBg: 'bg-success/15 text-success',
      title: 'Out for delivery',
      subtitle: 'Order #DL-2491 · Harare',
    },
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
    ctaLabel: 'Become a seller',
    ctaTo: '/vendor/apply',
    secondaryCta: { label: 'Vendor dashboard', to: '/vendor' },
    floating: {
      icon: 'lucide:trending-up',
      iconBg: 'bg-primary/15 text-primary',
      title: 'New order',
      subtitle: '3 items · $24.50',
    },
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
    ctaLabel: 'Send a parcel',
    ctaTo: '#parcel-delivery',
    secondaryCta: { label: 'Track delivery', to: '#parcel-delivery' },
    floating: {
      icon: 'lucide:map-pin',
      iconBg: 'bg-info/15 text-info',
      title: 'On the way',
      subtitle: 'ETA 45 min · Avondale',
    },
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
const prev = () => goTo(active.value - 1);

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
