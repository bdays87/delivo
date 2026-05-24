<template>
  <div>
    <div v-if="store.loading && !store.current" class="flex justify-center py-16">
      <span class="loading loading-spinner loading-lg"></span>
    </div>

    <div v-else-if="!store.current" class="rounded-3xl border border-base-300 bg-base-100 p-8 text-center">
      <p>Influencer not found.</p>
      <NuxtLink to="/admin/influencers" class="btn btn-primary mt-4 rounded-full">Back to influencers</NuxtLink>
    </div>

    <div v-else class="space-y-6">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
          <NuxtLink to="/admin/influencers" class="btn btn-ghost btn-sm rounded-full">
            <Icon name="lucide:arrow-left" class="h-4 w-4" />
          </NuxtLink>
          <div>
            <h1 class="text-2xl font-extrabold tracking-tight">{{ store.current.display_name }}</h1>
            <p class="mt-1 text-xs opacity-60 font-mono">{{ store.current.slug }}</p>
          </div>
        </div>
        <span :class="['badge badge-lg', statusBadge]">{{ store.current.status }}</span>
      </div>

      <div class="grid gap-6 lg:grid-cols-2">
        <section class="rounded-3xl border border-base-300 bg-base-100 p-6">
          <h2 class="text-sm font-semibold uppercase tracking-wider opacity-70">Profile</h2>
          <dl class="mt-3 grid gap-2 text-sm">
            <div><dt class="opacity-60">Niche</dt><dd>{{ store.current.niche || '—' }}</dd></div>
            <div><dt class="opacity-60">Email</dt><dd>{{ store.current.contact_email }}</dd></div>
            <div><dt class="opacity-60">Phone</dt><dd>{{ store.current.contact_phone }}</dd></div>
          </dl>
          <p v-if="store.current.bio" class="mt-4 whitespace-pre-line text-sm opacity-80">{{ store.current.bio }}</p>
        </section>

        <section class="rounded-3xl border border-base-300 bg-base-100 p-6">
          <h2 class="text-sm font-semibold uppercase tracking-wider opacity-70">Social handles</h2>
          <div v-if="!store.current.social_handles?.length" class="mt-3 text-sm opacity-70">No handles declared.</div>
          <ul v-else class="mt-3 divide-y divide-base-300">
            <li v-for="h in store.current.social_handles" :key="h.id" class="flex items-center justify-between gap-3 py-3 text-sm">
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
                <div class="dropdown dropdown-end">
                  <button tabindex="0" class="btn btn-xs btn-ghost rounded-full">
                    <Icon name="lucide:more-horizontal" class="h-3.5 w-3.5" />
                  </button>
                  <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-10 mt-1 w-40 p-2 shadow">
                    <li><button @click="store.onSetHandleStatus(providerId, h.id, 'APPROVED')">Approve</button></li>
                    <li><button @click="store.onSetHandleStatus(providerId, h.id, 'REJECTED')">Reject</button></li>
                    <li><button @click="store.onSetHandleStatus(providerId, h.id, 'PENDING')">Reset to pending</button></li>
                  </ul>
                </div>
              </div>
            </li>
          </ul>
        </section>
      </div>

      <div class="flex flex-wrap justify-end gap-2">
        <button
          v-if="store.current.status === 'ACTIVE'"
          class="btn btn-warning rounded-full"
          :disabled="store.submitting"
          @click="openSuspend"
        >
          <Icon name="lucide:hand" class="h-4 w-4" /> Suspend
        </button>
        <button
          v-if="store.current.status === 'PENDING'"
          class="btn btn-error rounded-full"
          :disabled="store.submitting"
          @click="openReject"
        >
          <Icon name="lucide:x" class="h-4 w-4" /> Reject
        </button>
        <button
          v-if="store.current.status === 'PENDING'"
          class="btn btn-primary rounded-full"
          :disabled="store.submitting"
          @click="onApprove"
        >
          <Icon name="lucide:check" class="h-4 w-4" /> Approve
        </button>
      </div>
    </div>

    <dialog :open="modalOpen" class="modal" :class="{ 'modal-open': modalOpen }">
      <div class="modal-box max-w-md">
        <h3 class="text-lg font-bold">{{ modalAction === 'reject' ? 'Reject influencer' : 'Suspend influencer' }}</h3>
        <label class="fieldset mt-3">
          <span class="fieldset-legend">Reason</span>
          <textarea
            v-model="reason"
            rows="4"
            :class="['textarea textarea-bordered w-full', reasonError ? 'textarea-error' : '']"
            placeholder="e.g. Handle content doesn't meet brand-safety guidelines."
          />
          <span v-if="reasonError" class="text-xs text-red-600">{{ reasonError }}</span>
        </label>
        <div class="modal-action">
          <button type="button" class="btn rounded-full" @click="modalOpen = false">Cancel</button>
          <button
            type="button"
            :class="['btn rounded-full', modalAction === 'reject' ? 'btn-error' : 'btn-warning']"
            :disabled="store.submitting"
            @click="onConfirmModal"
          >
            Confirm
          </button>
        </div>
      </div>
    </dialog>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ layout: 'admin', middleware: ['auth', 'admin'] });

const route = useRoute();
const router = useRouter();
const store = useAdminInfluencerStore();

const providerId = computed(() => Number(route.params.id));

onMounted(() => store.fetchOne(providerId.value));

useHead({ title: () => `${store.current?.display_name ?? 'Influencer'} — Delivo Admin` });

const statusBadge = computed(() => {
  const s = store.current?.status;
  if (s === 'ACTIVE') return 'badge-success';
  if (s === 'REJECTED' || s === 'SUSPENDED') return 'badge-error';
  return 'badge-warning';
});

const modalOpen = ref(false);
const modalAction = ref<'reject' | 'suspend'>('reject');
const reason = ref('');
const reasonError = ref('');

const openReject = () => { modalAction.value = 'reject'; reason.value = ''; reasonError.value = ''; modalOpen.value = true; };
const openSuspend = () => { modalAction.value = 'suspend'; reason.value = ''; reasonError.value = ''; modalOpen.value = true; };

const onApprove = async () => {
  const ok = await store.onApprove(providerId.value);
  if (ok) router.push('/admin/influencers');
};

const onConfirmModal = async () => {
  reasonError.value = '';
  if (modalAction.value === 'reject' && reason.value.trim().length < 5) {
    reasonError.value = 'Give the influencer a clear reason (5+ characters).';
    return;
  }
  const ok = modalAction.value === 'reject'
    ? await store.onReject(providerId.value, reason.value.trim())
    : await store.onSuspend(providerId.value, reason.value.trim() || undefined);
  if (ok) {
    modalOpen.value = false;
    router.push('/admin/influencers');
  }
};
</script>
