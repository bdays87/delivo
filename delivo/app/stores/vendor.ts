import { defineStore } from 'pinia';

export interface VendorKycDocument {
  id: number;
  type: string;
  original_filename: string;
  status: string;
  created_at: string;
}

export interface Vendor {
  id: number;
  owner_user_id: number;
  business_name: string;
  slug: string;
  support_email: string;
  support_phone: string;
  tin: string | null;
  registration_no: string | null;
  payout_method: 'MOBILE_WALLET' | 'BANK_TRANSFER' | null;
  mobile_wallet_id: number | null;
  mobile_wallet_msisdn: string | null;
  mobile_wallet?: { id: number; code: string; name: string } | null;
  bank_name: string | null;
  bank_account_name: string | null;
  bank_account_number: string | null;
  bank_currency: 'USD' | 'ZWG' | null;
  status: 'PENDING' | 'ACTIVE' | 'REJECTED' | 'SUSPENDED';
  rejection_reason: string | null;
  approved_at: string | null;
  rejected_at: string | null;
  suspended_at: string | null;
  kyc_documents?: VendorKycDocument[];
}

interface ApplyPayload {
  business_name: string;
  slug: string;
  support_email: string;
  support_phone: string;
  tin?: string | null;
  registration_no?: string | null;
  payout_method?: 'MOBILE_WALLET' | 'BANK_TRANSFER' | null;
  mobile_wallet_id?: number | null;
  mobile_wallet_msisdn?: string | null;
  bank_name?: string | null;
  bank_account_name?: string | null;
  bank_account_number?: string | null;
  bank_currency?: 'USD' | 'ZWG' | null;
}

export const useVendorStore = defineStore('vendor', () => {
  const vendor = ref<Vendor | null>(null);
  const loading = ref(false);
  const submitting = ref(false);

  const { apply: applyApi, getCurrentVendor, uploadKyc: uploadKycApi } = useVendorHelper();
  const toast = useToast();

  const fetchCurrent = async () => {
    loading.value = true;
    const { data, error } = await getCurrentVendor();
    if (!error.value) {
      vendor.value = (data.value as any)?.data ?? null;
    } else {
      vendor.value = null;
    }
    loading.value = false;
  };

  const apply = async (payload: ApplyPayload): Promise<Vendor | null> => {
    submitting.value = true;
    try {
      const { data: response, status, error } = await applyApi(payload as any);
      const res = (response?.value as any) ?? null;
      if (status?.value && res?.status === true) {
        vendor.value = res.data as Vendor;
        toast.success({
          title: 'Application submitted',
          message: 'Now upload your national ID to finish.',
          position: 'topRight',
          layout: 2,
        });
        // Backend just granted the `vendor` role — re-fetch identity so
        // role-gated UI (sidebar, /vendor middleware) sees it immediately.
        await useAuthStore().refresh();
        return vendor.value;
      }
      const msg = (error?.value as any)?.data?.message
        || (error?.value as any)?.message
        || res?.message
        || 'Failed to submit application.';
      toast.error({ title: 'Error', message: msg, position: 'topRight', layout: 2 });
      return null;
    } finally {
      submitting.value = false;
    }
  };

  const uploadKyc = async (type: string, file: File): Promise<boolean> => {
    submitting.value = true;
    try {
      const { data: response, status, error } = await uploadKycApi(type, file);
      const res = (response?.value as any) ?? null;
      if (status?.value && res?.status === true) {
        toast.success({
          title: 'Document uploaded',
          message: 'An admin will review your application shortly.',
          position: 'topRight',
          layout: 2,
        });
        await fetchCurrent();
        return true;
      }
      const msg = (error?.value as any)?.data?.message
        || (error?.value as any)?.message
        || res?.message
        || 'Failed to upload document.';
      toast.error({ title: 'Error', message: msg, position: 'topRight', layout: 2 });
      return false;
    } finally {
      submitting.value = false;
    }
  };

  return { vendor, loading, submitting, fetchCurrent, apply, uploadKyc };
});
