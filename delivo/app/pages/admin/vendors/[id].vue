<template>
  <div>
    <NuxtLink to="/admin/vendors" class="text-sm opacity-70 hover:opacity-100">← Back to vendors</NuxtLink>

    <div v-if="store.loading" class="flex justify-center py-20">
      <span class="loading loading-spinner loading-lg"></span>
    </div>

    <div v-else-if="!vendor" class="rounded-3xl border border-base-300 bg-base-100 p-8 text-center">
      Vendor not found.
    </div>

    <div v-else class="mt-4 space-y-6">
      <div class="rounded-3xl bg-base-100 p-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
          <div>
            <h1 class="text-2xl font-extrabold tracking-tight">{{ vendor.business_name }}</h1>
            <div class="text-sm opacity-70">delivo.co.zw/store/{{ vendor.slug }}</div>
          </div>
          <span :class="['badge badge-lg', statusBadge]">{{ vendor.status }}</span>
        </div>
      </div>

      <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-3xl bg-base-100 p-6">
          <h2 class="text-lg font-bold">Owner</h2>
          <dl class="mt-3 grid gap-2 text-sm">
            <div><dt class="opacity-60">Name</dt><dd>{{ (vendor as any).owner?.name }}</dd></div>
            <div><dt class="opacity-60">Email</dt><dd>{{ (vendor as any).owner?.email }}</dd></div>
            <div><dt class="opacity-60">Phone</dt><dd>{{ (vendor as any).owner?.phone }}</dd></div>
          </dl>
        </div>

        <div class="rounded-3xl bg-base-100 p-6">
          <h2 class="text-lg font-bold">Business contact</h2>
          <dl class="mt-3 grid gap-2 text-sm">
            <div><dt class="opacity-60">Support email</dt><dd>{{ vendor.support_email }}</dd></div>
            <div><dt class="opacity-60">Support phone</dt><dd>{{ vendor.support_phone }}</dd></div>
            <div><dt class="opacity-60">TIN</dt><dd>{{ vendor.tin || '—' }}</dd></div>
            <div><dt class="opacity-60">Reg. number</dt><dd>{{ vendor.registration_no || '—' }}</dd></div>
          </dl>
        </div>

        <div class="rounded-3xl bg-base-100 p-6">
          <h2 class="text-lg font-bold">Payout</h2>

          <div v-if="!(vendor as any).payout_method" class="mt-3 text-sm opacity-70">Not configured yet.</div>

          <template v-else-if="(vendor as any).payout_method === 'MOBILE_WALLET'">
            <div class="mt-3 inline-flex items-center gap-1 rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary">
              <Icon name="lucide:smartphone" class="h-3 w-3" />
              Mobile wallet
            </div>
            <dl class="mt-3 grid gap-2 text-sm">
              <div><dt class="opacity-60">Wallet</dt><dd>{{ (vendor as any).mobile_wallet?.name || '—' }}</dd></div>
              <div><dt class="opacity-60">Wallet number</dt><dd>{{ (vendor as any).mobile_wallet_msisdn || '—' }}</dd></div>
            </dl>
          </template>

          <template v-else-if="(vendor as any).payout_method === 'BANK_TRANSFER'">
            <div class="mt-3 inline-flex items-center gap-1 rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary">
              <Icon name="lucide:landmark" class="h-3 w-3" />
              Bank transfer
            </div>
            <dl class="mt-3 grid gap-2 text-sm">
              <div><dt class="opacity-60">Bank</dt><dd>{{ vendor.bank_name || '—' }}</dd></div>
              <div><dt class="opacity-60">Account name</dt><dd>{{ vendor.bank_account_name || '—' }}</dd></div>
              <div><dt class="opacity-60">Account number</dt><dd>{{ vendor.bank_account_number || '—' }}</dd></div>
              <div><dt class="opacity-60">Currency</dt><dd>{{ (vendor as any).bank_currency || '—' }}</dd></div>
            </dl>
          </template>
        </div>

        <div class="rounded-3xl bg-base-100 p-6">
          <h2 class="text-lg font-bold">KYC documents</h2>
          <div v-if="!(vendor.kyc_documents?.length)" class="mt-3 text-sm opacity-70">
            No documents uploaded.
          </div>
          <ul v-else class="mt-3 divide-y divide-base-300">
            <li v-for="d in vendor.kyc_documents" :key="d.id" class="flex items-center justify-between py-3 text-sm">
              <div>
                <div class="font-medium">{{ d.original_filename }}</div>
                <div class="text-xs opacity-60">{{ d.type }} · uploaded {{ d.created_at?.slice(0, 10) }}</div>
              </div>
              <div class="flex items-center gap-2">
                <span :class="['badge badge-sm', d.status === 'APPROVED' ? 'badge-success' : d.status === 'REJECTED' ? 'badge-error' : 'badge-ghost']">{{ d.status }}</span>
                <button class="btn btn-xs btn-outline rounded-full" :disabled="opening === d.id" @click="openDocument(d.id)">
                  <span v-if="opening === d.id">Opening…</span>
                  <span v-else>View</span>
                </button>
              </div>
            </li>
          </ul>
        </div>
      </div>

      <div v-if="vendor.rejection_reason" class="rounded-3xl border border-error/40 bg-error/5 p-6">
        <div class="font-semibold text-error">Reason on file</div>
        <p class="mt-1 text-sm opacity-80">{{ vendor.rejection_reason }}</p>
      </div>

      <div class="rounded-3xl bg-base-100 p-6">
        <h2 class="text-lg font-bold">Actions</h2>
        <div class="mt-4 flex flex-wrap gap-3">
          <button
            v-if="vendor.status !== 'ACTIVE'"
            class="btn btn-success rounded-full"
            :disabled="store.submitting"
            @click="handleApprove"
          >Approve</button>
          <button
            v-if="vendor.status === 'PENDING'"
            class="btn btn-error rounded-full"
            :disabled="store.submitting"
            @click="showRejectModal = true"
          >Reject</button>
          <button
            v-if="vendor.status === 'ACTIVE'"
            class="btn btn-warning rounded-full"
            :disabled="store.submitting"
            @click="showSuspendModal = true"
          >Suspend</button>
        </div>
      </div>
    </div>

    <!-- Reject modal -->
    <dialog :open="showRejectModal" class="modal" :class="{ 'modal-open': showRejectModal }">
      <div class="modal-box">
        <h3 class="text-lg font-bold">Reject vendor</h3>
        <p class="mt-1 text-sm opacity-70">Give a clear reason — the vendor sees this.</p>
        <textarea v-model="rejectReason" class="textarea textarea-bordered mt-4 w-full" rows="4"
                  placeholder="e.g. National ID upload is unreadable — please resubmit." />
        <div class="modal-action">
          <button class="btn" @click="showRejectModal = false">Cancel</button>
          <button class="btn btn-error" :disabled="store.submitting || rejectReason.trim().length < 5" @click="confirmReject">
            <span v-if="store.submitting">Rejecting…</span>
            <span v-else>Reject</span>
          </button>
        </div>
      </div>
    </dialog>

    <!-- Suspend modal -->
    <dialog :open="showSuspendModal" class="modal" :class="{ 'modal-open': showSuspendModal }">
      <div class="modal-box">
        <h3 class="text-lg font-bold">Suspend vendor</h3>
        <p class="mt-1 text-sm opacity-70">Optional note — the vendor sees this if you add one.</p>
        <textarea v-model="suspendReason" class="textarea textarea-bordered mt-4 w-full" rows="4" />
        <div class="modal-action">
          <button class="btn" @click="showSuspendModal = false">Cancel</button>
          <button class="btn btn-warning" :disabled="store.submitting" @click="confirmSuspend">
            <span v-if="store.submitting">Suspending…</span>
            <span v-else>Suspend</span>
          </button>
        </div>
      </div>
    </dialog>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'admin',
  middleware: ['auth', 'admin'],
});
useHead({ title: 'Vendor — Delivo Admin' });

