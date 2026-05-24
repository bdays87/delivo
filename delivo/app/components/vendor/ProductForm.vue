<template>
  <form class="space-y-6" @submit.prevent="handleSubmit">
    <section class="rounded-3xl border border-base-300 bg-base-100 p-6">
      <h2 class="text-lg font-bold">Basics</h2>
      <div class="mt-4 grid gap-4 md:grid-cols-2">
        <label class="fieldset md:col-span-2">
          <span class="fieldset-legend">Product name</span>
          <input
            v-model="form.name"
            type="text"
            placeholder="e.g. Premium Mealie Meal 10kg"
            :class="['input input-bordered w-full', errors.name ? 'input-error' : '']"
          />
          <span v-if="errors.name" class="text-xs text-red-600">{{ errors.name }}</span>
        </label>

        <label class="fieldset">
          <span class="fieldset-legend">Category</span>
          <select
            v-model.number="form.category_id"
            :class="['select select-bordered w-full', errors.category_id ? 'select-error' : '']"
          >
            <option :value="0" disabled>Choose a category…</option>
            <option v-for="c in categoryStore.categories" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
          <span v-if="errors.category_id" class="text-xs text-red-600">{{ errors.category_id }}</span>
        </label>

        <label class="fieldset">
          <span class="fieldset-legend">Vendor SKU (optional)</span>
          <input
            v-model="form.sku"
            type="text"
            placeholder="Your internal SKU"
            class="input input-bordered w-full"
          />
        </label>

        <label class="fieldset">
          <span class="fieldset-legend">Weight (kg) — for shipping</span>
          <input
            v-model.number="form.weight_kg"
            type="number"
            step="0.01"
            min="0"
            class="input input-bordered w-full"
          />
        </label>

        <label class="fieldset md:col-span-2">
          <span class="fieldset-legend">Description</span>
          <textarea
            v-model="form.description"
            rows="4"
            class="textarea textarea-bordered w-full"
            placeholder="What is this product? Any specs or compatibility notes?"
          />
        </label>
      </div>
    </section>

    <section class="rounded-3xl border border-base-300 bg-base-100 p-6">
      <div>
        <h2 class="text-lg font-bold">Affiliate program (optional)</h2>
        <p class="mt-1 text-sm opacity-70">
          Reward influencers for promoting this product and give buyers a discount when they use
          the influencer's code. Set both to 0 to opt out of the program.
        </p>
      </div>
      <div class="mt-4 grid gap-4 md:grid-cols-3">
        <label class="fieldset">
          <span class="fieldset-legend">Influencer commission (%)</span>
          <input
            v-model.number="form.affiliate_influencer_pct"
            type="number"
            step="0.01"
            min="0"
            max="100"
            placeholder="e.g. 10"
            :class="['input input-bordered w-full', errors.affiliate_influencer_pct ? 'input-error' : '']"
          />
          <span v-if="errors.affiliate_influencer_pct" class="text-xs text-red-600">{{ errors.affiliate_influencer_pct }}</span>
        </label>
        <label class="fieldset">
          <span class="fieldset-legend">Buyer discount (%)</span>
          <input
            v-model.number="form.affiliate_buyer_discount_pct"
            type="number"
            step="0.01"
            min="0"
            max="100"
            placeholder="e.g. 5"
            class="input input-bordered w-full"
          />
        </label>
        <div class="fieldset">
          <span class="fieldset-legend">Total commission</span>
          <div class="rounded-2xl bg-base-200/40 px-4 py-3 text-lg font-bold">
            {{ affiliateTotal.toFixed(2) }}%
          </div>
          <span class="text-xs opacity-60">Influencer % + buyer % — both paid out of the sale.</span>
        </div>
      </div>
    </section>

    <section class="rounded-3xl border border-base-300 bg-base-100 p-6">
      <div class="flex items-start justify-between gap-4">
        <div>
          <h2 class="text-lg font-bold">Price tiers (USD)</h2>
          <p class="mt-1 text-sm opacity-70">
            Prices are stored in USD. Storefront converts to ZWG using the platform rate.
            Tiers apply across all colors of this product.
          </p>
        </div>
        <button type="button" class="btn btn-sm btn-ghost rounded-full" @click="addTier">
          <Icon name="lucide:plus" class="h-4 w-4" /> Add tier
        </button>
      </div>

      <div class="mt-4 space-y-2">
        <div
          v-for="(tier, i) in form.price_tiers"
          :key="i"
          class="grid grid-cols-1 items-center gap-2 sm:grid-cols-[1fr_1fr_auto]"
        >
          <label class="fieldset">
            <span class="fieldset-legend">Min qty</span>
            <input
              v-model.number="tier.min_qty"
              type="number"
              min="1"
              :class="['input input-bordered w-full', errors[`price_tiers[${i}].min_qty`] ? 'input-error' : '']"
            />
            <span v-if="errors[`price_tiers[${i}].min_qty`]" class="text-xs text-red-600">
              {{ errors[`price_tiers[${i}].min_qty`] }}
            </span>
          </label>
          <label class="fieldset">
            <span class="fieldset-legend">Unit price (USD)</span>
            <input
              v-model.number="tier.unit_price"
              type="number"
              step="0.01"
              min="0.01"
              :class="['input input-bordered w-full', errors[`price_tiers[${i}].unit_price`] ? 'input-error' : '']"
            />
            <span v-if="errors[`price_tiers[${i}].unit_price`]" class="text-xs text-red-600">
              {{ errors[`price_tiers[${i}].unit_price`] }}
            </span>
          </label>
          <button
            type="button"
            class="btn btn-sm btn-ghost rounded-full text-error"
            :disabled="form.price_tiers.length <= 1"
            @click="removeTier(i)"
          >
            <Icon name="lucide:trash-2" class="h-4 w-4" />
          </button>
        </div>
      </div>
      <span v-if="errors.price_tiers" class="text-xs text-red-600">{{ errors.price_tiers }}</span>
    </section>

    <section class="rounded-3xl border border-base-300 bg-base-100 p-6">
      <div class="flex items-start justify-between gap-4">
        <div>
          <h2 class="text-lg font-bold">Variants</h2>
          <p class="mt-1 text-sm opacity-70">
            Each row is a stock-keeping color. If your product has no color choice, leave color blank and
            keep a single row.
          </p>
        </div>
        <button type="button" class="btn btn-sm btn-ghost rounded-full" @click="addVariant">
          <Icon name="lucide:plus" class="h-4 w-4" /> Add variant
        </button>
      </div>

      <div class="mt-4 space-y-2">
        <div
          v-for="(variant, i) in form.variants"
          :key="i"
          class="grid grid-cols-1 items-center gap-2 sm:grid-cols-[1fr_1fr_1fr_auto]"
        >
          <label class="fieldset">
            <span class="fieldset-legend">Color (optional)</span>
            <input
              v-model="variant.color"
              type="text"
              placeholder="e.g. Red"
              class="input input-bordered w-full"
            />
          </label>
          <label class="fieldset">
            <span class="fieldset-legend">Stock</span>
            <input
              v-model.number="variant.stock_quantity"
              type="number"
              min="0"
              :class="['input input-bordered w-full', errors[`variants[${i}].stock_quantity`] ? 'input-error' : '']"
            />
            <span v-if="errors[`variants[${i}].stock_quantity`]" class="text-xs text-red-600">
              {{ errors[`variants[${i}].stock_quantity`] }}
            </span>
          </label>
          <label class="fieldset">
            <span class="fieldset-legend">SKU (optional)</span>
            <input v-model="variant.sku" type="text" class="input input-bordered w-full" />
          </label>
          <button
            type="button"
            class="btn btn-sm btn-ghost rounded-full text-error"
            :disabled="form.variants.length <= 1"
            @click="removeVariant(i)"
          >
            <Icon name="lucide:trash-2" class="h-4 w-4" />
          </button>
        </div>
      </div>
      <span v-if="errors.variants" class="text-xs text-red-600">{{ errors.variants }}</span>
    </section>

    <div class="flex justify-end gap-3">
      <button type="button" class="btn rounded-full" @click="$emit('cancel')">Cancel</button>
      <button type="submit" class="btn btn-primary rounded-full" :disabled="submitting">
        <span v-if="submitting" class="loading loading-spinner loading-xs"></span>
        {{ submitLabel }}
      </button>
    </div>
  </form>
