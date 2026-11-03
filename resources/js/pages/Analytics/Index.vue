<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps<{
  currentPeriod: number;
  metrics: {
    total_leads: number;
    won_leads: number;
    lost_leads: number;
    conversion_rate: number;
    won_revenue: number;
    pipeline_value: number;
    avg_deal_value: number;
    quote_acceptance_rate: number;
    currency: string;
  };
  sourceStats: {
    name: string;
    color: string;
    total_leads: number;
    won_leads: number;
    won_revenue: number;
    conversion_rate: number;
  }[];
  teamStats: {
    id: number;
    name: string;
    role: string;
    assigned_leads: number;
    won_leads: number;
    won_revenue: number;
    win_rate: number;
  }[];
}>();

const setPeriod = (days: number) => {
  router.get('/analytics', { period: days }, { preserveState: true, replace: true });
};

const formatMoney = (amount: number) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: props.metrics.currency || 'UGX',
    maximumFractionDigits: 0,
  }).format(amount || 0);
};
</script>

<template>
  <Head title="Analytics" />
  <AppLayout>
    <div class="space-y-5">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-xl font-bold text-[#242424]">Sales Analytics & Reports</h1>
          <p class="text-xs text-[#6B6578] mt-0.5">Review revenue growth, lead source conversion, and team performance.</p>
        </div>

        <!-- Period Selector -->
        <div class="flex flex-wrap bg-white p-1 rounded-md border border-[#E5E0EC] gap-1 self-start sm:self-auto">
          <button
            v-for="p in [
              { days: 30, label: 'Last 30 Days' },
              { days: 90, label: 'Last 90 Days' },
              { days: 365, label: 'Last 12 Months' },
            ]"
            :key="p.days"
            type="button"
            @click="setPeriod(p.days)"
            class="px-3 py-1.5 rounded text-xs font-medium transition-colors"
            :class="
              currentPeriod === p.days
                ? 'bg-[#280047] text-white'
                : 'text-[#6B6578] hover:text-[#242424]'
            "
          >
            {{ p.label }}
          </button>
        </div>
      </div>

      <!-- KPI Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="app-card p-4 sm:p-5 border-t-2 border-t-[#280047]">
          <span class="text-xs font-medium text-[#6B6578]">Closed-Won Revenue</span>
          <p class="text-lg sm:text-xl font-bold text-[#242424] font-mono-num mt-1.5 break-all sm:break-normal">
            {{ formatMoney(metrics.won_revenue) }}
          </p>
          <p class="text-[11px] text-[#6B6578] mt-1">
            From <strong class="text-[#242424]">{{ metrics.won_leads }}</strong> won deals
          </p>
        </div>

        <div class="app-card p-4 sm:p-5 border-t-2 border-t-[#F74D01]">
          <span class="text-xs font-medium text-[#6B6578]">Lead Conversion Rate</span>
          <p class="text-lg sm:text-xl font-bold text-[#242424] font-mono-num mt-1.5">
            {{ metrics.conversion_rate }}%
          </p>
          <p class="text-[11px] text-[#6B6578] mt-1">
            Out of <strong class="text-[#242424]">{{ metrics.total_leads }}</strong> total leads
          </p>
        </div>

        <div class="app-card p-4 sm:p-5 border-t-2 border-t-[#280047]">
          <span class="text-xs font-medium text-[#6B6578]">Average Deal Size</span>
          <p class="text-lg sm:text-xl font-bold text-[#242424] font-mono-num mt-1.5 break-all sm:break-normal">
            {{ formatMoney(metrics.avg_deal_value) }}
          </p>
          <p class="text-[11px] text-[#6B6578] mt-1">Average value per won opportunity</p>
        </div>

        <div class="app-card p-4 sm:p-5 border-t-2 border-t-[#C3B6D3]">
          <span class="text-xs font-medium text-[#6B6578]">Quotation Acceptance</span>
          <p class="text-lg sm:text-xl font-bold text-[#242424] font-mono-num mt-1.5">
            {{ metrics.quote_acceptance_rate }}%
          </p>
          <p class="text-[11px] text-[#6B6578] mt-1">Percentage of sent quotes approved</p>
        </div>
      </div>

      <!-- Breakdown Tables -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <!-- Performance by Lead Source -->
        <div class="app-card overflow-hidden">
          <div class="px-4 sm:px-5 py-4 border-b border-[#E5E0EC]">
            <h2 class="text-sm font-bold text-[#242424]">Performance by Lead Source</h2>
            <p class="text-xs text-[#6B6578]">Which acquisition channels generate the most revenue</p>
          </div>

          <!-- Mobile Stacked Source Cards (< 640px) — Zero Content Loss -->
          <div class="sm:hidden divide-y divide-[#E5E0EC] text-xs">
            <div
              v-for="src in sourceStats"
              :key="`mob-src-${src.name}`"
              class="p-4 space-y-2 hover:bg-[#FAFAFB]"
            >
              <div class="flex items-center justify-between gap-2">
                <span class="font-bold text-[#242424]">{{ src.name }}</span>
                <span class="font-mono-num font-bold text-[#280047]">{{ formatMoney(src.won_revenue) }}</span>
              </div>
              <div class="grid grid-cols-3 gap-2 pt-2 border-t border-[#F0EDF5]">
                <div>
                  <p class="text-[10px] uppercase tracking-wider text-[#6B6578]">Leads</p>
                  <p class="font-mono-num text-[#242424] mt-0.5">{{ src.total_leads }}</p>
                </div>
                <div>
                  <p class="text-[10px] uppercase tracking-wider text-[#6B6578]">Won</p>
                  <p class="font-mono-num text-[#242424] mt-0.5">{{ src.won_leads }}</p>
                </div>
                <div>
                  <p class="text-[10px] uppercase tracking-wider text-[#6B6578]">Win Rate</p>
                  <p class="font-mono-num font-semibold text-[#F74D01] mt-0.5">{{ src.conversion_rate }}%</p>
                </div>
              </div>
            </div>
          </div>

          <div class="hidden sm:block overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
              <thead>
                <tr class="bg-[#F8F6FA] border-b border-[#E5E0EC] text-[11px] font-semibold uppercase text-[#6B6578]">
                  <th class="px-4 py-3">Channel</th>
                  <th class="px-4 py-3 text-right">Leads</th>
                  <th class="px-4 py-3 text-right">Won</th>
                  <th class="px-4 py-3 text-right">Win Rate</th>
                  <th class="px-4 py-3 text-right">Revenue</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#E5E0EC]">
                <tr v-for="src in sourceStats" :key="src.name" class="hover:bg-[#FAFAFB]">
                  <td class="px-4 py-3 font-semibold text-[#242424]">{{ src.name }}</td>
                  <td class="px-4 py-3 text-right font-mono-num">{{ src.total_leads }}</td>
                  <td class="px-4 py-3 text-right font-mono-num">{{ src.won_leads }}</td>
                  <td class="px-4 py-3 text-right font-mono-num font-semibold text-[#F74D01]">
                    {{ src.conversion_rate }}%
                  </td>
                  <td class="px-4 py-3 text-right font-mono-num font-bold text-[#280047]">
                    {{ formatMoney(src.won_revenue) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Team Performance -->
        <div class="app-card overflow-hidden">
          <div class="px-4 sm:px-5 py-4 border-b border-[#E5E0EC]">
            <h2 class="text-sm font-bold text-[#242424]">Team Performance</h2>
            <p class="text-xs text-[#6B6578]">Individual conversion rates, assigned leads, and closed revenue</p>
          </div>

          <!-- Mobile Stacked Team Cards (< 640px) — Zero Content Loss -->
          <div class="sm:hidden divide-y divide-[#E5E0EC] text-xs">
            <div
              v-for="member in teamStats"
              :key="`mob-team-${member.id}`"
              class="p-4 space-y-2 hover:bg-[#FAFAFB]"
            >
              <div class="flex items-start justify-between gap-2">
                <div>
                  <p class="font-bold text-[#242424]">{{ member.name }}</p>
                  <p class="text-[10px] text-[#6B6578] capitalize">{{ member.role }}</p>
                </div>
                <span class="font-mono-num font-bold text-[#280047]">{{ formatMoney(member.won_revenue) }}</span>
              </div>
              <div class="grid grid-cols-3 gap-2 pt-2 border-t border-[#F0EDF5]">
                <div>
                  <p class="text-[10px] uppercase tracking-wider text-[#6B6578]">Leads</p>
                  <p class="font-mono-num text-[#242424] mt-0.5">{{ member.assigned_leads }}</p>
                </div>
                <div>
                  <p class="text-[10px] uppercase tracking-wider text-[#6B6578]">Won</p>
                  <p class="font-mono-num text-[#242424] mt-0.5">{{ member.won_leads }}</p>
                </div>
                <div>
                  <p class="text-[10px] uppercase tracking-wider text-[#6B6578]">Win Rate</p>
                  <p class="font-mono-num font-semibold text-[#F74D01] mt-0.5">{{ member.win_rate }}%</p>
                </div>
              </div>
            </div>
          </div>

          <div class="hidden sm:block overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
              <thead>
                <tr class="bg-[#F8F6FA] border-b border-[#E5E0EC] text-[11px] font-semibold uppercase text-[#6B6578]">
                  <th class="px-4 py-3">Team Member</th>
                  <th class="px-4 py-3 text-right">Leads</th>
                  <th class="px-4 py-3 text-right">Won</th>
                  <th class="px-4 py-3 text-right">Win Rate</th>
                  <th class="px-4 py-3 text-right">Revenue</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#E5E0EC]">
                <tr v-for="member in teamStats" :key="member.id" class="hover:bg-[#FAFAFB]">
                  <td class="px-4 py-3">
                    <p class="font-semibold text-[#242424]">{{ member.name }}</p>
                    <p class="text-[10px] text-[#6B6578]">{{ member.role }}</p>
                  </td>
                  <td class="px-4 py-3 text-right font-mono-num">{{ member.assigned_leads }}</td>
                  <td class="px-4 py-3 text-right font-mono-num">{{ member.won_leads }}</td>
                  <td class="px-4 py-3 text-right font-mono-num font-semibold text-[#F74D01]">
                    {{ member.win_rate }}%
                  </td>
                  <td class="px-4 py-3 text-right font-mono-num font-bold text-[#280047]">
                    {{ formatMoney(member.won_revenue) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
