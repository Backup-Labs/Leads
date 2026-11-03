<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { ArrowLeft, Plus, Trash2, Calculator } from 'lucide-vue-next';

const props = defineProps<{
    lead?: any;
    defaultTaxRate: number;
    defaultTerms?: string;
    currency: string;
}>();

const form = useForm({
    lead_id: props.lead?.id || '',
    customer_name: props.lead?.customer_name || '',
    customer_email: props.lead?.email || '',
    customer_phone: props.lead?.phone || '',
    company_name: props.lead?.company_name || '',
    discount_amount: 0,
    tax_rate: props.defaultTaxRate ?? 18.00,
    validity_date: new Date(Date.now() + 14 * 86400000).toISOString().split('T')[0],
    notes: '',
    terms_and_conditions: props.defaultTerms || '',
    items: [
        { description: props.lead?.requested_service || 'Standard Professional Service', quantity: 1, unit_price: Number(props.lead?.estimated_value) || 0 }
    ],
});

const addItem = () => {
    form.items.push({ description: '', quantity: 1, unit_price: 0 });
};

const removeItem = (index: number) => {
    if (form.items.length > 1) {
        form.items.splice(index, 1);
    }
};

// Real-time calculations
const subtotal = computed(() => {
    return form.items.reduce((acc, item) => acc + (Number(item.quantity) * Number(item.unit_price) || 0), 0);
});

const discount = computed(() => {
    return Math.min(subtotal.value, Math.max(0, Number(form.discount_amount) || 0));
});

const taxableAmount = computed(() => {
    return Math.max(0, subtotal.value - discount.value);
});

const taxAmount = computed(() => {
    return Math.round(taxableAmount.value * ((Number(form.tax_rate) || 0) / 100) * 100) / 100;
});

const grandTotal = computed(() => {
    return taxableAmount.value + taxAmount.value;
});

const formatMoney = (val: number) => {
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    }).format(val);
};

const submit = () => {
    form.post('/quotations');
};
</script>

