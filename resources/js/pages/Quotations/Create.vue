<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import CustomSelect from '@/components/CustomSelect.vue';
import CustomDatePicker from '@/components/CustomDatePicker.vue';
import { CURRENCY_OPTIONS, convertCurrencyAmount, formatCurrencyAmount } from '@/utils/currency';
import type { Lead } from '@/types';

const props = defineProps<{
  lead: Lead | null;
  defaultTaxRate: number;
  defaultTerms?: string;
  currency: string;
}>();

const currentStep = ref(1);
const activeCurrency = ref(props.currency || 'UGX');

const form = useForm({
  lead_id: props.lead?.id || '',
  customer_name: props.lead?.customer_name || '',
  customer_email: props.lead?.email || '',
  customer_phone: props.lead?.phone || '',
  company_name: props.lead?.company_name || '',
  currency: props.currency || 'UGX',
  tax_rate: props.defaultTaxRate ?? 18,
  discount_amount: 0,
  validity_date: new Date(Date.now() + 14 * 86400000).toISOString().slice(0, 10),
  notes: '',
  terms_and_conditions:
    props.defaultTerms || 'Payment terms: 50% advance deposit upon acceptance, 50% upon completion.',
  items: [
    {
      description: props.lead?.requested_service || '',
      quantity: 1,
      unit_price: Number(props.lead?.estimated_value) || 0,
    },
  ],
});

const handleCurrencyChange = (newCurr: string) => {
  const prevCurr = activeCurrency.value;
  if (newCurr && newCurr !== prevCurr) {
    form.items = form.items.map((item) => ({
      ...item,
      unit_price: convertCurrencyAmount(Number(item.unit_price || 0), prevCurr, newCurr),
    }));
    form.discount_amount = convertCurrencyAmount(Number(form.discount_amount || 0), prevCurr, newCurr);
    activeCurrency.value = newCurr;
    form.currency = newCurr;
  }
};

const addItem = () => {
  form.items.push({ description: '', quantity: 1, unit_price: 0 });
};

const removeItem = (idx: number) => {
  if (form.items.length > 1) {
    form.items.splice(idx, 1);
  }
};

const subtotal = computed(() =>
  form.items.reduce((acc, item) => acc + Number(item.quantity || 0) * Number(item.unit_price || 0), 0)
);

const taxableAmount = computed(() => Math.max(0, subtotal.value - Number(form.discount_amount || 0)));
const vatAmount = computed(() => Math.round(taxableAmount.value * (Number(form.tax_rate || 0) / 100)));
const grandTotal = computed(() => taxableAmount.value + vatAmount.value);

const formatMoney = (amount: number) => {
  return formatCurrencyAmount(amount, activeCurrency.value);
};

const submit = () => {
  form.post('/quotations');
};
</script>

