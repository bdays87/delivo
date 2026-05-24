import * as yup from 'yup';

const zwPhoneRegex = /^(\+?263|0)?7\d{8}$/;
const slugRegex = /^[a-z0-9-]+$/;

export const InfluencerApplySchema = yup.object({
  display_name: yup.string().required('Display name is required').max(150),
  slug: yup.string().required('URL slug is required').max(160).matches(slugRegex, 'Use lowercase letters, numbers and hyphens only'),
  contact_email: yup.string().required('Contact email is required').email('Enter a valid email'),
  contact_phone: yup.string().required('Contact phone is required').matches(zwPhoneRegex, 'Enter a Zimbabwean mobile number, e.g. 0772 000 000'),
  bio: yup.string().nullable().max(1000),
  niche: yup.string().nullable().max(120),
});
