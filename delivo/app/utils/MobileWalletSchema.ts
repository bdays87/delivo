import * as yup from 'yup';

export const MobileWalletSchema = yup.object({
  code: yup
    .string()
    .required('Code is required')
    .max(30)
    .matches(/^[A-Z0-9_]+$/, 'Use uppercase letters, numbers and underscores only'),
  name: yup.string().required('Name is required').max(100),
  sort_order: yup
    .number()
    .typeError('Sort order must be a number')
    .min(0)
    .max(1000)
    .default(0),
  status: yup
    .string()
    .oneOf(['ACTIVE', 'ARCHIVED'])
    .default('ACTIVE'),
});
