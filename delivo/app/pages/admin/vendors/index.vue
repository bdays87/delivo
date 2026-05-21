<template>
  <div>
    <div class="flex flex-wrap items-end justify-between gap-3">
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight">Vendors</h1>
        <p class="mt-1 text-sm opacity-70">Approve applications, suspend bad actors.</p>
      </div>
      <div class="join">
        <button
          v-for="t in tabs"
          :key="t.key"
          class="btn join-item btn-sm"
          :class="active === t.key ? 'btn-primary' : 'btn-ghost'"
          @click="setTab(t.key)"
        >
          {{ t.label }}
        </button>
      </div>
    </div>

    <div v-if="store.loading" class="flex justify-center py-20">
      <span class="loading loading-spinner loading-lg"></span>
    </div>

    <div v-else class="mt-6 overflow-hidden rounded-3xl border border-base-300 bg-base-100">
      <table class="table">
        <thead class="bg-base-200/50 text-xs uppercase tracking-wider opacity-70">
          <tr>
            <th>#</th>
            <th>Business</th>
            <th>Owner</th>
            <th>Submitted</th>
            <th>KYC</th>
            <th>Status</th>
            <th class="text-right">Action</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="!store.items.length">
            <td colspan="7" class="py-10 text-center text-base-content/60">No vendors in this list.</td>
          </tr>
          <tr v-for="(v, i) in store.items" :key="v.id">
            <td>{{ i + 1 }}</td>
            <td>
              <div class="font-semibold">{{ v.business_name }}</div>
              <div class="text-xs opacity-60">delivo.co.zw/store/{{ v.slug }}</div>
            </td>
            <td>
              <div class="text-sm">{{ (v as any).owner?.name }}</div>
              <div class="text-xs opacity-60">{{ (v as any).owner?.email }}</div>
            </td>
            <td class="text-xs opacity-70">{{ (v as any).created_at?.slice(0, 10) }}</td>
            <td>
              <span class="badge badge-sm" :class="kycBadge(v)">
                {{ (v.kyc_documents ?? []).length }} doc{{ (v.kyc_documents ?? []).length === 1 ? '' : 's' }}
              </span>
            </td>
            <td>
              <span :class="['badge badge-sm', statusBadge(v.status)]">{{ v.status }}</span>
            </td>
            <td class="text-right">
              <NuxtLink :to="`/admin/vendors/${v.id}`" class="btn btn-xs btn-primary rounded-full">Review</NuxtLink>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { Vendor } from '~/stores/vendor';

definePageMeta({
  layout: 'admin',
  middleware: ['auth', 'admin'],
});
useHead({ title: 'Vendors — Delivo Admin' });

const store = useAdminVendorStore();
const route = useRoute();
const router = useRouter();

const tabs = [
  { key: 'PENDING', label: 'Pending' },
  { key: 'ACTIVE', label: 'Active' },
  { key: 'REJECTED', label: 'Rejected' },
  { key: 'SUSPENDED', label: 'Suspended' },
  { key: 'ALL', label: 'All' },
] as const;

const active = ref<typeof tabs[number]['key']>((route.query.status as any) || 'PENDING');

const setTab = (k: typeof active.value) => {
  active.value = k;
  router.replace({ query: { status: k === 'ALL' ? undefined : k } });
  store.fetchList(k === 'ALL' ? undefined : k);
};

onMounted(() => {
  store.fetchList(active.value === 'ALL' ? undefined : active.value);
});

const statusBadge = (s: Vendor['status']) => {
  if (s === 'ACTIVE') return 'badge-success';
  if (s === 'PENDING') return 'badge-warning';
  if (s === 'REJECTED' || s === 'SUSPENDED') return 'badge-error';
  return 'badge-ghost';
};

const kycBadge = (v: Vendor) => {
  const count = v.kyc_documents?.length ?? 0;
  if (count === 0) return 'badge-error';
  const pending = (v.kyc_documents ?? []).some((d) => d.status === 'PENDING');
  return pending ? 'badge-warning' : 'badge-success';
};
</script>
