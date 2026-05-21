export interface FeaturedCategory {
  name: string;
  count: number;
  emoji: string;
  tint: string;
}

/**
 * Mock catalogue categories surfaced on the storefront. Will be swapped for
 * `useFetch('/api/v1/categories/featured')` once the catalogue API ships.
 */
export const useFeaturedCategories = (): FeaturedCategory[] => [
  { name: 'Groceries', count: 1240, emoji: '🛒', tint: 'from-emerald-100 to-emerald-50' },
  { name: 'Fashion', count: 980, emoji: '👜', tint: 'from-rose-100 to-rose-50' },
  { name: 'Electronics', count: 612, emoji: '🎧', tint: 'from-sky-100 to-sky-50' },
  { name: 'Home & Living', count: 845, emoji: '🛋️', tint: 'from-amber-100 to-amber-50' },
  { name: 'Beauty', count: 412, emoji: '💄', tint: 'from-fuchsia-100 to-fuchsia-50' },
  { name: 'Sports', count: 388, emoji: '⚽', tint: 'from-lime-100 to-lime-50' },
  { name: 'Books', count: 760, emoji: '📚', tint: 'from-orange-100 to-orange-50' },
  { name: 'Toys', count: 296, emoji: '🧸', tint: 'from-violet-100 to-violet-50' },
];
