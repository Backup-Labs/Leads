<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps<{
    metrics: {
        total_leads: number;
        active_leads: number;
        won_leads: number;
        lost_leads: number;
        conversion_rate: number;
        pipeline_value: number;
        won_revenue: number;
        overdue_followups: number;
        pending_quotes_count: number;
        pending_quotes_value: number;
        currency: string;
    };
    stages: Array<{
        id: number;
        name: string;
        color: string;
        leads_count: number;
        total_value: number;
        is_won: boolean;
        is_lost: boolean;
    }>;
    recentActivities: Array<any>;
    recoveryAlerts: Array<any>;
    upcomingFollowUps: Array<any>;
}>();

const formatMoney = (amount: number) => {
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    }).format(amount);
};

const refreshRecovery = () => {
    router.post('/recovery/scan');
};

const completeFollowUp = (id: number) => {
    router.patch(`/follow-ups/${id}/complete`);
};
</script>

<template>
    <AppLayout>
        <!-- Page Title & Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 mb-6">
            <div>
                <h1 class="text-xl font-bold text-brand-indigo">Dashboard</h1>
                <p class="text-xs text-gray-500 mt-0.5">
                    Overview of your active leads, quotations, follow-ups, and revenue performance.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button
                    @click="refreshRecovery"
                    class="btn-secondary inline-flex items-center gap-1.5 cursor-pointer"
                >
                    <i class="fa-solid fa-rotate text-brand-indigo"></i>
                    <span>Check Neglected Deals</span>
                </button>
                <Link href="/leads/create" class="btn-primary inline-flex items-center gap-1.5">
                    <i class="fa-solid fa-plus"></i>
                    <span>New Lead</span>
                </Link>
            </div>
        </div>

        <!-- Detailed KPI Cards (4 Columns) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <!-- Card 1: Pipeline Value -->
            <div class="app-card p-4 border-t-2 border-t-brand-indigo">
                <div class="flex items-center justify-between gap-2 text-xs text-gray-500 font-medium">
                    <span>Open Pipeline Value</span>
                    <i class="fa-solid fa-sack-dollar text-brand-indigo shrink-0"></i>
                </div>
                <div class="mt-2 flex flex-wrap items-baseline gap-1">
                    <span class="text-xs font-semibold text-gray-500">{{ metrics.currency }}</span>
                    <span class="text-xl sm:text-2xl font-bold font-mono text-brand-indigo break-all sm:break-normal">{{ formatMoney(metrics.pipeline_value) }}</span>
                </div>
                <div class="mt-3 pt-2.5 border-t border-gray-100 flex flex-wrap items-center justify-between gap-1 text-[11px]">
                    <span class="text-gray-500"><strong class="text-brand-dark">{{ metrics.active_leads }}</strong> active leads</span>
                    <span class="text-gray-400">Total: {{ metrics.total_leads }}</span>
                </div>
            </div>

            <!-- Card 2: Revenue Won -->
            <div class="app-card p-4 border-t-2 border-t-brand-indigo">
                <div class="flex items-center justify-between gap-2 text-xs text-gray-500 font-medium">
                    <span>Revenue Won</span>
                    <i class="fa-solid fa-circle-check text-brand-indigo shrink-0"></i>
                </div>
                <div class="mt-2 flex flex-wrap items-baseline gap-1">
                    <span class="text-xs font-semibold text-gray-500">{{ metrics.currency }}</span>
                    <span class="text-xl sm:text-2xl font-bold font-mono text-brand-indigo break-all sm:break-normal">{{ formatMoney(metrics.won_revenue) }}</span>
                </div>
                <div class="mt-3 pt-2.5 border-t border-gray-100 flex flex-wrap items-center justify-between gap-1 text-[11px]">
                    <span class="text-gray-500"><strong class="text-brand-dark">{{ metrics.won_leads }}</strong> deals won</span>
                    <span class="text-gray-400">{{ metrics.lost_leads }} lost</span>
                </div>
            </div>

            <!-- Card 3: Pending Quotations -->
            <div class="app-card p-4 border-t-2 border-t-brand-indigo">
                <div class="flex items-center justify-between gap-2 text-xs text-gray-500 font-medium">
                    <span>Pending Quotations</span>
                    <i class="fa-solid fa-file-invoice-dollar text-brand-indigo shrink-0"></i>
                </div>
                <div class="mt-2 flex flex-wrap items-baseline gap-1">
                    <span class="text-xs font-semibold text-gray-500">{{ metrics.currency }}</span>
                    <span class="text-xl sm:text-2xl font-bold font-mono text-brand-indigo break-all sm:break-normal">{{ formatMoney(metrics.pending_quotes_value) }}</span>
                </div>
                <div class="mt-3 pt-2.5 border-t border-gray-100 flex flex-wrap items-center justify-between gap-1 text-[11px]">
                    <span class="text-gray-500"><strong class="text-brand-dark">{{ metrics.pending_quotes_count }}</strong> awaiting reply</span>
                    <span class="font-semibold text-brand-indigo">Win rate: {{ metrics.conversion_rate }}%</span>
                </div>
            </div>

            <!-- Card 4: Overdue Follow-ups -->
            <div class="app-card p-4 border-t-2 border-t-brand-orange">
                <div class="flex items-center justify-between gap-2 text-xs text-gray-500 font-medium">
                    <span>Overdue Follow-ups</span>
                    <i class="fa-solid fa-clock text-brand-orange shrink-0"></i>
                </div>
                <div class="mt-2 flex flex-wrap items-baseline gap-1.5">
                    <span class="text-xl sm:text-2xl font-bold font-mono" :class="metrics.overdue_followups > 0 ? 'text-brand-orange' : 'text-brand-indigo'">
                        {{ metrics.overdue_followups }}
                    </span>
                    <span class="text-xs text-gray-500">tasks overdue</span>
                </div>
                <div class="mt-3 pt-2.5 border-t border-gray-100 flex flex-wrap items-center justify-between gap-1 text-[11px]">
                    <span class="text-gray-500">{{ recoveryAlerts.length }} deals at risk</span>
                    <Link href="/follow-ups?status=overdue" class="font-semibold text-brand-orange hover:underline">View tasks &rarr;</Link>
                </div>
            </div>
        </div>

        <!-- Deals Needing Attention (Revenue Recovery) -->
        <div v-if="recoveryAlerts.length > 0" class="app-card mb-6 overflow-hidden">
            <div class="px-4 sm:px-5 py-3.5 bg-brand-indigo text-white flex flex-wrap items-center justify-between gap-2">
                <div class="flex items-center gap-2.5 min-w-0">
                    <span class="w-6 h-6 rounded bg-brand-orange flex items-center justify-center text-white text-xs shrink-0">
                        <i class="fa-solid fa-bolt"></i>
                    </span>
                    <h2 class="text-xs font-bold uppercase tracking-wider">Deals Needing Immediate Attention ({{ recoveryAlerts.length }})</h2>
                </div>
                <Link href="/recovery" class="text-xs font-semibold text-brand-yellow hover:underline shrink-0">
                    View All &rarr;
                </Link>
            </div>

            <div class="divide-y divide-gray-100">
                <div
                    v-for="alert in recoveryAlerts"
                    :key="alert.id"
                    class="p-4 flex flex-col md:flex-row md:items-center justify-between gap-3 hover:bg-gray-50/60"
                >
                    <div class="min-w-0 space-y-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span
                                class="text-[10px] font-bold uppercase px-2 py-0.5 rounded"
                                :class="alert.severity === 'critical' ? 'bg-brand-orange text-white' : 'bg-brand-yellow text-brand-indigo border border-brand-indigo/20'"
                            >
                                {{ alert.severity }}
                            </span>
                            <span class="text-xs font-bold text-brand-indigo">{{ alert.lead?.customer_name || 'Lead' }}</span>
                            <span v-if="alert.potential_revenue_at_risk > 0" class="text-xs font-mono font-semibold text-brand-dark">
                                • {{ metrics.currency }} {{ formatMoney(alert.potential_revenue_at_risk) }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-600">{{ alert.reason }}</p>
                        <p class="text-[11px] font-medium text-brand-indigo">
                            <i class="fa-solid fa-arrow-right text-brand-orange mr-1"></i>Next step: {{ alert.suggested_action }}
                        </p>
                    </div>
                    <Link
                        v-if="alert.lead"
                        :href="`/leads/${alert.lead.id}`"
                        class="btn-indigo self-start md:self-center shrink-0"
                    >
                        Open Lead
                    </Link>
                </div>
            </div>
        </div>

        <!-- Main Grid: Pipeline Breakdown + Upcoming Follow-ups -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Pipeline Stage Breakdown Table (2 Columns) -->
            <div class="lg:col-span-2 app-card overflow-hidden">
                <div class="px-4 sm:px-5 py-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <h2 class="text-sm font-bold text-brand-indigo">Sales Pipeline Breakdown</h2>
                        <p class="text-xs text-gray-500">Number of leads and total value at each stage</p>
                    </div>
                    <Link href="/pipeline" class="btn-secondary text-xs">
                        Open Board
                    </Link>
                </div>

                <div class="p-4 sm:p-5 space-y-4">
                    <div v-for="stage in stages" :key="stage.id" class="space-y-1.5">
                        <div class="flex flex-wrap justify-between items-center gap-1.5 text-xs">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-xs bg-brand-indigo shrink-0" :style="{ backgroundColor: stage.color }"></span>
                                <span class="font-semibold text-brand-dark">{{ stage.name }}</span>
                            </div>
                            <div class="flex flex-wrap items-center gap-2 sm:gap-4">
                                <span class="text-gray-500 text-[11px]">{{ stage.leads_count }} leads</span>
                                <span class="font-mono font-bold text-brand-indigo sm:w-28 sm:text-right">
                                    {{ metrics.currency }} {{ formatMoney(stage.total_value) }}
                                </span>
                            </div>
                        </div>
                        <div class="w-full bg-gray-100 h-2 rounded-xs overflow-hidden">
                            <div
                                class="h-full bg-brand-indigo"
                                :style="{
                                    width: `${metrics.total_leads > 0 ? Math.max(2, (stage.leads_count / metrics.total_leads) * 100) : 0}%`,
                                    backgroundColor: stage.color
                                }"
                            ></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upcoming Follow-ups (1 Column) -->
            <div class="app-card flex flex-col overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-brand-indigo">Upcoming Follow-ups</h2>
                        <p class="text-xs text-gray-500">Scheduled calls and tasks</p>
                    </div>
                    <Link href="/follow-ups" class="text-xs font-semibold text-brand-orange hover:underline">View all</Link>
                </div>

                <div v-if="upcomingFollowUps.length === 0" class="flex-1 p-8 text-center text-xs text-gray-400">
                    <i class="fa-solid fa-calendar-check text-xl text-brand-purple mb-2 block"></i>
                    No pending follow-ups scheduled.
                </div>

                <div v-else class="divide-y divide-gray-100 flex-1">
                    <div
                        v-for="fu in upcomingFollowUps"
                        :key="fu.id"
                        class="p-4 flex items-start justify-between gap-3 hover:bg-gray-50/60"
                    >
                        <div class="min-w-0">
                            <p class="text-xs font-semibold text-brand-dark truncate">{{ fu.title }}</p>
                            <Link v-if="fu.lead" :href="`/leads/${fu.lead.id}`" class="text-[11px] text-brand-orange font-medium hover:underline block truncate mt-0.5">
                                {{ fu.lead.customer_name }}
                            </Link>
                            <p class="text-[11px] font-mono text-gray-400 mt-1">
                                {{ new Date(fu.scheduled_at).toLocaleString() }}
                            </p>
                        </div>
                        <button
                            @click="completeFollowUp(fu.id)"
                            title="Mark Done"
                            class="px-2.5 py-1 rounded border border-brand-purple hover:bg-brand-indigo hover:text-white text-[11px] font-semibold text-brand-indigo transition-colors cursor-pointer shrink-0"
                        >
                            Done
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
