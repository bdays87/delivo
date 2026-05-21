<template>
  <section class="border-b border-base-300 bg-base-100 py-12 md:py-16">
    <div class="mx-auto max-w-3xl px-4">
      <div class="text-center">
        <div class="badge badge-lg gap-2 border-primary/20 bg-primary/10 px-4 py-3 font-medium text-primary">
          <Icon name="lucide:sparkles" class="h-4 w-4" />
          AI search
        </div>
        <h2 class="mt-4 text-2xl font-extrabold tracking-tight md:text-3xl">
          Describe what you need — we'll find the right stores
        </h2>
        <p class="mt-2 text-sm opacity-70 md:text-base">
          Tell us in your own words; Delivo matches you with vendors that sell it.
        </p>
      </div>

      <form class="mt-8" @submit.prevent="runSearch">
        <label class="sr-only" for="ai-search-query">What are you looking for?</label>
        <textarea
          id="ai-search-query"
          v-model="query"
          rows="3"
          class="textarea textarea-bordered w-full resize-none rounded-3xl bg-base-200/50 text-base leading-relaxed md:text-lg"
          placeholder="Explain what you're looking for — for example: fresh vegetables and bread for the week, or a birthday gift under $30 — and I'll find stores selling what you need."
          :disabled="searching"
        />
        <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
          <p class="text-xs opacity-50">
            Powered by Delivo AI · Results are suggestions from active vendors
          </p>
          <button
            type="submit"
            class="btn btn-primary rounded-full px-8"
            :disabled="searching || !query.trim()"
          >
            <Icon v-if="searching" name="lucide:loader-circle" class="h-4 w-4 animate-spin" />
            <Icon v-else name="lucide:search" class="h-4 w-4" />
            {{ searching ? 'Searching…' : 'Find stores' }}
          </button>
        </div>
      </form>

      <!-- Results -->
      <div v-if="hasSearched" class="mt-10">
        <div v-if="searching" class="flex flex-col items-center gap-3 py-8 text-center">
          <span class="loading loading-dots loading-lg text-primary"></span>
          <p class="text-sm opacity-70">Scanning vendors across Zimbabwe…</p>
        </div>

        <div v-else-if="results.length" class="rounded-3xl border border-base-300 bg-base-200/40 p-5 md:p-6">
          <p class="text-sm font-semibold text-primary">
            {{ results.length }} store{{ results.length === 1 ? '' : 's' }} match your search
          </p>
          <ul class="mt-4 divide-y divide-base-300">
            <li v-for="store in results" :key="store.id" class="flex flex-wrap items-center gap-4 py-4 first:pt-0 last:pb-0">
              <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-primary/10 text-primary">
                <Icon name="lucide:store" class="h-6 w-6" />
              </div>
              <div class="min-w-0 flex-1">
                <div class="font-semibold">{{ store.name }}</div>
                <div class="text-sm opacity-70">{{ store.matchReason }}</div>
                <div class="mt-1 flex flex-wrap gap-2">
                  <span
                    v-for="tag in store.tags"
                    :key="tag"
                    class="badge badge-sm badge-ghost"
                  >{{ tag }}</span>
                </div>
              </div>
              <NuxtLink
                to="#vendors"
                class="btn btn-sm btn-outline rounded-full"
              >
                View store
              </NuxtLink>
            </li>
          </ul>
        </div>

        <div v-else class="rounded-3xl border border-dashed border-base-300 bg-base-200/30 px-6 py-10 text-center">
          <Icon name="lucide:search-x" class="mx-auto h-10 w-10 opacity-40" />
          <p class="mt-3 font-semibold">No matching stores yet</p>
          <p class="mt-1 text-sm opacity-70">
            Try different words — brands, product types, or what you need it for.
          </p>
          <NuxtLink to="/vendor/apply" class="btn btn-link btn-sm mt-2">Know a seller? List on Delivo</NuxtLink>
        </div>
      </div>

      <!-- Quick prompts -->
      <div v-if="!hasSearched" class="mt-6 flex flex-wrap justify-center gap-2">
        <button
          v-for="hint in quickHints"
          :key="hint"
          type="button"
          class="btn btn-sm btn-ghost rounded-full border border-base-300"
          @click="applyHint(hint)"
        >
          {{ hint }}
        </button>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
interface SearchableStore {
  id: string;
  name: string;
  tags: string[];
  haystack: string;
  matchReason: string;
}

interface SearchResult extends SearchableStore {
  score: number;
}

const query = ref('');
const searching = ref(false);
const hasSearched = ref(false);
const results = ref<SearchResult[]>([]);

const quickHints = [
  'Fresh groceries',
  'Phone accessories',
  'Pharmacy essentials',
  'Birthday gift ideas',
];

const catalog = computed<SearchableStore[]>(() => {
  const stores = useFeaturedStores();
  const vendors = useFeaturedVendors();

  const fromStores: SearchableStore[] = stores.map((s) => ({
    id: `store-${s.name}`,
    name: s.name,
    tags: [...s.cats, s.delivery, s.loc],
    haystack: [s.name, ...s.cats, s.delivery, s.loc].join(' ').toLowerCase(),
    matchReason: `${s.cats.join(' · ')} · ${s.delivery} · ${s.loc}`,
  }));

  const fromVendors: SearchableStore[] = vendors
    .filter((v) => !fromStores.some((s) => s.name === v.name))
    .map((v) => ({
      id: `vendor-${v.name}`,
      name: v.name,
      tags: [v.tagline],
      haystack: [v.name, v.tagline].join(' ').toLowerCase(),
      matchReason: v.tagline,
    }));

  return [...fromStores, ...fromVendors];
});

const tokenize = (text: string): string[] =>
  text
    .toLowerCase()
    .replace(/[^a-z0-9\s]/g, ' ')
    .split(/\s+/)
    .filter((w) => w.length > 2);

const scoreStore = (store: SearchableStore, tokens: string[]): number => {
  if (!tokens.length) return 0;
  let score = 0;
  for (const token of tokens) {
    if (store.haystack.includes(token)) score += 2;
    for (const tag of store.tags) {
      if (tag.toLowerCase().includes(token)) score += 1;
    }
  }
  return score;
};

const runSearch = async () => {
  const text = query.value.trim();
  if (!text) return;

  hasSearched.value = true;
  searching.value = true;
  results.value = [];

  // Brief delay so the AI search feels responsive, not instant mock data.
  await new Promise((r) => setTimeout(r, 900));

  const tokens = tokenize(text);
  results.value = catalog.value
    .map((store) => ({ ...store, score: scoreStore(store, tokens) }))
    .filter((s) => s.score > 0)
    .sort((a, b) => b.score - a.score)
    .slice(0, 6);

  searching.value = false;
};

const applyHint = (hint: string) => {
  query.value = `I'm looking for ${hint.toLowerCase()}`;
  runSearch();
};
</script>