<template>
    <AppLayout>
        <div class="max-w-4xl mx-auto">
            <div class="mb-6">
                <Link href="/quotations" class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-500 hover:text-[#280047]">
                    <ArrowLeft class="w-4 h-4" />
                    <span>Back to Quotations</span>
                </Link>
                <h1 class="text-2xl font-extrabold text-[#280047] mt-2">Generate Commercial Quotation</h1>
                <p class="text-xs text-gray-500">Configure multi-item quotation with automated tax and subtotal computation</p>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Customer Details Card -->
                <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm space-y-4">
                    <h2 class="text-xs font-bold text-[#280047] uppercase tracking-wider border-b border-gray-100 pb-2">
                        1. Customer Information
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700">Customer Name *</label>
                            <input
                                v-model="form.customer_name"
                                type="text"
                                required
                                class="mt-1 block w-full px-3 py-2 text-xs bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#F74D01] outline-none"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700">Company Name</label>
                            <input
                                v-model="form.company_name"
                                type="text"
                                class="mt-1 block w-full px-3 py-2 text-xs bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#F74D01] outline-none"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700">Email Address</label>
                            <input
                                v-model="form.customer_email"
                                type="email"
                                class="mt-1 block w-full px-3 py-2 text-xs bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#F74D01] outline-none"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700">Phone Number</label>
                            <input
                                v-model="form.customer_phone"
                                type="text"
                                class="mt-1 block w-full px-3 py-2 text-xs bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#F74D01] outline-none"
                            />
                        </div>
                    </div>
                </div>

                <!-- Line Items Table Card -->
                <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                        <h2 class="text-xs font-bold text-[#280047] uppercase tracking-wider">
                            2. Quotation Line Items
                        </h2>
                        <button
                            type="button"
                            @click="addItem"
                            class="inline-flex items-center gap-1 text-xs font-bold text-[#F74D01] hover:underline"
                        >
                            <Plus class="w-3.5 h-3.5" />
                            <span>Add Item</span>
                        </button>
                    </div>

                    <div class="space-y-3">
                        <div
                            v-for="(item, index) in form.items"
                            :key="index"
                            class="flex flex-col sm:flex-row items-center gap-2 p-2.5 rounded-xl bg-gray-50/70 border border-gray-200/70"
                        >
                            <div class="flex-1 w-full">
                                <input
                                    v-model="item.description"
                                    type="text"
                                    required
                                    placeholder="Service / Product Description"
                                    class="w-full px-3 py-1.5 text-xs bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#F74D01] outline-none"
                                />
                            </div>
                            <div class="w-full sm:w-24">
                                <input
                                    v-model="item.quantity"
                                    type="number"
                                    step="0.01"
                                    required
                                    placeholder="Qty"
                                    class="w-full px-2.5 py-1.5 text-xs bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#F74D01] outline-none text-right"
                                />
                            </div>
                            <div class="w-full sm:w-32">
                                <input
                                    v-model="item.unit_price"
                                    type="number"
                                    step="0.01"
                                    required
                                    placeholder="Unit Price"
                                    class="w-full px-2.5 py-1.5 text-xs bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#F74D01] outline-none text-right"
                                />
                            </div>
                            <div class="w-full sm:w-28 text-right font-bold text-xs text-[#280047] px-2">
                                {{ formatMoney(Number(item.quantity) * Number(item.unit_price)) }}
                            </div>
                            <button
                                type="button"
                                @click="removeItem(index)"
                                class="text-gray-400 hover:text-red-600 p-1"
                                title="Remove item"
                            >
                                <Trash2 class="w-4 h-4" />
                            </button>
                        </div>
                    </div>

                    <!-- Financial Summary & Tax Calculation Box -->
                    <div class="pt-4 border-t border-gray-100 flex flex-col sm:flex-row justify-between items-start gap-4">
                        <div class="w-full sm:w-1/2 space-y-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700">Validity Date</label>
                                <input
                                    v-model="form.validity_date"
                                    type="date"
                                    class="mt-1 block w-full px-3 py-1.5 text-xs bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#F74D01] outline-none"
                                />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700">Discount Amount ({{ currency }})</label>
                                <input
                                    v-model="form.discount_amount"
                                    type="number"
                                    step="0.01"
                                    class="mt-1 block w-full px-3 py-1.5 text-xs bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#F74D01] outline-none"
                                />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700">VAT Tax Rate (%)</label>
                                <input
                                    v-model="form.tax_rate"
                                    type="number"
                                    step="0.01"
                                    class="mt-1 block w-full px-3 py-1.5 text-xs bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#F74D01] outline-none"
                                />
                            </div>
                        </div>

                        <!-- Calculations totals card -->
                        <div class="w-full sm:w-72 bg-gray-50 p-4 rounded-xl border border-gray-200 space-y-2 text-xs">
                            <div class="flex justify-between text-gray-600">
                                <span>Subtotal:</span>
                                <span>{{ currency }} {{ formatMoney(subtotal) }}</span>
                            </div>
                            <div v-if="discount > 0" class="flex justify-between text-emerald-600 font-semibold">
                                <span>Discount:</span>
                                <span>- {{ currency }} {{ formatMoney(discount) }}</span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span>VAT ({{ form.tax_rate }}%):</span>
                                <span>{{ currency }} {{ formatMoney(taxAmount) }}</span>
                            </div>
                            <div class="pt-2 border-t border-gray-200 flex justify-between text-base font-extrabold text-[#280047]">
                                <span>Total:</span>
                                <span>{{ currency }} {{ formatMoney(grandTotal) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Terms & Conditions Card -->
                <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm space-y-3">
                    <h2 class="text-xs font-bold text-[#280047] uppercase tracking-wider">
                        3. Terms, Conditions & Payment Milestones
                    </h2>
                    <textarea
                        v-model="form.terms_and_conditions"
                        rows="3"
                        class="w-full p-3 text-xs bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#F74D01] outline-none"
                    ></textarea>
                </div>

                <!-- Form Submit Actions -->
                <div class="flex items-center justify-end gap-3 pt-2">
                    <Link href="/quotations" class="px-4 py-2 text-xs font-semibold text-gray-600 hover:text-gray-900">
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex items-center gap-1.5 px-6 py-2.5 rounded-xl text-xs font-bold text-white bg-[#F74D01] hover:bg-[#d94200] disabled:opacity-50 shadow-sm transition-all"
                    >
                        <span>{{ form.processing ? 'Saving...' : 'Save Draft Quotation' }}</span>
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
