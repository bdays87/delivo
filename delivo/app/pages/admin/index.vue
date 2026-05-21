<template>
  <div>
    <div class="flex flex-wrap items-end justify-between gap-3">
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight">Welcome back, {{ firstName }}.</h1>
        <p class="mt-1 text-sm opacity-70">Here's a quick snapshot of the platform.</p>
      </div>
    </div>

    <div class="mt-6 grid gap-4 md:grid-cols-2 lg:grid-cols-4">
      <div v-for="kpi in kpis" :key="kpi.label" class="rounded-3xl bg-base-100 p-5">
        <div class="flex items-start justify-between">
          <div>
            <div class="text-xs uppercase tracking-wider opacity-60">{{ kpi.label }}</div>
            <div class="mt-2 text-2xl font-bold">{{ kpi.value }}</div>
          </div>
          <span class="grid h-10 w-10 place-items-center rounded-2xl bg-primary/10 text-primary">
            <Icon :name="kpi.icon" class="h-5 w-5" />
          </span>
        </div>
        <div class="mt-3 text-xs opacity-60">{{ kpi.hint }}</div>
      </div>
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-2">
      <div class="rounded-3xl bg-base-100 p-6">
        <h2 class="text-lg font-bold">Open queues</h2>
        <ul class="mt-4 divide-y divide-base-200 text-sm">
          <li class="flex items-center justify-between py-3">
            <span>
              Vendors awaiting review
              <span v-if="!statsLoading" class="ml-1 font-semibold text-warning">({{ pendingVendorCount }})</span>
            </span>
            <NuxtLink to="/admin/vendors?status=PENDING" class="link link-primary text-xs">Open queue →</NuxtLink>
          </li>
          <li class="flex items-center justify-between py-3">
            <span>Products awaiting approval</span>
            <span class="text-xs opacity-60">Coming in next slice</span>
          </li>
          <li class="flex items-center justify-between py-3">
            <span>Open disputes</span>
            <span class="text-xs opacity-60">Coming later</span>
          </li>
        </ul>
      </div>

      <div class="rounded-3xl bg-base-100 p-6">
        <h2 class="text-lg font-bold">Your access</h2>
        <p class="mt-1 text-sm opacity-70">
          {{ auth.user?.permissions?.length ?? 0 }} permissions across {{ auth.user?.modules?.length ?? 0 }} modules.
        </p>
        <div class="mt-4 flex flex-wrap gap-1.5">
          <span v-for="r in auth.user?.roles ?? []" :key="r" class="badge badge-primary">{{ r }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'admin',
  middleware: ['auth', 'admin'],
});
useHead({ title: 'Dashboard — Delivo Admin' });

const auth = useAuthStore();
const { listVendors } = useAdminVendorHelper();

const firstName = computed(() => (auth.user?.name ?? '').split(' ')[0] ?? '');

const activeVendorCount = ref(0);
const pendingVendorCount = ref(0);
const statsLoading = ref(true);

const vendorListFromResponse = (res: { data: { value: unknown }; error: { value: unknown } }) => {
  if (res.error.value) return [];
  const body = res.data.value as { data?: unknown[] } | null;
  return body?.data ?? [];
};

const loadVendorStats = async () => {
  statsLoading.value = true;
  const [activeRes, pendingRes] = await Promise.all([
    listVendors('ACTIVE'),
    listVendors('PENDING'),
  ]);
  activeVendorCount.value = vendorListFromResponse(activeRes).length;
  pendingVendorCount.value = vendorListFromResponse(pendingRes).length;
  statsLoading.value = false;
};

onMounted(() => {
  loadVendorStats();
});

const kpis = computed(() => [
  { label: 'GMV today', value: '$0', icon: 'lucide:banknote', hint: 'No sales recorded yet' },
  {
    label: 'Active vendors',
    value: statsLoading.value ? '—' : String(activeVendorCount.value),
    icon: 'lucide:building-2',
    hint: activeVendorCount.value === 0 && !statsLoading.value
      ? 'Approve vendors to see them here'
      : statsLoading.value
        ? 'Loading…'
        : `${activeVendorCount.value} vendor${activeVendorCount.value === 1 ? '' : 's'} on the platform`,
  },
  { label: 'Orders to ship', value: '0', icon: 'lucide:truck', hint: 'Awaiting checkout slice' },
  { label: 'Open disputes', value: '0', icon: 'lucide:gavel', hint: '—' },
]);
</script>
