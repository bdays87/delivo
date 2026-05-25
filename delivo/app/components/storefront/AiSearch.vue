<template>
  <section class="bg-base-100 py-10 md:py-16">
    <div class="mx-auto max-w-3xl px-4">
      <div class="text-center">
        <div class="badge badge-lg gap-2 border-primary/20 bg-primary/10 px-4 py-3 font-medium text-primary">
          <Icon name="lucide:sparkles" class="h-4 w-4" />
          AI Shopping Assistant
        </div>
        <h1 class="mt-4 text-2xl font-extrabold tracking-tight md:text-3xl">
          Describe what you need — or snap your shopping list
        </h1>
        <p class="mt-2 text-sm opacity-70 md:text-base">
          Type in your own words, or take a photo of a handwritten or printed shopping list — Delivo
          reads the items and matches you with vendors that sell them.
        </p>
      </div>

      <!-- Mode tabs -->
      <div class="tabs tabs-box mt-8 w-full rounded-2xl bg-base-200/60 p-1">
        <button
          type="button"
          class="tab flex-1 gap-2 rounded-xl font-semibold transition"
          :class="mode === 'text' ? 'tab-active bg-base-100 shadow-sm' : ''"
          @click="setMode('text')"
        >
          <Icon name="lucide:message-square-text" class="h-4 w-4" />
          Describe
        </button>
        <button
          type="button"
          class="tab flex-1 gap-2 rounded-xl font-semibold transition"
          :class="mode === 'photo' ? 'tab-active bg-base-100 shadow-sm' : ''"
          @click="setMode('photo')"
        >
          <Icon name="lucide:camera" class="h-4 w-4" />
          Shopping list photo
        </button>
      </div>

      <!-- Text mode -->
      <form v-if="mode === 'text'" class="mt-6" @submit.prevent="runSearch()">
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

      <!-- Photo mode -->
      <div v-else class="mt-6">
        <input
          ref="fileInput"
          type="file"
          accept="image/*"
          capture="environment"
          class="hidden"
          @change="onFileSelected"
        />

        <div
          v-if="!listImagePreview"
          class="flex cursor-pointer flex-col items-center rounded-3xl border-2 border-dashed border-base-300 bg-base-200/40 px-6 py-12 text-center transition hover:border-primary/40 hover:bg-base-200/60"
          role="button"
          tabindex="0"
          @click="openFilePicker"
          @keydown.enter.prevent="openFilePicker"
          @keydown.space.prevent="openFilePicker"
        >
          <span class="grid h-14 w-14 place-items-center rounded-2xl bg-primary/15 text-primary">
            <Icon name="lucide:camera" class="h-7 w-7" />
          </span>
          <p class="mt-4 font-semibold">Take a photo or upload your list</p>
          <p class="mt-2 max-w-sm text-sm opacity-70">
            Works with handwritten notes, printed lists, or screenshots. Make sure the text is clear
            and well lit.
          </p>
          <span class="btn btn-outline btn-sm mt-5 rounded-full">Choose image</span>
        </div>

        <div v-else class="rounded-3xl border border-base-300 bg-base-200/40 p-4 md:p-5">
          <div class="relative overflow-hidden rounded-2xl bg-base-300">
            <img
              :src="listImagePreview"
              alt="Your shopping list"
              class="max-h-72 w-full object-contain"
            />
          </div>
          <div class="mt-4 flex flex-wrap gap-2">
            <button
              type="button"
              class="btn btn-ghost btn-sm rounded-full"
              :disabled="scanningList || searching"
              @click="clearPhoto"
            >
              <Icon name="lucide:image-plus" class="h-4 w-4" />
              Replace photo
            </button>
            <button
              type="button"
              class="btn btn-primary btn-sm rounded-full"
              :disabled="scanningList || searching"
              @click="scanListFromPhoto"
            >
              <Icon
                v-if="scanningList"
                name="lucide:loader-circle"
                class="h-4 w-4 animate-spin"
              />
              <Icon v-else name="lucide:scan-line" class="h-4 w-4" />
              {{ scanningList ? 'Reading list…' : 'Read shopping list' }}
            </button>
          </div>
        </div>

        <div v-if="extractedItems.length" class="mt-6">
          <p class="text-sm font-semibold">Items we found on your list</p>
          <p class="mt-1 text-xs opacity-60">Edit anything before searching for stores.</p>
          <ul class="mt-3 flex flex-wrap gap-2">
            <li
              v-for="(item, index) in extractedItems"
              :key="`${item}-${index}`"
              class="badge badge-lg gap-1 border-base-300 bg-base-100 py-3 pl-3 pr-1 font-normal"
            >
              {{ item }}
              <button
                type="button"
                class="btn btn-ghost btn-xs btn-circle"
                aria-label="Remove item"
                @click="removeExtractedItem(index)"
              >
                <Icon name="lucide:x" class="h-3 w-3" />
              </button>
            </li>
          </ul>
          <textarea
            v-model="query"
            rows="2"
            class="textarea textarea-bordered mt-4 w-full resize-none rounded-2xl bg-base-200/50 text-sm"
            :disabled="searching"
          />
          <div class="mt-4 flex justify-end">
            <button
              type="button"
              class="btn btn-primary rounded-full px-8"
              :disabled="searching || !query.trim()"
              @click="runSearch()"
            >
              <Icon v-if="searching" name="lucide:loader-circle" class="h-4 w-4 animate-spin" />
              <Icon v-else name="lucide:search" class="h-4 w-4" />
              {{ searching ? 'Searching…' : 'Find stores' }}
            </button>
          </div>
        </div>

        <p v-else class="mt-4 text-center text-xs opacity-50">
          Powered by Delivo AI · List reading works best with clear, upright photos
        </p>
      </div>

      <!-- Results -->
      <div v-if="hasSearched" class="mt-10">
        <div v-if="searching" class="flex flex-col items-center gap-3 py-8 text-center">
          <span class="loading loading-dots loading-lg text-primary"></span>
          <p class="text-sm opacity-70">
            {{ lastSearchFromPhoto ? 'Matching list items to vendors…' : 'Scanning vendors on our platform…' }}
          </p>
        </div>

        <div v-else-if="results.length" class="rounded-3xl border border-base-300 bg-base-200/40 p-5 md:p-6">
          <p class="text-sm font-semibold text-primary">
            {{ results.length }} store{{ results.length === 1 ? '' : 's' }} match your search
          </p>
          <ul class="mt-4 divide-y divide-base-300">
            <li
              v-for="store in results"
              :key="store.id"
              class="flex flex-wrap items-center gap-4 py-4 first:pt-0 last:pb-0"
            >
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
              <NuxtLink to="#vendors" class="btn btn-sm btn-outline rounded-full">
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

      <!-- Quick prompts (text mode only, before first search) -->
      <div v-if="mode === 'text' && !hasSearched" class="mt-6 flex flex-wrap justify-center gap-2">
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