</template>

<script setup lang="ts">
import { ProductSchema } from '~/utils/ProductSchemas';
import type { Product, ProductPriceTier, ProductVariant } from '~/stores/product';
import type { VendorProductPayload } from '~/stores/vendorProduct';

const props = defineProps<{
  initial?: Product | null;
  submitLabel: string;
  submitting: boolean;
}>();

const emit = defineEmits<{
  (e: 'submit', payload: VendorProductPayload): void;
  (e: 'cancel'): void;
}>();

const categoryStore = useCategoryStore();

interface FormState {
  category_id: number;
  name: string;
  description: string;
  sku: string;
  weight_kg: number | null;
  affiliate_influencer_pct: number;
  affiliate_buyer_discount_pct: number;
  price_tiers: ProductPriceTier[];
  variants: ProductVariant[];
}

const blank = (): FormState => ({
  category_id: 0,
  name: '',
  description: '',
  sku: '',
  weight_kg: null,
  affiliate_influencer_pct: 0,
  affiliate_buyer_discount_pct: 0,
  price_tiers: [{ min_qty: 1, unit_price: 0 }],
  variants: [{ color: '', stock_quantity: 0, sku: '' }],
});

const affiliateTotal = computed(() =>
  Number(form.affiliate_influencer_pct ?? 0) + Number(form.affiliate_buyer_discount_pct ?? 0),
);