const store = useAdminVendorStore();
const route = useRoute();
const helper = useAdminVendorHelper();

const vendorId = computed(() => Number(route.params.id));
const vendor = computed(() => store.current);

const showRejectModal = ref(false);
const showSuspendModal = ref(false);
const rejectReason = ref('');
const suspendReason = ref('');
const opening = ref<number | null>(null);

onMounted(() => {
  store.fetchOne(vendorId.value);
});

const statusBadge = computed(() => {
  switch (vendor.value?.status) {
    case 'ACTIVE': return 'badge-success';
    case 'PENDING': return 'badge-warning';
    case 'REJECTED': return 'badge-error';
    case 'SUSPENDED': return 'badge-error';
    default: return 'badge-ghost';
  }
});

const handleApprove = async () => {
  await store.approve(vendorId.value);
};

const confirmReject = async () => {
  const ok = await store.reject(vendorId.value, rejectReason.value.trim());
  if (ok) {
    showRejectModal.value = false;
    rejectReason.value = '';
  }
};

const confirmSuspend = async () => {
  const ok = await store.suspend(vendorId.value, suspendReason.value.trim() || undefined);
  if (ok) {
    showSuspendModal.value = false;
    suspendReason.value = '';
  }
};

const openDocument = async (docId: number) => {
  opening.value = docId;
  try {
    const url = await helper.kycDocumentBlobUrl(vendorId.value, docId);
    if (url) {
      window.open(url, '_blank');
    }
  } finally {
    opening.value = null;
  }
};
</script>
