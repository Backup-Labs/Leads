<script setup lang="ts">
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    ArrowLeft,
    Send,
    Printer,
    Copy,
    Check,
    CheckCircle2,
    Clock,
    FileText,
    Building2,
    Calendar
} from 'lucide-vue-next';

const props = defineProps<{
    quotation: any;
    publicUrl: string;
}>();

const copied = ref(false);

const copyLink = () => {
    navigator.clipboard.writeText(props.publicUrl);
    copied.value = true;
    setTimeout(() => { copied.value = false; }, 2000);
};

const sendQuote = () => {
    if (confirm(`Mark quotation #${props.quotation.quotation_number} as sent to client?`)) {
        router.post(`/quotations/${props.quotation.id}/send`);
    }
};

const formatMoney = (amount: number, currency: string) => {
    return `${currency} ` + new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    }).format(amount);
};
</script>

<template>
    <AppLayout>
        <div class="max-w-4xl mx-auto">
            <div class="mb-4">
                <Link href="/quotations" class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-500 hover:text-[#280047]">
                    <ArrowLeft class="w-4 h-4" />
                    <span>Back to Quotations</span>
                </Link>
            </div>

            <!-- Top Header & Actions -->
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2.5">
                        <h1 class="text-2xl font-extrabold text-[#280047]">Quotation #{{ quotation.quotation_number }}</h1>
                        <span
                            class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-md"
                            :class="{
                                'bg-gray-100 text-gray-700': quotation.status === 'draft',
                                'bg-blue-50 text-blue-700 border border-blue-200': quotation.status === 'sent',
                                'bg-emerald-50 text-emerald-700 border border-emerald-200': quotation.status === 'accepted',
                                'bg-rose-50 text-rose-700 border border-rose-200': quotation.status === 'rejected',
                                'bg-amber-50 text-amber-700 border border-amber-200': quotation.status === 'expired'
                            }"
                        >
                            {{ quotation.status }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Issued for customer <strong>{{ quotation.customer_name }}</strong></p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <a
                        :href="`/quotations/${quotation.id}/preview`"
                        target="_blank"
                        class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-[#280047] shadow-sm transition-all"
                    >
                        <Printer class="w-4 h-4 text-gray-500" />
                        <span>Print / PDF</span>
                    </a>

                    <button
                        v-if="quotation.status === 'draft'"
                        @click="sendQuote"
                        class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold rounded-xl text-white bg-[#F74D01] hover:bg-[#d94200] shadow-sm transition-all"
                    >
                        <Send class="w-4 h-4" />
                        <span>Mark Sent</span>
                    </button>
                </div>
            </div>

            <!-- Public Link Share Box -->
            <div class="bg-gradient-to-r from-purple-50/50 via-white to-orange-50/40 p-4 rounded-2xl border border-purple-100 shadow-sm mb-6 flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="text-xs">
                    <p class="font-bold text-[#280047]">Client Interactive Proposal Link</p>
                    <p class="text-gray-500 text-[11px]">Share this direct tokenized URL with the client for one-click digital acceptance:</p>
                </div>
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <input
                        type="text"
                        readonly
                        :value="publicUrl"
                        class="px-3 py-1.5 text-xs bg-white border border-gray-200 rounded-xl text-gray-600 w-full sm:w-72 select-all outline-none"
                    />
                    <button
                        @click="copyLink"
                        class="px-3 py-1.5 text-xs font-bold rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-[#280047] flex items-center gap-1 flex-shrink-0"
                    >
                        <Check v-if="copied" class="w-3.5 h-3.5 text-emerald-600" />
                        <Copy v-else class="w-3.5 h-3.5 text-gray-500" />
                        <span>{{ copied ? 'Copied' : 'Copy' }}</span>
                    </button>
                </div>
            </div>

            <!-- Quotation Paper Container -->
            <div class="bg-white p-8 rounded-2xl border border-gray-100 shadow-sm space-y-6">
                <!-- Quotation Header Grid -->
                <div class="grid grid-cols-2 gap-6 border-b border-gray-100 pb-6 text-xs">
                    <div>
                        <span class="text-gray-400 font-bold uppercase tracking-wider block text-[10px] mb-1">Prepared For</span>
                        <p class="text-sm font-bold text-[#280047]">{{ quotation.customer_name }}</p>
                        <p v-if="quotation.company_name" class="text-gray-600">{{ quotation.company_name }}</p>
                        <p v-if="quotation.customer_email" class="text-gray-500">{{ quotation.customer_email }}</p>
                        <p v-if="quotation.customer_phone" class="text-gray-500">{{ quotation.customer_phone }}</p>
                    </div>
                    <div class="text-right">
                        <span class="text-gray-400 font-bold uppercase tracking-wider block text-[10px] mb-1">Quotation Summary</span>
                        <p class="text-gray-600">Created: <strong class="text-gray-800">{{ new Date(quotation.created_at).toLocaleDateString() }}</strong></p>
                        <p v-if="quotation.validity_date" class="text-gray-600">Valid Until: <strong class="text-rose-600">{{ new Date(quotation.validity_date).toLocaleDateString() }}</strong></p>
                        <p v-if="quotation.viewed_at" class="text-[11px] text-blue-600 mt-1 font-semibold">Client Viewed: {{ new Date(quotation.viewed_at).toLocaleString() }}</p>
                    </div>
                </div>

                <!-- Line Items Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-[#280047] text-white text-[11px] font-bold uppercase tracking-wider">
                                <th class="py-2.5 px-4 rounded-l-lg">Description</th>
                                <th class="py-2.5 px-4 text-right">Quantity</th>
                                <th class="py-2.5 px-4 text-right">Unit Price</th>
                                <th class="py-2.5 px-4 text-right rounded-r-lg">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-[#242424]">
                            <tr v-for="item in quotation.items" :key="item.id">
                                <td class="py-3 px-4 font-medium">{{ item.description }}</td>
                                <td class="py-3 px-4 text-right">{{ Number(item.quantity) }}</td>
                                <td class="py-3 px-4 text-right">{{ formatMoney(item.unit_price, quotation.currency) }}</td>
                                <td class="py-3 px-4 text-right font-bold text-[#280047]">{{ formatMoney(item.total_price, quotation.currency) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Financial Calculation Totals Box -->
                <div class="flex justify-end pt-4 border-t border-gray-100">
                    <div class="w-72 space-y-2 text-xs">
                        <div class="flex justify-between text-gray-600">
                            <span>Subtotal:</span>
                            <span>{{ formatMoney(quotation.subtotal, quotation.currency) }}</span>
                        </div>
                        <div v-if="quotation.discount_amount > 0" class="flex justify-between text-emerald-600 font-semibold">
                            <span>Discount:</span>
                            <span>- {{ formatMoney(quotation.discount_amount, quotation.currency) }}</span>
                        </div>
                        <div v-if="quotation.tax_rate > 0" class="flex justify-between text-gray-600">
                            <span>VAT ({{ quotation.tax_rate }}%):</span>
                            <span>{{ formatMoney(quotation.tax_amount, quotation.currency) }}</span>
                        </div>
                        <div class="pt-2 border-t-2 border-[#280047] flex justify-between text-base font-extrabold text-[#280047]">
                            <span>Grand Total:</span>
                            <span>{{ formatMoney(quotation.total_amount, quotation.currency) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Terms and Conditions -->
                <div v-if="quotation.terms_and_conditions" class="p-4 bg-gray-50 rounded-xl border border-gray-200 text-xs text-gray-600 space-y-1">
                    <span class="font-bold text-[#280047] block text-[11px] uppercase tracking-wider">Terms & Conditions</span>
                    <p class="whitespace-pre-line">{{ quotation.terms_and_conditions }}</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
