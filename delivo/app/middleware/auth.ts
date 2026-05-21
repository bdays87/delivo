/**
 * Replaces nuxt-auth-sanctum's `sanctum:auth` middleware. The shipped one
 * bails immediately when `user.value === null`, which races the plugin's
 * `page:loading:start` identity load — meaning the first hit to a guarded
 * page after a hard refresh (or even some client-side navigations)
 * bounces to /auth/login even though a valid token cookie is sitting
 * right there.
 *
 * Behaviour:
 *   1. If we already have a user in state, pass.
 *   2. Otherwise, if there's a token cookie, try refreshing identity.
 *   3. Re-check. Still no user → redirect to /auth/login, preserving the
 *      requested path in `?redirect=` so we can bounce back after login.
 *
 * Pair with `admin` / `vendor` middlewares for role-specific gates:
 *   definePageMeta({ middleware: ['auth', 'admin'] })
 */
export default defineNuxtRouteMiddleware(async (to) => {
  const auth = useAuthStore();

  if (auth.isAuthenticated) return;

  const tokenCookie = useCookie<string | null>('sanctum.token.cookie', { readonly: true });
  if (tokenCookie.value) {
    try {
      await auth.refresh();
    } catch {
      // token is invalid / server unreachable — fall through to redirect
    }
  }

  if (auth.isAuthenticated) return;

  return navigateTo({
    path: '/auth/login',
    query: { redirect: to.fullPath },
  });
});
