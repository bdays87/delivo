<template>
  <div class="mx-auto max-w-5xl px-4 py-10">
    <div class="flex flex-wrap items-end justify-between gap-3">
      <div>
        <h1 class="text-3xl font-extrabold tracking-tight md:text-4xl">Payouts</h1>
        <p class="mt-1 text-sm opacity-70">
          Request a withdrawal of your cleared earnings. Delivo deducts a service fee, then
          settles the balance to the destination you provide.
        </p>
      </div>
      <NuxtLink to="/influencer/earnings" class="btn btn-ghost rounded-full">
        <Icon name="lucide:list" class="h-4 w-4" /> Earnings
      </NuxtLink>
    </div>

    <div v-if="loading" class="flex justify-center py-16">
      <span class="loading loading-spinner loading-lg"></span>
    </div>

    <div v-else class="mt-6 grid gap-6 lg:grid-cols-[1fr_360px]">
      <div>
        <h2 class="text-sm font-semibold uppercase tracking-wider opacity-70">History</h2>
        <div v-if="!requests.length" class="mt-3 rounded-3xl border border-dashed border-base-300 p-12 text-center text-sm opacity-70">
          You haven't requested any payouts yet.
        </div>
        <div v-else class="mt-3 overflow-hidden rounded-3xl border border-base-300 bg-base-100">
          <table class="table">
            <thead class="bg-base-200/50 text-xs uppercase tracking-wider opacity-70">
              <tr>
                <th>Requested</th>
                <th>Fee</th>
                <th>Net</th>
                <th>Method</th>
                <th>Status</th>
                <th class="text-right">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="r in requests" :key="r.id">
                <td class="font-semibold">{{ format(r.requested_usd) }}</td>
                <td class="text-error">− {{ format(r.service_fee_usd) }}</td>
                <td class="font-semibold text-success">{{ format(r.net_payout_usd) }}</td>
                <td class="text-xs">
                  {{ methodLabel(r.method) }}
                  <div class="opacity-60">{{ r.destination_account }}</div>
                </td>
                <td><span :class="['badge badge-sm', statusBadge(r.status)]">{{ r.status }}</span></td>
                <td class="text-right">
                  <button
                    v-if="r.status === 'PENDING'"
                    class="btn btn-ghost btn-xs rounded-full text-error"
                    @click="onCancel(r.id)"
                  >
                    Cancel
                  </button>
                  <div v-else-if="r.rejection_reason" class="text-xs opacity-60">{{ r.rejection_reason }}</div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <aside class="h-fit rounded-3xl border border-base-300 bg-base-100 p-6">
        <h2 class="text-sm font-semibold uppercase tracking-wider opacity-70">Request a payout</h2>
        <dl class="mt-3 space-y-2 text-sm">
          <div class="flex justify-between"><dt>Available</dt><dd class="font-semibold text-success">{{ format(summary.cleared_usd) }}</dd></div>
          <div class="flex justify-between"><dt>Service fee ({{ summary.service_fee_pct }}%)</dt><dd>− {{ format(estimateFee) }}</dd></div>
          <div class="flex justify-between border-t border-base-300 pt-2 font-bold"><dt>You'd receive</dt><dd class="text-primary">{{ format(estimateNet) }}</dd></div>
        </dl>

        <form class="mt-4 space-y-3" @submit.prevent="onSubmit">
          <label class="fieldset">
            <span class="fieldset-legend">Method</span>
            <select v-model="form.method" class="select select-bordered" required>
              <option value="MOBILE_MONEY">Mobile money</option>
              <option value="BANK_TRANSFER">Bank transfer</option>
            </select>
          </label>
          <label class="fieldset">
            <span class="fieldset-legend">{{ form.method === 'MOBILE_MONEY' ? 'Wallet provider' : 'Bank' }} (optional)</span>
            <input v-model="form.destination_label" type="text" :placeholder="form.method === 'MOBILE_MONEY' ? 'EcoCash / OneMoney' : 'Bank name'" class="input input-bordered" maxlength="120" />
          </label>
          <label class="fieldset">
            <span class="fieldset-legend">{{ form.method === 'MOBILE_MONEY' ? 'Phone number' : 'Account number' }}</span>
            <input v-model="form.destination_account" type="text" class="input input-bordered" required maxlength="120" />
          </label>
          <label class="fieldset">
            <span class="fieldset-legend">Notes (optional)</span>
            <textarea v-model="form.notes" rows="2" class="textarea textarea-bordered" maxlength="1000" />
          </label>
          <button
            type="submit"
            class="btn btn-primary btn-lg w-full rounded-full"
            :disabled="!canRequest || submitting"
          >
            <span v-if="submitting" class="loading loading-spinner loading-xs"></span>
            Request payout
          </button>
          <p v-if="!canRequest && !loading" class="text-xs opacity-60">
            <span v-if="summary.cleared_usd < summary.min_payout_usd">
              Need {{ format(summary.min_payout_usd) }} cleared to withdraw — you have {{ format(summary.cleared_usd) }}.
            </span>
            <span v-else-if="hasOpenRequest">You already have a pending request below.</span>
          </p>
        </form>
      </aside>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ middleware: ['auth'] });
