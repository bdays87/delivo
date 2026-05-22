import { defineStore } from 'pinia';

export interface Address {
  id: number;
  label: string | null;
  recipient_name: string;
  recipient_phone: string;
  city: string;
  suburb: string;
  street: string;
  notes: string | null;
  is_default: boolean;
}

export interface AddressPayload {
  label?: string | null;
  recipient_name: string;
  recipient_phone: string;
  city: string;
  suburb: string;
  street: string;
  notes?: string | null;
  is_default?: boolean;
}

export const useAddressStore = defineStore('address', () => {
  const addresses = ref<Address[]>([]);
  const loading = ref(false);
  const submitting = ref(false);

  const { listAddresses, createAddress, updateAddress, deleteAddress, setDefaultAddress } = useAddressHelper();
  const toast = useToast();

  const defaultAddress = computed(() => addresses.value.find((a) => a.is_default) ?? addresses.value[0] ?? null);

  const fetchAll = async () => {
    loading.value = true;
    const { data, error } = await listAddresses();
    if (!error.value) {
      addresses.value = (data.value as any)?.data ?? [];
    }
    loading.value = false;
  };

  const create = async (payload: AddressPayload): Promise<Address | null> => {
    submitting.value = true;
    try {
      const { data, status, error } = await createAddress(payload as Record<string, unknown>);
      if (status?.value) {
        const created = ((data.value as any)?.data ?? null) as Address | null;
        await fetchAll();
        toast.success({ title: 'Address saved', message: '', position: 'topRight', layout: 2 });
        return created;
      }
      toast.error({
        title: 'Error',
        message: (error?.value as any)?.data?.message || 'Failed to save address.',
        position: 'topRight',
        layout: 2,
      });
      return null;
    } finally {
      submitting.value = false;
    }
  };

  const update = async (id: number, payload: AddressPayload): Promise<boolean> => {
    submitting.value = true;
    try {
      const { status, error } = await updateAddress(id, payload as Record<string, unknown>);
      if (status?.value) {
        await fetchAll();
        toast.success({ title: 'Address updated', message: '', position: 'topRight', layout: 2 });
        return true;
      }
      toast.error({
        title: 'Error',
        message: (error?.value as any)?.data?.message || 'Failed to update address.',
        position: 'topRight',
        layout: 2,
      });
      return false;
    } finally {
      submitting.value = false;
    }
  };

  const remove = async (id: number): Promise<boolean> => {
    submitting.value = true;
    try {
      const { status } = await deleteAddress(id);
      if (status?.value) {
        await fetchAll();
        return true;
      }
      return false;
    } finally {
      submitting.value = false;
    }
  };

  const setDefault = async (id: number): Promise<boolean> => {
    submitting.value = true;
    try {
      const { status } = await setDefaultAddress(id);
      if (status?.value) {
        await fetchAll();
        return true;
      }
      return false;
    } finally {
      submitting.value = false;
    }
  };

  return { addresses, loading, submitting, defaultAddress, fetchAll, create, update, remove, setDefault };
});
