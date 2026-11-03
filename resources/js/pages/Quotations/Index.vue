<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { FileText, Plus, Eye, Send, CheckCircle2, Clock, XCircle } from 'lucide-vue-next';

const props = defineProps<{
    quotations: {
        data: Array<any>;
        links: Array<any>;
        total: number;
    };
    filters: {
        status?: string;
        search?: string;
    };
}>();

const formatMoney = (amount: number, currency: string) => {
    return `${currency} ` + new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    }).format(amount);
};
</script>

<template>
    <AppLayout>
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-extrabold text-[#280047]">Commercial Quotations</h1>
                <p class="text-xs text-gray-500 mt-1">Issue, track client responses, and close proposals faster</p>
            </div>
            <Link
                href="/quotations/create"
                class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold rounded-xl text-white bg-[#F74D01] hover:bg-[#d94200] shadow-sm transition-all"
            >
                <Plus class="w-4 h-4" />
                <span>Create Quotation</span>
            </Link>
        </div>

        <!-- Quotations Table -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/70 border-b border-gray-100 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                            <th class="py-3.5 px-4">Quote Number</th>
                            <th class="py-3.5 px-4">Customer / Lead</th>
                            <th class="py-3.5 px-4">Subtotal</th>
                            <th class="py-3.5 px-4">VAT Tax</th>
                            <th class="py-3.5 px-4">Grand Total</th>
                            <th class="py-3.5 px-4">Status</th>
                            <th class="py-3.5 px-4">Created Date</th>
                            <th class="py-3.5 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-xs text-[#242424]">
                        <tr v-if="quotations.data.length === 0">
                            <td colspan="8" class="py-12 text-center text-gray-400 font-medium">
                                No quotations issued yet. Click 'Create Quotation' to build your first proposal.
                            </td>
                        </tr>
                        <tr v-for="q in quotations.data" :key="q.id" class="hover:bg-gray-50/60 transition-colors">
                            <td class="py-3.5 px-4">
                                <Link :href="`/quotations/${q.id}`" class="font-extrabold text-[#280047] hover:text-[#F74D01]">
                                    #{{ q.quotation_number }}
                                </Link>
                            </td>

                            <td class="py-3.5 px-4">
                                <span class="font-bold text-gray-800">{{ q.customer_name }}</span>
                                <span v-if="q.company_name" class="block text-[11px] text-gray-500">{{ q.company_name }}</span>
                            </td>

                            <td class="py-3.5 px-4 text-gray-600">
                                {{ formatMoney(q.subtotal, q.currency) }}
                            </td>

                            <td class="py-3.5 px-4 text-gray-600">
                                {{ formatMoney(q.tax_amount, q.currency) }} ({{ q.tax_rate }}%)
                            </td>

                            <td class="py-3.5 px-4 font-extrabold text-[#280047]">
                                {{ formatMoney(q.total_amount, q.currency) }}
                            </td>

                            <td class="py-3.5 px-4">
                                <span
                                    class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-md"
                                    :class="{
                                        'bg-gray-100 text-gray-700': q.status === 'draft',
                                        'bg-blue-50 text-blue-700 border border-blue-200': q.status === 'sent',
                                        'bg-emerald-50 text-emerald-700 border border-emerald-200': q.status === 'accepted',
                                        'bg-rose-50 text-rose-700 border border-rose-200': q.status === 'rejected',
                                        'bg-amber-50 text-amber-700 border border-amber-200': q.status === 'expired'
                                    }"
                                >
                                    {{ q.status }}
                                </span>
                            </td>

                            <td class="py-3.5 px-4 text-gray-500">
                                {{ new Date(q.created_at).toLocaleDateString() }}
                            </td>

                            <td class="py-3.5 px-4 text-right">
                                <Link
                                    :href="`/quotations/${q.id}`"
                                    class="inline-flex p-1.5 text-gray-500 hover:text-[#280047] hover:bg-gray-100 rounded-lg transition-colors"
                                    title="View Quotation"
                                >
                                    <Eye class="w-4 h-4" />
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
