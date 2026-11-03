<script setup lang="ts">
import { ref } from 'vue';
import { useForm, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    ArrowLeft,
    Phone,
    Mail,
    Building2,
    Calendar,
    Clock,
    FileText,
    CheckCircle2,
    Plus,
    UserCheck,
    MessageSquare,
    Zap,
    AlertCircle
} from 'lucide-vue-next';

const props = defineProps<{
    lead: any;
    stages: Array<any>;
    team: Array<any>;
}>();

const newNote = ref('');
const isLoggingNote = ref(false);

const followUpModalOpen = ref(false);
const followUpForm = useForm({
    lead_id: props.lead.id,
    title: '',
    scheduled_at: '',
    assigned_to_user_id: props.lead.assigned_to_user_id || '',
    notes: '',
});

const submitNote = () => {
    if (!newNote.value.trim()) return;
    router.post(`/leads/${props.lead.id}/notes`, { note: newNote.value }, {
        onSuccess: () => {
            newNote.value = '';
            isLoggingNote.value = false;
        }
    });
};

const changeStage = (stageId: number) => {
    router.patch(`/leads/${props.lead.id}/stage`, { pipeline_stage_id: stageId });
};

const changeAssignee = (userId: string) => {
    router.patch(`/leads/${props.lead.id}/assign`, { assigned_to_user_id: userId || null });
};

const submitFollowUp = () => {
    followUpForm.post('/follow-ups', {
        onSuccess: () => {
            followUpModalOpen.value = false;
            followUpForm.reset('title', 'scheduled_at', 'notes');
        }
    });
};

