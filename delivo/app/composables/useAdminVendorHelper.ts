export const useAdminVendorHelper = () => {
  const client = useSanctumClient();

  const listVendors = async (status?: string) => {
    try {
      const url = status ? `/api/v1/admin/vendors?status=${status}` : '/api/v1/admin/vendors';
      const data = await client(url, { method: 'GET' });
      return { data: ref(data), error: ref(null) };
    } catch (err) {
      return { data: ref(null), error: ref(err) };
    }
  };

  const getVendor = async (id: number) => {
    try {
      const data = await client(`/api/v1/admin/vendors/${id}`, { method: 'GET' });
      return { data: ref(data), error: ref(null) };
    } catch (err) {
      return { data: ref(null), error: ref(err) };
    }
  };

  const approveVendor = async (id: number) => {
    try {
      const data = await client(`/api/v1/admin/vendors/${id}/approve`, { method: 'POST' });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  const rejectVendor = async (id: number, reason: string) => {
    try {
      const data = await client(`/api/v1/admin/vendors/${id}/reject`, {
        method: 'POST',
        body: { reason },
      });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  const suspendVendor = async (id: number, reason?: string) => {
    try {
      const data = await client(`/api/v1/admin/vendors/${id}/suspend`, {
        method: 'POST',
        body: { reason: reason ?? null },
      });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  const kycDocumentBlobUrl = async (vendorId: number, documentId: number): Promise<string | null> => {
    try {
      const blob = await client(`/api/v1/admin/vendors/${vendorId}/kyc-documents/${documentId}`, {
        method: 'GET',
        responseType: 'blob',
      });
      return URL.createObjectURL(blob as Blob);
    } catch (err) {
      console.error(err);
      return null;
    }
  };

  return {
    listVendors,
    getVendor,
    approveVendor,
    rejectVendor,
    suspendVendor,
    kycDocumentBlobUrl,
  };
};
