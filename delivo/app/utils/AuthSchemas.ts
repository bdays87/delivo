import * as yup from 'yup';

const zwPhoneRegex = /^(\+?263|0)?7\d{8}$/;

export const LoginSchema = yup.object({
  email: yup.string().required('Email is required').email('Enter a valid email'),
  password: yup.string().required('Password is required'),
});

export const RegisterSchema = yup.object({
  name: yup.string().required('Full name is required').max(100, 'Name must be at most 100 characters'),
  email: yup.string().required('Email is required').email('Enter a valid email').max(255),
  phone: yup
    .string()
    .required('Phone is required')
    .matches(zwPhoneRegex, 'Enter a Zimbabwean mobile number, e.g. 0772 000 000'),
  password: yup.string().required('Password is required').min(8, 'Password must be at least 8 characters'),
  password_confirmation: yup
    .string()
    .required('Confirm your password')
    .oneOf([yup.ref('password')], 'Passwords must match'),
});
