import { defineStore } from 'pinia';

export type ProviderStatus = 'PENDING' | 'ACTIVE' | 'REJECTED' | 'SUSPENDED';

export interface ProviderKycDocument {
  id: number;
  type: string;
  original_filename: string;
  status: 'PENDING' | 'APPROVED' | 'REJECTED';
  created_at?: string | null;
}

export interface ProviderCoverageArea {
  id: number;
  city: string;
}

export interface DeliveryProvider {
  id: number;
  owner_user_id: number;
  business_name: string;
  slug: string;
  support_email: string;
  support_phone: string;
  base_city: string;
  vehicle_types: string | null;
  status: ProviderStatus;
  rejection_reason: string | null;
  approved_at: string | null;
  kyc_documents?: ProviderKycDocument[];
  coverage_areas?: ProviderCoverageArea[];
}

export const useProviderStore = defineStore('provider', () => {
  const provider = ref<DeliveryProvider | null>(null);
  const loading = ref(false);
  const submitting = ref(false);

  const { apply, getCurrent, uploadKyc, syncCoverage } = useProviderHelper();
  const toast = useToast();

  const fetchCurrent = async () => {
    loading.value = true;
    const { data, error } = await getCurrent();
    if (!error.value) {
      provider.value = ((data.value as any)?.data ?? null) as DeliveryProvider | null;
    } else {
      provider.value = null;
    }
    loading.value = false;
  };

  const applyAsProvider = async (payload: Record<string, unknown>): Promise<DeliveryProvider | null> => {
    submitting.value = true;
    try {
      const { data, status, error } = await apply(payload);
      if (status?.value) {
        provider.value = ((data.value as any)?.data ?? null) as DeliveryProvider | null;
        toast.success({ title: 'Application submitted', message: 'Awaiting admin review.', position: 'topRight', layout: 2 });
        return provider.value;
      }
      const msg = (error?.value as any)?.data?.message
        || Object.values((error?.value as any)?.data?.errors ?? {}).flat()[0]
        || 'Failed to submit application.';
      toast.error({ title: 'Error', message: String(msg), position: 'topRight', layout: 2 });
      return null;
    } finally {
      submitting.value = false;
    }
  };

  const uploadKycDocument = async (type: string, file: File): Promise<boolean> => {
    submitting.value = true;
    try {
      const { status, error } = await uploadKyc(type, file);
      if (status?.value) {
        toast.success({ title: 'Document uploaded', message: '', position: 'topRight', layout: 2 });
        await fetchCurrent();
        return true;
      }
      toast.error({
        title: 'Error',
        message: (error?.value as any)?.data?.message || 'Upload failed.',
        position: 'topRight',
        layout: 2,
      });
      return false;
    } finally {
      submitting.value = false;
    }
  };

  const saveCoverage = async (zoneIds: number[]): Promise<boolean> => {
    submitting.value = true;
    try {
      const { status, error } = await syncCoverage(zoneIds);
      if (status?.value) {
        toast.success({ title: 'Coverage saved', message: `${zoneIds.length} cities`, position: 'topRight', layout: 2 });
        await fetchCurrent();
        return true;
      }
      toast.error({
        title: 'Error',
        message: (error?.value as any)?.data?.message || 'Could not save coverage.',
        position: 'topRight',
        layout: 2,
      });
      return false;
    } finally {
      submitting.value = false;
    }
  };

  return { provider, loading, submitting, fetchCurrent, applyAsProvider, uploadKycDocument, saveCoverage };
});
