<script setup lang="ts">
import { ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    Search,
    Filter,
    Plus,
    Download,
    Eye,
    Trash2,
    Calendar,
    Phone,
    Mail,
    Building2,
    Clock
} from 'lucide-vue-next';

const props = defineProps<{
    leads: {
        data: Array<any>;
        links: Array<any>;
        current_page: number;
        last_page: number;
        total: number;
    };
    filters: {
        search?: string;
        stage_id?: string;
        source_id?: string;
        status?: string;
        assigned_to?: string;
    };
    stages: Array<any>;
    sources: Array<any>;
    team: Array<any>;
}>();

const search = ref(props.filters.search || '');
const stageId = ref(props.filters.stage_id || '');
const sourceId = ref(props.filters.source_id || '');
const status = ref(props.filters.status || '');
const assignedTo = ref(props.filters.assigned_to || '');

let searchTimeout: any = null;

const applyFilters = () => {
    router.get('/leads', {
        search: search.value || undefined,
        stage_id: stageId.value || undefined,
        source_id: sourceId.value || undefined,
        status: status.value || undefined,
        assigned_to: assignedTo.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 300);
});

const formatMoney = (amount: number, currency: string) => {
    return `${currency} ` + new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    }).format(amount);
};

const deleteLead = (lead: any) => {
    if (confirm(`Are you sure you want to archive lead '${lead.customer_name}'?`)) {
        router.delete(`/leads/${lead.id}`);
    }
};
</script>

