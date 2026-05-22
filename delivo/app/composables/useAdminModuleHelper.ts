export const useAdminModuleHelper = () => {
  const client = useSanctumClient();

  const listModules = async () => {
    try {
      const data = await client('/api/v1/admin/modules', { method: 'GET' });
      return { data: ref(data), error: ref(null) };
    } catch (err) {
      return { data: ref(null), error: ref(err) };
    }
  };

  const getModule = async (id: number) => {
    try {
      const data = await client(`/api/v1/admin/modules/${id}`, { method: 'GET' });
      return { data: ref(data), error: ref(null) };
    } catch (err) {
      return { data: ref(null), error: ref(err) };
    }
  };

  const getSubmodulePermissions = async (moduleId: number, submoduleId: number) => {
    try {
      const data = await client(
        `/api/v1/admin/modules/${moduleId}/submodules/${submoduleId}/permissions`,
        { method: 'GET' },
      );
      return { data: ref(data), error: ref(null) };
    } catch (err) {
      return { data: ref(null), error: ref(err) };
    }
  };

  const getModuleTree = async () => {
    try {
      const data = await client('/api/v1/admin/modules/tree', { method: 'GET' });
      return { data: ref(data), error: ref(null) };
    } catch (err) {
      return { data: ref(null), error: ref(err) };
    }
  };

  return { listModules, getModule, getSubmodulePermissions, getModuleTree };
};
