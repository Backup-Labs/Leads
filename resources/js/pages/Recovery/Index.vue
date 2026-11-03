<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    Zap,
    AlertTriangle,
    ShieldAlert,
    CheckCircle2,
    DollarSign,
    ArrowRight,
    RefreshCw,
    X
} from 'lucide-vue-next';

const props = defineProps<{
    alerts: {
        data: Array<any>;
        links: Array<any>;
        total: number;
    };
    totalRevenueAtRisk: number;
    counts: {
        critical: number;
        warning: number;
        total: number;
    };
    currency: string;
    filters: {
        type?: string;
        severity?: string;
    };
}>();

const formatMoney = (val: number) => {
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    }).format(val);
};

const scanNow = () => {
    router.post('/recovery/scan');
};

const dismissAlert = (id: number) => {
    router.patch(`/recovery/${id}/dismiss`);
};
</script>

<template>
    <AppLayout>
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-[#F74D01] flex items-center justify-center text-white">
                        <Zap class="w-4 h-4" />
                    </div>
                    <h1 class="text-2xl font-extrabold text-[#280047]">Revenue Recovery Cockpit</h1>
                </div>
                <p class="text-xs text-gray-500 mt-1">Automated radar tracking stalled deals, unresponded quotes, and overdue customer commitments</p>
            </div>

            <button
                @click="scanNow"
                class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold rounded-xl text-white bg-[#F74D01] hover:bg-[#d94200] shadow-sm transition-all"
            >
                <RefreshCw class="w-4 h-4" />
                <span>Run Recovery Scan</span>
            </button>
        </div>

        <!-- Revenue at Risk Top Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
            <div class="bg-white p-5 rounded-2xl border border-red-200 shadow-sm bg-gradient-to-br from-red-50/40 via-white to-white">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Potential Revenue at Risk</span>
                <div class="mt-2 flex items-baseline gap-1">
                    <span class="text-xs font-bold text-gray-400">{{ currency }}</span>
                    <span class="text-2xl font-extrabold text-red-600">{{ formatMoney(totalRevenueAtRisk) }}</span>
                </div>
                <p class="text-[11px] text-gray-500 mt-1">Across {{ counts.total }} flagged opportunities</p>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Critical Action Items</span>
                <span class="text-2xl font-extrabold text-red-700 block mt-2">{{ counts.critical }}</span>
                <p class="text-[11px] text-gray-500 mt-1">Overdue follow-ups & high-value stalled quotes</p>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Attention Notices</span>
                <span class="text-2xl font-extrabold text-amber-700 block mt-2">{{ counts.warning }}</span>
                <p class="text-[11px] text-gray-500 mt-1">Inquiries with no contact for > 7 days</p>
            </div>
        </div>

        <!-- Opportunities List -->
        <div class="space-y-3">
            <div v-if="alerts.data.length === 0" class="bg-white p-16 rounded-2xl border border-gray-100 text-center shadow-sm">
                <CheckCircle2 class="w-12 h-12 text-emerald-500 mx-auto mb-3" />
                <h3 class="text-sm font-bold text-[#280047]">Pipeline Healthy!</h3>
                <p class="text-xs text-gray-500 mt-1">No neglected opportunities or stalled quotations detected right now.</p>
            </div>

            <div
                v-for="alert in alerts.data"
                :key="alert.id"
                class="bg-white p-5 rounded-2xl border shadow-sm transition-all flex flex-col md:flex-row md:items-center justify-between gap-4"
                :class="alert.severity === 'critical' ? 'border-red-200 bg-red-50/15' : 'border-gray-200'"
            >
                <div class="min-w-0 flex-1 space-y-1">
                    <div class="flex items-center gap-2">
                        <span
                            class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-md"
                            :class="alert.severity === 'critical' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-800'"
                        >
                            {{ alert.severity }}
                        </span>

                        <span v-if="alert.lead" class="text-sm font-extrabold text-[#280047]">
                            {{ alert.lead.customer_name }}
                        </span>
                        <span v-if="alert.potential_revenue_at_risk > 0" class="text-xs font-bold text-red-600">
                            ({{ currency }} {{ formatMoney(alert.potential_revenue_at_risk) }} at risk)
                        </span>
                    </div>

                    <p class="text-xs font-medium text-gray-700">
                        {{ alert.reason }}
                    </p>

                    <div class="p-2.5 rounded-xl bg-orange-50 border border-orange-200/80 text-xs text-[#280047] font-semibold flex items-center gap-2 mt-2">
                        <span class="text-[#F74D01] font-bold">Action Plan:</span>
                        <span>{{ alert.suggested_action }}</span>
                    </div>
                </div>

                <div class="flex items-center gap-2 flex-shrink-0 self-start md:self-center">
                    <button
                        @click="dismissAlert(alert.id)"
                        class="p-2 rounded-xl text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors"
                        title="Dismiss alert"
                    >
                        <X class="w-4 h-4" />
                    </button>
                    <Link
                        v-if="alert.lead"
                        :href="`/leads/${alert.lead.id}`"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-white bg-[#280047] hover:bg-[#3a0066] transition-all shadow-xs"
                    >
                        <span>Take Action</span>
                        <ArrowRight class="w-3.5 h-3.5" />
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
