import tailwindcss from '@tailwindcss/vite';

export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },

  modules: ['@pinia/nuxt', 'nuxt-auth-sanctum', '@nuxt/icon'],

  css: ['~/assets/css/main.css'],

  vite: {
    plugins: [tailwindcss() as any],
  },

  runtimeConfig: {
    public: {
      apiBase: process.env.NUXT_PUBLIC_API_BASE || 'http://localhost:8000',
    },
  },

  sanctum: {
    baseUrl: process.env.NUXT_PUBLIC_API_BASE || 'http://localhost:8000',
    mode: 'token',
    endpoints: {
      login: '/api/v1/auth/login',
      logout: '/api/v1/auth/logout',
      user: '/api/v1/me',
    },
    redirect: {
      // The auth store handles the post-login redirect itself so we can route
      // admins to /admin, vendors to /vendor, and customers to /. Leaving the
      // module's redirect off avoids a flash through "/".
      onLogin: false,
      onLogout: '/',
      onAuthOnly: '/auth/login',
    },
    globalMiddleware: {
      enabled: false,
    },
  },
});
