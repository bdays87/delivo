export const useProviderHelper = () => {
  const client = useSanctumClient();

  const apply = async (payload: Record<string, unknown>) => {
    try {
      const data = await client('/api/v1/provider/apply', { method: 'POST', body: payload });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  const getCurrent = async () => {
    try {
      const data = await client('/api/v1/provider/me', { method: 'GET' });
      return { data: ref(data), error: ref(null) };
    } catch (err) {
      return { data: ref(null), error: ref(err) };
    }
  };

  const uploadKyc = async (type: string, file: File) => {
    try {
      const body = new FormData();
      body.append('type', type);
      body.append('document', file);
      const data = await client('/api/v1/provider/me/kyc-documents', { method: 'POST', body });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  const syncCoverage = async (zoneIds: number[]) => {
    try {
      const data = await client('/api/v1/provider/me/coverage', {
        method: 'POST',
        body: { delivery_zone_ids: zoneIds },
      });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  return { apply, getCurrent, uploadKyc, syncCoverage };
};