const form = reactive<FormState>(blank());
const errors = reactive<Record<string, string>>({});

const hydrate = (product: Product) => {
  form.category_id = product.category_id;
  form.name = product.name;
  form.description = product.description ?? '';
  form.sku = product.sku ?? '';
  form.weight_kg = product.weight_kg !== null ? Number(product.weight_kg) : null;
  form.affiliate_influencer_pct = Number(product.affiliate_influencer_pct ?? 0);
  form.affiliate_buyer_discount_pct = Number(product.affiliate_buyer_discount_pct ?? 0);
  form.price_tiers = (product.price_tiers ?? []).map((t) => ({
    min_qty: Number(t.min_qty),
    unit_price: Number(t.unit_price),
  }));
  if (!form.price_tiers.length) form.price_tiers = [{ min_qty: 1, unit_price: 0 }];
  form.variants = (product.variants ?? []).map((v) => ({
    color: v.color ?? '',
    stock_quantity: Number(v.stock_quantity),
    sku: v.sku ?? '',
  }));
  if (!form.variants.length) form.variants = [{ color: '', stock_quantity: 0, sku: '' }];
};

onMounted(async () => {
  if (!categoryStore.categories.length) await categoryStore.fetchActive();
  if (props.initial) hydrate(props.initial);
});

watch(() => props.initial, (next) => {
  if (next) hydrate(next);
});

const addTier = () => {
  const last = form.price_tiers[form.price_tiers.length - 1];
  form.price_tiers.push({ min_qty: Number(last?.min_qty ?? 1) + 1, unit_price: Number(last?.unit_price ?? 0) });
};
const removeTier = (i: number) => form.price_tiers.splice(i, 1);

const addVariant = () => form.variants.push({ color: '', stock_quantity: 0, sku: '' });
const removeVariant = (i: number) => form.variants.splice(i, 1);

const clearErrors = () => Object.keys(errors).forEach((k) => delete errors[k]);

const handleSubmit = async () => {
  clearErrors();
  try {
    const valid = await ProductSchema.validate(
      {
        ...form,
        sku: form.sku || null,
        description: form.description || null,
      },
      { abortEarly: false },
    );

    const payload: VendorProductPayload = {
      category_id: valid.category_id,
      name: valid.name,
      description: valid.description ?? null,
      sku: valid.sku ?? null,
      weight_kg: valid.weight_kg ?? null,
      affiliate_influencer_pct: Number(valid.affiliate_influencer_pct ?? 0),
      affiliate_buyer_discount_pct: Number(valid.affiliate_buyer_discount_pct ?? 0),
      price_tiers: valid.price_tiers.map((t) => ({
        min_qty: Number(t.min_qty),
        unit_price: Number(t.unit_price),
      })),
      variants: valid.variants.map((v) => ({
        color: v.color ?? null,
        stock_quantity: Number(v.stock_quantity),
        sku: v.sku ?? null,
      })),
    };

    emit('submit', payload);
  } catch (err: any) {
    if (err?.inner?.length) {
      err.inner.forEach((e: any) => {
        if (e.path) errors[e.path] = e.message;
      });
    }
  }
};
</script>
