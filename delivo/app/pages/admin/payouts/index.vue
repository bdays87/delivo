<template>
  <div>
    <div>
      <h1 class="text-2xl font-extrabold tracking-tight">Influencer payouts</h1>
      <p class="mt-1 text-sm opacity-70">
        Approve, mark paid, or reject withdrawal requests. Rejecting returns the influencer's
        ledger entries to Cleared so they can request again.
      </p>
    </div>

    <div class="mt-6 flex flex-wrap gap-2">
      <button
        v-for="tab in tabs"
        :key="tab.value"
        :class="['btn btn-sm rounded-full', filterStatus === tab.value ? 'btn-primary' : 'btn-ghost']"
        @click="setFilter(tab.value)"
      >
        {{ tab.label }}
      </button>
    </div>

    <div v-if="loading" class="flex justify-center py-16">
      <span class="loading loading-spinner loading-lg"></span>
    </div>

    <div v-else-if="!rows.length" class="mt-6 rounded-3xl border border-dashed border-base-300 p-12 text-center text-sm opacity-70">
      Nothing in this queue.
    </div>

    <div v-else class="mt-6 overflow-hidden rounded-3xl border border-base-300 bg-base-100">
      <table class="table">
        <thead class="bg-base-200/50 text-xs uppercase tracking-wider opacity-70">
          <tr>
            <th>Influencer</th>
            <th>Requested</th>
            <th>Fee</th>
            <th>Net</th>
            <th>Destination</th>
            <th>Status</th>
            <th class="text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="r in rows" :key="r.id">
            <td>
              <div class="font-semibold">{{ r.influencer?.display_name ?? '—' }}</div>
              <div class="text-xs opacity-60">{{ r.influencer?.contact_email }}</div>
            </td>
            <td class="font-semibold">${{ Number(r.requested_usd).toFixed(2) }}</td>
            <td class="text-error">− ${{ Number(r.service_fee_usd).toFixed(2) }} <span class="text-xs opacity-60">({{ Number(r.service_fee_pct).toFixed(2) }}%)</span></td>
            <td class="font-semibold text-success">${{ Number(r.net_payout_usd).toFixed(2) }}</td>
            <td class="text-xs">
              <div>{{ methodLabel(r.method) }}</div>
              <div class="opacity-60">{{ r.destination_label ? `${r.destination_label} · ` : '' }}{{ r.destination_account }}</div>
            </td>
            <td><span :class="['badge badge-sm', statusBadge(r.status)]">{{ r.status }}</span></td>
            <td class="text-right">
              <div class="flex flex-wrap justify-end gap-1">
                <button
                  v-if="r.status === 'PENDING'"
                  class="btn btn-info btn-xs rounded-full"
                  :disabled="submitting === r.id"
                  @click="onApprove(r)"
                >
                  Approve
                </button>
                <button
                  v-if="r.status === 'PENDING' || r.status === 'APPROVED'"
                  class="btn btn-success btn-xs rounded-full"
                  :disabled="submitting === r.id"
                  @click="onMarkPaid(r)"
                >
                  Mark paid
                </button>
                <button
                  v-if="r.status === 'PENDING' || r.status === 'APPROVED'"
                  class="btn btn-error btn-xs btn-ghost rounded-full"
                  :disabled="submitting === r.id"
                  @click="onReject(r)"
                >
                  Reject
                </button>
                <span v-else-if="r.status === 'PAID'" class="text-xs opacity-60">Paid {{ r.paid_at?.slice(0, 10) }}</span>
                <span v-else-if="r.status === 'REJECTED' && r.rejection_reason" class="text-xs opacity-60">{{ r.rejection_reason }}</span>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ layout: 'admin', middleware: ['auth', 'admin'] });
useHead({ title: 'Payouts — Delivo Admin' });

type PayoutStatus = 'PENDING' | 'APPROVED' | 'PAID' | 'REJECTED';

