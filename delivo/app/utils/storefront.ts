/**
 * Returns a picsum.photos URL seeded by `key` so the same key always renders
 * the same placeholder image. Replace with real CDN URLs when the catalogue
 * API lands.
 */
export const mockImage = (key: string, w = 600, h = 600): string =>
  `https://picsum.photos/seed/delivo-${key}/${w}/${h}`;

/**
 * Builds a 5-element array where the first `n` are `true`. Use for star
 * ratings: each `true` renders a filled star, each `false` renders a faded
 * one.
 */
export const starFlags = (n: number): boolean[] =>
  Array.from({ length: 5 }, (_, i) => i < n);
