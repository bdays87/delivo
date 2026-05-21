export interface FeaturedStore {
  name: string;
  cats: string[];
  delivery: string;
  loc: string;
}

/**
 * Mock "best-selling stores near you" cards. Replace with `useFetch` against
 * a geo-scoped vendors endpoint once vendor onboarding ships.
 */
export const useFeaturedStores = (): FeaturedStore[] => [
  { name: 'Fresh Goods', cats: ['Fresh', 'Bakery'], delivery: 'Same day', loc: 'Harare' },
  { name: 'Daily Pharmacy', cats: ['Pharmacy', 'Essentials'], delivery: 'Same day', loc: 'Bulawayo' },
  { name: 'Sip & Spice', cats: ['Wine', 'Spirits'], delivery: 'Next day', loc: 'Mutare' },
  { name: 'Local Market', cats: ['Grocery', 'Produce'], delivery: 'Next day', loc: 'Gweru' },
];
