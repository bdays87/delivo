import { defineStore } from 'pinia';

export type InfluencerStatus = 'PENDING' | 'ACTIVE' | 'REJECTED' | 'SUSPENDED';
export type SocialPlatform = 'INSTAGRAM' | 'TIKTOK' | 'X' | 'YOUTUBE' | 'FACEBOOK' | 'OTHER';
export type HandleStatus = 'PENDING' | 'APPROVED' | 'REJECTED';

export interface InfluencerSocialHandle {
  id: number;
  platform: SocialPlatform;
  handle: string;
  url: string | null;
  followers: number | null;
  status: HandleStatus;
}

export interface Influencer {
  id: number;
  owner_user_id: number;
  display_name: string;
  slug: string;
  contact_email: string;
  contact_phone: string;
  bio: string | null;
  niche: string | null;
  status: InfluencerStatus;
  rejection_reason: string | null;
  approved_at: string | null;
  social_handles?: InfluencerSocialHandle[];
}

export const useInfluencerStore = defineStore('influencer', () => {
  const influencer = ref<Influencer | null>(null);
  const loading = ref(false);
  const submitting = ref(false);

  const { apply, getCurrent, addHandle, deleteHandle } = useInfluencerHelper();
  const toast = useToast();

  const fetchCurrent = async () => {
    loading.value = true;
    const { data, error } = await getCurrent();
    if (!error.value) {
      influencer.value = ((data.value as any)?.data ?? null) as Influencer | null;
    } else {
      influencer.value = null;
    }
    loading.value = false;
  };

  const applyAsInfluencer = async (payload: Record<string, unknown>): Promise<Influencer | null> => {
    submitting.value = true;
    try {
      const { data, status, error } = await apply(payload);
      if (status?.value) {
        influencer.value = ((data.value as any)?.data ?? null) as Influencer | null;
        toast.success({ title: 'Application submitted', message: 'Awaiting admin review.', position: 'topRight', layout: 2 });
        return influencer.value;
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

  const addSocialHandle = async (payload: Record<string, unknown>): Promise<boolean> => {
    submitting.value = true;
    try {
      const { status, error } = await addHandle(payload);
      if (status?.value) {
        await fetchCurrent();
        toast.success({ title: 'Handle added', message: '', position: 'topRight', layout: 2 });
        return true;
      }
      toast.error({
        title: 'Error',
        message: (error?.value as any)?.data?.message || 'Could not add handle.',
        position: 'topRight',
        layout: 2,
      });
      return false;
    } finally {
      submitting.value = false;
    }
  };

  const removeSocialHandle = async (id: number): Promise<boolean> => {
    submitting.value = true;
    try {
      const { status } = await deleteHandle(id);
      if (status?.value) {
        await fetchCurrent();
        return true;
      }
      return false;
    } finally {
      submitting.value = false;
    }
  };

  return { influencer, loading, submitting, fetchCurrent, applyAsInfluencer, addSocialHandle, removeSocialHandle };
});
