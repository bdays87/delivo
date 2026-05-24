import * as yup from 'yup';

const priceTierSchema = yup.object({
  min_qty: yup
    .number()
    .typeError('Min qty must be a number')
    .integer('Min qty must be a whole number')
    .min(1, 'Min qty must be at least 1')
    .required('Min qty is required'),
  unit_price: yup
    .number()
    .typeError('Unit price must be a number')
    .min(0.01, 'Unit price must be at least 0.01')
    .required('Unit price is required'),
});

const variantSchema = yup.object({
  color: yup
    .string()
    .nullable()
    .max(60)
    .transform((v, orig) => (orig === '' || orig === undefined ? null : v)),
  stock_quantity: yup
    .number()
    .typeError('Stock must be a number')
    .integer('Stock must be a whole number')
    .min(0, 'Stock cannot be negative')
    .required('Stock is required'),
  sku: yup.string().nullable().max(80),
});

export const ProductSchema = yup.object({
  category_id: yup
    .number()
    .typeError('Choose a category')
    .integer()
    .min(1, 'Choose a category')
    .required('Category is required'),
  name: yup.string().required('Name is required').max(180),
  description: yup.string().nullable().max(5000),
  sku: yup.string().nullable().max(80),
  weight_kg: yup
    .number()
    .nullable()
    .transform((v, orig) => (orig === '' || orig === null || orig === undefined ? null : v))
    .min(0)
    .max(9999.999),
  affiliate_influencer_pct: yup
    .number()
    .transform((v, orig) => (orig === '' || orig === null || orig === undefined ? 0 : v))
    .min(0, 'Cannot be negative')
    .max(100)
    .default(0),
  affiliate_buyer_discount_pct: yup
    .number()
    .transform((v, orig) => (orig === '' || orig === null || orig === undefined ? 0 : v))
    .min(0, 'Cannot be negative')
    .max(100)
    .default(0),
  price_tiers: yup.array().of(priceTierSchema).min(1, 'Add at least one price tier').required(),
  variants: yup.array().of(variantSchema).min(1, 'Add at least one variant').required(),
});

export type ProductFormValues = yup.InferType<typeof ProductSchema>;
