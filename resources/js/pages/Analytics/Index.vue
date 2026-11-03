<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    BarChart3,
    TrendingUp,
    DollarSign,
    Users,
    FileCheck,
    Calendar
} from 'lucide-vue-next';

const props = defineProps<{
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
    sourceStats: Array<any>;
    teamStats: Array<any>;
    currentPeriod: number;
}>();

const formatMoney = (val: number) => {
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    }).format(val);
};

const setPeriod = (days: number) => {
    router.get('/analytics', { period: days }, { preserveState: true });
};
</script>

<template>
    <AppLayout>
        <!-- Header & Time Period Filter -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-extrabold text-[#280047]">Revenue & Sales Analytics</h1>
                <p class="text-xs text-gray-500 mt-1">Measure conversion velocity, revenue closed, and channel attribution</p>
            </div>

            <div class="flex bg-white p-1 rounded-xl border border-gray-200 shadow-xs">
                <button
                    @click="setPeriod(30)"
                    class="px-3 py-1.5 text-xs font-bold rounded-lg transition-all"
                    :class="currentPeriod === 30 ? 'bg-[#280047] text-white' : 'text-gray-600 hover:text-gray-900'"
                >
                    Last 30 Days
                </button>
                <button
                    @click="setPeriod(90)"
                    class="px-3 py-1.5 text-xs font-bold rounded-lg transition-all"
                    :class="currentPeriod === 90 ? 'bg-[#280047] text-white' : 'text-gray-600 hover:text-gray-900'"
                >
                    Last 90 Days
                </button>
                <button
                    @click="setPeriod(365)"
                    class="px-3 py-1.5 text-xs font-bold rounded-lg transition-all"
                    :class="currentPeriod === 365 ? 'bg-[#280047] text-white' : 'text-gray-600 hover:text-gray-900'"
                >
                    Past Year
                </button>
            </div>
        </div>

        <!-- Metric Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Won Revenue</span>
                <div class="mt-2 flex items-baseline gap-1">
                    <span class="text-xs font-bold text-gray-400">{{ metrics.currency }}</span>
                    <span class="text-2xl font-extrabold text-emerald-600">{{ formatMoney(metrics.won_revenue) }}</span>
                </div>
                <p class="text-[11px] text-gray-500 mt-1">From {{ metrics.won_leads }} closed won opportunities</p>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Conversion Rate</span>
                <div class="mt-2 flex items-baseline gap-1">
                    <span class="text-2xl font-extrabold text-[#280047]">{{ metrics.conversion_rate }}%</span>
                </div>
                <p class="text-[11px] text-gray-500 mt-1">{{ metrics.won_leads }} won / {{ metrics.won_leads + metrics.lost_leads }} resolved</p>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Avg Deal Value</span>
                <div class="mt-2 flex items-baseline gap-1">
                    <span class="text-xs font-bold text-gray-400">{{ metrics.currency }}</span>
                    <span class="text-2xl font-extrabold text-[#280047]">{{ formatMoney(metrics.avg_deal_value) }}</span>
                </div>
                <p class="text-[11px] text-gray-500 mt-1">Per converted customer contract</p>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Quote Acceptance</span>
                <div class="mt-2 flex items-baseline gap-1">
                    <span class="text-2xl font-extrabold text-[#F74D01]">{{ metrics.quote_acceptance_rate }}%</span>
                </div>
                <p class="text-[11px] text-gray-500 mt-1">Ratio of accepted commercial proposals</p>
            </div>
        </div>

        <!-- Tables: Source Performance & Team Performance -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Source Channel Performance -->
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
                <h2 class="text-base font-bold text-[#280047] mb-1">Inquiry Source Performance</h2>
                <p class="text-xs text-gray-500 mb-4">Attribution of leads and actual won revenue by marketing channel</p>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-gray-100 text-gray-400 font-bold uppercase text-[10px] pb-2">
                                <th class="pb-2">Source Channel</th>
                                <th class="pb-2 text-right">Inquiries</th>
                                <th class="pb-2 text-right">Won Deals</th>
                                <th class="pb-2 text-right">Revenue Closed</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-for="src in sourceStats" :key="src.name" class="py-2.5">
                                <td class="py-2.5 font-bold text-[#280047] flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full" :style="{ backgroundColor: src.color }"></span>
                                    <span>{{ src.name }}</span>
                                </td>
                                <td class="py-2.5 text-right font-medium text-gray-600">{{ src.total_leads }}</td>
                                <td class="py-2.5 text-right font-medium text-emerald-600">{{ src.won_leads }}</td>
                                <td class="py-2.5 text-right font-extrabold text-[#280047]">
                                    {{ metrics.currency }} {{ formatMoney(src.won_revenue) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Team Sales Performance -->
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
                <h2 class="text-base font-bold text-[#280047] mb-1">Team Member Performance</h2>
                <p class="text-xs text-gray-500 mb-4">Individual sales execution, deal wins, and conversion efficiency</p>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-gray-100 text-gray-400 font-bold uppercase text-[10px] pb-2">
                                <th class="pb-2">Sales Representative</th>
                                <th class="pb-2 text-right">Assigned</th>
                                <th class="pb-2 text-right">Win Rate</th>
                                <th class="pb-2 text-right">Revenue Generated</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-for="t in teamStats" :key="t.id" class="py-2.5">
                                <td class="py-2.5">
                                    <span class="font-bold text-[#280047] block">{{ t.name }}</span>
                                    <span class="text-[10px] text-gray-400">{{ t.role }}</span>
                                </td>
                                <td class="py-2.5 text-right font-medium text-gray-600">{{ t.assigned_leads }}</td>
                                <td class="py-2.5 text-right font-bold text-purple-700">{{ t.win_rate }}%</td>
                                <td class="py-2.5 text-right font-extrabold text-emerald-700">
                                    {{ metrics.currency }} {{ formatMoney(t.won_revenue) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
