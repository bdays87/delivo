export interface TopProduct {
  name: string;
  price: number;
  oldPrice: number | null;
  seed: string;
}

/**
 * Mock "trending" product carousel data. Replace with `useFetch` against the
 * products feed endpoint once the catalogue API lands.
 */
export const useTopProducts = (): TopProduct[] => [
  { name: 'Apple AirPods Pro 2', price: 249, oldPrice: 279, seed: 'airpods' },
  { name: 'Sony WH-1000XM5', price: 349, oldPrice: 399, seed: 'sonyhead' },
  { name: 'Bose QuietComfort', price: 299, oldPrice: null, seed: 'bose' },
  { name: 'Beats Studio Buds+', price: 169.95, oldPrice: 199, seed: 'beats' },
  { name: 'Marshall Major V', price: 149, oldPrice: 179, seed: 'marshall' },
];
