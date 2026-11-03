<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import CustomSelect from '@/components/CustomSelect.vue';
import { formatCurrencyAmount } from '@/utils/currency';
import type { PipelineStage, PageProps } from '@/types';

const props = defineProps<{
  stages: PipelineStage[];
}>();

const page = usePage<PageProps>();
const currency = computed(() => page.props.auth?.currentOrganization?.currency || 'UGX');

const stageOptions = computed(() =>
  props.stages.map((st) => ({
    value: st.id,
    label: st.name,
  }))
);

const moveStage = (leadId: number, stageId: number) => {
  router.patch(`/leads/${leadId}/stage`, { pipeline_stage_id: Number(stageId) }, { preserveScroll: true });
};

const formatMoney = (amount: number, leadCurrency?: string) => {
  return formatCurrencyAmount(amount, leadCurrency || currency.value);
};

const stageSum = (stage: PipelineStage) => {
  return (stage.leads || []).reduce((acc, l) => acc + Number(l.estimated_value || 0), 0);
};
</script>

<template>
  <Head title="Sales Pipeline" />
  <AppLayout>
    <div class="space-y-5">
      <!-- Page Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-xl font-bold text-[#242424]">Sales Pipeline</h1>
          <p class="text-xs text-[#6B6578] mt-0.5">Track leads across stages and move deals forward.</p>
        </div>
        <div class="flex items-center gap-2.5">
          <Link href="/leads" class="btn-secondary text-xs">
            <i class="fa-solid fa-list text-[#280047]"></i> Table View
          </Link>
          <Link href="/leads/create" class="btn-primary text-xs">
            <i class="fa-solid fa-plus"></i> Add New Lead
          </Link>
        </div>
      </div>

      <!-- Horizontal Pipeline Board -->
      <div class="flex gap-4 overflow-x-auto pb-4">
        <div
          v-for="stage in stages"
          :key="stage.id"
          class="w-72 shrink-0 flex flex-col bg-[#F4F2F7] rounded-lg border border-[#E5E0EC]"
        >
          <!-- Column Header -->
          <div class="p-3.5 border-b border-[#E5E0EC] bg-white rounded-t-lg">
            <div class="flex items-center justify-between">
              <h2 class="text-xs font-bold uppercase tracking-wider text-[#280047]">
                {{ stage.name }}
              </h2>
              <span class="px-2 py-0.5 rounded text-[11px] font-bold font-mono-num bg-[#F3F1F6] text-[#280047]">
                {{ stage.leads?.length || 0 }}
              </span>
            </div>
            <p class="text-xs font-semibold text-[#6B6578] font-mono-num mt-1">
              {{ formatMoney(stageSum(stage)) }}
            </p>
          </div>

          <!-- Cards List -->
          <div class="p-2.5 space-y-2.5 flex-1 min-h-[420px]">
            <div
              v-for="lead in stage.leads || []"
              :key="lead.id"
              class="app-card p-3.5 hover:border-[#280047] transition-colors flex flex-col justify-between gap-2.5"
            >
              <div>
                <div class="flex items-start justify-between gap-2">
                  <Link
                    :href="`/leads/${lead.id}`"
                    class="text-xs font-bold text-[#242424] hover:text-[#F74D01] transition-colors leading-snug"
                  >
                    {{ lead.customer_name }}
                  </Link>
                  <span
                    class="px-1.5 py-0.5 rounded bg-[#F3F1F6] text-[#280047] text-[10px] font-semibold uppercase shrink-0"
                  >
                    {{ lead.priority }}
                  </span>
                </div>

                <p class="text-[11px] text-[#6B6578] truncate mt-0.5">
                  {{ lead.company_name || lead.requested_service || 'Direct Inquiry' }}
                </p>

                <p class="text-sm font-bold text-[#280047] font-mono-num mt-2">
                  {{ formatMoney(Number(lead.estimated_value), lead.currency) }}
                </p>
              </div>

              <div class="pt-2.5 border-t border-[#E5E0EC] flex items-center justify-between gap-2">
                <span class="text-[11px] text-[#6B6578] truncate">
                  <i class="fa-regular fa-user text-[10px] mr-1"></i>
                  {{ lead.assigned_to?.name?.split(' ')[0] || 'Unassigned' }}
                </span>

                <div class="w-36">
                  <CustomSelect
                    :model-value="stage.id"
                    :options="stageOptions"
                    size="sm"
                    align="right"
                    @change="(val: number) => moveStage(lead.id, val)"
                  />
                </div>
              </div>
            </div>

            <div
              v-if="!stage.leads || stage.leads.length === 0"
              class="h-32 rounded-md border border-dashed border-[#D5CCE2] flex items-center justify-center text-xs text-[#6B6578]"
            >
              No leads in this stage
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
