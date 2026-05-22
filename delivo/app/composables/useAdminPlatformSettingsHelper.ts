export const useAdminPlatformSettingsHelper = () => {
  const client = useSanctumClient();

  const getSettings = async () => {
    try {
      const data = await client('/api/v1/admin/platform-settings', { method: 'GET' });
      return { data: ref(data), error: ref(null) };
    } catch (err) {
      return { data: ref(null), error: ref(err) };
    }
  };

  const updateSettings = async (payload: Record<string, unknown>) => {
    try {
      const data = await client('/api/v1/admin/platform-settings', { method: 'PUT', body: payload });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  return { getSettings, updateSettings };
};