useHead({ title: 'Payouts — Delivo' });

interface PayoutRequestRow {
  id: number;
  requested_usd: string;
  service_fee_pct: string;
  service_fee_usd: string;
  net_payout_usd: string;
  status: 'PENDING' | 'APPROVED' | 'PAID' | 'REJECTED';
  method: 'MOBILE_MONEY' | 'BANK_TRANSFER';
  destination_label: string | null;
  destination_account: string;
  influencer_notes: string | null;
  rejection_reason: string | null;
  created_at: string;
}

interface SummaryRow {
  pending_usd: number;
  cleared_usd: number;
  paid_usd: number;
  min_payout_usd: number;
  service_fee_pct: number;
}

const { earningsSummary, listPayoutRequests, createPayoutRequest, cancelPayoutRequest } = useInfluencerHelper();
const toast = useToast();

const loading = ref(false);
const submitting = ref(false);
const summary = ref<SummaryRow>({ pending_usd: 0, cleared_usd: 0, paid_usd: 0, min_payout_usd: 10, service_fee_pct: 5 });
const requests = ref<PayoutRequestRow[]>([]);

const form = reactive({
  method: 'MOBILE_MONEY' as 'MOBILE_MONEY' | 'BANK_TRANSFER',
  destination_label: '',
  destination_account: '',
  notes: '',
});

const format = (v: string | number) => `$${Number(v).toFixed(2)}`;

const estimateFee = computed(() => Number(summary.value.cleared_usd) * (Number(summary.value.service_fee_pct) / 100));
const estimateNet = computed(() => Math.max(0, Number(summary.value.cleared_usd) - estimateFee.value));

const hasOpenRequest = computed(() => requests.value.some((r) => r.status === 'PENDING' || r.status === 'APPROVED'));
const canRequest = computed(() =>
  Number(summary.value.cleared_usd) >= Number(summary.value.min_payout_usd) && !hasOpenRequest.value,
);

const fetchAll = async () => {
  loading.value = true;
  const [{ data: s }, { data: r }] = await Promise.all([earningsSummary(), listPayoutRequests()]);
  summary.value = ((s.value as any)?.data ?? summary.value) as SummaryRow;
  requests.value = ((r.value as any)?.data ?? []) as PayoutRequestRow[];
  loading.value = false;
};

onMounted(fetchAll);

const onSubmit = async () => {
  if (!form.destination_account.trim()) return;
  submitting.value = true;
  const { status, error } = await createPayoutRequest({
    method: form.method,
    destination_label: form.destination_label || null,
    destination_account: form.destination_account,
    notes: form.notes || null,
  });
  if (status?.value) {
    toast.success({ title: 'Request submitted', message: 'Delivo will process this shortly.', position: 'topRight', layout: 2 });
    form.destination_label = '';
    form.destination_account = '';
    form.notes = '';
    await fetchAll();
  } else {
    toast.error({
      title: 'Could not request payout',
      message: (error?.value as any)?.data?.message || 'Try again.',
      position: 'topRight',
      layout: 2,
    });
  }
  submitting.value = false;
};

const onCancel = async (id: number) => {
  if (!window.confirm('Cancel this payout request?')) return;
  const { status, error } = await cancelPayoutRequest(id);
  if (status?.value) {
    toast.success({ title: 'Cancelled', message: 'Your cleared balance is available again.', position: 'topRight', layout: 2 });
    await fetchAll();
  } else {
    toast.error({
      title: 'Could not cancel',
      message: (error?.value as any)?.data?.message || 'Try again.',
      position: 'topRight',
      layout: 2,
    });
  }
};

const methodLabel = (m: string) => ({
  MOBILE_MONEY: 'Mobile money',
  BANK_TRANSFER: 'Bank transfer',
}[m] ?? m);

const statusBadge = (s: string) => ({
  PENDING: 'badge-warning',
  APPROVED: 'badge-info',
  PAID: 'badge-success',
  REJECTED: 'badge-error',
}[s] ?? 'badge-ghost');
</script>
