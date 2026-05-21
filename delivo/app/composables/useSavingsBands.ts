export interface SavingsBand {
  amount: string;
  tag: string;
  seed: string;
}

/**
 * Mock curated savings strips. Replace with `useFetch` against a featured
 * promotions endpoint once promos land.
 */
export const useSavingsBands = (): SavingsBand[] => [
  { amount: 'Save $120', tag: 'Furniture', seed: 'sofa' },
  { amount: 'Save $39', tag: 'Kitchenware', seed: 'kitchen' },
  { amount: 'Save $74', tag: 'Lighting', seed: 'lamp' },
  { amount: 'Save $59', tag: 'Decor', seed: 'decor' },
];
