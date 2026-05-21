/**
 * Page-level guard. Lets only admin-role users through; sends others to
 * their natural landing page (vendors → /vendor, customers → /).
 * Pair with `sanctum:auth` to first require any authenticated user.
 *
 *   definePageMeta({ middleware: ['sanctum:auth', 'admin'] })
 */
export default defineNuxtRouteMiddleware(() => {
  const auth = useAuthStore();
  if (!auth.isAdmin) {
    return navigateTo(auth.landingPath);
  }
});
