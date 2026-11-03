<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import CustomSelect from '@/components/CustomSelect.vue';
import { CURRENCY_OPTIONS, convertCurrencyAmount, formatCurrencyAmount } from '@/utils/currency';
import type { PageProps, PipelineStage, LeadSource, OrganizationMember } from '@/types';

const props = defineProps<{
  stages: PipelineStage[];
  sources: LeadSource[];
  team: OrganizationMember[];
  priorities: { value: string; label: string }[];
}>();

const page = usePage<PageProps>();
const orgCurrency = computed(() => page.props.auth?.currentOrganization?.currency || 'UGX');

const currentStep = ref(1);
const totalSteps = 3;

const steps = [
  { number: 1, title: 'Contact Details' },
  { number: 2, title: 'Service & Value' },
  { number: 3, title: 'Assignment & Notes' },
];

const form = useForm({
  customer_name: '',
  company_name: '',
  phone: '',
  email: '',
  lead_source_id: props.sources[0]?.id || '',
  pipeline_stage_id: props.stages[0]?.id || '',
  requested_service: '',
  estimated_value: 0,
  currency: orgCurrency.value,
  priority: 'medium',
  assigned_to_user_id: page.props.auth?.user?.id || '',
  description: '',
});

const sourceOptions = computed(() =>
  props.sources.map((s) => ({
    value: s.id,
    label: s.name,
    icon: 'fa-solid fa-bullhorn',
  }))
);

const stageOptions = computed(() =>
  props.stages.map((st) => ({
    value: st.id,
    label: st.name,
    icon: 'fa-solid fa-layer-group',
  }))
);

const teamOptions = computed(() => [
  { value: '', label: 'Unassigned', icon: 'fa-regular fa-user' },
  ...props.team.map((m) => ({
    value: m.user_id,
    label: m.user?.name || 'Team Member',
    sublabel: m.user?.email,
    icon: 'fa-solid fa-user-tie',
  })),
]);

const handleCurrencyChange = (newCurr: string) => {
  const prevCurr = form.currency || orgCurrency.value;
  if (newCurr && newCurr !== prevCurr) {
    form.estimated_value = convertCurrencyAmount(Number(form.estimated_value || 0), prevCurr, newCurr);
    form.currency = newCurr;
  }
};

const canAdvanceStep1 = computed(() => form.customer_name.trim().length > 1);

const nextStep = () => {
  if (currentStep.value < totalSteps) {
    currentStep.value++;
  }
};

const prevStep = () => {
  if (currentStep.value > 1) {
    currentStep.value--;
  }
};

const submit = () => {
  form.post('/leads');
};

const formatMoney = (amount: number) => {
  return formatCurrencyAmount(amount, form.currency);
};

const selectedSourceName = computed(() => {
  const found = props.sources.find((s) => s.id === Number(form.lead_source_id));
  return found?.name || 'Direct';
});

const selectedStageName = computed(() => {
  const found = props.stages.find((s) => s.id === Number(form.pipeline_stage_id));
  return found?.name || 'Initial Stage';
});
</script>

