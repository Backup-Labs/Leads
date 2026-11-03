<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { CheckCircle2, XCircle, Building2, Printer, ShieldCheck } from 'lucide-vue-next';

const props = defineProps<{
    quotation: any;
}>();

const declineModalOpen = ref(false);
const declineReason = ref('');

const form = useForm({
    action: 'accept',
    reason: '',
});

const acceptQuote = () => {
    if (confirm('Accept this quotation proposal?')) {
        form.action = 'accept';
        form.post(`/quotes/${props.quotation.token}/respond`);
    }
};

const declineQuote = () => {
    form.action = 'reject';
    form.reason = declineReason.value;
    form.post(`/quotes/${props.quotation.token}/respond`, {
        onSuccess: () => {
            declineModalOpen.value = false;
        }
    });
};

const formatMoney = (amount: number, currency: string) => {
    return `${currency} ` + new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    }).format(amount);
};
</script>

<template>
    <div class="min-h-screen bg-[#F8F9FA] py-10 px-4 sm:px-6 lg:px-8 font-sans">
        <div class="max-w-3xl mx-auto space-y-6">
            <!-- Header Brand Banner -->
            <div class="bg-white p-6 sm:p-8 rounded-2xl border border-gray-100 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-black text-[#280047]">{{ quotation.organization?.name || 'Leads Proposal' }}</h1>
                    <p class="text-xs text-gray-500 mt-0.5">
                        {{ quotation.organization?.billing_email }} • {{ quotation.organization?.phone }}
                    </p>
                </div>

                <div class="text-left sm:text-right">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-400 block">Proposal Number</span>
                    <span class="text-xl font-extrabold text-[#F74D01]">#{{ quotation.quotation_number }}</span>
                </div>
            </div>

            <!-- Status Banner (If already responded) -->
            <div
                v-if="quotation.status === 'accepted'"
                class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center gap-3 text-emerald-800 text-sm font-semibold"
            >
                <CheckCircle2 class="w-5 h-5 text-emerald-600 flex-shrink-0" />
                <span>You have accepted this quotation. The team has been notified and will proceed with fulfillment.</span>
            </div>

            <div
                v-else-if="quotation.status === 'rejected'"
                class="p-4 bg-rose-50 border border-rose-200 rounded-2xl flex items-center gap-3 text-rose-800 text-sm font-semibold"
            >
                <XCircle class="w-5 h-5 text-rose-600 flex-shrink-0" />
                <span>This proposal has been declined.</span>
            </div>

            <!-- Quotation Paper Container -->
            <div class="bg-white p-6 sm:p-10 rounded-2xl border border-gray-100 shadow-sm space-y-8">
                <!-- Metadata Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 border-b border-gray-100 pb-6 text-xs">
                    <div>
                        <span class="text-[10px] font-bold uppercase text-gray-400 tracking-wider block mb-1">Prepared For</span>
                        <p class="text-sm font-bold text-[#280047]">{{ quotation.customer_name }}</p>
                        <p v-if="quotation.company_name" class="text-gray-600 font-medium">{{ quotation.company_name }}</p>
                        <p v-if="quotation.customer_email" class="text-gray-500">{{ quotation.customer_email }}</p>
                    </div>
                    <div class="sm:text-right">
                        <span class="text-[10px] font-bold uppercase text-gray-400 tracking-wider block mb-1">Validity</span>
                        <p class="text-gray-600">Date: <strong>{{ new Date(quotation.created_at).toLocaleDateString() }}</strong></p>
                        <p v-if="quotation.validity_date" class="text-gray-600">
                            Valid Until: <strong class="text-[#F74D01]">{{ new Date(quotation.validity_date).toLocaleDateString() }}</strong>
                        </p>
                    </div>
                </div>

                <!-- Line Items Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-[#280047] text-white text-[11px] font-bold uppercase tracking-wider">
                                <th class="py-3 px-4 rounded-l-lg">Description</th>
                                <th class="py-3 px-4 text-right">Quantity</th>
                                <th class="py-3 px-4 text-right">Unit Price</th>
                                <th class="py-3 px-4 text-right rounded-r-lg">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-[#242424]">
                            <tr v-for="item in quotation.items" :key="item.id">
                                <td class="py-3.5 px-4 font-medium">{{ item.description }}</td>
                                <td class="py-3.5 px-4 text-right">{{ Number(item.quantity) }}</td>
                                <td class="py-3.5 px-4 text-right">{{ formatMoney(item.unit_price, quotation.currency) }}</td>
                                <td class="py-3.5 px-4 text-right font-bold text-[#280047]">{{ formatMoney(item.total_price, quotation.currency) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Totals Summary -->
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

                <!-- Terms & Conditions -->
                <div v-if="quotation.terms_and_conditions" class="p-4 bg-gray-50 rounded-xl border border-gray-200 text-xs text-gray-600 space-y-1">
                    <span class="font-bold text-[#280047] block text-[11px] uppercase tracking-wider">Terms & Conditions</span>
                    <p class="whitespace-pre-line">{{ quotation.terms_and_conditions }}</p>
                </div>

                <!-- Acceptance Actions -->
                <div
                    v-if="quotation.status === 'sent' || quotation.status === 'draft'"
                    class="pt-6 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4"
                >
                    <div class="flex items-center gap-2 text-xs text-gray-500">
                        <ShieldCheck class="w-4 h-4 text-emerald-600" />
                        <span>Secure digital response. Your decision is instantly transmitted.</span>
                    </div>

                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <button
                            type="button"
                            @click="declineModalOpen = true"
                            class="flex-1 sm:flex-none px-4 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors"
                        >
                            Decline Proposal
                        </button>
                        <button
                            type="button"
                            @click="acceptQuote"
                            class="flex-1 sm:flex-none px-6 py-2.5 text-xs font-bold rounded-xl text-white bg-[#F74D01] hover:bg-[#d94200] shadow-md transition-all"
                        >
                            Accept Quotation
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Decline Feedback Modal -->
        <div v-if="declineModalOpen" class="fixed inset-0 bg-black/40 flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl space-y-4">
                <h3 class="text-sm font-bold text-[#280047]">Decline Quotation</h3>
                <p class="text-xs text-gray-500">Please let the team know why this quotation is being declined so they can adjust scope or terms:</p>

                <div>
                    <label class="block text-xs font-semibold text-gray-700">Reason / Feedback (Optional)</label>
                    <textarea
                        v-model="declineReason"
                        rows="3"
                        placeholder="Price higher than budget, requirements changed, etc..."
                        class="mt-1 block w-full px-3 py-2 text-xs bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#F74D01] outline-none"
                    ></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button
                        @click="declineModalOpen = false"
                        class="px-3 py-1.5 text-xs text-gray-500 hover:text-gray-700"
                    >
                        Cancel
                    </button>
                    <button
                        @click="declineQuote"
                        class="px-4 py-2 text-xs font-bold text-white bg-red-600 hover:bg-red-700 rounded-xl transition-all"
                    >
                        Confirm Decline
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
