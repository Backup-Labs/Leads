<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import CustomSelect from '@/components/CustomSelect.vue';
import { formatCurrencyAmount } from '@/utils/currency';
import type { Lead, PaginatedData, PageProps, PipelineStage, LeadSource, OrganizationMember } from '@/types';

const props = defineProps<{
  leads: PaginatedData<Lead>;
  filters: {
    search?: string;
    stage_id?: string | number;
    source_id?: string | number;
    status?: string;
    assigned_to?: string | number;
  };
  stages: PipelineStage[];
  sources: LeadSource[];
  team: OrganizationMember[];
}>();

const page = usePage<PageProps>();
const currency = computed(() => page.props.auth?.currentOrganization?.currency || 'UGX');

const search = ref(props.filters.search || '');
const stage_id = ref(props.filters.stage_id || '');
const source_id = ref(props.filters.source_id || '');
const assigned_to = ref(props.filters.assigned_to || '');
const status = ref(props.filters.status || '');

const stageFilterOptions = computed(() => [
  { value: '', label: 'All Stages', icon: 'fa-solid fa-layer-group' },
  ...props.stages.map((s) => ({ value: s.id, label: s.name })),
]);

const sourceFilterOptions = computed(() => [
  { value: '', label: 'All Sources', icon: 'fa-solid fa-bullhorn' },
  ...props.sources.map((src) => ({ value: src.id, label: src.name })),
]);

const ownerFilterOptions = computed(() => [
  { value: '', label: 'All Owners', icon: 'fa-regular fa-user' },
  ...props.team.map((m) => ({ value: m.user_id, label: m.user?.name || 'Member' })),
]);

const statusFilterOptions = [
  { value: '', label: 'All Statuses', icon: 'fa-solid fa-filter' },
  { value: 'active', label: 'Active' },
  { value: 'won', label: 'Won' },
  { value: 'lost', label: 'Lost' },
];

const applyFilters = () => {
  router.get(
    '/leads',
    {
      search: search.value || undefined,
      stage_id: stage_id.value || undefined,
      source_id: source_id.value || undefined,
      assigned_to: assigned_to.value || undefined,
      status: status.value || undefined,
    },
    { preserveState: true, replace: true }
  );
};

const clearFilters = () => {
  search.value = '';
  stage_id.value = '';
  source_id.value = '';
  assigned_to.value = '';
  status.value = '';
  applyFilters();
};

let timeout: ReturnType<typeof setTimeout>;
watch(search, () => {
  clearTimeout(timeout);
  timeout = setTimeout(applyFilters, 300);
});

watch([stage_id, source_id, assigned_to, status], applyFilters);

const formatMoney = (amount: number, leadCurrency?: string) => {
  return formatCurrencyAmount(amount, leadCurrency || currency.value);
};

const priorityBadge: Record<string, string> = {
  low: 'text-[#6B6578]',
  medium: 'text-[#280047]',
  high: 'text-[#F74D01] font-semibold',
  urgent: 'text-[#F74D01] font-bold',
};
</script>