<template>
  <Head title="Add New Lead" />
  <AppLayout>
    <div class="max-w-5xl mx-auto space-y-6">
      <!-- Page Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-[#E5E0EC]">
        <div>
          <div class="flex items-center gap-2 text-xs text-[#6B6578] mb-1">
            <Link href="/leads" class="hover:text-[#280047] transition-colors flex items-center gap-1.5 font-medium">
              <i class="fa-solid fa-arrow-left text-[10px]"></i> Leads
            </Link>
            <span>/</span>
            <span class="text-[#242424] font-medium">New Lead</span>
          </div>
          <h1 class="text-xl font-bold text-[#242424]">Add New Lead</h1>
          <p class="text-xs text-[#6B6578] mt-0.5">Complete the 3 steps below to register a new lead and assign an owner.</p>
        </div>

        <!-- Step Indicator -->
        <div class="grid grid-cols-1 min-[380px]:grid-cols-3 sm:flex items-center gap-1.5 bg-white p-1.5 rounded-lg border border-[#E5E0EC] w-full sm:w-auto">
          <button
            v-for="step in steps"
            :key="step.number"
            type="button"
            @click="step.number < currentStep ? (currentStep = step.number) : null"
            class="flex items-center gap-2 px-2.5 sm:px-3 py-1.5 rounded-md text-xs font-medium transition-colors"
            :class="
              currentStep === step.number
                ? 'bg-[#280047] text-white'
                : currentStep > step.number
                  ? 'bg-[#280047]/5 text-[#280047] hover:bg-[#280047]/10 cursor-pointer'
                  : 'text-[#6B6578] cursor-default'
            "
          >
            <span
              class="w-5 h-5 rounded flex items-center justify-center text-[11px] font-bold shrink-0"
              :class="
                currentStep === step.number
                  ? 'bg-[#F74D01] text-white'
                  : currentStep > step.number
                    ? 'bg-[#280047] text-white'
                    : 'bg-[#F3F1F6] text-[#6B6578]'
              "
            >
              <i v-if="currentStep > step.number" class="fa-solid fa-check text-[9px]"></i>
              <span v-else>{{ step.number }}</span>
            </span>
            <span class="truncate">{{ step.title }}</span>
          </button>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Form Card -->
        <div class="lg:col-span-2">
          <form @submit.prevent="submit" class="app-card p-4 sm:p-6">
            <!-- STEP 1: CONTACT DETAILS -->
            <div v-if="currentStep === 1" class="space-y-5">
              <div class="pb-4 border-b border-[#E5E0EC] flex items-center justify-between">
                <div>
                  <span class="text-[11px] font-semibold uppercase tracking-wider text-[#F74D01]">Step 1 of 3</span>
                  <h2 class="text-base font-bold text-[#242424] mt-0.5">Contact Information</h2>
                  <p class="text-xs text-[#6B6578]">Enter the client's name, company, and primary contact details.</p>
                </div>
                <div class="w-9 h-9 rounded-md bg-[#F3F1F6] text-[#280047] flex items-center justify-center">
                  <i class="fa-solid fa-address-card text-sm"></i>
                </div>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-medium text-[#242424] mb-1.5">
                    Contact Full Name <span class="text-[#F74D01]">*</span>
                  </label>
                  <div class="relative">
                    <i class="fa-regular fa-user absolute left-3 top-1/2 -translate-y-1/2 text-[#6B6578] text-xs"></i>
                    <input
                      v-model="form.customer_name"
                      type="text"
                      required
                      placeholder="e.g. Grace Nakato"
                      class="app-input pl-8"
                    />
                  </div>
                  <p v-if="form.errors.customer_name" class="text-xs text-red-600 mt-1">{{ form.errors.customer_name }}</p>
                </div>

                <div>
                  <label class="block text-xs font-medium text-[#242424] mb-1.5">Company / Organization</label>
                  <div class="relative">
                    <i class="fa-regular fa-building absolute left-3 top-1/2 -translate-y-1/2 text-[#6B6578] text-xs"></i>
                    <input
                      v-model="form.company_name"
                      type="text"
                      placeholder="e.g. Crest Logistics Ltd"
                      class="app-input pl-8"
                    />
                  </div>
                </div>

                <div>
                  <label class="block text-xs font-medium text-[#242424] mb-1.5">Phone / WhatsApp Number</label>
                  <div class="relative">
                    <i class="fa-solid fa-phone absolute left-3 top-1/2 -translate-y-1/2 text-[#6B6578] text-xs"></i>
                    <input
                      v-model="form.phone"
                      type="text"
                      placeholder="+256 772 000000"
                      class="app-input pl-8 font-mono-num"
                    />
                  </div>
                  <p v-if="form.errors.phone" class="text-xs text-red-600 mt-1">{{ form.errors.phone }}</p>
                </div>

                <div>
                  <label class="block text-xs font-medium text-[#242424] mb-1.5">Email Address</label>
                  <div class="relative">
                    <i class="fa-regular fa-envelope absolute left-3 top-1/2 -translate-y-1/2 text-[#6B6578] text-xs"></i>
                    <input
                      v-model="form.email"
                      type="email"
                      placeholder="grace@company.com"
                      class="app-input pl-8"
                    />
                  </div>
                  <p v-if="form.errors.email" class="text-xs text-red-600 mt-1">{{ form.errors.email }}</p>
                </div>
              </div>

              <div class="flex flex-wrap items-center justify-between gap-2 pt-4 border-t border-[#E5E0EC]">
                <Link href="/leads" class="btn-secondary">Cancel</Link>
                <button
                  type="button"
                  :disabled="!canAdvanceStep1"
                  @click="nextStep"
                  class="btn-indigo disabled:opacity-40"
                >
                  Continue to Service & Value <i class="fa-solid fa-arrow-right text-xs"></i>
                </button>
              </div>
            </div>

            <!-- STEP 2: SERVICE & VALUE -->
            <div v-if="currentStep === 2" class="space-y-5">
              <div class="pb-4 border-b border-[#E5E0EC] flex items-center justify-between gap-3">
                <div>
                  <span class="text-[11px] font-semibold uppercase tracking-wider text-[#F74D01]">Step 2 of 3</span>
                  <h2 class="text-base font-bold text-[#242424] mt-0.5">Service Interest & Deal Value</h2>
                  <p class="text-xs text-[#6B6578]">Select how this lead found you, initial stage, and potential revenue.</p>
                </div>
                <div class="w-9 h-9 rounded-md bg-[#F3F1F6] text-[#280047] flex items-center justify-center shrink-0">
                  <i class="fa-solid fa-briefcase text-sm"></i>
                </div>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-medium text-[#242424] mb-1.5">Lead Source</label>
                  <CustomSelect
                    v-model="form.lead_source_id"
                    :options="sourceOptions"
                    placeholder="Select lead source"
                  />
                </div>

                <div>
                  <label class="block text-xs font-medium text-[#242424] mb-1.5">Pipeline Stage</label>
                  <CustomSelect
                    v-model="form.pipeline_stage_id"
                    :options="stageOptions"
                    placeholder="Select stage"
                  />
                </div>

                <div class="sm:col-span-2">
                  <label class="block text-xs font-medium text-[#242424] mb-1.5">Service or Product Needed</label>
                  <div class="relative">
                    <i class="fa-solid fa-briefcase absolute left-3 top-1/2 -translate-y-1/2 text-[#6B6578] text-xs"></i>
                    <input
                      v-model="form.requested_service"
                      type="text"
                      placeholder="e.g. Annual Maintenance Contract"
                      class="app-input pl-8"
                    />
                  </div>
                </div>

                <div>
                  <label class="block text-xs font-medium text-[#242424] mb-1.5">Currency (Auto-converts value)</label>
                  <CustomSelect
                    :model-value="form.currency"
                    :options="CURRENCY_OPTIONS"
                    @change="handleCurrencyChange"
                  />
                </div>

                <div>
                  <label class="block text-xs font-medium text-[#242424] mb-1.5">
                    Estimated Value ({{ form.currency }})
                  </label>
                  <div class="relative">
                    <i class="fa-solid fa-coins absolute left-3 top-1/2 -translate-y-1/2 text-[#6B6578] text-xs"></i>
                    <input
                      v-model="form.estimated_value"
                      type="number"
                      min="0"
                      step="any"
                      class="app-input pl-8 font-mono-num font-semibold"
                    />
                  </div>
                </div>
              </div>

              <div class="flex flex-wrap items-center justify-between gap-2 pt-4 border-t border-[#E5E0EC]">
                <button type="button" @click="prevStep" class="btn-secondary">
                  <i class="fa-solid fa-arrow-left text-xs"></i> Back
                </button>
                <button type="button" @click="nextStep" class="btn-indigo">
                  Continue to Assignment <i class="fa-solid fa-arrow-right text-xs"></i>
                </button>
              </div>
            </div>

            <!-- STEP 3: ASSIGNMENT & NOTES -->
            <div v-if="currentStep === 3" class="space-y-5">
              <div class="pb-4 border-b border-[#E5E0EC] flex items-center justify-between gap-3">
                <div>
                  <span class="text-[11px] font-semibold uppercase tracking-wider text-[#F74D01]">Step 3 of 3</span>
                  <h2 class="text-base font-bold text-[#242424] mt-0.5">Priority, Owner & Notes</h2>
                  <p class="text-xs text-[#6B6578]">Set urgency level, assign a team member, and add initial context.</p>
                </div>
                <div class="w-9 h-9 rounded-md bg-[#F3F1F6] text-[#280047] flex items-center justify-center shrink-0">
                  <i class="fa-solid fa-user-check text-sm"></i>
                </div>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-medium text-[#242424] mb-1.5">Priority Level</label>
                  <div class="grid grid-cols-1 min-[320px]:grid-cols-2 gap-2">
                    <button
                      v-for="p in priorities"
                      :key="p.value"
                      type="button"
                      @click="form.priority = p.value"
                      class="p-2.5 rounded-md border text-xs font-medium flex items-center gap-2 transition-colors cursor-pointer"
                      :class="
                        form.priority === p.value
                          ? 'border-[#F74D01] bg-[#F74D01] text-white'
                          : 'border-[#E5E0EC] bg-white text-[#242424] hover:border-[#C3B6D3]'
                      "
                    >
                      <i class="fa-solid fa-flag text-[11px]"></i>
                      {{ p.label }}
                    </button>
                  </div>
                </div>

                <div>
                  <label class="block text-xs font-medium text-[#242424] mb-1.5">Assign Lead Owner</label>
                  <CustomSelect
                    v-model="form.assigned_to_user_id"
                    :options="teamOptions"
                    placeholder="Select team member"
                  />
                </div>
              </div>

              <div>
                <label class="block text-xs font-medium text-[#242424] mb-1.5">Initial Notes & Requirements</label>
                <textarea
                  v-model="form.description"
                  rows="3"
                  placeholder="Write key details from your conversation, timeline expectations, or specific questions..."
                  class="app-input"
                ></textarea>
              </div>

              <div class="flex flex-wrap items-center justify-between gap-2 pt-4 border-t border-[#E5E0EC]">
                <button type="button" @click="prevStep" class="btn-secondary">
                  <i class="fa-solid fa-arrow-left text-xs"></i> Back
                </button>
                <button type="submit" :disabled="form.processing" class="btn-primary">
                  <i class="fa-solid fa-check text-xs"></i>
                  {{ form.processing ? 'Saving Lead...' : 'Save Lead' }}
                </button>
              </div>
            </div>
          </form>
        </div>

        <!-- Lead Summary Card -->
        <div>
          <div class="app-card p-5 sticky top-6">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-[#E5E0EC]">
              <h3 class="text-xs font-bold uppercase tracking-wider text-[#280047]">Lead Summary</h3>
              <span class="px-2 py-0.5 rounded text-[11px] font-medium bg-[#FDFEBA] text-[#280047] border border-[#E5E0EC]">
                Step {{ currentStep }} of 3
              </span>
            </div>

            <div class="space-y-3 text-xs">
              <div>
                <p class="text-[#6B6578]">Contact & Company</p>
                <p class="font-semibold text-[#242424] text-sm mt-0.5">{{ form.customer_name || 'Not entered yet' }}</p>
                <p class="text-[#6B6578]">{{ form.company_name || 'Individual Client' }}</p>
              </div>

              <div class="grid grid-cols-2 gap-2 pt-3 border-t border-[#E5E0EC]">
                <div>
                  <p class="text-[#6B6578]">Phone</p>
                  <p class="font-mono-num font-medium text-[#242424] mt-0.5">{{ form.phone || '—' }}</p>
                </div>
                <div>
                  <p class="text-[#6B6578]">Source</p>
                  <p class="font-medium text-[#242424] mt-0.5">{{ selectedSourceName }}</p>
                </div>
              </div>

              <div class="pt-3 border-t border-[#E5E0EC]">
                <p class="text-[#6B6578]">Stage & Service</p>
                <p class="font-medium text-[#242424] mt-0.5">
                  {{ selectedStageName }} &bull; {{ form.requested_service || 'General Inquiry' }}
                </p>
              </div>

              <div class="pt-3 border-t border-[#E5E0EC] flex items-center justify-between">
                <div>
                  <p class="text-[#6B6578]">Estimated Value</p>
                  <p class="text-base font-bold text-[#280047] font-mono-num mt-0.5">
                    {{ formatMoney(Number(form.estimated_value)) }}
                  </p>
                </div>
                <span
                  class="px-2.5 py-1 rounded text-[11px] font-semibold uppercase"
                  :class="
                    form.priority === 'urgent' || form.priority === 'high'
                      ? 'bg-[#F74D01]/10 text-[#F74D01]'
                      : 'bg-[#F3F1F6] text-[#280047]'
                  "
                >
                  {{ form.priority }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
