<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { RecoveryAlert, PaginatedData } from '@/types';

const props = defineProps<{
  alerts: PaginatedData<RecoveryAlert>;
  totalRevenueAtRisk: number;
  counts: {
    critical: number;
    warning: number;
    total: number;
  };
  currency: string;
  filters: {
    type?: string;
    severity?: string;
  };
}>();

const runScan = () => {
  router.post('/recovery/scan', {}, { preserveScroll: true });
};

const dismissAlert = (id: number) => {
  router.patch(`/recovery/${id}/dismiss`, {}, { preserveScroll: true });
};

const formatMoney = (amount: number) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: props.currency || 'UGX',
    maximumFractionDigits: 0,
  }).format(amount || 0);
};
</script>

<template>
  <Head title="Revenue Recovery" />
  <AppLayout>
    <div class="space-y-5">
      <!-- Page Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-xl font-bold text-[#242424]">Revenue Recovery</h1>
          <p class="text-xs text-[#6B6578] mt-0.5">
            Identify neglected deals, overdue quotations, and stalled leads before revenue is lost.
          </p>
        </div>
        <div class="flex items-center gap-2">
          <Link href="/follow-ups?status=overdue" class="btn-secondary text-xs">
            <i class="fa-regular fa-clock text-[#F74D01]"></i> Overdue Follow-ups
          </Link>
          <button type="button" @click="runScan" class="btn-primary text-xs">
            <i class="fa-solid fa-rotate"></i> Scan Pipeline
          </button>
        </div>
      </div>

      <!-- Summary Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="app-card p-5 border-t-2 border-t-[#F74D01]">
          <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-medium text-[#6B6578]">Total Revenue at Risk</span>
            <div class="w-8 h-8 rounded-md bg-[#F74D01]/10 text-[#F74D01] flex items-center justify-center">
              <i class="fa-solid fa-triangle-exclamation text-xs"></i>
            </div>
          </div>
          <p class="text-2xl font-bold text-[#242424] font-mono-num">
            {{ formatMoney(totalRevenueAtRisk) }}
          </p>
          <p class="text-[11px] text-[#6B6578] mt-1">
            Across <strong class="text-[#242424]">{{ counts.total }}</strong> active opportunities
          </p>
        </div>

        <div class="app-card p-5 border-t-2 border-t-[#280047]">
          <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-medium text-[#6B6578]">Critical Alerts</span>
            <div class="w-8 h-8 rounded-md bg-[#F3F1F6] text-[#280047] flex items-center justify-center">
              <i class="fa-solid fa-bolt text-xs"></i>
            </div>
          </div>
          <p class="text-2xl font-bold text-[#242424] font-mono-num">{{ counts.critical }}</p>
          <p class="text-[11px] text-[#6B6578] mt-1">
            Plus <strong class="text-[#242424]">{{ counts.warning }}</strong> warning-level alerts
          </p>
        </div>

        <div class="app-card p-5 border-t-2 border-t-[#C3B6D3]">
          <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-medium text-[#6B6578]">Recommended Action</span>
            <div class="w-8 h-8 rounded-md bg-[#FDFEBA] text-[#280047] flex items-center justify-center">
              <i class="fa-solid fa-phone-volume text-xs"></i>
            </div>
          </div>
          <p class="text-sm font-bold text-[#280047] mt-1">Follow up within 24 hours</p>
          <p class="text-[11px] text-[#6B6578] mt-1">Contacting stalled leads increases close rate by up to 35%</p>
        </div>
      </div>

      <!-- At-Risk Deals List -->
      <div class="app-card overflow-hidden">
        <div class="px-5 py-4 border-b border-[#E5E0EC] flex items-center justify-between">
          <div>
            <h2 class="text-sm font-bold text-[#242424]">Deals Requiring Follow-up</h2>
            <p class="text-xs text-[#6B6578]">Sorted by urgency and potential deal value</p>
          </div>
          <span class="px-2.5 py-1 rounded text-xs font-semibold bg-[#F3F1F6] text-[#280047] font-mono-num">
            {{ alerts.total }} Alerts
          </span>
        </div>

        <div v-if="alerts.data.length === 0" class="p-12 text-center">
          <div class="w-10 h-10 rounded-md bg-emerald-50 text-emerald-700 flex items-center justify-center mx-auto mb-2.5">
            <i class="fa-solid fa-check text-sm"></i>
          </div>
          <p class="text-sm font-semibold text-[#242424]">All deals are up to date</p>
          <p class="text-xs text-[#6B6578] mt-0.5">No neglected leads or overdue quotations detected.</p>
        </div>

        <div v-else class="divide-y divide-[#E5E0EC]">
          <div
            v-for="item in alerts.data"
            :key="item.id"
            class="p-4 sm:p-5 hover:bg-[#FAFAFB] transition-colors flex flex-col lg:flex-row lg:items-center justify-between gap-4"
          >
            <div class="space-y-1.5">
              <div class="flex flex-wrap items-center gap-2">
                <span
                  class="px-2 py-0.5 rounded text-[10px] font-bold uppercase"
                  :class="
                    item.severity === 'critical'
                      ? 'bg-[#F74D01] text-white'
                      : 'bg-[#FDFEBA] text-[#280047] border border-[#C3B6D3]'
                  "
                >
                  {{ item.severity }}
                </span>
                <Link
                  v-if="item.lead"
                  :href="`/leads/${item.lead.id}`"
                  class="text-sm font-bold text-[#242424] hover:text-[#F74D01] transition-colors"
                >
                  {{ item.lead.customer_name }}
                </Link>
                <span v-if="item.lead?.stage" class="px-2 py-0.5 rounded bg-[#F3F1F6] text-[#280047] text-[10px] font-semibold">
                  {{ item.lead.stage.name }}
                </span>
              </div>

              <p class="text-xs font-medium text-[#F74D01]">{{ item.reason }}</p>

              <p class="text-xs text-[#6B6578]">
                <strong class="text-[#280047]">Suggested Action:</strong> {{ item.suggested_action }}
              </p>
            </div>

            <div class="flex items-center justify-between lg:justify-end gap-4 shrink-0 pt-3 lg:pt-0 border-t lg:border-t-0 border-[#E5E0EC]">
              <div class="lg:text-right">
                <p class="text-sm font-bold text-[#280047] font-mono-num">
                  {{ formatMoney(Number(item.potential_revenue_at_risk)) }}
                </p>
                <p class="text-[11px] text-[#6B6578]">
                  Owner: <strong class="text-[#242424]">{{ item.lead?.assigned_to?.name || 'Unassigned' }}</strong>
                </p>
              </div>

              <div class="flex items-center gap-2">
                <button
                  type="button"
                  @click="dismissAlert(item.id)"
                  class="btn-secondary text-xs py-1.5 px-2.5"
                  title="Dismiss Alert"
                >
                  Dismiss
                </button>
                <Link
                  v-if="item.lead"
                  :href="`/leads/${item.lead.id}`"
                  class="btn-indigo text-xs py-1.5 px-3"
                >
                  Open Lead <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
