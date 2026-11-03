<script setup lang="ts">
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    Plus,
    DollarSign,
    User,
    Clock,
    AlertCircle,
    Building2,
    CheckCircle2
} from 'lucide-vue-next';

const props = defineProps<{
    stages: Array<{
        id: number;
        name: string;
        color: string;
        sort_order: number;
        is_won: boolean;
        is_lost: boolean;
        leads: Array<any>;
    }>;
}>();

const draggedLeadId = ref<number | null>(null);
const draggedSourceStageId = ref<number | null>(null);
const dragOverStageId = ref<number | null>(null);

const onDragStart = (leadId: number, stageId: number) => {
    draggedLeadId.value = leadId;
    draggedSourceStageId.value = stageId;
};

const onDragOver = (stageId: number, event: DragEvent) => {
    event.preventDefault();
    dragOverStageId.value = stageId;
};

const onDragLeave = (stageId: number) => {
    if (dragOverStageId.value === stageId) {
        dragOverStageId.value = null;
    }
};

const onDrop = (targetStageId: number) => {
    if (!draggedLeadId.value || draggedSourceStageId.value === targetStageId) {
        dragOverStageId.value = null;
        return;
    }

    const leadId = draggedLeadId.value;
    dragOverStageId.value = null;

    const targetStage = props.stages.find(s => s.id === targetStageId);
    let lostReason: string | null = null;

    if (targetStage?.is_lost) {
        lostReason = prompt('Please specify reason for lost deal (optional):');
    }

    router.post('/pipeline/stage-update', {
        lead_id: leadId,
        stage_id: targetStageId,
        lost_reason: lostReason,
    }, {
        preserveScroll: true,
    });
};

const formatMoney = (amount: number, currency: string = 'RWF') => {
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    }).format(amount);
};

const calculateStageTotal = (leads: Array<any>) => {
    return leads.reduce((acc, lead) => acc + (Number(lead.estimated_value) || 0), 0);
};
</script>

<template>
    <AppLayout>
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-extrabold text-[#280047]">Interactive Sales Pipeline</h1>
                <p class="text-xs text-gray-500 mt-1">Drag and drop leads to advance deals across pipeline stages</p>
            </div>
            <Link
                href="/leads/create"
                class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold rounded-xl text-white bg-[#F74D01] hover:bg-[#d94200] shadow-sm transition-all"
            >
                <Plus class="w-4 h-4" />
                <span>New Lead</span>
            </Link>
        </div>

        <!-- Horizontal Scrollable Kanban Columns Container -->
        <div class="flex gap-4 overflow-x-auto pb-6 items-start">
            <div
                v-for="stage in stages"
                :key="stage.id"
                @dragover="onDragOver(stage.id, $event)"
                @dragleave="onDragLeave(stage.id)"
                @drop="onDrop(stage.id)"
                class="w-72 flex-shrink-0 bg-gray-100/70 rounded-2xl p-3 flex flex-col max-h-[calc(100vh-200px)] border transition-all"
                :class="dragOverStageId === stage.id ? 'border-[#F74D01] ring-2 ring-orange-200 bg-orange-50/20' : 'border-gray-200/60'"
            >
                <!-- Stage Column Header -->
                <div class="mb-3 px-1.5">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full" :style="{ backgroundColor: stage.color }"></span>
                            <h2 class="text-xs font-bold text-[#280047] uppercase tracking-wider truncate">{{ stage.name }}</h2>
                        </div>
                        <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-white text-gray-700 shadow-2xs">
                            {{ stage.leads.length }}
                        </span>
                    </div>
                    <div class="mt-1 flex items-center justify-between text-[11px] text-gray-500 font-semibold">
                        <span>Total Value</span>
                        <span class="text-[#280047] font-bold">
                            {{ formatMoney(calculateStageTotal(stage.leads)) }}
                        </span>
                    </div>
                </div>

                <!-- Column Cards Scroll Area -->
                <div class="flex-1 overflow-y-auto space-y-2.5 px-0.5">
                    <div v-if="stage.leads.length === 0" class="py-8 text-center border-2 border-dashed border-gray-200 rounded-xl text-xs text-gray-400">
                        Drop leads here
                    </div>

                    <!-- Lead Card -->
                    <div
                        v-for="lead in stage.leads"
                        :key="lead.id"
                        draggable="true"
                        @dragstart="onDragStart(lead.id, stage.id)"
                        class="bg-white p-3.5 rounded-xl border border-gray-200 shadow-2xs hover:shadow-md transition-all cursor-grab active:cursor-grabbing group select-none"
                    >
                        <div class="flex items-start justify-between gap-1 mb-1.5">
                            <Link
                                :href="`/leads/${lead.id}`"
                                class="text-xs font-bold text-[#280047] group-hover:text-[#F74D01] transition-colors leading-tight line-clamp-1"
                            >
                                {{ lead.customer_name }}
                            </Link>
                            <span
                                class="text-[9px] font-bold uppercase px-1.5 py-0.5 rounded flex-shrink-0"
                                :class="{
                                    'bg-red-50 text-red-700': lead.priority === 'urgent',
                                    'bg-amber-50 text-amber-700': lead.priority === 'high',
                                    'bg-blue-50 text-blue-700': lead.priority === 'medium',
                                    'bg-gray-50 text-gray-600': lead.priority === 'low'
                                }"
                            >
                                {{ lead.priority }}
                            </span>
                        </div>

                        <p v-if="lead.company_name" class="text-[11px] text-gray-500 truncate mb-1">
                            {{ lead.company_name }}
                        </p>

                        <!-- Deal Value & Currency -->
                        <div class="mt-2 pt-2 border-t border-gray-100 flex items-center justify-between">
                            <span class="text-xs font-extrabold text-[#280047]">
                                {{ lead.currency }} {{ formatMoney(lead.estimated_value) }}
                            </span>

                            <div class="flex items-center gap-1.5">
                                <span v-if="lead.source" class="text-[9px] font-semibold text-gray-400">
                                    {{ lead.source.name }}
                                </span>
                                <div
                                    v-if="lead.assigned_to"
                                    :title="`Assigned to ${lead.assigned_to.name}`"
                                    class="w-5 h-5 rounded-full bg-[#280047] text-white flex items-center justify-center font-bold text-[9px]"
                                >
                                    {{ lead.assigned_to.name.charAt(0) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
