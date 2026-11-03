<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { FollowUp, PaginatedData, OrganizationMember } from '@/types';

defineProps<{
  followUps: PaginatedData<FollowUp>;
  currentStatus: string;
  counts: {
    upcoming: number;
    overdue: number;
    completed: number;
  };
  team: OrganizationMember[];
}>();

const completingFollowUp = ref<FollowUp | null>(null);
const completeForm = useForm({
  completion_notes: '',
});

const setFilter = (status: string) => {
  router.get('/follow-ups', { status }, { preserveState: true, replace: true });
};

const openCompleteModal = (fu: FollowUp) => {
  completingFollowUp.value = fu;
  completeForm.completion_notes = '';
};

const submitComplete = () => {
  if (!completingFollowUp.value) return;
  completeForm.patch(`/follow-ups/${completingFollowUp.value.id}/complete`, {
    preserveScroll: true,
    onSuccess: () => {
      completingFollowUp.value = null;
    },
  });
};
</script>

<template>
  <Head title="Follow-ups" />
  <AppLayout>
    <div class="space-y-5">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-xl font-bold text-[#242424]">Follow-ups Schedule</h1>
          <p class="text-xs text-[#6B6578] mt-0.5">Manage scheduled calls, WhatsApp check-ins, and client meetings.</p>
        </div>
      </div>

      <!-- Status Tabs -->
      <div class="flex items-center gap-2 border-b border-[#E5E0EC] pb-3">
        <button
          type="button"
          @click="setFilter('upcoming')"
          class="px-3.5 py-2 rounded-md text-xs font-semibold transition-colors flex items-center gap-2"
          :class="
            currentStatus === 'upcoming'
              ? 'bg-[#280047] text-white'
              : 'bg-white text-[#242424] border border-[#E5E0EC] hover:bg-[#F3F1F6]'
          "
        >
          <i class="fa-regular fa-clock"></i> Upcoming
          <span class="px-1.5 py-0.5 rounded bg-black/10 text-[10px] font-mono-num">{{ counts.upcoming }}</span>
        </button>

        <button
          type="button"
          @click="setFilter('overdue')"
          class="px-3.5 py-2 rounded-md text-xs font-semibold transition-colors flex items-center gap-2"
          :class="
            currentStatus === 'overdue'
              ? 'bg-[#F74D01] text-white'
              : 'bg-white text-[#242424] border border-[#E5E0EC] hover:bg-[#F3F1F6]'
          "
        >
          <i class="fa-solid fa-triangle-exclamation"></i> Overdue
          <span class="px-1.5 py-0.5 rounded bg-black/10 text-[10px] font-mono-num">{{ counts.overdue }}</span>
        </button>

        <button
          type="button"
          @click="setFilter('completed')"
          class="px-3.5 py-2 rounded-md text-xs font-semibold transition-colors flex items-center gap-2"
          :class="
            currentStatus === 'completed'
              ? 'bg-[#280047] text-white'
              : 'bg-white text-[#242424] border border-[#E5E0EC] hover:bg-[#F3F1F6]'
          "
        >
          <i class="fa-solid fa-check-double"></i> Completed
          <span class="px-1.5 py-0.5 rounded bg-black/10 text-[10px] font-mono-num">{{ counts.completed }}</span>
        </button>
      </div>

      <!-- Follow-ups List -->
      <div class="app-card overflow-hidden">
        <div v-if="followUps.data.length === 0" class="p-12 text-center">
          <div class="w-10 h-10 rounded-md bg-[#F3F1F6] text-[#280047] flex items-center justify-center mx-auto mb-2.5">
            <i class="fa-regular fa-calendar-check text-sm"></i>
          </div>
          <p class="text-sm font-semibold text-[#242424]">No {{ currentStatus }} follow-ups found</p>
          <p class="text-xs text-[#6B6578] mt-0.5">Schedule follow-ups directly from any lead profile.</p>
        </div>

        <div v-else class="divide-y divide-[#E5E0EC]">
          <div
            v-for="fu in followUps.data"
            :key="fu.id"
            class="p-4 hover:bg-[#FAFAFB] transition-colors flex flex-col sm:flex-row sm:items-center justify-between gap-4"
          >
            <div class="flex items-start gap-3.5">
              <div
                class="w-9 h-9 rounded-md flex items-center justify-center shrink-0 mt-0.5"
                :class="
                  currentStatus === 'overdue'
                    ? 'bg-[#F74D01]/10 text-[#F74D01]'
                    : 'bg-[#F3F1F6] text-[#280047]'
                "
              >
                <i class="fa-regular fa-calendar-check text-sm"></i>
              </div>

              <div>
                <div class="flex flex-wrap items-center gap-2">
                  <Link
                    v-if="fu.lead"
                    :href="`/leads/${fu.lead.id}`"
                    class="text-sm font-bold text-[#242424] hover:text-[#F74D01] transition-colors"
                  >
                    {{ fu.lead.customer_name }}
                  </Link>
                  <span class="px-2 py-0.5 rounded bg-[#F3F1F6] text-[#280047] text-[10px] font-semibold">
                    {{ fu.title }}
                  </span>
                </div>

                <p v-if="fu.notes" class="text-xs text-[#242424] mt-1">{{ fu.notes }}</p>

                <div class="flex flex-wrap items-center gap-4 text-[11px] text-[#6B6578] mt-1.5">
                  <span class="font-mono-num">
                    <i class="fa-regular fa-calendar mr-1"></i>
                    Due: {{ new Date(fu.scheduled_at).toLocaleString() }}
                  </span>
                  <span>
                    <i class="fa-regular fa-user mr-1"></i>
                    Assigned to: <strong class="text-[#242424]">{{ fu.assigned_to?.name || 'Team Member' }}</strong>
                  </span>
                </div>

                <p
                  v-if="fu.completion_notes"
                  class="mt-2 text-xs bg-[#F8F6FA] p-2.5 rounded border border-[#E5E0EC] text-[#242424]"
                >
                  <strong class="text-[#280047]">Outcome:</strong> {{ fu.completion_notes }}
                </p>
              </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
              <Link
                v-if="fu.lead"
                :href="`/leads/${fu.lead.id}`"
                class="btn-secondary text-xs py-1.5 px-3"
              >
                View Lead
              </Link>
              <button
                v-if="fu.status !== 'completed'"
                type="button"
                @click="openCompleteModal(fu)"
                class="btn-primary text-xs py-1.5 px-3"
              >
                <i class="fa-solid fa-check"></i> Mark Done
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Complete Follow-up Modal -->
      <div
        v-if="completingFollowUp"
        class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4"
      >
        <div class="app-card w-full max-w-md p-6">
          <h3 class="text-base font-bold text-[#242424]">Complete Follow-up</h3>
          <p class="text-xs text-[#6B6578] mt-0.5 mb-4">
            Record the outcome of your conversation with
            <strong class="text-[#242424]">{{ completingFollowUp.lead?.customer_name }}</strong>.
          </p>

          <form @submit.prevent="submitComplete" class="space-y-4">
            <div>
              <label class="block text-xs font-medium text-[#242424] mb-1.5">Call / Meeting Outcome</label>
              <textarea
                v-model="completeForm.completion_notes"
                rows="3"
                required
                placeholder="e.g. Client requested a revised quotation with 10% volume discount."
                class="app-input"
              ></textarea>
            </div>

            <div class="flex justify-end gap-2">
              <button type="button" @click="completingFollowUp = null" class="btn-secondary">Cancel</button>
              <button type="submit" :disabled="completeForm.processing" class="btn-primary">
                Save Outcome
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