<template>
  <Head title="New Quotation" />
  <AppLayout>
    <div class="max-w-5xl mx-auto space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-[#E5E0EC]">
        <div>
          <div class="flex items-center gap-2 text-xs text-[#6B6578] mb-1">
            <Link href="/quotations" class="hover:text-[#280047] font-medium flex items-center gap-1.5">
              <i class="fa-solid fa-arrow-left text-[10px]"></i> Quotations
            </Link>
            <span>/</span>
            <span class="text-[#242424] font-medium">Create Quotation</span>
          </div>
          <h1 class="text-xl font-bold text-[#242424]">Create Client Quotation</h1>
          <p class="text-xs text-[#6B6578] mt-0.5">Prepare a clear, itemized proposal in 3 simple steps.</p>
        </div>

        <!-- Step Buttons -->
        <div class="flex items-center gap-1.5 bg-white p-1.5 rounded-lg border border-[#E5E0EC]">
          <button
            v-for="s in [
              { n: 1, label: 'Client Details' },
              { n: 2, label: 'Line Items' },
              { n: 3, label: 'Tax & Terms' },
            ]"
            :key="s.n"
            type="button"
            @click="currentStep = s.n"
            class="flex items-center gap-2 px-3 py-1.5 rounded-md text-xs font-medium transition-colors cursor-pointer"
            :class="
              currentStep === s.n
                ? 'bg-[#280047] text-white'
                : 'text-[#6B6578] hover:text-[#242424]'
            "
          >
            <span
              class="w-5 h-5 rounded flex items-center justify-center text-[11px] font-bold"
              :class="currentStep === s.n ? 'bg-[#F74D01] text-white' : 'bg-[#F3F1F6] text-[#280047]'"
            >
              {{ s.n }}
            </span>
            <span class="hidden sm:inline">{{ s.label }}</span>
          </button>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Form Column -->
        <div class="lg:col-span-2">
          <form @submit.prevent="submit" class="app-card p-6">
            <!-- STEP 1: CLIENT DETAILS -->
            <div v-if="currentStep === 1" class="space-y-5">
              <div class="pb-4 border-b border-[#E5E0EC]">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-[#F74D01]">Step 1 of 3</span>
                <h2 class="text-base font-bold text-[#242424] mt-0.5">Client Information</h2>
                <p class="text-xs text-[#6B6578]">Verify or enter the recipient details for this quotation.</p>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-medium text-[#242424] mb-1.5">
                    Client Full Name <span class="text-[#F74D01]">*</span>
                  </label>
                  <input v-model="form.customer_name" type="text" required placeholder="Client name" class="app-input" />
                  <p v-if="form.errors.customer_name" class="text-xs text-red-600 mt-1">{{ form.errors.customer_name }}</p>
                </div>
                <div>
                  <label class="block text-xs font-medium text-[#242424] mb-1.5">Company Name</label>
                  <input v-model="form.company_name" type="text" placeholder="Company Ltd" class="app-input" />
                </div>
                <div>
                  <label class="block text-xs font-medium text-[#242424] mb-1.5">Phone Number</label>
                  <input v-model="form.customer_phone" type="text" placeholder="+256 772 000000" class="app-input font-mono-num" />
                </div>
                <div>
                  <label class="block text-xs font-medium text-[#242424] mb-1.5">Email Address</label>
                  <input v-model="form.customer_email" type="email" placeholder="client@company.com" class="app-input" />
                </div>
              </div>

              <div class="flex justify-between pt-4 border-t border-[#E5E0EC]">
                <Link href="/quotations" class="btn-secondary">Cancel</Link>
                <button type="button" @click="currentStep = 2" class="btn-indigo">
                  Continue to Line Items <i class="fa-solid fa-arrow-right text-xs"></i>
                </button>
              </div>
            </div>

            <!-- STEP 2: LINE ITEMS -->
            <div v-if="currentStep === 2" class="space-y-5">
              <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-[#E5E0EC]">
                <div>
                  <span class="text-[11px] font-semibold uppercase tracking-wider text-[#F74D01]">Step 2 of 3</span>
                  <h2 class="text-base font-bold text-[#242424] mt-0.5">Quotation Line Items</h2>
                  <p class="text-xs text-[#6B6578]">Add the services or products included in this quotation.</p>
                </div>
                <div class="flex items-center gap-2">
                  <div class="w-44">
                    <CustomSelect
                      :model-value="activeCurrency"
                      :options="CURRENCY_OPTIONS"
                      size="sm"
                      align="right"
                      @change="handleCurrencyChange"
                    />
                  </div>
                  <button type="button" @click="addItem" class="btn-secondary text-xs shrink-0">
                    <i class="fa-solid fa-plus text-[#F74D01]"></i> Add Item
                  </button>
                </div>
              </div>

              <div class="space-y-3">
                <div
                  v-for="(item, idx) in form.items"
                  :key="idx"
                  class="p-3.5 rounded-md border border-[#E5E0EC] bg-[#FAFAFB] grid grid-cols-1 sm:grid-cols-12 gap-3 items-end"
                >
                  <div class="sm:col-span-6">
                    <label class="block text-[11px] font-medium text-[#6B6578] mb-1">Description</label>
                    <input
                      v-model="item.description"
                      type="text"
                      required
                      placeholder="Service or product description"
                      class="app-input bg-white"
                    />
                  </div>
                  <div class="sm:col-span-2">
                    <label class="block text-[11px] font-medium text-[#6B6578] mb-1">Qty</label>
                    <input
                      v-model="item.quantity"
                      type="number"
                      min="1"
                      step="any"
                      required
                      class="app-input bg-white font-mono-num"
                    />
                  </div>
                  <div class="sm:col-span-3">
                    <label class="block text-[11px] font-medium text-[#6B6578] mb-1">Unit Price ({{ activeCurrency }})</label>
                    <input
                      v-model="item.unit_price"
                      type="number"
                      min="0"
                      step="any"
                      required
                      class="app-input bg-white font-mono-num"
                    />
                  </div>
                  <div class="sm:col-span-1 flex justify-end">
                    <button
                      type="button"
                      @click="removeItem(idx)"
                      :disabled="form.items.length === 1"
                      class="h-[38px] w-[38px] rounded-md border border-[#E5E0EC] bg-white text-[#6B6578] hover:text-red-600 disabled:opacity-30 flex items-center justify-center cursor-pointer"
                    >
                      <i class="fa-regular fa-trash-can text-xs"></i>
                    </button>
                  </div>
                </div>
              </div>

              <div class="flex justify-between pt-4 border-t border-[#E5E0EC]">
                <button type="button" @click="currentStep = 1" class="btn-secondary">
                  <i class="fa-solid fa-arrow-left text-xs"></i> Back
                </button>
                <button type="button" @click="currentStep = 3" class="btn-indigo">
                  Continue to Tax & Terms <i class="fa-solid fa-arrow-right text-xs"></i>
                </button>
              </div>
            </div>

            <!-- STEP 3: TAX, DISCOUNT & TERMS -->
            <div v-if="currentStep === 3" class="space-y-5">
              <div class="pb-4 border-b border-[#E5E0EC]">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-[#F74D01]">Step 3 of 3</span>
                <h2 class="text-base font-bold text-[#242424] mt-0.5">Tax, Discount & Validity</h2>
                <p class="text-xs text-[#6B6578]">Configure VAT rate, optional discount, expiration date, and terms.</p>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                  <label class="block text-xs font-medium text-[#242424] mb-1.5">VAT Rate (%)</label>
                  <input v-model="form.tax_rate" type="number" min="0" max="100" step="any" class="app-input font-mono-num" />
                </div>
                <div>
                  <label class="block text-xs font-medium text-[#242424] mb-1.5">Discount ({{ activeCurrency }})</label>
                  <input v-model="form.discount_amount" type="number" min="0" step="any" class="app-input font-mono-num" />
                </div>
                <div>
                  <label class="block text-xs font-medium text-[#242424] mb-1.5">Valid Until</label>
                  <CustomDatePicker
                    v-model="form.validity_date"
                    mode="date"
                    placeholder="Select expiration date"
                  />
                </div>
              </div>

              <div>
                <label class="block text-xs font-medium text-[#242424] mb-1.5">Terms & Payment Instructions</label>
                <textarea v-model="form.terms_and_conditions" rows="3" class="app-input"></textarea>
              </div>

              <div class="flex justify-between pt-4 border-t border-[#E5E0EC]">
                <button type="button" @click="currentStep = 2" class="btn-secondary">
                  <i class="fa-solid fa-arrow-left text-xs"></i> Back
                </button>
                <button type="submit" :disabled="form.processing" class="btn-primary">
                  <i class="fa-solid fa-check text-xs"></i>
                  {{ form.processing ? 'Generating Quotation...' : 'Create Quotation' }}
                </button>
              </div>
            </div>
          </form>
        </div>

        <!-- Live Totals Card -->
        <div>
          <div class="app-card p-5 sticky top-6">
            <h3 class="text-xs font-bold uppercase tracking-wider text-[#280047] pb-3 mb-4 border-b border-[#E5E0EC]">
              Quotation Summary
            </h3>

            <div class="space-y-2.5 text-xs">
              <div class="flex justify-between">
                <span class="text-[#6B6578]">Client</span>
                <span class="font-semibold text-[#242424]">{{ form.customer_name || '—' }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-[#6B6578]">Items Count</span>
                <span class="font-semibold text-[#242424] font-mono-num">{{ form.items.length }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-[#6B6578]">Subtotal</span>
                <span class="font-semibold text-[#242424] font-mono-num">{{ formatMoney(subtotal) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-[#6B6578]">Discount</span>
                <span class="font-semibold text-[#F74D01] font-mono-num">- {{ formatMoney(Number(form.discount_amount || 0)) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-[#6B6578]">VAT ({{ form.tax_rate }}%)</span>
                <span class="font-semibold text-[#242424] font-mono-num">{{ formatMoney(vatAmount) }}</span>
              </div>

              <div class="pt-3 border-t border-[#E5E0EC] flex items-center justify-between">
                <span class="font-bold text-[#242424]">Grand Total</span>
                <span class="text-lg font-bold text-[#280047] font-mono-num">{{ formatMoney(grandTotal) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
