import * as yup from 'yup';

const zwPhoneRegex = /^(\+?263|0)?7\d{8}$/;
const slugRegex = /^[a-z0-9-]+$/;

export const DeliveryProviderApplySchema = yup.object({
  business_name: yup.string().required('Business name is required').max(150),
  slug: yup.string().required('Storefront URL is required').max(160).matches(slugRegex, 'Use lowercase letters, numbers and hyphens only'),
  support_email: yup.string().required('Support email is required').email('Enter a valid email'),
  support_phone: yup.string().required('Support phone is required').matches(zwPhoneRegex, 'Enter a Zimbabwean mobile number, e.g. 0772 000 000'),
  base_city: yup.string().required('Pick your base city').max(120),
  vehicle_types: yup.string().nullable().max(255),
});
