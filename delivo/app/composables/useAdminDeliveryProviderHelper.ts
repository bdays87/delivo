export const useAdminDeliveryProviderHelper = () => {
  const client = useSanctumClient();

  const listProviders = async (status?: string) => {
    try {
      const url = status
        ? `/api/v1/admin/delivery-providers?status=${status}`
        : '/api/v1/admin/delivery-providers';
      const data = await client(url, { method: 'GET' });
      return { data: ref(data), error: ref(null) };
    } catch (err) {
      return { data: ref(null), error: ref(err) };
    }
  };

  const getProvider = async (id: number) => {
    try {
      const data = await client(`/api/v1/admin/delivery-providers/${id}`, { method: 'GET' });
      return { data: ref(data), error: ref(null) };
    } catch (err) {
      return { data: ref(null), error: ref(err) };
    }
  };

  const approve = async (id: number) => {
    try {
      const data = await client(`/api/v1/admin/delivery-providers/${id}/approve`, { method: 'POST' });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  const reject = async (id: number, reason: string) => {
    try {
      const data = await client(`/api/v1/admin/delivery-providers/${id}/reject`, {
        method: 'POST',
        body: { reason },
      });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  const suspend = async (id: number, reason?: string) => {
    try {
      const data = await client(`/api/v1/admin/delivery-providers/${id}/suspend`, {
        method: 'POST',
        body: { reason: reason ?? null },
      });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  const kycDocumentBlobUrl = async (providerId: number, documentId: number): Promise<string | null> => {
    try {
      const blob = await client(
        `/api/v1/admin/delivery-providers/${providerId}/kyc-documents/${documentId}`,
        { method: 'GET', responseType: 'blob' },
      );
      return URL.createObjectURL(blob as Blob);
    } catch (err) {
      console.error(err);
      return null;
    }
  };

  return { listProviders, getProvider, approve, reject, suspend, kycDocumentBlobUrl };
};
