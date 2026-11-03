<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    Users,
    TrendingUp,
    DollarSign,
    Clock,
    FileText,
    Zap,
    AlertTriangle,
    ArrowRight,
    CheckCircle2,
    Calendar,
    PhoneCall,
    Plus
} from 'lucide-vue-next';

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
        <!-- Welcome & Quick Action Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-extrabold text-[#280047]">Sales Command Center</h1>
                <p class="text-xs text-gray-500 mt-1">Real-time pipeline visibility and revenue recovery opportunities</p>
            </div>
            <div class="flex items-center gap-2">
                <button
                    @click="refreshRecovery"
                    class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-[#280047] shadow-sm transition-all"
                >
                    <Zap class="w-4 h-4 text-[#F74D01]" />
                    <span>Run Recovery Scan</span>
                </button>
                <Link
                    href="/leads/create"
                    class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold rounded-xl text-white bg-[#F74D01] hover:bg-[#d94200] shadow-sm transition-all"
                >
                    <Plus class="w-4 h-4" />
                    <span>Capture Lead</span>
                </Link>
            </div>
        </div>

        <!-- Metric Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <!-- Pipeline Value -->
            <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Pipeline Value</span>
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center text-[#280047]">
                        <DollarSign class="w-4 h-4" />
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-1">
                    <span class="text-xs font-bold text-gray-400">{{ metrics.currency }}</span>
                    <span class="text-2xl font-extrabold text-[#280047]">{{ formatMoney(metrics.pipeline_value) }}</span>
                </div>
                <p class="text-[11px] text-gray-500 mt-1">{{ metrics.active_leads }} active open inquiries</p>
            </div>

            <!-- Won Revenue -->
            <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Won Revenue</span>
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600">
                        <TrendingUp class="w-4 h-4" />
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-1">
                    <span class="text-xs font-bold text-gray-400">{{ metrics.currency }}</span>
                    <span class="text-2xl font-extrabold text-emerald-700">{{ formatMoney(metrics.won_revenue) }}</span>
                </div>
                <p class="text-[11px] text-gray-500 mt-1">{{ metrics.won_leads }} deals successfully closed</p>
            </div>

            <!-- Conversion Win Rate -->
            <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Win Rate</span>
                    <div class="w-8 h-8 rounded-lg bg-purple-50 flex items-center justify-center text-[#280047]">
                        <Users class="w-4 h-4" />
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-1">
                    <span class="text-2xl font-extrabold text-[#280047]">{{ metrics.conversion_rate }}%</span>
                </div>
                <p class="text-[11px] text-gray-500 mt-1">{{ metrics.won_leads }} won / {{ metrics.won_leads + metrics.lost_leads }} closed</p>
            </div>

            <!-- Overdue Attention Needed -->
            <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm" :class="{'border-l-4 border-l-[#F74D01]': metrics.overdue_followups > 0}">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Attention Needed</span>
                    <div class="w-8 h-8 rounded-lg bg-orange-50 flex items-center justify-center text-[#F74D01]">
                        <AlertTriangle class="w-4 h-4" />
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-1">
                    <span class="text-2xl font-extrabold" :class="metrics.overdue_followups > 0 ? 'text-[#F74D01]' : 'text-gray-700'">
                        {{ metrics.overdue_followups }}
                    </span>
                    <span class="text-xs font-semibold text-gray-500">overdue follow-ups</span>
                </div>
                <p class="text-[11px] text-gray-500 mt-1">{{ metrics.pending_quotes_count }} quotes awaiting response</p>
            </div>
        </div>

        <!-- Revenue Recovery Priority Cockpit -->
        <div v-if="recoveryAlerts.length > 0" class="mb-8 bg-gradient-to-r from-orange-50/70 via-white to-purple-50/50 p-6 rounded-2xl border border-orange-200 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-lg bg-[#F74D01] flex items-center justify-center text-white">
                        <Zap class="w-4 h-4" />
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-[#280047]">Revenue Recovery Radar</h2>
                        <p class="text-xs text-gray-600">Immediate high-value risks and stalled customer opportunities</p>
                    </div>
                </div>
                <Link href="/recovery" class="text-xs font-bold text-[#F74D01] hover:underline flex items-center gap-1">
                    <span>View All ({{ recoveryAlerts.length }})</span>
                    <ArrowRight class="w-3.5 h-3.5" />
                </Link>
            </div>

            <div class="space-y-2.5">
                <div
                    v-for="alert in recoveryAlerts"
                    :key="alert.id"
                    class="bg-white p-3.5 rounded-xl border border-orange-100 flex flex-col md:flex-row md:items-center justify-between gap-3 shadow-xs"
                >
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <span
                                class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-md"
                                :class="alert.severity === 'critical' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-800'"
                            >
                                {{ alert.severity }}
                            </span>
                            <span class="text-xs font-bold text-[#280047] truncate">{{ alert.lead?.customer_name || 'Opportunity' }}</span>
                            <span v-if="alert.potential_revenue_at_risk > 0" class="text-xs font-semibold text-gray-500">
                                ({{ metrics.currency }} {{ formatMoney(alert.potential_revenue_at_risk) }} at risk)
                            </span>
                        </div>
                        <p class="text-xs text-gray-600 mt-1">{{ alert.reason }}</p>
                        <p class="text-[11px] text-[#F74D01] font-medium mt-0.5">👉 Suggested Action: {{ alert.suggested_action }}</p>
                    </div>
                    <Link
                        v-if="alert.lead"
                        :href="`/leads/${alert.lead.id}`"
                        class="self-start md:self-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-[#280047] text-white hover:bg-[#3a0066] transition-colors"
                    >
                        Resolve Lead
                    </Link>
                </div>
            </div>
        </div>

        <!-- Two Column Layout: Pipeline Distribution & Upcoming Tasks -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Pipeline Stages Visual Funnel -->
            <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h2 class="text-base font-bold text-[#280047]">Pipeline Funnel Distribution</h2>
                        <p class="text-xs text-gray-500">Live lead distribution and stage value</p>
                    </div>
                    <Link href="/pipeline" class="text-xs font-bold text-[#280047] hover:underline flex items-center gap-1">
                        <span>Open Kanban</span>
                        <ArrowRight class="w-3.5 h-3.5" />
                    </Link>
                </div>

                <div class="space-y-4">
                    <div v-for="stage in stages" :key="stage.id" class="space-y-1.5">
                        <div class="flex justify-between items-center text-xs">
                            <span class="font-bold flex items-center gap-1.5" :style="{ color: stage.color }">
                                <span class="w-2.5 h-2.5 rounded-full" :style="{ backgroundColor: stage.color }"></span>
                                {{ stage.name }}
                            </span>
                            <div class="flex items-center gap-3 text-gray-600">
                                <span>{{ stage.leads_count }} leads</span>
                                <span class="font-bold text-[#280047]">{{ metrics.currency }} {{ formatMoney(stage.total_value) }}</span>
                            </div>
                        </div>
                        <!-- Progress bar -->
                        <div class="w-full bg-gray-100 h-2.5 rounded-full overflow-hidden">
                            <div
                                class="h-full rounded-full transition-all duration-500"
                                :style="{
                                    width: `${metrics.total_leads > 0 ? (stage.leads_count / metrics.total_leads) * 100 : 0}%`,
                                    backgroundColor: stage.color
                                }"
                            ></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upcoming Follow-ups -->
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex flex-col">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h2 class="text-base font-bold text-[#280047]">Upcoming Tasks</h2>
                        <p class="text-xs text-gray-500">Scheduled for today and tomorrow</p>
                    </div>
                    <Link href="/follow-ups" class="text-xs font-bold text-[#F74D01] hover:underline">View All</Link>
                </div>

                <div v-if="upcomingFollowUps.length === 0" class="flex-1 flex flex-col items-center justify-center py-8 text-center">
                    <Calendar class="w-10 h-10 text-gray-300 mb-2" />
                    <p class="text-xs text-gray-500 font-medium">No follow-ups due right now</p>
                    <Link href="/leads" class="mt-2 text-xs text-[#F74D01] font-semibold hover:underline">Schedule on a lead</Link>
                </div>

                <div v-else class="space-y-3">
                    <div
                        v-for="fu in upcomingFollowUps"
                        :key="fu.id"
                        class="p-3 rounded-xl border border-gray-100 bg-gray-50/50 flex items-center justify-between gap-2"
                    >
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-[#280047] truncate">{{ fu.title }}</p>
                            <p class="text-[11px] text-gray-500 truncate">{{ fu.lead?.customer_name }} • {{ fu.assigned_to?.name }}</p>
                            <span class="text-[10px] text-gray-400">{{ new Date(fu.scheduled_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}</span>
                        </div>
                        <button
                            @click="completeFollowUp(fu.id)"
                            title="Mark Completed"
                            class="p-1.5 rounded-lg text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 transition-colors"
                        >
                            <CheckCircle2 class="w-5 h-5" />
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activities Timeline -->
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
            <h2 class="text-base font-bold text-[#280047] mb-4">Recent Sales Team Activities</h2>
            <div v-if="recentActivities.length === 0" class="text-xs text-gray-400 py-4 text-center">
                No recent activity recorded yet.
            </div>
            <div v-else class="divide-y divide-gray-50">
                <div v-for="act in recentActivities" :key="act.id" class="py-3 flex items-start gap-3">
                    <div class="w-7 h-7 rounded-full bg-purple-50 text-[#280047] flex items-center justify-center font-bold text-xs flex-shrink-0 mt-0.5">
                        {{ act.user?.name?.charAt(0) || 'A' }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-bold text-[#280047]">
                                {{ act.user?.name || 'System' }} •
                                <span class="font-normal text-gray-600">{{ act.subject }}</span>
                                <span v-if="act.lead" class="font-semibold text-[#F74D01]"> ({{ act.lead.customer_name }})</span>
                            </p>
                            <span class="text-[10px] text-gray-400">{{ new Date(act.created_at).toLocaleDateString() }}</span>
                        </div>
                        <p v-if="act.description" class="text-xs text-gray-500 mt-0.5">{{ act.description }}</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
