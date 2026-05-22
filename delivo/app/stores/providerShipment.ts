import { defineStore } from 'pinia';

export type ShipmentStatus = 'AWAITING_PROVIDER' | 'ASSIGNED' | 'PICKED_UP' | 'OUT_FOR_DELIVERY' | 'DELIVERED' | 'CANCELLED';

export interface ProviderShipment {
  id: number;
  order_id: number;
  vendor_id: number;
  hub_id: number | null;
  delivery_provider_id: number | null;
  hub_name_snapshot: string | null;
  hub_address_snapshot: string | null;
  distance_km: string | number | null;
  fee_usd: string | number;
  shipment_status: ShipmentStatus;
  assigned_at: string | null;
  picked_up_at: string | null;
  out_for_delivery_at: string | null;
  delivered_at: string | null;
  order?: {
    id: number;
    order_number: string;
    status: string;
    ship_recipient_name: string;
    ship_recipient_phone: string;
    ship_city: string;
    ship_suburb: string;
    ship_street: string;
    ship_notes: string | null;
  } | null;
  vendor?: { id: number; business_name: string; city: string } | null;
  hub?: { id: number; city: string; hub_name: string | null; hub_address: string | null } | null;
}

export const useProviderShipmentStore = defineStore('providerShipment', () => {
  const shipments = ref<ProviderShipment[]>([]);
  const loading = ref(false);
  const submitting = ref(false);
  const filterStatus = ref<string | null>(null);

  const { listShipments, transition } = useProviderShipmentHelper();
  const toast = useToast();

  const counts = computed(() => {
    const acc = {
      ASSIGNED: 0,
      PICKED_UP: 0,
      OUT_FOR_DELIVERY: 0,
      DELIVERED: 0,
    } as Record<string, number>;
    for (const s of shipments.value) acc[s.shipment_status] = (acc[s.shipment_status] ?? 0) + 1;
    return acc;
  });

  const fetchAll = async (status?: string) => {
    loading.value = true;
    filterStatus.value = status ?? null;
    const { data, error } = await listShipments(status);
    if (!error.value) {
      shipments.value = (data.value as any)?.data ?? [];
    } else {
      const msg = (error.value as any)?.data?.message || 'Failed to load shipments.';
      toast.error({ title: 'Error', message: msg, position: 'topRight', layout: 2 });
    }
    loading.value = false;
  };

  const doTransition = async (id: number, action: 'pickup' | 'dispatch' | 'deliver'): Promise<boolean> => {
    submitting.value = true;
    try {
      const { status, error } = await transition(id, action);
      if (status?.value) {
        toast.success({ title: 'Updated', message: `Shipment ${action}.`, position: 'topRight', layout: 2 });
        await fetchAll(filterStatus.value ?? undefined);
        return true;
      }
      toast.error({
        title: 'Error',
        message: (error?.value as any)?.data?.message || 'Could not update shipment.',
        position: 'topRight',
        layout: 2,
      });
      return false;
    } finally {
      submitting.value = false;
    }
  };

  return { shipments, loading, submitting, filterStatus, counts, fetchAll, doTransition };
});
