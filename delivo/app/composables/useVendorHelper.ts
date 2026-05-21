export const useVendorHelper = () => {
  const client = useSanctumClient();

  const apply = async (payload: Record<string, unknown>) => {
    try {
      const data = await client('/api/v1/vendor/apply', { method: 'POST', body: payload });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  const getCurrentVendor = async () => {
    try {
      const data = await client('/api/v1/vendor/me', { method: 'GET' });
      return { data: ref(data), error: ref(null) };
    } catch (err) {
      return { data: ref(null), error: ref(err) };
    }
  };

  const uploadKyc = async (type: string, file: File) => {
    const form = new FormData();
    form.append('type', type);
    form.append('document', file);
    try {
      const data = await client('/api/v1/vendor/me/kyc-documents', {
        method: 'POST',
        body: form,
      });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  return { apply, getCurrentVendor, uploadKyc };
};
