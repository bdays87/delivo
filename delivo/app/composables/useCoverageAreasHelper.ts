export const useCoverageAreasHelper = () => {
  const client = useSanctumClient();

  const listCoverageAreas = async () => {
    try {
      const data = await client('/api/v1/coverage-areas/list', { method: 'GET' });
      return { data: ref(data), error: ref(null) };
    } catch (err) {
      return { data: ref(null), error: ref(err) };
    }
  };

  return { listCoverageAreas };
};
