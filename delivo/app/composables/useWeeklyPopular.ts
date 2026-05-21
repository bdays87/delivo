export interface WeeklyPopularProduct {
  name: string;
  price: number;
  seed: string;
  rating: number;
  badge?: string;
}

/**
 * Mock "weekly popular products". Replace with `useFetch` against the
 * trending products endpoint once it exists.
 */
export const useWeeklyPopular = (): WeeklyPopularProduct[] => [
  { name: 'Smart LED Lamp', price: 49, seed: 'lamp2', rating: 5, badge: 'New' },
  { name: 'Leather Tote Bag', price: 89, seed: 'tote', rating: 4 },
  { name: 'Ceramic Mug Set', price: 29, seed: 'mugs', rating: 5, badge: '-30%' },
  { name: 'Wireless Charger', price: 39, seed: 'charger', rating: 4 },
  { name: 'Cotton Throw', price: 59, seed: 'throw', rating: 5 },
  { name: 'Espresso Grinder', price: 219, seed: 'grinder', rating: 5, badge: 'Hot' },
  { name: 'Linen Apron', price: 32, seed: 'apron', rating: 4 },
  { name: 'Bamboo Cutting Board', price: 24, seed: 'board', rating: 4 },
];
