export interface FeaturedVendor {
  name: string;
  tagline: string;
}

/**
 * Mock "shop by store" tiles. Replace with `useFetch` against the vendors
 * endpoint once vendor onboarding ships.
 */
export const useFeaturedVendors = (): FeaturedVendor[] => [
  { name: 'Fresh Goods', tagline: 'Fresh groceries' },
  { name: 'Daily Essentials', tagline: 'Everyday picks' },
  { name: 'Home Bazaar', tagline: 'Home organisation' },
  { name: 'Active Gear', tagline: 'Outdoor gear' },
  { name: 'Sip & Spice', tagline: 'Drinks & spirits' },
  { name: 'Local Market', tagline: 'Neighbourhood produce' },
  { name: 'Smart Bargains', tagline: 'Deals every day' },
  { name: 'Work & Study', tagline: 'Office and school' },
];
