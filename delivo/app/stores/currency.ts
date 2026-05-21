import { defineStore } from 'pinia';

export type Currency = 'USD' | 'ZWG';

interface RatePayload {
  from_currency: string;
  to_currency: string;
  rate: string | number | null;
}

const STORAGE_KEY = 'delivo.currency';

export const useCurrencyStore = defineStore('currency', () => {
  const code = ref<Currency>('USD');
  // USD per 1 USD = 1. ZWG amount = USD * usdToZwgRate.
  const usdToZwgRate = ref<number | null>(null);

  // Hydrate from localStorage on first read (client only).
  if (import.meta.client) {
    const saved = localStorage.getItem(STORAGE_KEY);
    if (saved === 'ZWG' || saved === 'USD') code.value = saved;
  }

  const setCode = (next: Currency) => {
    code.value = next;
    if (import.meta.client) localStorage.setItem(STORAGE_KEY, next);
  };

  /**
   * Storefront APIs (`/api/v1/products`, `/api/v1/products/{slug}`) embed the
   * current USD→ZWG rate in their response. Components call this to keep the
   * store in sync without a second round trip.
   */
  const captureRateFromApi = (rate: RatePayload | null | undefined) => {
    if (!rate) return;
    const value = rate.rate !== null && rate.rate !== undefined ? Number(rate.rate) : null;
    if (value !== null && !Number.isNaN(value) && value > 0) {
      usdToZwgRate.value = value;
    }
  };

  const symbol = computed(() => (code.value === 'USD' ? '$' : 'ZWG '));

  const hasZwgRate = computed(() => usdToZwgRate.value !== null && usdToZwgRate.value > 0);

  /**
   * Format a USD amount in the currently-selected currency. Falls back to USD
   * formatting when a ZWG rate hasn't been published yet.
   */
  const format = (usdAmount: number | string | null | undefined, opts?: { withCode?: boolean }): string => {
    if (usdAmount === null || usdAmount === undefined) return '—';
    const amount = Number(usdAmount);
    if (Number.isNaN(amount)) return '—';

    if (code.value === 'ZWG' && hasZwgRate.value) {
      const zwg = amount * (usdToZwgRate.value as number);
      const formatted = zwg.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      return opts?.withCode === false ? formatted : `ZWG ${formatted}`;
    }

    const formatted = amount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    return opts?.withCode === false ? formatted : `$${formatted}`;
  };

  return { code, usdToZwgRate, symbol, hasZwgRate, setCode, captureRateFromApi, format };
});
