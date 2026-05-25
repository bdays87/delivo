export const useInfluencerHelper = () => {
  const client = useSanctumClient();

  const apply = async (payload: Record<string, unknown>) => {
    try {
      const data = await client('/api/v1/influencer/apply', { method: 'POST', body: payload });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  const getCurrent = async () => {
    try {
      const data = await client('/api/v1/influencer/me', { method: 'GET' });
      return { data: ref(data), error: ref(null) };
    } catch (err) {
      return { data: ref(null), error: ref(err) };
    }
  };

  const addHandle = async (payload: Record<string, unknown>) => {
    try {
      const data = await client('/api/v1/influencer/me/handles', { method: 'POST', body: payload });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  const deleteHandle = async (id: number) => {
    try {
      const data = await client(`/api/v1/influencer/me/handles/${id}`, { method: 'DELETE' });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  const listProducts = async () => {
    try {
      const data = await client('/api/v1/influencer/me/products', { method: 'GET' });
      return { data: ref(data), error: ref(null) };
    } catch (err) {
      return { data: ref(null), error: ref(err) };
    }
  };

  const generateCode = async (productId: number) => {
    try {
      const data = await client(`/api/v1/influencer/me/products/${productId}/code`, { method: 'POST' });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  const listCodes = async () => {
    try {
      const data = await client('/api/v1/influencer/me/codes', { method: 'GET' });
      return { data: ref(data), error: ref(null) };
    } catch (err) {
      return { data: ref(null), error: ref(err) };
    }
  };

  const earningsSummary = async () => {
    try {
      const data = await client('/api/v1/influencer/me/earnings/summary', { method: 'GET' });
      return { data: ref(data), error: ref(null) };
    } catch (err) {
      return { data: ref(null), error: ref(err) };
    }
  };

  const listEarnings = async () => {
    try {
      const data = await client('/api/v1/influencer/me/earnings', { method: 'GET' });
      return { data: ref(data), error: ref(null) };
    } catch (err) {
      return { data: ref(null), error: ref(err) };
    }
  };

  const listPayoutRequests = async () => {
    try {
      const data = await client('/api/v1/influencer/me/payouts', { method: 'GET' });
      return { data: ref(data), error: ref(null) };
    } catch (err) {
      return { data: ref(null), error: ref(err) };
    }
  };

  const createPayoutRequest = async (payload: Record<string, unknown>) => {
    try {
      const data = await client('/api/v1/influencer/me/payouts', { method: 'POST', body: payload });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  const cancelPayoutRequest = async (id: number) => {
    try {
      const data = await client(`/api/v1/influencer/me/payouts/${id}`, { method: 'DELETE' });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  return {
    apply, getCurrent, addHandle, deleteHandle, listProducts, generateCode, listCodes,
    earningsSummary, listEarnings, listPayoutRequests, createPayoutRequest, cancelPayoutRequest,
  };
};

export const useAdminInfluencerHelper = () => {
  const client = useSanctumClient();

  const listInfluencers = async (status?: string) => {
    try {
      const url = status ? `/api/v1/admin/influencers?status=${status}` : '/api/v1/admin/influencers';
      const data = await client(url, { method: 'GET' });
      return { data: ref(data), error: ref(null) };
    } catch (err) {
      return { data: ref(null), error: ref(err) };
    }
  };

  const getInfluencer = async (id: number) => {
    try {
      const data = await client(`/api/v1/admin/influencers/${id}`, { method: 'GET' });
      return { data: ref(data), error: ref(null) };
    } catch (err) {
      return { data: ref(null), error: ref(err) };
    }
  };

  const approve = async (id: number) => {
    try {
      const data = await client(`/api/v1/admin/influencers/${id}/approve`, { method: 'POST' });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  const reject = async (id: number, reason: string) => {
    try {
      const data = await client(`/api/v1/admin/influencers/${id}/reject`, { method: 'POST', body: { reason } });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  const suspend = async (id: number, reason?: string) => {
    try {
      const data = await client(`/api/v1/admin/influencers/${id}/suspend`, { method: 'POST', body: { reason: reason ?? null } });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  const setHandleStatus = async (influencerId: number, handleId: number, status: 'PENDING' | 'APPROVED' | 'REJECTED') => {
    try {
      const data = await client(`/api/v1/admin/influencers/${influencerId}/handles/${handleId}/status`, {
        method: 'POST',
        body: { status },
      });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  return { listInfluencers, getInfluencer, approve, reject, suspend, setHandleStatus };
};