<template>
  <Head title="Leads" />
  <AppLayout>
    <div class="space-y-5">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
        <div>
          <h1 class="text-xl font-bold text-brand-dark">All Leads</h1>
          <p class="text-xs text-[#6B6578] mt-0.5">
            Showing <span class="font-semibold text-brand-dark font-mono-num">{{ leads.total }}</span> leads in your workspace
          </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
          <a href="/leads/export" class="btn-secondary text-xs">
            <i class="fa-solid fa-download text-brand-indigo"></i> Export CSV
          </a>
          <Link href="/pipeline" class="btn-secondary text-xs">
            <i class="fa-solid fa-table-columns text-brand-indigo"></i> Pipeline Board
          </Link>
          <Link href="/leads/create" class="btn-primary text-xs">
            <i class="fa-solid fa-plus"></i> Add New Lead
          </Link>
        </div>
      </div>

      <!-- Filter Bar with Custom Dropdowns -->
      <div class="app-card p-3.5">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-2.5">
          <div class="sm:col-span-2 relative">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-[#6B6578] text-xs"></i>
            <input
              v-model="search"
              type="text"
              placeholder="Search name, company, phone, or email..."
              class="app-input pl-8"
            />
          </div>

          <CustomSelect v-model="stage_id" :options="stageFilterOptions" placeholder="All Stages" />

          <CustomSelect v-model="source_id" :options="sourceFilterOptions" placeholder="All Sources" />

          <CustomSelect v-model="assigned_to" :options="ownerFilterOptions" placeholder="All Owners" />

          <div class="flex items-center gap-2">
            <div class="flex-1 min-w-0">
              <CustomSelect v-model="status" :options="statusFilterOptions" placeholder="All Statuses" align="right" />
            </div>
            <button
              v-if="search || stage_id || source_id || assigned_to || status"
              type="button"
              @click="clearFilters"
              title="Reset filters"
              aria-label="Reset filters"
              class="h-9.5 px-2.5 rounded-md border border-[#D5CCE2] text-[#6B6578] hover:text-brand-dark hover:bg-[#F3F1F6] text-xs cursor-pointer shrink-0"
            >
              <i class="fa-solid fa-xmark"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Leads Responsive Container (Mobile Card List + Desktop Table) -->
      <div class="app-card overflow-hidden">
        <!-- Empty State -->
        <div v-if="leads.data.length === 0" class="px-4 sm:px-6 py-12 text-center">
          <div class="w-10 h-10 rounded-md bg-[#F3F1F6] text-brand-indigo flex items-center justify-center mx-auto mb-2.5">
            <i class="fa-solid fa-address-book text-sm"></i>
          </div>
          <p class="font-semibold text-brand-dark text-sm">No matching leads found</p>
          <p class="text-xs text-[#6B6578] mt-0.5 mb-4">Try clearing your filters or add a new lead to get started.</p>
          <Link href="/leads/create" class="btn-primary text-xs">
            <i class="fa-solid fa-plus"></i> Add New Lead
          </Link>
        </div>

        <template v-else>
          <!-- Mobile & Small Tablet Card Presentation (< 768px) — Preserves 100% of Table Columns -->
          <div class="md:hidden divide-y divide-[#E5E0EC]">
            <div
              v-for="lead in leads.data"
              :key="`card-${lead.id}`"
              class="p-4 space-y-3 hover:bg-[#FAFAFB] transition-colors text-xs"
            >
              <div class="flex items-start justify-between gap-2.5">
                <div class="flex items-start gap-2.5 min-w-0">
                  <div class="w-8 h-8 rounded-md bg-brand-indigo text-yellow flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">
                    {{ lead.customer_name.charAt(0).toUpperCase() }}
                  </div>
                  <div class="min-w-0">
                    <Link
                      :href="`/leads/${lead.id}`"
                      class="font-bold text-brand-dark hover:text-brand-orange transition-colors block wrap-break-word text-sm"
                    >
                      {{ lead.customer_name }}
                    </Link>
                    <p class="text-[#6B6578] wrap-break-word">{{ lead.company_name || 'Individual Client' }}</p>
                    <p class="text-[11px] text-[#6B6578] font-mono-num mt-0.5 break-all">{{ lead.phone || lead.email || '—' }}</p>
                  </div>
                </div>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium border bg-[#F3F1F6] text-brand-indigo border-brand-purple shrink-0">
                  {{ lead.stage?.name || 'Active' }}
                </span>
              </div>

              <div class="grid grid-cols-2 gap-2.5 pt-2.5 border-t border-[#F3F1F6]">
                <div>
                  <p class="text-[10px] uppercase tracking-wider text-[#6B6578]">Service & Source</p>
                  <p class="font-medium text-brand-dark mt-0.5">{{ lead.requested_service || 'General Inquiry' }}</p>
                  <p class="text-[11px] text-[#6B6578]">
                    <i class="fa-solid fa-tag text-[9px] text-brand-purple mr-1"></i>{{ lead.source?.name || 'Direct' }}
                  </p>
                </div>
                <div>
                  <p class="text-[10px] uppercase tracking-wider text-[#6B6578]">Deal Value</p>
                  <p class="font-mono-num font-bold text-brand-indigo text-sm mt-0.5">
                    {{ formatMoney(Number(lead.estimated_value), lead.currency) }}
                  </p>
                  <p class="text-[11px] capitalize flex items-center gap-1 mt-0.5" :class="priorityBadge[lead.priority]">
                    <i class="fa-solid fa-circle text-[6px]"></i> {{ lead.priority }} Priority
                  </p>
                </div>
              </div>

              <div class="pt-2.5 border-t border-[#F3F1F6] flex flex-wrap items-center justify-between gap-2">
                <span class="text-[11px] text-[#6B6578]">
                  Owner: <strong class="text-brand-dark">{{ lead.assigned_to?.name || 'Unassigned' }}</strong>
                </span>
                <div class="flex flex-wrap items-center gap-1.5">
                  <Link
                    :href="`/quotations/create?lead_id=${lead.id}`"
                    class="px-2.5 py-1.5 rounded border border-[#E5E0EC] text-brand- hover:bg-[#F3F1F6] text-[11px] font-semibold"
                  >
                    <i class="fa-solid fa-file-invoice mr-1"></i> Quote
                  </Link>
                  <Link
                    :href="`/leads/${lead.id}`"
                    class="px-3 py-1.5 rounded bg-brand-indigo text-white hover:bg-[#3a0066] text-[11px] font-semibold"
                  >
                    View Lead
                  </Link>
                </div>
              </div>
            </div>
          </div>

          <!-- Tablet & Desktop Full Data Table (>= 768px) -->
          <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-[#F8F6FA] border-b border-[#E5E0EC] text-[11px] font-semibold uppercase tracking-wider text-[#6B6578]">
                  <th class="px-4 py-3">Contact & Company</th>
                  <th class="px-4 py-3">Service & Source</th>
                  <th class="px-4 py-3">Stage</th>
                  <th class="px-4 py-3">Deal Value</th>
                  <th class="px-4 py-3">Priority</th>
                  <th class="px-4 py-3">Owner</th>
                  <th class="px-4 py-3 text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#E5E0EC] text-xs">
                <tr
                  v-for="lead in leads.data"
                  :key="lead.id"
                  class="hover:bg-[#FAFAFB] transition-colors"
                >
                  <td class="px-4 py-3.5">
                    <div class="flex items-start gap-2.5">
                      <div class="w-8 h-8 rounded-md bg-brand-indigo text-brand-yellow flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">
                        {{ lead.customer_name.charAt(0).toUpperCase() }}
                      </div>
                      <div class="min-w-0">
                        <Link
                          :href="`/leads/${lead.id}`"
                          class="font-semibold text-brand-dark hover:text-brand-orange transition-colors block"
                        >
                          {{ lead.customer_name }}
                        </Link>
                        <p class="text-[#6B6578]">{{ lead.company_name || 'Individual Client' }}</p>
                        <p class="text-[11px] text-[#6B6578] font-mono-num mt-0.5">{{ lead.phone || lead.email || '—' }}</p>
                      </div>
                    </div>
                  </td>

                  <td class="px-4 py-3.5">
                    <p class="font-medium text-brand-dark">{{ lead.requested_service || 'General Inquiry' }}</p>
                    <span class="inline-flex items-center gap-1 text-[11px] text-[#6B6578] mt-0.5">
                      <i class="fa-solid fa-tag text-[9px] text-brand-purple"></i>
                      {{ lead.source?.name || 'Direct' }}
                    </span>
                  </td>

                  <td class="px-4 py-3.5">
                    <span
                      class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium border bg-[#F3F1F6] text-brand-indigo border-brand-purple"
                    >
                      {{ lead.stage?.name || 'Active' }}
                    </span>
                  </td>

                  <td class="px-4 py-3.5 font-mono-num font-semibold text-brand-dark">
                    {{ formatMoney(Number(lead.estimated_value), lead.currency) }}
                  </td>

                  <td class="px-4 py-3.5">
                    <span class="capitalize flex items-center gap-1.5" :class="priorityBadge[lead.priority]">
                      <i class="fa-solid fa-circle text-[6px]"></i>
                      {{ lead.priority }}
                    </span>
                  </td>

                  <td class="px-4 py-3.5 text-brand-dark">
                    <span class="font-medium">{{ lead.assigned_to?.name || 'Unassigned' }}</span>
                  </td>

                  <td class="px-4 py-3.5 text-right">
                    <div class="inline-flex items-center gap-1.5">
                      <Link
                        :href="`/quotations/create?lead_id=${lead.id}`"
                        class="px-2 py-1 rounded border border-[#E5E0EC] text-brand-indigo hover:bg-[#F3F1F6] text-[11px] font-medium"
                        title="Create Quotation"
                      >
                        <i class="fa-solid fa-file-invoice"></i> Quote
                      </Link>
                      <Link
                        :href="`/leads/${lead.id}`"
                        class="px-2.5 py-1 rounded bg-brand-indigo text-white hover:bg-[#3a0066] text-[11px] font-medium"
                      >
                        View
                      </Link>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </template>

        <!-- Pagination -->
        <div
          v-if="leads.last_page > 1"
          class="px-4 py-3 border-t border-[#E5E0EC] flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-[#6B6578] bg-[#FAFAFB]"
        >
          <span>Showing page {{ leads.current_page }} of {{ leads.last_page }} ({{ leads.total }} total)</span>
          <div class="flex flex-wrap justify-center gap-1">
            <Link
              v-for="link in leads.links"
              :key="link.label"
              :href="link.url || '#'"
              class="px-2.5 py-1 rounded border text-xs font-medium transition-colors"
              :class="[
                link.active
                  ? 'bg-brand-indigo text-white border-brand-indigo'
                  : 'bg-white text-brand-dark border-[#E5E0EC] hover:bg-[#F3F1F6]',
                !link.url && 'opacity-40 pointer-events-none',
              ]"
              v-html="link.label"
            />
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