const completeFollowUp = (id: number) => {
    router.patch(`/follow-ups/${id}/complete`);
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
        <!-- Top Back Navigation -->
        <div class="mb-4">
            <Link href="/leads" class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-500 hover:text-[#280047]">
                <ArrowLeft class="w-4 h-4" />
                <span>Back to Leads</span>
            </Link>
        </div>

        <!-- Lead Dossier Hero Card -->
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm mb-6 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="flex items-center gap-2.5">
                    <h1 class="text-2xl font-extrabold text-[#280047]">{{ lead.customer_name }}</h1>
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
                    <span
                        v-if="lead.status === 'won'"
                        class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200"
                    >
                        Won
                    </span>
                    <span
                        v-else-if="lead.status === 'lost'"
                        class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-md bg-rose-50 text-rose-700 border border-rose-200"
                    >
                        Lost
                    </span>
                </div>
                <p v-if="lead.company_name" class="text-xs font-semibold text-gray-600 mt-0.5">{{ lead.company_name }}</p>
                <p v-if="lead.requested_service" class="text-xs text-gray-500 mt-1">Interested in: <strong class="text-gray-700">{{ lead.requested_service }}</strong></p>
            </div>

            <!-- Value & Stage Controls -->
            <div class="flex flex-wrap items-center gap-4">
                <div class="text-left md:text-right">
                    <span class="text-[10px] uppercase font-bold text-gray-400 block tracking-wider">Opportunity Value</span>
                    <span class="text-xl font-extrabold text-[#280047]">{{ formatMoney(lead.estimated_value, lead.currency) }}</span>
                </div>

                <div class="flex flex-col gap-1">
                    <span class="text-[10px] uppercase font-bold text-gray-400 tracking-wider">Stage</span>
                    <select
                        :value="lead.pipeline_stage_id"
                        @change="changeStage(Number(($event.target as HTMLSelectElement).value))"
                        class="px-3 py-1.5 text-xs font-bold rounded-xl border border-gray-200 bg-gray-50 focus:ring-2 focus:ring-[#F74D01] outline-none"
                    >
                        <option v-for="s in stages" :key="s.id" :value="s.id">{{ s.name }}</option>
                    </select>
                </div>

                <div class="flex flex-col gap-1">
                    <span class="text-[10px] uppercase font-bold text-gray-400 tracking-wider">Assigned Rep</span>
                    <select
                        :value="lead.assigned_to_user_id || ''"
                        @change="changeAssignee(($event.target as HTMLSelectElement).value)"
                        class="px-3 py-1.5 text-xs font-semibold rounded-xl border border-gray-200 bg-gray-50 focus:ring-2 focus:ring-[#F74D01] outline-none"
                    >
                        <option value="">Unassigned</option>
                        <option v-for="m in team" :key="m.user_id" :value="m.user_id">{{ m.user.name }}</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Quick Action Trigger Buttons -->
        <div class="flex flex-wrap items-center gap-2 mb-6">
            <button
                @click="isLoggingNote = !isLoggingNote"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-xl bg-white border border-gray-200 hover:bg-gray-50 text-[#280047] shadow-sm transition-all"
            >
                <MessageSquare class="w-4 h-4 text-[#F74D01]" />
                <span>Log Note / Call</span>
            </button>
            <button
                @click="followUpModalOpen = true"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-xl bg-white border border-gray-200 hover:bg-gray-50 text-[#280047] shadow-sm transition-all"
            >
                <Calendar class="w-4 h-4 text-[#F74D01]" />
                <span>Schedule Follow-up</span>
            </button>
            <Link
                :href="`/quotations/create?lead_id=${lead.id}`"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold rounded-xl text-white bg-[#F74D01] hover:bg-[#d94200] shadow-sm transition-all"
            >
                <FileText class="w-4 h-4" />
                <span>Draft Quotation</span>
            </Link>
        </div>

        <!-- Expandable Note Logging Box -->
        <div v-if="isLoggingNote" class="bg-white p-4 rounded-2xl border border-gray-200 shadow-sm mb-6">
            <h3 class="text-xs font-bold text-[#280047] mb-2">Record Conversation or Note</h3>
            <textarea
                v-model="newNote"
                rows="3"
                placeholder="Spoke with client on phone, agreed to review quotation on Thursday..."
                class="w-full p-3 text-xs bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#F74D01] focus:bg-white outline-none"
            ></textarea>
            <div class="mt-2 flex justify-end gap-2">
                <button @click="isLoggingNote = false" class="px-3 py-1.5 text-xs text-gray-500 hover:text-gray-700">Cancel</button>
                <button
                    @click="submitNote"
                    class="px-4 py-1.5 text-xs font-bold text-white bg-[#280047] hover:bg-[#3a0066] rounded-xl transition-colors"
                >
                    Save Activity
                </button>
            </div>
        </div>

        <!-- Two Column Layout: Timeline & Right Details Sidebar -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left: Interaction Timeline -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Activity History Card -->
                <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
                    <h2 class="text-base font-bold text-[#280047] mb-4">Customer Journey & Activity History</h2>
                    <div v-if="lead.activities.length === 0" class="text-xs text-gray-400 py-6 text-center">
                        No interactions recorded yet. Click 'Log Note / Call' above to start tracking.
                    </div>
                    <div v-else class="relative border-l-2 border-gray-100 ml-4 pl-4 space-y-5">
                        <div v-for="act in lead.activities" :key="act.id" class="relative">
                            <!-- Bullet Icon -->
                            <div class="absolute -left-[23px] top-0 w-3 h-3 rounded-full bg-[#F74D01] border-2 border-white shadow-xs"></div>
                            <div>
                                <div class="flex items-center justify-between">
                                    <p class="text-xs font-bold text-[#280047]">
                                        {{ act.subject }}
                                        <span class="text-[11px] font-normal text-gray-400 ml-1">by {{ act.user?.name || 'System' }}</span>
                                    </p>
                                    <span class="text-[10px] text-gray-400">{{ new Date(act.created_at).toLocaleString() }}</span>
                                </div>
                                <p v-if="act.description" class="text-xs text-gray-600 mt-1 whitespace-pre-line">{{ act.description }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Details Sidebar -->
            <div class="space-y-6">
                <!-- Contact Details -->
                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
                    <h3 class="text-xs font-bold text-[#280047] uppercase tracking-wider mb-3">Contact Dossier</h3>
                    <div class="space-y-2.5 text-xs">
                        <div class="flex items-center gap-2 text-gray-600">
                            <Phone class="w-4 h-4 text-gray-400" />
                            <span>{{ lead.phone || 'No phone recorded' }}</span>
                        </div>
                        <div class="flex items-center gap-2 text-gray-600">
                            <Mail class="w-4 h-4 text-gray-400" />
                            <span>{{ lead.email || 'No email recorded' }}</span>
                        </div>
                        <div class="flex items-center gap-2 text-gray-600">
                            <Building2 class="w-4 h-4 text-gray-400" />
                            <span>{{ lead.company_name || 'Individual Prospect' }}</span>
                        </div>
                        <div class="pt-2 border-t border-gray-100 text-[11px] text-gray-400">
                            Source: <strong class="text-gray-600">{{ lead.source?.name || 'Direct' }}</strong>
                        </div>
                    </div>
                </div>

                <!-- Scheduled Follow-ups -->
                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-xs font-bold text-[#280047] uppercase tracking-wider">Follow-up Reminders</h3>
                        <button @click="followUpModalOpen = true" class="text-xs text-[#F74D01] font-bold hover:underline">+ Add</button>
                    </div>

                    <div v-if="lead.follow_ups.length === 0" class="text-xs text-gray-400 py-3 text-center">
                        No reminders scheduled.
                    </div>
                    <div v-else class="space-y-2.5">
                        <div
                            v-for="fu in lead.follow_ups"
                            :key="fu.id"
                            class="p-2.5 rounded-xl border border-gray-100 bg-gray-50/50 flex items-center justify-between gap-2"
                        >
                            <div class="min-w-0 text-xs">
                                <p class="font-bold text-[#280047] truncate">{{ fu.title }}</p>
                                <p class="text-[10px] text-gray-500">{{ new Date(fu.scheduled_at).toLocaleString() }}</p>
                                <span
                                    v-if="fu.status === 'completed'"
                                    class="text-[9px] text-emerald-600 font-bold bg-emerald-50 px-1 py-0.5 rounded"
                                >
                                    Completed
                                </span>
                            </div>
                            <button
                                v-if="fu.status === 'pending'"
                                @click="completeFollowUp(fu.id)"
                                class="text-gray-400 hover:text-emerald-600 p-1"
                                title="Mark Completed"
                            >
                                <CheckCircle2 class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Associated Quotations -->
                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-xs font-bold text-[#280047] uppercase tracking-wider">Quotations</h3>
                        <Link :href="`/quotations/create?lead_id=${lead.id}`" class="text-xs text-[#F74D01] font-bold hover:underline">+ Draft</Link>
                    </div>

                    <div v-if="lead.quotations.length === 0" class="text-xs text-gray-400 py-3 text-center">
                        No quotations created yet.
                    </div>
                    <div v-else class="space-y-2.5">
                        <div
                            v-for="quote in lead.quotations"
                            :key="quote.id"
                            class="p-2.5 rounded-xl border border-gray-100 bg-gray-50/50 flex items-center justify-between"
                        >
                            <div class="text-xs">
                                <Link :href="`/quotations/${quote.id}`" class="font-bold text-[#280047] hover:underline">
                                    #{{ quote.quotation_number }}
                                </Link>
                                <p class="text-[11px] font-semibold text-[#F74D01]">{{ formatMoney(quote.total_amount, quote.currency) }}</p>
                            </div>
                            <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded" :class="{
                                'bg-gray-100 text-gray-700': quote.status === 'draft',
                                'bg-blue-50 text-blue-700': quote.status === 'sent',
                                'bg-emerald-50 text-emerald-700': quote.status === 'accepted',
                                'bg-rose-50 text-rose-700': quote.status === 'rejected',
                            }">
                                {{ quote.status }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Schedule Follow-Up Modal -->
        <div v-if="followUpModalOpen" class="fixed inset-0 bg-black/40 flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl space-y-4">
                <h3 class="text-sm font-bold text-[#280047]">Schedule Follow-up Reminder</h3>

                <div>
                    <label class="block text-xs font-semibold text-gray-700">Follow-up Objective / Title *</label>
                    <input
                        v-model="followUpForm.title"
                        type="text"
                        required
                        placeholder="e.g. Call to discuss quotation discount"
                        class="mt-1 block w-full px-3 py-2 text-xs bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#F74D01] outline-none"
                    />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700">Scheduled Date & Time *</label>
                    <input
                        v-model="followUpForm.scheduled_at"
                        type="datetime-local"
                        required
                        class="mt-1 block w-full px-3 py-2 text-xs bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#F74D01] outline-none"
                    />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700">Assignee</label>
                    <select
                        v-model="followUpForm.assigned_to_user_id"
                        class="mt-1 block w-full px-3 py-2 text-xs bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#F74D01] outline-none"
                    >
                        <option v-for="m in team" :key="m.user_id" :value="m.user_id">{{ m.user.name }}</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700">Notes (Optional)</label>
                    <textarea
                        v-model="followUpForm.notes"
                        rows="2"
                        placeholder="Specific questions to ask or details to confirm..."
                        class="mt-1 block w-full px-3 py-2 text-xs bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#F74D01] outline-none"
                    ></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button
                        @click="followUpModalOpen = false"
                        class="px-3 py-1.5 text-xs text-gray-500 hover:text-gray-700"
                    >
                        Cancel
                    </button>
                    <button
                        @click="submitFollowUp"
                        :disabled="followUpForm.processing"
                        class="px-4 py-2 text-xs font-bold text-white bg-[#F74D01] hover:bg-[#d94200] rounded-xl transition-all"
                    >
                        Save Reminder
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