interface PayoutRow {
  id: number;
  influencer: { id: number; display_name: string; slug: string; contact_email: string } | null;
  requested_usd: string;
  service_fee_pct: string;
  service_fee_usd: string;
  net_payout_usd: string;
  status: PayoutStatus;
  method: 'MOBILE_MONEY' | 'BANK_TRANSFER';
  destination_label: string | null;
  destination_account: string;
  rejection_reason: string | null;
  paid_at: string | null;
  created_at: string;
}

const { list, approve, markPaid, reject } = useAdminPayoutHelper();
const toast = useToast();

const tabs = [
  { label: 'Pending', value: 'PENDING' as const },
  { label: 'Approved', value: 'APPROVED' as const },
  { label: 'Paid', value: 'PAID' as const },
  { label: 'Rejected', value: 'REJECTED' as const },
  { label: 'All', value: '' as const },
];

const loading = ref(false);
const submitting = ref<number | null>(null);
const filterStatus = ref<'' | PayoutStatus>('PENDING');
const rows = ref<PayoutRow[]>([]);

const fetchAll = async () => {
  loading.value = true;
  const { data, error } = await list(filterStatus.value || undefined);
  if (!error.value) {
    rows.value = ((data.value as any)?.data ?? []) as PayoutRow[];
  } else {
    toast.error({
      title: 'Error',
      message: (error.value as any)?.data?.message || 'Failed to load payouts.',
      position: 'topRight',
      layout: 2,
    });
  }
  loading.value = false;
};

onMounted(fetchAll);

const setFilter = async (v: '' | PayoutStatus) => {
  filterStatus.value = v;
  await fetchAll();
};

const onApprove = async (r: PayoutRow) => {
  const notes = window.prompt('Optional note for the influencer:') ?? undefined;
  submitting.value = r.id;
  const { status, error } = await approve(r.id, notes || undefined);
  if (status?.value) {
    toast.success({ title: 'Approved', message: `Request #${r.id}`, position: 'topRight', layout: 2 });
    await fetchAll();
  } else {
    toast.error({
      title: 'Could not approve',
      message: (error?.value as any)?.data?.message || 'Try again.',
      position: 'topRight',
      layout: 2,
    });
  }
  submitting.value = null;
};

const onMarkPaid = async (r: PayoutRow) => {
  if (!window.confirm(`Mark $${Number(r.net_payout_usd).toFixed(2)} payout to ${r.influencer?.display_name ?? '—'} as PAID?`)) return;
  const notes = window.prompt('Payment reference / note (optional):') ?? undefined;
  submitting.value = r.id;
  const { status, error } = await markPaid(r.id, notes || undefined);
  if (status?.value) {
    toast.success({ title: 'Paid', message: `Request #${r.id}`, position: 'topRight', layout: 2 });
    await fetchAll();
  } else {
    toast.error({
      title: 'Could not mark paid',
      message: (error?.value as any)?.data?.message || 'Try again.',
      position: 'topRight',
      layout: 2,
    });
  }
  submitting.value = null;
};

const onReject = async (r: PayoutRow) => {
  const reason = window.prompt('Reason for rejecting this payout request:');
  if (!reason || !reason.trim()) return;
  submitting.value = r.id;
  const { status, error } = await reject(r.id, reason.trim());
  if (status?.value) {
    toast.success({ title: 'Rejected', message: `Request #${r.id}`, position: 'topRight', layout: 2 });
    await fetchAll();
  } else {
    toast.error({
      title: 'Could not reject',
      message: (error?.value as any)?.data?.message || 'Try again.',
      position: 'topRight',
      layout: 2,
    });
  }
  submitting.value = null;
};

const methodLabel = (m: string) => ({ MOBILE_MONEY: 'Mobile money', BANK_TRANSFER: 'Bank transfer' }[m] ?? m);

const statusBadge = (s: string) => ({
  PENDING: 'badge-warning',
  APPROVED: 'badge-info',
  PAID: 'badge-success',
  REJECTED: 'badge-error',
}[s] ?? 'badge-ghost');
</script>
