export interface BestDeal {
  name: string;
  desc: string;
  price: number;
  old: number;
  seed: string;
  rating: number;
  reviews: number;
}

/**
 * Mock "today's best deals" feed. Replace with `useFetch` against the
 * promotions/deals endpoint once it exists.
 */
export const useBestDeals = (): BestDeal[] => [
  {
    name: 'HomePod mini',
    desc: 'Compact smart speaker with rich 360° sound',
    price: 99,
    old: 129,
    seed: 'homepod',
    rating: 5,
    reviews: 312,
  },
  {
    name: 'Instax Mini 12',
    desc: 'Instant film camera — pastel pink edition',
    price: 79.95,
    old: 99.95,
    seed: 'instax',
    rating: 5,
    reviews: 248,
  },
  {
    name: 'Base Camp Duffel M',
    desc: 'Tough, water-resistant 71L travel duffel',
    price: 159,
    old: 189,
    seed: 'duffel',
    rating: 4,
    reviews: 186,
  },
  {
    name: 'Kindle Paperwhite',
    desc: '300 ppi glare-free display, weeks of battery',
    price: 139,
    old: 169,
    seed: 'kindle',
    rating: 5,
    reviews: 421,
  },
  {
    name: 'Nike Air Max 270',
    desc: 'All-day cushioning, breathable mesh upper',
    price: 129,
    old: 160,
    seed: 'airmax',
    rating: 5,
    reviews: 530,
  },
  {
    name: 'Hydro Flask 32oz',
    desc: 'Keeps drinks cold 24h, hot 12h',
    price: 39.95,
    old: 49.95,
    seed: 'hydro',
    rating: 4,
    reviews: 290,
  },
];
