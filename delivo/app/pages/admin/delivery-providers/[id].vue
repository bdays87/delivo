<template>
  <div>
    <div v-if="store.loading && !store.current" class="flex justify-center py-16">
      <span class="loading loading-spinner loading-lg"></span>
    </div>

    <div v-else-if="!store.current" class="rounded-3xl border border-base-300 bg-base-100 p-8 text-center">
      <p>Provider not found.</p>
      <NuxtLink to="/admin/delivery-providers" class="btn btn-primary mt-4 rounded-full">Back to providers</NuxtLink>
    </div>

    <div v-else class="space-y-6">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
          <NuxtLink to="/admin/delivery-providers" class="btn btn-ghost btn-sm rounded-full">
            <Icon name="lucide:arrow-left" class="h-4 w-4" />
          </NuxtLink>
          <div>
            <h1 class="text-2xl font-extrabold tracking-tight">{{ store.current.business_name }}</h1>
            <p class="mt-1 text-xs opacity-60 font-mono">{{ store.current.slug }}</p>
          </div>
        </div>
        <span :class="['badge badge-lg', statusBadge]">{{ store.current.status }}</span>
      </div>

      <div class="grid gap-6 lg:grid-cols-2">
        <section class="rounded-3xl border border-base-300 bg-base-100 p-6">
          <h2 class="text-sm font-semibold uppercase tracking-wider opacity-70">Business</h2>
          <dl class="mt-3 grid gap-2 text-sm">
            <div><dt class="opacity-60">Base city</dt><dd>{{ store.current.base_city }}</dd></div>
            <div><dt class="opacity-60">Vehicle types</dt><dd>{{ store.current.vehicle_types || '—' }}</dd></div>
            <div><dt class="opacity-60">Support email</dt><dd>{{ store.current.support_email }}</dd></div>
            <div><dt class="opacity-60">Support phone</dt><dd>{{ store.current.support_phone }}</dd></div>
          </dl>
        </section>

        <section class="rounded-3xl border border-base-300 bg-base-100 p-6">
          <h2 class="text-sm font-semibold uppercase tracking-wider opacity-70">Coverage</h2>
          <div v-if="!store.current.coverage_areas?.length" class="mt-3 text-sm opacity-70">No coverage selected.</div>
          <div v-else class="mt-3 flex flex-wrap gap-2">
            <span v-for="c in store.current.coverage_areas" :key="c.id" class="badge badge-ghost">{{ c.city }}</span>
          </div>
        </section>
      </div>

      <section class="rounded-3xl border border-base-300 bg-base-100 p-6">
        <h2 class="text-sm font-semibold uppercase tracking-wider opacity-70">KYC documents</h2>
        <div v-if="!store.current.kyc_documents?.length" class="mt-3 text-sm opacity-70">No documents uploaded.</div>
        <ul v-else class="mt-3 divide-y divide-base-300">
          <li v-for="d in store.current.kyc_documents" :key="d.id" class="flex items-center justify-between py-3 text-sm">
            <div>
              <div class="font-semibold">{{ d.original_filename }}</div>
              <div class="text-xs opacity-60">{{ d.type }}</div>
            </div>
            <div class="flex items-center gap-2">
              <button class="btn btn-xs btn-ghost rounded-full" @click="previewDoc(d.id)">
                <Icon name="lucide:eye" class="h-3.5 w-3.5" /> Preview
              </button>
              <span :class="['badge badge-sm', d.status === 'APPROVED' ? 'badge-success' : d.status === 'REJECTED' ? 'badge-error' : 'badge-ghost']">
                {{ d.status }}
              </span>
            </div>
          </li>
        </ul>
      </section>

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
        <h3 class="text-lg font-bold">{{ modalAction === 'reject' ? 'Reject provider' : 'Suspend provider' }}</h3>
        <label class="fieldset mt-3">
          <span class="fieldset-legend">Reason</span>
          <textarea
            v-model="reason"
            rows="4"
            :class="['textarea textarea-bordered w-full', reasonError ? 'textarea-error' : '']"
            placeholder="e.g. Missing vehicle registration."
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

    <dialog :open="previewOpen" class="modal" :class="{ 'modal-open': previewOpen }">
      <div class="modal-box max-w-3xl">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-bold">KYC document</h3>
          <button class="btn btn-ghost btn-sm" @click="closePreview">Close</button>
        </div>
        <div v-if="previewUrl" class="mt-3">
          <embed :src="previewUrl" class="h-[60vh] w-full rounded-2xl border border-base-300" />
        </div>
        <div v-else class="py-10 text-center text-sm opacity-70">Loading…</div>
      </div>
    </dialog>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ layout: 'admin', middleware: ['auth', 'admin'] });

const route = useRoute();
const router = useRouter();
const store = useAdminDeliveryProviderStore();

const providerId = computed(() => Number(route.params.id));

onMounted(() => store.fetchOne(providerId.value));

useHead({ title: () => `${store.current?.business_name ?? 'Provider'} — Delivo Admin` });

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

const openReject = () => {
  modalAction.value = 'reject';
  reason.value = '';
  reasonError.value = '';
  modalOpen.value = true;
};
const openSuspend = () => {
  modalAction.value = 'suspend';
  reason.value = '';
  reasonError.value = '';
  modalOpen.value = true;
};

const onApprove = async () => {
  const ok = await store.onApprove(providerId.value);
  if (ok) router.push('/admin/delivery-providers');
};

const onConfirmModal = async () => {
  reasonError.value = '';
  if (modalAction.value === 'reject' && reason.value.trim().length < 5) {
    reasonError.value = 'Give the provider a clear reason (5+ characters).';
    return;
  }
  const ok = modalAction.value === 'reject'
    ? await store.onReject(providerId.value, reason.value.trim())
    : await store.onSuspend(providerId.value, reason.value.trim() || undefined);
  if (ok) {
    modalOpen.value = false;
    router.push('/admin/delivery-providers');
  }
};

const previewOpen = ref(false);
const previewUrl = ref<string | null>(null);
const previewDoc = async (docId: number) => {
  previewOpen.value = true;
  previewUrl.value = await store.kycDocumentBlobUrl(providerId.value, docId);
};
const closePreview = () => {
  if (previewUrl.value) URL.revokeObjectURL(previewUrl.value);
  previewUrl.value = null;
  previewOpen.value = false;
};
</script>
