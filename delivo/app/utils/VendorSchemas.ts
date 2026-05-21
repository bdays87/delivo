import * as yup from 'yup';

const zwPhoneRegex = /^(\+?263|0)?7\d{8}$/;
const slugRegex = /^[a-z0-9-]+$/;

export const VendorApplySchema = yup.object({
  business_name: yup
    .string()
    .required('Business name is required')
    .max(150, 'Business name must be at most 150 characters'),
  slug: yup
    .string()
    .required('Storefront URL is required')
    .max(160)
    .matches(slugRegex, 'Use lowercase letters, numbers and hyphens only'),
  support_email: yup
    .string()
    .required('Support email is required')
    .email('Enter a valid email'),
  support_phone: yup
    .string()
    .required('Support phone is required')
    .matches(zwPhoneRegex, 'Enter a Zimbabwean mobile number, e.g. 0772 000 000'),
  tin: yup.string().max(64).nullable(),
  registration_no: yup.string().max(64).nullable(),

  payout_method: yup
    .string()
    .oneOf(['MOBILE_WALLET', 'BANK_TRANSFER'])
    .nullable(),

  // Mobile wallet branch
  mobile_wallet_id: yup
    .number()
    .nullable()
    .transform((v) => (Number.isFinite(v) ? v : null))
    .when('payout_method', {
      is: 'MOBILE_WALLET',
      then: (s) => s.required('Pick a mobile wallet').typeError('Pick a mobile wallet'),
      otherwise: (s) => s.optional(),
    }),
  mobile_wallet_msisdn: yup
    .string()
    .nullable()
    .when('payout_method', {
      is: 'MOBILE_WALLET',
      then: (s) => s.required('Wallet number is required').matches(zwPhoneRegex, 'Enter a Zimbabwean mobile number'),
      otherwise: (s) => s.optional(),
    }),

  // Bank transfer branch
  bank_name: yup.string().nullable().when('payout_method', {
    is: 'BANK_TRANSFER',
    then: (s) => s.required('Bank name is required').max(120),
    otherwise: (s) => s.optional(),
  }),
  bank_account_name: yup.string().nullable().when('payout_method', {
    is: 'BANK_TRANSFER',
    then: (s) => s.required('Account holder name is required').max(150),
    otherwise: (s) => s.optional(),
  }),
  bank_account_number: yup.string().nullable().when('payout_method', {
    is: 'BANK_TRANSFER',
    then: (s) => s.required('Account number is required').max(64),
    otherwise: (s) => s.optional(),
  }),
  bank_currency: yup.string().nullable().when('payout_method', {
    is: 'BANK_TRANSFER',
    then: (s) => s.required('Pick a currency').oneOf(['USD', 'ZWG'], 'Pick USD or ZWG'),
    otherwise: (s) => s.optional(),
  }),
});
