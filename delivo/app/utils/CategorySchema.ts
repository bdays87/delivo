import * as yup from 'yup';

export const CategorySchema = yup.object({
  parent_id: yup
    .number()
    .nullable()
    .transform((v, orig) => (orig === '' || orig === null || orig === undefined ? null : v)),
  name: yup.string().required('Name is required').max(120),
  slug: yup
    .string()
    .required('Slug is required')
    .max(120)
    .matches(/^[a-z0-9]+(?:-[a-z0-9]+)*$/, 'Use lowercase letters, numbers and hyphens only'),
  icon: yup.string().max(80).default('lucide:tag'),
  emoji: yup.string().nullable().max(16),
  tint: yup.string().nullable().max(120),
  description: yup.string().nullable().max(2000),
  sort_order: yup
    .number()
    .typeError('Sort order must be a number')
    .min(0)
    .max(1000)
    .default(0),
  status: yup.string().oneOf(['ACTIVE', 'ARCHIVED']).default('ACTIVE'),
});

export const slugifyCategoryName = (name: string): string =>
  name
    .trim()
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '');
