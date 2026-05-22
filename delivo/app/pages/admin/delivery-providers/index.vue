<template>
  <div>
    <div>
      <h1 class="text-2xl font-extrabold tracking-tight">Delivery providers</h1>
      <p class="mt-1 text-sm opacity-70">
        Approve, reject or suspend delivery service providers. Approved providers can carry orders
        in cities listed in their coverage.
      </p>
    </div>

    <div class="mt-6 flex flex-wrap gap-2">
      <button
        v-for="tab in tabs"
        :key="tab.value"
        :class="['btn btn-sm rounded-full', store.filterStatus === tab.value ? 'btn-primary' : 'btn-ghost']"
        @click="store.fetchAll(tab.value)"
      >
        {{ tab.label }}
      </button>
    </div>

    <div v-if="store.loading" class="flex justify-center py-16">
      <span class="loading loading-spinner loading-lg"></span>
    </div>

    <div v-else-if="!store.items.length" class="mt-6 rounded-3xl border border-dashed border-base-300 p-12 text-center text-sm opacity-70">
      Nothing in this queue.
    </div>

    <div v-else class="mt-6 overflow-hidden rounded-3xl border border-base-300 bg-base-100">
      <table class="table">
        <thead class="bg-base-200/50 text-xs uppercase tracking-wider opacity-70">
          <tr>
            <th>Business</th>
            <th>Base city</th>
            <th>Coverage</th>
            <th>Status</th>
            <th class="text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="p in store.items" :key="p.id">
            <td>
              <div class="font-semibold">{{ p.business_name }}</div>
              <div class="text-xs opacity-60 font-mono">{{ p.slug }}</div>
            </td>
            <td>{{ p.base_city }}</td>
            <td>{{ p.coverage_areas?.length ?? 0 }} cities</td>
            <td>
              <span :class="['badge badge-sm', statusBadge(p.status)]">{{ p.status }}</span>
            </td>
            <td class="text-right">
              <NuxtLink :to="`/admin/delivery-providers/${p.id}`" class="btn btn-xs btn-ghost rounded-full">
                Review →
              </NuxtLink>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { ProviderStatus } from '~/stores/provider';

definePageMeta({ layout: 'admin', middleware: ['auth', 'admin'] });
useHead({ title: 'Delivery providers — Delivo Admin' });

const store = useAdminDeliveryProviderStore();

const tabs = [
  { label: 'Pending', value: 'PENDING' },
  { label: 'Active', value: 'ACTIVE' },
  { label: 'Rejected', value: 'REJECTED' },
  { label: 'Suspended', value: 'SUSPENDED' },
];

onMounted(() => store.fetchAll('PENDING'));

const statusBadge = (s: ProviderStatus): string => ({
  PENDING: 'badge-warning',
  ACTIVE: 'badge-success',
  REJECTED: 'badge-error',
  SUSPENDED: 'badge-error',
}[s]);
</script>