<template>
    <AppLayout>
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-extrabold text-[#280047]">Leads Directory</h1>
                <p class="text-xs text-gray-500 mt-1">Manage and track {{ leads.total }} customer inquiries across all sales channels</p>
            </div>
            <div class="flex items-center gap-2">
                <a
                    href="/leads/export"
                    class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-[#280047] shadow-sm transition-all"
                >
                    <Download class="w-4 h-4 text-gray-500" />
                    <span>Export CSV</span>
                </a>
                <Link
                    href="/leads/create"
                    class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold rounded-xl text-white bg-[#F74D01] hover:bg-[#d94200] shadow-sm transition-all"
                >
                    <Plus class="w-4 h-4" />
                    <span>Capture Lead</span>
                </Link>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm mb-6 flex flex-col md:flex-row gap-3">
            <!-- Search Input -->
            <div class="relative flex-1">
                <Search class="w-4 h-4 text-gray-400 absolute left-3.5 top-3" />
                <input
                    v-model="search"
                    type="text"
                    placeholder="Search customer name, email, phone, company..."
                    class="w-full pl-10 pr-4 py-2 text-xs bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#F74D01] focus:bg-white outline-none"
                />
            </div>

            <!-- Stage Filter -->
            <select
                v-model="stageId"
                @change="applyFilters"
                class="px-3 py-2 text-xs bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#F74D01] focus:bg-white outline-none text-gray-700"
            >
                <option value="">All Stages</option>
                <option v-for="s in stages" :key="s.id" :value="s.id">{{ s.name }}</option>
            </select>

            <!-- Source Filter -->
            <select
                v-model="sourceId"
                @change="applyFilters"
                class="px-3 py-2 text-xs bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#F74D01] focus:bg-white outline-none text-gray-700"
            >
                <option value="">All Sources</option>
                <option v-for="src in sources" :key="src.id" :value="src.id">{{ src.name }}</option>
            </select>

            <!-- Status Filter -->
            <select
                v-model="status"
                @change="applyFilters"
                class="px-3 py-2 text-xs bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#F74D01] focus:bg-white outline-none text-gray-700"
            >
                <option value="">All Statuses</option>
                <option value="active">Active</option>
                <option value="won">Won</option>
                <option value="lost">Lost</option>
            </select>
        </div>

        <!-- Leads Table -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/70 border-b border-gray-100 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                            <th class="py-3.5 px-4">Prospect / Company</th>
                            <th class="py-3.5 px-4">Contact Details</th>
                            <th class="py-3.5 px-4">Pipeline Stage</th>
                            <th class="py-3.5 px-4">Estimated Value</th>
                            <th class="py-3.5 px-4">Priority</th>
                            <th class="py-3.5 px-4">Assigned Rep</th>
                            <th class="py-3.5 px-4">Last Activity</th>
                            <th class="py-3.5 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-xs text-[#242424]">
                        <tr v-if="leads.data.length === 0">
                            <td colspan="8" class="py-12 text-center text-gray-400 font-medium">
                                No leads match your filters. Try clearing search or capture a new inquiry.
                            </td>
                        </tr>
                        <tr v-for="lead in leads.data" :key="lead.id" class="hover:bg-gray-50/60 transition-colors">
                            <!-- Prospect Info -->
                            <td class="py-3.5 px-4">
                                <Link :href="`/leads/${lead.id}`" class="font-bold text-[#280047] hover:text-[#F74D01] block">
                                    {{ lead.customer_name }}
                                </Link>
                                <span v-if="lead.company_name" class="text-[11px] text-gray-500 block">
                                    {{ lead.company_name }}
                                </span>
                                <span v-if="lead.requested_service" class="text-[10px] text-gray-400 block mt-0.5">
                                    {{ lead.requested_service }}
                                </span>
                            </td>

                            <!-- Contact Details -->
                            <td class="py-3.5 px-4 space-y-0.5">
                                <div v-if="lead.phone" class="flex items-center gap-1.5 text-gray-600">
                                    <Phone class="w-3 h-3 text-gray-400" />
                                    <span>{{ lead.phone }}</span>
                                </div>
                                <div v-if="lead.email" class="flex items-center gap-1.5 text-gray-500">
                                    <Mail class="w-3 h-3 text-gray-400" />
                                    <span>{{ lead.email }}</span>
                                </div>
                                <div v-if="lead.source" class="text-[10px] font-semibold text-gray-400 mt-1">
                                    via {{ lead.source.name }}
                                </div>
                            </td>

                            <!-- Pipeline Stage -->
                            <td class="py-3.5 px-4">
                                <span
                                    v-if="lead.stage"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold"
                                    :style="{ backgroundColor: `${lead.stage.color}15`, color: lead.stage.color }"
                                >
                                    <span class="w-1.5 h-1.5 rounded-full" :style="{ backgroundColor: lead.stage.color }"></span>
                                    {{ lead.stage.name }}
                                </span>
                            </td>

                            <!-- Deal Value -->
                            <td class="py-3.5 px-4 font-bold text-[#280047]">
                                {{ formatMoney(lead.estimated_value, lead.currency) }}
                            </td>

                            <!-- Priority -->
                            <td class="py-3.5 px-4">
                                <span
                                    class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-md"
                                    :class="{
                                        'bg-red-50 text-red-700': lead.priority === 'urgent',
                                        'bg-amber-50 text-amber-700': lead.priority === 'high',
                                        'bg-blue-50 text-blue-700': lead.priority === 'medium',
                                        'bg-gray-50 text-gray-600': lead.priority === 'low'
                                    }"
                                >
                                    {{ lead.priority }}
                                </span>
                            </td>

                            <!-- Assigned Rep -->
                            <td class="py-3.5 px-4">
                                <span v-if="lead.assigned_to" class="font-medium text-gray-700">
                                    {{ lead.assigned_to.name }}
                                </span>
                                <span v-else class="text-gray-400 italic">Unassigned</span>
                            </td>

                            <!-- Last Activity -->
                            <td class="py-3.5 px-4 text-gray-500">
                                <span v-if="lead.last_contacted_at">
                                    {{ new Date(lead.last_contacted_at).toLocaleDateString() }}
                                </span>
                                <span v-else class="text-gray-400">Never</span>
                            </td>

                            <!-- Actions -->
                            <td class="py-3.5 px-4 text-right space-x-1">
                                <Link
                                    :href="`/leads/${lead.id}`"
                                    class="inline-flex p-1.5 text-gray-500 hover:text-[#280047] hover:bg-gray-100 rounded-lg transition-colors"
                                    title="View Dossier"
                                >
                                    <Eye class="w-4 h-4" />
                                </Link>
                                <button
                                    @click="deleteLead(lead)"
                                    class="inline-flex p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                    title="Archive Lead"
                                >
                                    <Trash2 class="w-4 h-4" />
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Bar -->
            <div v-if="leads.links && leads.links.length > 3" class="px-6 py-4 border-t border-gray-100 flex items-center justify-between">
                <span class="text-xs text-gray-500">
                    Showing {{ leads.data.length }} of {{ leads.total }} leads
                </span>
                <div class="flex gap-1">
                    <Link
                        v-for="(link, i) in leads.links"
                        :key="i"
                        :href="link.url || '#'"
                        v-html="link.label"
                        class="px-2.5 py-1 text-xs rounded-lg font-medium transition-colors"
                        :class="link.active ? 'bg-[#280047] text-white' : 'text-gray-600 hover:bg-gray-100'"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
