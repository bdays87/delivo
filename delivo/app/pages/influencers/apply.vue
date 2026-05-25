<template>
  <div class="mx-auto max-w-4xl px-4 py-12">
    <div class="breadcrumbs text-sm opacity-70">
      <ul>
        <li><NuxtLink to="/">Home</NuxtLink></li>
        <li>Promote on Delivo</li>
      </ul>
    </div>

    <h1 class="mt-4 text-3xl font-extrabold tracking-tight md:text-4xl">Promote on Delivo</h1>
    <p class="mt-3 max-w-2xl text-base opacity-70">
      Earn commission by promoting vendor products to your audience. Tell us about your channel,
      add your social handles, and our team will review and activate your account.
    </p>

    <StorefrontCreateAccountGate
      v-if="!auth.isAuthenticated"
      role-label="influencer"
      icon="lucide:megaphone"
      icon-bg="bg-accent/25 text-accent-content"
      benefits-title="What you get as an influencer"
      :benefits="[
        'Browse vendor products paying affiliate commission',
        'Share your unique code across your channels',
        'Earn a cut on every sale made through your link',
      ]"
      :steps="[
        { title: 'Create a free customer account', detail: 'Used to sign in and track your earnings.' },
        { title: 'Submit your influencer application', detail: 'Channel name, niche, and contact details.' },
        { title: 'Add your social handles', detail: 'Admins review handles before activating your account.' },
      ]"
    />

    <!-- After application: dashboard sections -->
    <div v-else-if="store.influencer" class="mt-8 space-y-6">
      <div class="rounded-3xl border border-base-300 bg-base-100 p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
          <div class="flex items-center gap-3">
            <span class="grid h-10 w-10 place-items-center rounded-2xl bg-primary/10 text-primary">
              <Icon name="lucide:megaphone" class="h-5 w-5" />
            </span>
            <div>
              <div class="font-semibold">{{ store.influencer.display_name }}</div>
              <div class="text-sm opacity-70">
                {{ store.influencer.niche || 'Influencer' }} · {{ statusLabel }}
              </div>
            </div>
          </div>
          <span :class="['badge badge-lg', statusBadge]">{{ statusLabel }}</span>
        </div>
        <div v-if="store.influencer.status === 'REJECTED' && store.influencer.rejection_reason" class="mt-3 rounded-2xl border border-error/40 bg-error/5 p-3 text-sm">
          <div class="font-semibold">Rejection reason</div>
          <div class="opacity-80">{{ store.influencer.rejection_reason }}</div>
        </div>
      </div>

      <div class="rounded-3xl border border-base-300 bg-base-100 p-6">
        <div class="flex items-start justify-between gap-3">
          <div>
            <h2 class="text-lg font-bold">Social handles</h2>
            <p class="mt-1 text-sm opacity-70">
              Add every channel you'll use to promote Delivo products. Admins review handle
              content before approving — keep it brand-safe.
            </p>
          </div>
        </div>

        <ul v-if="store.influencer.social_handles?.length" class="mt-3 divide-y divide-base-300">
          <li v-for="h in store.influencer.social_handles" :key="h.id" class="flex items-center justify-between gap-3 py-3 text-sm">
            <div>
              <div class="font-semibold">{{ h.platform }} · @{{ h.handle }}</div>
              <div class="text-xs opacity-60">
                <a v-if="h.url" :href="h.url" target="_blank" rel="noopener" class="link link-hover">{{ h.url }}</a>
                <span v-if="h.followers"> · {{ h.followers.toLocaleString() }} followers</span>
              </div>
            </div>
            <div class="flex items-center gap-2">
              <span :class="['badge badge-sm', h.status === 'APPROVED' ? 'badge-success' : h.status === 'REJECTED' ? 'badge-error' : 'badge-ghost']">
                {{ h.status }}
              </span>
              <button class="btn btn-xs btn-ghost rounded-full text-error" @click="onRemoveHandle(h.id)">
                <Icon name="lucide:trash-2" class="h-3.5 w-3.5" />
              </button>
            </div>
          </li>
        </ul>

        <form class="mt-4 grid gap-3 sm:grid-cols-[1fr_1.5fr_auto]" @submit.prevent="onAddHandle">
          <select v-model="handleForm.platform" class="select select-bordered">
            <option value="INSTAGRAM">Instagram</option>
            <option value="TIKTOK">TikTok</option>
            <option value="X">X (Twitter)</option>
            <option value="YOUTUBE">YouTube</option>
            <option value="FACEBOOK">Facebook</option>
            <option value="OTHER">Other</option>
          </select>
          <input
            v-model="handleForm.handle"
            type="text"
            placeholder="@username or handle"
            class="input input-bordered"
            required
          />
          <button class="btn btn-primary rounded-full" :disabled="store.submitting">
            <Icon name="lucide:plus" class="h-4 w-4" /> Add
          </button>
          <input
            v-model="handleForm.url"
            type="url"
            placeholder="https://… (optional)"
            class="input input-bordered sm:col-span-2"
          />
          <input
            v-model.number="handleForm.followers"
            type="number"
            min="0"
            placeholder="Followers"
            class="input input-bordered"
          />
        </form>
      </div>
    </div>

    <!-- Apply form -->
    <form v-else class="mt-8 grid gap-6 lg:grid-cols-2" @submit.prevent="handleApply">
      <section class="rounded-3xl border border-base-300 bg-base-100 p-6 lg:col-span-2">
        <h2 class="text-lg font-bold">Your profile</h2>
        <div class="mt-4 grid gap-4 md:grid-cols-2">
          <label class="fieldset">
            <span class="fieldset-legend">Display name *</span>
            <input v-model="form.display_name" type="text" placeholder="e.g. Tariro Style"
                   :class="['input input-bordered w-full', errors.display_name ? 'input-error' : '']" />
            <span v-if="errors.display_name" class="text-xs text-red-600">{{ errors.display_name }}</span>
          </label>
          <label class="fieldset">
            <span class="fieldset-legend">URL slug *</span>
            <input v-model="form.slug" type="text" placeholder="tariro-style"
                   :class="['input input-bordered w-full', errors.slug ? 'input-error' : '']" />
            <span v-if="errors.slug" class="text-xs text-red-600">{{ errors.slug }}</span>
          </label>
          <label class="fieldset">
            <span class="fieldset-legend">Contact email *</span>
            <input v-model="form.contact_email" type="email" placeholder="you@example.co.zw"
                   :class="['input input-bordered w-full', errors.contact_email ? 'input-error' : '']" />
            <span v-if="errors.contact_email" class="text-xs text-red-600">{{ errors.contact_email }}</span>
          </label>
          <label class="fieldset">
            <span class="fieldset-legend">Contact phone *</span>
            <input v-model="form.contact_phone" type="tel" placeholder="0772 000 000"
                   :class="['input input-bordered w-full', errors.contact_phone ? 'input-error' : '']" />
            <span v-if="errors.contact_phone" class="text-xs text-red-600">{{ errors.contact_phone }}</span>
          </label>
          <label class="fieldset">
            <span class="fieldset-legend">Niche</span>
            <input v-model="form.niche" type="text" placeholder="Fashion, Tech, Food, Lifestyle…"
                   class="input input-bordered w-full" />
          </label>
          <label class="fieldset md:col-span-2">
            <span class="fieldset-legend">Bio</span>
            <textarea v-model="form.bio" rows="3"
                      placeholder="Tell us about your audience and the kind of content you make."
                      class="textarea textarea-bordered w-full" />
          </label>
        </div>
      </section>

      <div class="flex flex-wrap items-center gap-3 lg:col-span-2">
        <button class="btn btn-primary btn-lg rounded-full px-7" type="submit" :disabled="store.submitting">
          <span v-if="store.submitting">Submitting…</span><span v-else>Submit application</span>
        </button>
        <NuxtLink to="/" class="btn btn-ghost rounded-full">Cancel</NuxtLink>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { InfluencerApplySchema } from '~/utils/InfluencerSchemas';