type InputMode = 'text' | 'photo';

/** Placeholder items until a vision API is wired — simulates reading a typical grocery list. */
const MOCK_LIST_ITEMS = [
  'milk',
  'bread',
  'eggs',
  'tomatoes',
  'cooking oil',
  'rice',
  'chicken',
  'soap',
];

const mode = ref<InputMode>('text');
const query = ref('');
const searching = ref(false);
const scanningList = ref(false);
const hasSearched = ref(false);
const lastSearchFromPhoto = ref(false);
const results = ref<SearchResult[]>([]);
const fileInput = ref<HTMLInputElement | null>(null);
const listImage = ref<File | null>(null);
const listImagePreview = ref<string | null>(null);
const extractedItems = ref<string[]>([]);

let previewObjectUrl: string | null = null;

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

const syncQueryFromItems = () => {
  query.value = extractedItems.value.join(', ');
};

const revokePreviewUrl = () => {
  if (previewObjectUrl) {
    URL.revokeObjectURL(previewObjectUrl);
    previewObjectUrl = null;
  }
};

const setMode = (next: InputMode) => {
  mode.value = next;
};

const openFilePicker = () => {
  fileInput.value?.click();
};

const clearPhoto = () => {
  revokePreviewUrl();
  listImage.value = null;
  listImagePreview.value = null;
  extractedItems.value = [];
  if (fileInput.value) fileInput.value.value = '';
  openFilePicker();
};

const onFileSelected = (event: Event) => {
  const input = event.target as HTMLInputElement;
  const file = input.files?.[0];
  if (!file || !file.type.startsWith('image/')) return;

  revokePreviewUrl();
  listImage.value = file;
  previewObjectUrl = URL.createObjectURL(file);
  listImagePreview.value = previewObjectUrl;
  extractedItems.value = [];
  query.value = '';
  hasSearched.value = false;
  results.value = [];
};

const scanListFromPhoto = async () => {
  if (!listImage.value) return;

  scanningList.value = true;
  extractedItems.value = [];
  hasSearched.value = false;
  results.value = [];

  // TODO: POST image to Delivo vision/OCR endpoint when available.
  await new Promise((r) => setTimeout(r, 1600));

  extractedItems.value = [...MOCK_LIST_ITEMS];
  syncQueryFromItems();
  scanningList.value = false;
  await runSearch(true);
};

const removeExtractedItem = (index: number) => {
  extractedItems.value = extractedItems.value.filter((_, i) => i !== index);
  syncQueryFromItems();
};

const runSearch = async (fromPhoto = false) => {
  const text = query.value.trim();
  if (!text) return;

  lastSearchFromPhoto.value = fromPhoto || mode.value === 'photo';
  hasSearched.value = true;
  searching.value = true;
  results.value = [];

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

onUnmounted(() => {
  revokePreviewUrl();
});
</script>
