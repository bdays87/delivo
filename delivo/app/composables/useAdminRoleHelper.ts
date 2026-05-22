export const useAdminRoleHelper = () => {
  const client = useSanctumClient();

  const listRoles = async () => {
    try {
      const data = await client('/api/v1/admin/roles', { method: 'GET' });
      return { data: ref(data), error: ref(null) };
    } catch (err) {
      return { data: ref(null), error: ref(err) };
    }
  };

  const getRole = async (id: number) => {
    try {
      const data = await client(`/api/v1/admin/roles/${id}`, { method: 'GET' });
      return { data: ref(data), error: ref(null) };
    } catch (err) {
      return { data: ref(null), error: ref(err) };
    }
  };

  const createRole = async (name: string) => {
    try {
      const data = await client('/api/v1/admin/roles', { method: 'POST', body: { name } });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  const updateRole = async (id: number, name: string) => {
    try {
      const data = await client(`/api/v1/admin/roles/${id}`, { method: 'PUT', body: { name } });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  const deleteRole = async (id: number) => {
    try {
      const data = await client(`/api/v1/admin/roles/${id}`, { method: 'DELETE' });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  const syncPermissions = async (id: number, permissionIds: number[]) => {
    try {
      const data = await client(`/api/v1/admin/roles/${id}/permissions/sync`, {
        method: 'POST',
        body: { permission_ids: permissionIds },
      });
      return { data: ref(data), status: ref(true), error: ref(null) };
    } catch (err) {
      return { data: ref(null), status: ref(false), error: ref(err) };
    }
  };

  return { listRoles, getRole, createRole, updateRole, deleteRole, syncPermissions };
};