definePageMeta({ layout: 'default' });
useHead({ title: 'Promote on Delivo' });

const auth = useAuthStore();
const store = useInfluencerStore();

const form = reactive({
  display_name: '',
  slug: '',
  contact_email: '',
  contact_phone: '',
  bio: '' as string | null,
  niche: '' as string | null,
});
const errors = reactive<Record<string, string>>({});

const handleForm = reactive<{
  platform: 'INSTAGRAM' | 'TIKTOK' | 'X' | 'YOUTUBE' | 'FACEBOOK' | 'OTHER';
  handle: string;
  url: string;
  followers: number | null;
}>({
  platform: 'INSTAGRAM',
  handle: '',
  url: '',
  followers: null,
});

onMounted(() => {
  if (auth.isAuthenticated) store.fetchCurrent();
});

const slugify = (s: string) => s.toLowerCase().trim().replace(/[^a-z0-9-]+/g, '-').replace(/^-+|-+$/g, '');
const prevName = ref('');
watch(() => form.display_name, (v) => {
  if (form.slug === '' || form.slug === slugify(prevName.value)) form.slug = slugify(v);
  prevName.value = v;
});

const statusLabel = computed(() => {
  const s = store.influencer?.status;
  if (s === 'PENDING') return 'Pending review';
  if (s === 'ACTIVE') return 'Active';
  if (s === 'REJECTED') return 'Rejected';
  if (s === 'SUSPENDED') return 'Suspended';
  return '';
});
const statusBadge = computed(() => {
  const s = store.influencer?.status;
  if (s === 'ACTIVE') return 'badge-success';
  if (s === 'REJECTED' || s === 'SUSPENDED') return 'badge-error';
  return 'badge-warning';
});

const clearErrors = () => Object.keys(errors).forEach((k) => delete errors[k]);

const applyServerErrors = (payload: any) => {
  const fields = payload?.errors;
  if (fields && typeof fields === 'object') {
    Object.keys(fields).forEach((k) => {
      if (Array.isArray(fields[k]) && fields[k][0]) errors[k] = fields[k][0];
    });
  }
};

const handleApply = async () => {
  clearErrors();
  try {
    const valid = await InfluencerApplySchema.validate(form, { abortEarly: false });
    await store.applyAsInfluencer(valid as any);
  } catch (err: any) {
    if (err?.inner?.length) {
      err.inner.forEach((e: any) => { if (e.path) errors[e.path] = e.message; });
      return;
    }
    if (err?.response?._data) applyServerErrors(err.response._data);
  }
};

const onAddHandle = async () => {
  if (!handleForm.handle.trim()) return;
  const payload: Record<string, unknown> = {
    platform: handleForm.platform,
    handle: handleForm.handle.trim().replace(/^@/, ''),
  };
  if (handleForm.url.trim()) payload.url = handleForm.url.trim();
  if (handleForm.followers !== null && handleForm.followers >= 0) payload.followers = handleForm.followers;

  const ok = await store.addSocialHandle(payload);
  if (ok) {
    handleForm.handle = '';
    handleForm.url = '';
    handleForm.followers = null;
  }
};

const onRemoveHandle = async (id: number) => {
  if (!window.confirm('Remove this handle?')) return;
  await store.removeSocialHandle(id);
};
</script>
