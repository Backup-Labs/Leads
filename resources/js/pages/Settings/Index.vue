<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import CustomSelect from '@/components/CustomSelect.vue';
import { CURRENCY_OPTIONS, convertCurrencyAmount } from '@/utils/currency';
import type { Organization, OrganizationSetting } from '@/types';

const props = defineProps<{
  organization: Organization;
  settings: OrganizationSetting | null;
}>();

const activeTab = ref(1);

const form = useForm({
  name: props.organization.name || '',
  billing_email: props.organization.billing_email || '',
  phone: props.organization.phone || '',
  currency: props.organization.currency || 'UGX',
  default_tax_rate: props.settings?.default_tax_rate ?? 18,
  stale_lead_threshold_days: props.settings?.stale_lead_threshold_days ?? 3,
  quote_followup_threshold_days: props.settings?.quote_followup_threshold_days ?? 3,
  high_value_threshold: props.settings?.high_value_threshold ?? 5000000,
  quotation_default_terms:
    props.settings?.quotation_default_terms ||
    'Payment terms: 50% deposit upon acceptance, 50% upon completion.',
});

const handleCurrencyChange = (newCurrency: string) => {
  const prevCurrency = form.currency;
  if (newCurrency && newCurrency !== prevCurrency) {
    form.high_value_threshold = Math.round(
      convertCurrencyAmount(Number(form.high_value_threshold || 0), prevCurrency, newCurrency)
    );
    form.currency = newCurrency;
  }
};

const submit = () => {
  form.patch('/settings', { preserveScroll: true });
};
</script>

<template>
  <Head title="Settings" />
  <AppLayout>
    <div class="max-w-4xl mx-auto space-y-5">
      <!-- Page Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-[#E5E0EC]">
        <div>
          <h1 class="text-xl font-bold text-brand-dark">Workspace Settings</h1>
          <p class="text-xs text-[#6B6578] mt-0.5">Configure your company profile, follow-up rules, and quotation defaults.</p>
        </div>

        <!-- Section Tabs -->
        <div class="flex flex-wrap bg-white p-1 rounded-md border border-[#E5E0EC] gap-1 w-full sm:w-auto">
          <button
            v-for="tab in [
              { id: 1, label: '1. Company Profile' },
              { id: 2, label: '2. Follow-up Rules' },
              { id: 3, label: '3. Quotation Defaults' },
            ]"
            :key="tab.id"
            type="button"
            @click="activeTab = tab.id"
            class="px-3 py-1.5 rounded text-xs font-medium transition-colors cursor-pointer"
            :class="
              activeTab === tab.id
                ? 'bg-brand-indigo text-white'
                : 'text-[#6B6578] hover:text-brand-dark'
            "
          >
            {{ tab.label }}
          </button>
        </div>
      </div>

      <form @submit.prevent="submit" class="app-card p-4 sm:p-6 space-y-5">
        <!-- TAB 1: COMPANY PROFILE -->
        <div v-if="activeTab === 1" class="space-y-4">
          <div class="pb-3 border-b border-[#E5E0EC]">
            <h2 class="text-base font-bold text-brand-dark">Company Profile</h2>
            <p class="text-xs text-[#6B6578]">This information appears on client quotations and reports.</p>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-medium text-brand-dark mb-1.5">Company Name</label>
              <input v-model="form.name" type="text" required class="app-input" />
            </div>
            <div>
              <label class="block text-xs font-medium text-brand-dark mb-1.5">Official Billing Email</label>
              <input v-model="form.billing_email" type="email" class="app-input" />
            </div>
            <div>
              <label class="block text-xs font-medium text-brand-dark mb-1.5">Business Phone</label>
              <input v-model="form.phone" type="text" class="app-input font-mono-num" />
            </div>
            <div>
              <label class="block text-xs font-medium text-brand-dark mb-1.5">
                Workspace Currency <span class="text-brand-orange text-[10px]">(Auto-converts all deals)</span>
              </label>
              <CustomSelect
                :model-value="form.currency"
                :options="CURRENCY_OPTIONS"
                @change="handleCurrencyChange"
              />
            </div>
          </div>
        </div>

        <!-- TAB 2: FOLLOW-UP RULES -->
        <div v-if="activeTab === 2" class="space-y-4">
          <div class="pb-3 border-b border-[#E5E0EC]">
            <h2 class="text-base font-bold text-brand-dark">Automated Follow-up & Recovery Rules</h2>
            <p class="text-xs text-[#6B6578]">Define when leads and quotations are flagged as needing attention.</p>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="p-4 rounded-md bg-[#FAFAFB] border border-[#E5E0EC]">
              <label class="block text-xs font-bold text-brand-dark mb-1">
                Stale Lead Threshold (Days)
              </label>
              <p class="text-[11px] text-[#6B6578] mb-2.5">
                Flag active leads if not contacted within this many days.
              </p>
              <input v-model="form.stale_lead_threshold_days" type="number" min="1" max="90" class="app-input bg-white font-mono-num" />
            </div>

            <div class="p-4 rounded-md bg-[#FAFAFB] border border-[#E5E0EC]">
              <label class="block text-xs font-bold text-brand-dark mb-1">
                Quotation Reminder (Days)
              </label>
              <p class="text-[11px] text-[#6B6578] mb-2.5">
                Flag sent quotations awaiting response after this many days.
              </p>
              <input v-model="form.quote_followup_threshold_days" type="number" min="1" max="30" class="app-input bg-white font-mono-num" />
            </div>

            <div class="p-4 rounded-md bg-[#FAFAFB] border border-[#E5E0EC]">
              <label class="block text-xs font-bold text-brand-dark mb-1">
                High-Value Deal ({{ form.currency }})
              </label>
              <p class="text-[11px] text-[#6B6578] mb-2.5">
                Minimum value to prioritize a deal as critical in Recovery.
              </p>
              <input v-model="form.high_value_threshold" type="number" min="0" step="any" class="app-input bg-white font-mono-num" />
            </div>
          </div>
        </div>

        <!-- TAB 3: QUOTATION DEFAULTS -->
        <div v-if="activeTab === 3" class="space-y-4">
          <div class="pb-3 border-b border-[#E5E0EC]">
            <h2 class="text-base font-bold text-brand-dark">Quotation Defaults</h2>
            <p class="text-xs text-[#6B6578]">Set default tax rates and standard payment terms.</p>
          </div>

          <div class="grid grid-cols-1 gap-4">
            <div class="max-w-xs">
              <label class="block text-xs font-medium text-brand-dark mb-1.5">Default VAT Rate (%)</label>
              <input v-model="form.default_tax_rate" type="number" min="0" max="100" step="any" class="app-input font-mono-num" />
            </div>
            <div>
              <label class="block text-xs font-medium text-brand-dark mb-1.5">Default Payment Terms & Notes</label>
              <textarea v-model="form.quotation_default_terms" rows="4" class="app-input"></textarea>
            </div>
          </div>
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-4 border-t border-[#E5E0EC]">
          <span class="text-xs text-[#6B6578]">Changes apply across your entire workspace.</span>
          <button type="submit" :disabled="form.processing" class="btn-primary self-start sm:self-auto">
            <i class="fa-solid fa-check text-xs"></i>
            {{ form.processing ? 'Saving...' : 'Save Settings' }}
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
