/**
 * Page-level guard. Lets only users holding the `vendor` role through;
 * sends everyone else to their natural landing page. Pair with
 * `sanctum:auth` to first require any authenticated user.
 *
 *   definePageMeta({ middleware: ['sanctum:auth', 'vendor'] })
 *
 * Note: `/vendor/apply` is intentionally NOT guarded by this middleware —
 * the whole point of that page is to let a customer apply to *become* a
 * vendor, so any authenticated user must be able to open it.
 */
export default defineNuxtRouteMiddleware(() => {
  const auth = useAuthStore();
  if (!auth.isVendor) {
    return navigateTo(auth.landingPath);
  }
});
