<script setup lang="ts">
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    CalendarCheck,
    Clock,
    AlertTriangle,
    CheckCircle2,
    Calendar,
    Phone,
    Building2,
    Check
} from 'lucide-vue-next';

const props = defineProps<{
    followUps: {
        data: Array<any>;
        links: Array<any>;
        total: number;
    };
    currentStatus: string;
    counts: {
        upcoming: number;
        overdue: number;
        completed: number;
    };
    team: Array<any>;
}>();

const completeModalOpen = ref(false);
const activeFollowUp = ref<any>(null);
const completionNotes = ref('');

const openCompleteModal = (fu: any) => {
    activeFollowUp.value = fu;
    completionNotes.value = '';
    completeModalOpen.value = true;
};

const confirmComplete = () => {
    if (!activeFollowUp.value) return;

    router.patch(`/follow-ups/${activeFollowUp.value.id}/complete`, {
        completion_notes: completionNotes.value,
    }, {
        onSuccess: () => {
            completeModalOpen.value = false;
            activeFollowUp.value = null;
        }
    });
};
</script>

<template>
    <AppLayout>
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-extrabold text-[#280047]">Follow-up Activity Engine</h1>
                <p class="text-xs text-gray-500 mt-1">Never let an inquiry slip away with structured reminder schedules</p>
            </div>
        </div>

        <!-- Status Filter Tabs -->
        <div class="flex gap-2 border-b border-gray-200 mb-6 pb-2">
            <Link
                href="/follow-ups?status=upcoming"
                class="px-4 py-2 text-xs font-bold rounded-xl flex items-center gap-2 transition-all"
                :class="currentStatus === 'upcoming' ? 'bg-[#280047] text-white' : 'text-gray-600 hover:bg-gray-100'"
            >
                <Clock class="w-4 h-4" />
                <span>Upcoming</span>
                <span class="text-[10px] px-1.5 py-0.2 rounded-full" :class="currentStatus === 'upcoming' ? 'bg-[#F74D01] text-white' : 'bg-gray-200 text-gray-700'">
                    {{ counts.upcoming }}
                </span>
            </Link>

            <Link
                href="/follow-ups?status=overdue"
                class="px-4 py-2 text-xs font-bold rounded-xl flex items-center gap-2 transition-all"
                :class="currentStatus === 'overdue' ? 'bg-[#F74D01] text-white' : 'text-gray-600 hover:bg-gray-100'"
            >
                <AlertTriangle class="w-4 h-4" />
                <span>Overdue</span>
                <span class="text-[10px] px-1.5 py-0.2 rounded-full" :class="currentStatus === 'overdue' ? 'bg-white text-[#F74D01]' : 'bg-red-100 text-red-700'">
                    {{ counts.overdue }}
                </span>
            </Link>

            <Link
                href="/follow-ups?status=completed"
                class="px-4 py-2 text-xs font-bold rounded-xl flex items-center gap-2 transition-all"
                :class="currentStatus === 'completed' ? 'bg-[#280047] text-white' : 'text-gray-600 hover:bg-gray-100'"
            >
                <CheckCircle2 class="w-4 h-4" />
                <span>Completed</span>
                <span class="text-[10px] px-1.5 py-0.2 rounded-full bg-gray-200 text-gray-700">
                    {{ counts.completed }}
                </span>
            </Link>
        </div>

        <!-- Follow-ups List -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div v-if="followUps.data.length === 0" class="py-16 text-center text-xs text-gray-400 font-medium">
                No {{ currentStatus }} follow-ups found.
            </div>

            <div v-else class="divide-y divide-gray-100">
                <div
                    v-for="fu in followUps.data"
                    :key="fu.id"
                    class="p-5 flex flex-col md:flex-row md:items-center justify-between gap-4 hover:bg-gray-50/50 transition-colors"
                >
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="font-bold text-sm text-[#280047]">{{ fu.title }}</span>
                            <span
                                v-if="fu.status === 'pending' && new Date(fu.scheduled_at) < new Date()"
                                class="text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-red-100 text-red-700"
                            >
                                Overdue
                            </span>
                            <span
                                v-else-if="fu.status === 'completed'"
                                class="text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-emerald-100 text-emerald-700"
                            >
                                Completed
                            </span>
                        </div>

                        <!-- Lead Context -->
                        <div class="flex flex-wrap items-center gap-3 text-xs text-gray-600">
                            <Link v-if="fu.lead" :href="`/leads/${fu.lead.id}`" class="font-semibold text-[#F74D01] hover:underline">
                                {{ fu.lead.customer_name }}
                            </Link>
                            <span v-if="fu.lead?.phone" class="text-gray-400">• {{ fu.lead.phone }}</span>
                            <span v-if="fu.assigned_to" class="text-gray-400">• Assigned to {{ fu.assigned_to.name }}</span>
                        </div>

                        <p v-if="fu.notes" class="text-xs text-gray-500 mt-1 italic">{{ fu.notes }}</p>
                        <p v-if="fu.completion_notes" class="text-xs text-emerald-700 mt-1 font-medium">
                            Resolution: {{ fu.completion_notes }}
                        </p>
                    </div>

                    <div class="flex items-center gap-4 flex-shrink-0">
                        <div class="text-left md:text-right text-xs">
                            <span class="text-gray-400 block text-[10px] uppercase font-bold">Scheduled Time</span>
                            <span class="font-bold text-[#280047]">{{ new Date(fu.scheduled_at).toLocaleString() }}</span>
                        </div>

                        <button
                            v-if="fu.status === 'pending'"
                            @click="openCompleteModal(fu)"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-xl text-white bg-emerald-600 hover:bg-emerald-700 shadow-xs transition-colors"
                        >
                            <Check class="w-3.5 h-3.5" />
                            <span>Mark Done</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mark Done Modal -->
        <div v-if="completeModalOpen" class="fixed inset-0 bg-black/40 flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl space-y-4">
                <h3 class="text-sm font-bold text-[#280047]">Complete Follow-up</h3>
                <p class="text-xs text-gray-500">Record any notes or outcome from your conversation with the customer.</p>

                <div>
                    <label class="block text-xs font-semibold text-gray-700">Outcome Notes (Optional)</label>
                    <textarea
                        v-model="completionNotes"
                        rows="3"
                        placeholder="Customer requested a revised quotation with 10% discount..."
                        class="mt-1 block w-full px-3 py-2 text-xs bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#F74D01] outline-none"
                    ></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button
                        @click="completeModalOpen = false"
                        class="px-3 py-1.5 text-xs text-gray-500 hover:text-gray-700"
                    >
                        Cancel
                    </button>
                    <button
                        @click="confirmComplete"
                        class="px-4 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition-all"
                    >
                        Mark Done
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
