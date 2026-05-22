import { defineStore } from 'pinia';

export interface CoverageArea {
  id: number;
  city: string;
  fee_usd: string | number;
}

export const useCoverageStore = defineStore('coverage', () => {
  const areas = ref<CoverageArea[]>([]);
  const loading = ref(false);
  const loaded = ref(false);

  const { listCoverageAreas } = useCoverageAreasHelper();

  const ensureLoaded = async () => {
    if (loaded.value) return;
    loading.value = true;
    const { data, error } = await listCoverageAreas();
    if (!error.value) {
      areas.value = (data.value as any)?.data ?? [];
    }
    loaded.value = true;
    loading.value = false;
  };

  const refresh = async () => {
    loaded.value = false;
    await ensureLoaded();
  };

  const cities = computed(() => areas.value.map((a) => a.city));
  const isCovered = (city: string | null | undefined): boolean => {
    if (!city) return false;
    const target = city.trim().toLowerCase();
    return areas.value.some((a) => a.city.trim().toLowerCase() === target);
  };

  return { areas, cities, loading, loaded, ensureLoaded, refresh, isCovered };
});
