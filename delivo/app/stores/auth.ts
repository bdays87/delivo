import { defineStore } from 'pinia';

export interface ModuleSubmodule {
  uuid: string;
  name: string;
  icon: string;
  url: string;
  default_permission: string;
}

export interface ModuleTreeNode {
  uuid: string;
  name: string;
  icon: string;
  default_permission: string;
  submodules: ModuleSubmodule[];
}

export interface AuthUser {
  id: number;
  name: string;
  email: string;
  phone: string | null;
  roles: string[];
  permissions: string[];
  modules: ModuleTreeNode[];
}

interface RegisterPayload {
  name: string;
  email: string;
  phone: string;
  password: string;
  password_confirmation: string;
}

interface LoginPayload {
  email: string;
  password: string;
}

/**
 * Derive the landing route for a user from the roles attached to them.
 * Admins win over vendors win over customers — important when a user
 * holds multiple roles (e.g. a customer who is also a vendor).
 */
const landingForRoles = (roles: readonly string[] | undefined): string => {
  if (!roles?.length) return '/';
  if (roles.includes('admin')) return '/admin';
  if (roles.includes('vendor')) return '/vendor';
  return '/';
};

/**
 * Sits on top of nuxt-auth-sanctum's `useSanctumAuth` so we can:
 *  - expose login/logout from a single Pinia surface
 *  - add a `register()` action (the module doesn't ship one)
 *  - layer role helpers (`isCustomer`, `isVendor`, `isAdmin`)
 *  - own the post-login redirect so we can route by role
 */
export const useAuthStore = defineStore('auth', () => {
  const sanctum = useSanctumAuth<AuthUser>();
  const client = useSanctumClient();

  const user = computed<AuthUser | null>(() => sanctum.user.value);
  const isAuthenticated = computed(() => sanctum.isAuthenticated.value);

  const hasRole = (role: string) => computed(() => user.value?.roles?.includes(role) ?? false);
  const isCustomer = hasRole('customer');
  const isVendor = hasRole('vendor');
  const isAdmin = hasRole('admin');

  const landingPath = computed(() => landingForRoles(user.value?.roles));

  const login = async (credentials: LoginPayload) => {
    await sanctum.login(credentials);
    await navigateTo(landingForRoles(user.value?.roles));
  };

  const logout = async () => {
    await sanctum.logout();
  };

  const register = async (payload: RegisterPayload) => {
    const response = await client<{ token?: string; data?: { user?: AuthUser } }>(
      '/api/v1/auth/register',
      { method: 'POST', body: payload },
    );

    if (!response.token) {
      throw new Error('Registration succeeded but no token returned.');
    }

    const tokenCookie = useCookie<string | null>('sanctum.token.cookie', {
      secure: typeof window !== 'undefined' && window.location.protocol === 'https:',
    });
    tokenCookie.value = response.token;

    await sanctum.refreshIdentity();
    await navigateTo(landingForRoles(user.value?.roles));
  };

  /**
   * Re-fetch the user identity from the backend. Use after server-side role
   * changes (e.g. vendor application) so client-side `roles`/`modules`
   * reflect reality without forcing the user to log out and back in.
   */
  const refresh = async () => {
    await sanctum.refreshIdentity();
  };

  return {
    user,
    isAuthenticated,
    isCustomer,
    isVendor,
    isAdmin,
    landingPath,
    login,
    logout,
    register,
    refresh,
  };
});
