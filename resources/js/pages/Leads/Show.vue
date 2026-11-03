<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import CustomSelect from '@/components/CustomSelect.vue';
import CustomDatePicker from '@/components/CustomDatePicker.vue';
import { formatCurrencyAmount } from '@/utils/currency';
import type { Lead, PageProps, PipelineStage, OrganizationMember } from '@/types';

const props = defineProps<{
  lead: Lead;
  stages: PipelineStage[];
  team: OrganizationMember[];
}>();

const page = usePage<PageProps>();
const currency = computed(() => props.lead.currency || page.props.auth?.currentOrganization?.currency || 'UGX');

const showFollowUpModal = ref(false);
const followUpStep = ref(1);
const showLostModal = ref(false);
const showNoteModal = ref(false);

const teamOptions = computed(() =>
  props.team.map((m) => ({
    value: m.user_id,
    label: m.user?.name || 'Team Member',
    sublabel: m.user?.email,
    icon: 'fa-solid fa-user-tie',
  }))
);

const stageForm = useForm({
  pipeline_stage_id: props.lead.pipeline_stage_id,
  lost_reason: '',
});

const changeStage = (stage: PipelineStage) => {
  if (stage.is_lost) {
    stageForm.pipeline_stage_id = stage.id;
    showLostModal.value = true;
    return;
  }
  router.patch(`/leads/${props.lead.id}/stage`, { pipeline_stage_id: stage.id }, { preserveScroll: true });
};

const submitLostStage = () => {
  stageForm.patch(`/leads/${props.lead.id}/stage`, {
    preserveScroll: true,
    onSuccess: () => {
      showLostModal.value = false;
    },
  });
};

const followUpForm = useForm({
  lead_id: props.lead.id,
  title: 'Follow-up Call / Check-in',
  assigned_to_user_id: props.lead.assigned_to_user_id || page.props.auth?.user?.id || '',
  scheduled_at: new Date(Date.now() + 86400000).toISOString().slice(0, 16),
  notes: '',
});

const submitFollowUp = () => {
  followUpForm.post('/follow-ups', {
    preserveScroll: true,
    onSuccess: () => {
      showFollowUpModal.value = false;
      followUpStep.value = 1;
      followUpForm.reset('notes');
    },
  });
};

const noteForm = useForm({
  note: '',
});

const submitNote = () => {
  noteForm.post(`/leads/${props.lead.id}/notes`, {
    preserveScroll: true,
    onSuccess: () => {
      showNoteModal.value = false;
      noteForm.reset();
    },
  });
};

const formatMoney = (amount: number) => {
  return formatCurrencyAmount(amount, currency.value);
};
</script>

<template>
  <Head :title="lead.customer_name" />
  <AppLayout>
    <div class="space-y-5">
      <!-- Top Breadcrumb & Actions -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-2 text-xs text-[#6B6578]">
          <Link href="/leads" class="hover:text-[#280047] font-medium flex items-center gap-1.5">
            <i class="fa-solid fa-arrow-left text-[10px]"></i> All Leads
          </Link>
          <span>/</span>
          <span class="text-[#242424] font-semibold">{{ lead.customer_name }}</span>
        </div>

        <div class="flex flex-wrap items-center gap-2">
          <button type="button" @click="showNoteModal = true" class="btn-secondary text-xs">
            <i class="fa-regular fa-note-sticky text-[#280047]"></i> Add Note
          </button>
          <button type="button" @click="showFollowUpModal = true" class="btn-secondary text-xs">
            <i class="fa-regular fa-calendar-plus text-[#F74D01]"></i> Schedule Follow-up
          </button>
          <Link :href="`/quotations/create?lead_id=${lead.id}`" class="btn-primary text-xs">
            <i class="fa-solid fa-file-invoice"></i> Create Quotation
          </Link>
        </div>
      </div>

      <!-- Lead Summary Header Card -->
      <div class="app-card p-5 border-t-2 border-t-[#280047]">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-5 border-b border-[#E5E0EC]">
          <div class="flex items-start gap-3.5">
            <div class="w-11 h-11 rounded-md bg-[#280047] text-[#FDFEBA] flex items-center justify-center font-bold text-base shrink-0">
              {{ lead.customer_name.charAt(0).toUpperCase() }}
            </div>
            <div>
              <div class="flex flex-wrap items-center gap-2">
                <h1 class="text-lg font-bold text-[#242424]">{{ lead.customer_name }}</h1>
                <span
                  class="px-2 py-0.5 rounded text-[11px] font-semibold uppercase"
                  :class="
                    lead.priority === 'urgent' || lead.priority === 'high'
                      ? 'bg-[#F74D01]/10 text-[#F74D01]'
                      : 'bg-[#F3F1F6] text-[#280047]'
                  "
                >
                  {{ lead.priority }} Priority
                </span>
                <span
                  class="px-2 py-0.5 rounded text-[11px] font-semibold uppercase bg-[#FDFEBA] text-[#280047] border border-[#C3B6D3]"
                >
                  {{ lead.status }}
                </span>
              </div>
              <p class="text-xs text-[#6B6578] mt-0.5">
                {{ lead.company_name || 'Individual Prospect' }} &bull;
                {{ lead.requested_service || 'General Inquiry' }}
              </p>
            </div>
          </div>

          <div class="flex flex-wrap items-center gap-4 sm:gap-6">
            <div>
              <p class="text-[11px] uppercase tracking-wider text-[#6B6578] font-medium">Estimated Value</p>
              <p class="text-lg sm:text-xl font-bold text-[#280047] font-mono-num break-all sm:break-normal">
                {{ formatMoney(Number(lead.estimated_value)) }}
              </p>
            </div>
            <div class="sm:pl-6 sm:border-l border-[#E5E0EC]">
              <p class="text-[11px] uppercase tracking-wider text-[#6B6578] font-medium">Assigned Owner</p>
              <p class="text-sm font-semibold text-[#242424] mt-0.5">
                {{ lead.assigned_to?.name || 'Unassigned' }}
              </p>
            </div>
          </div>
        </div>

        <!-- Stage Progress Bar -->
        <div class="pt-4">
          <p class="text-[11px] font-semibold uppercase tracking-wider text-[#6B6578] mb-2">
            Pipeline Stage (Click to update)
          </p>
          <div class="grid grid-cols-1 min-[340px]:grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-1.5">
            <button
              v-for="s in stages"
              :key="s.id"
              type="button"
              @click="changeStage(s)"
              class="px-3 py-2 rounded-md text-xs font-medium border text-center transition-colors cursor-pointer"
              :class="
                lead.pipeline_stage_id === s.id
                  ? 'bg-[#280047] text-white border-[#280047]'
                  : 'bg-[#FAFAFB] text-[#242424] border-[#E5E0EC] hover:border-[#C3B6D3] hover:bg-[#F3F1F6]'
              "
            >
              <i
                v-if="lead.pipeline_stage_id === s.id"
                class="fa-solid fa-circle-check text-[#F74D01] mr-1 text-[10px]"
              ></i>
              {{ s.name }}
            </button>
          </div>
        </div>
      </div>

      <!-- Main Details Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <!-- Left Column: Contact Info, Follow-ups, Quotations -->
        <div class="space-y-5">
          <!-- Contact Card -->
          <div class="app-card p-4 sm:p-5">
            <h2 class="text-xs font-bold uppercase tracking-wider text-[#280047] pb-3 mb-3 border-b border-[#E5E0EC]">
              Contact Details
            </h2>
            <dl class="space-y-3 text-xs">
              <div class="flex flex-col min-[360px]:flex-row min-[360px]:items-center justify-between gap-0.5">
                <dt class="text-[#6B6578] flex items-center gap-2">
                  <i class="fa-solid fa-phone w-4 text-center text-[#280047]"></i> Phone
                </dt>
                <dd class="font-mono-num font-semibold text-[#242424] break-all">{{ lead.phone || 'Not provided' }}</dd>
              </div>
              <div class="flex flex-col min-[360px]:flex-row min-[360px]:items-center justify-between gap-0.5">
                <dt class="text-[#6B6578] flex items-center gap-2">
                  <i class="fa-regular fa-envelope w-4 text-center text-[#280047]"></i> Email
                </dt>
                <dd class="font-medium text-[#242424] break-all">{{ lead.email || 'Not provided' }}</dd>
              </div>
              <div class="flex flex-col min-[360px]:flex-row min-[360px]:items-center justify-between gap-0.5">
                <dt class="text-[#6B6578] flex items-center gap-2">
                  <i class="fa-solid fa-bullhorn w-4 text-center text-[#280047]"></i> Lead Source
                </dt>
                <dd class="font-medium text-[#242424]">{{ lead.source?.name || 'Direct' }}</dd>
              </div>
              <div class="pt-3 border-t border-[#E5E0EC]">
                <dt class="text-[#6B6578] mb-1">Description / Requirements</dt>
                <dd class="text-[#242424] bg-[#FAFAFB] p-3 rounded-md border border-[#E5E0EC] leading-relaxed">
                  {{ lead.description || 'No initial notes recorded.' }}
                </dd>
              </div>
            </dl>
          </div>

          <!-- Scheduled Follow-ups Card -->
          <div class="app-card p-5">
            <div class="flex items-center justify-between pb-3 mb-3 border-b border-[#E5E0EC]">
              <h2 class="text-xs font-bold uppercase tracking-wider text-[#280047]">Follow-ups</h2>
              <button
                type="button"
                @click="showFollowUpModal = true"
                class="text-xs font-semibold text-[#F74D01] hover:underline cursor-pointer"
              >
                + Add
              </button>
            </div>

            <div v-if="!lead.follow_ups?.length" class="text-center py-6 text-xs text-[#6B6578]">
              No follow-ups scheduled yet.
            </div>

            <div v-else class="space-y-2.5">
              <div
                v-for="fu in lead.follow_ups"
                :key="fu.id"
                class="p-3 rounded-md border border-[#E5E0EC] bg-[#FAFAFB] text-xs"
              >
                <div class="flex items-center justify-between mb-1">
                  <span class="font-semibold text-[#280047]">{{ fu.title }}</span>
                  <span
                    class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase"
                    :class="
                      fu.status === 'completed'
                        ? 'bg-emerald-50 text-emerald-700'
                        : 'bg-[#FDFEBA] text-[#280047]'
                    "
                  >
                    {{ fu.status }}
                  </span>
                </div>
                <p v-if="fu.notes" class="text-[#6B6578]">{{ fu.notes }}</p>
                <p class="text-[11px] text-[#6B6578] font-mono-num mt-1">
                  Due: {{ new Date(fu.scheduled_at).toLocaleString() }}
                </p>
              </div>
            </div>
          </div>

          <!-- Quotations Card -->
          <div class="app-card p-5">
            <div class="flex items-center justify-between pb-3 mb-3 border-b border-[#E5E0EC]">
              <h2 class="text-xs font-bold uppercase tracking-wider text-[#280047]">Quotations</h2>
              <Link
                :href="`/quotations/create?lead_id=${lead.id}`"
                class="text-xs font-semibold text-[#F74D01] hover:underline"
              >
                + New Quote
              </Link>
            </div>

            <div v-if="!lead.quotations?.length" class="text-center py-6 text-xs text-[#6B6578]">
              No quotations created for this lead.
            </div>

            <div v-else class="space-y-2.5">
              <Link
                v-for="q in lead.quotations"
                :key="q.id"
                :href="`/quotations/${q.id}`"
                class="block p-3 rounded-md border border-[#E5E0EC] hover:border-[#280047] transition-colors text-xs"
              >
                <div class="flex items-center justify-between">
                  <span class="font-bold text-[#280047] font-mono-num">{{ q.quotation_number }}</span>
                  <span class="px-2 py-0.5 rounded bg-[#F3F1F6] text-[#280047] text-[10px] font-semibold uppercase">
                    {{ q.status }}
                  </span>
                </div>
                <p class="text-sm font-bold text-[#242424] font-mono-num mt-1">
                  {{ formatMoney(Number(q.total_amount)) }}
                </p>
              </Link>
            </div>
          </div>
        </div>

        <!-- Right Column: Activity Timeline -->
        <div class="lg:col-span-2">
          <div class="app-card p-5">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-[#E5E0EC]">
              <div>
                <h2 class="text-sm font-bold text-[#242424]">Activity History</h2>
                <p class="text-xs text-[#6B6578]">Chronological log of stage updates, notes, follow-ups, and quotations</p>
              </div>
              <span class="text-xs font-mono-num text-[#6B6578]">
                {{ lead.activities?.length || 0 }} entries
              </span>
            </div>

            <div v-if="!lead.activities?.length" class="text-center py-12 text-xs text-[#6B6578]">
              No activity recorded yet.
            </div>

            <div v-else class="space-y-4">
              <div
                v-for="act in lead.activities"
                :key="act.id"
                class="flex gap-3.5 pb-4 border-b border-[#E5E0EC] last:border-b-0 last:pb-0"
              >
                <div class="w-8 h-8 rounded-md bg-[#F3F1F6] text-[#280047] flex items-center justify-center shrink-0 mt-0.5">
                  <i class="fa-solid fa-clock-rotate-left text-xs"></i>
                </div>
                <div class="flex-1 min-w-0">
                  <div class="flex flex-wrap items-center justify-between gap-2">
                    <p class="text-xs font-semibold text-[#242424]">{{ act.subject }}</p>
                    <span class="text-[11px] text-[#6B6578] font-mono-num">
                      {{ new Date(act.created_at).toLocaleString() }}
                    </span>
                  </div>
                  <p v-if="act.description" class="text-xs text-[#242424] mt-0.5">{{ act.description }}</p>
                  <p class="text-[11px] text-[#6B6578] mt-0.5">
                    Logged by <span class="font-medium text-[#242424]">{{ act.user?.name || 'System' }}</span>
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Schedule Follow-up Modal (2 Steps with Custom DatePicker & CustomSelect) -->
      <div
        v-if="showFollowUpModal"
        class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-3 sm:p-4"
      >
        <div class="app-card w-full max-w-md max-h-[90vh] overflow-y-auto p-4 sm:p-6">
          <div class="flex items-center justify-between pb-3 mb-4 border-b border-[#E5E0EC]">
            <div>
              <span class="text-[11px] font-semibold uppercase tracking-wider text-[#F74D01]">
                Step {{ followUpStep }} of 2
              </span>
              <h3 class="text-base font-bold text-[#242424]">Schedule Follow-up</h3>
            </div>
            <button
              type="button"
              @click="showFollowUpModal = false"
              class="text-[#6B6578] hover:text-[#242424] cursor-pointer"
            >
              <i class="fa-solid fa-xmark"></i>
            </button>
          </div>

          <form @submit.prevent="submitFollowUp" class="space-y-4">
            <div v-if="followUpStep === 1" class="space-y-4">
              <div>
                <label class="block text-xs font-medium text-[#242424] mb-1.5">Follow-up Title</label>
                <input
                  v-model="followUpForm.title"
                  type="text"
                  required
                  placeholder="e.g. WhatsApp check-in on proposal"
                  class="app-input"
                />
              </div>

              <div>
                <label class="block text-xs font-medium text-[#242424] mb-1.5">Scheduled Date & Time</label>
                <CustomDatePicker
                  v-model="followUpForm.scheduled_at"
                  mode="datetime"
                  placeholder="Select date and time"
                />
              </div>

              <div class="flex flex-wrap justify-end gap-2 pt-3 border-t border-[#E5E0EC]">
                <button type="button" @click="showFollowUpModal = false" class="btn-secondary">Cancel</button>
                <button type="button" @click="followUpStep = 2" class="btn-indigo">
                  Next <i class="fa-solid fa-arrow-right text-xs"></i>
                </button>
              </div>
            </div>

            <div v-if="followUpStep === 2" class="space-y-4">
              <div>
                <label class="block text-xs font-medium text-[#242424] mb-1.5">Assign To</label>
                <CustomSelect
                  v-model="followUpForm.assigned_to_user_id"
                  :options="teamOptions"
                  placeholder="Select team member"
                />
              </div>

              <div>
                <label class="block text-xs font-medium text-[#242424] mb-1.5">Follow-up Notes</label>
                <textarea
                  v-model="followUpForm.notes"
                  rows="3"
                  placeholder="What needs to be discussed or sent?"
                  class="app-input"
                ></textarea>
              </div>

              <div class="flex flex-wrap justify-between gap-2 pt-3 border-t border-[#E5E0EC]">
                <button type="button" @click="followUpStep = 1" class="btn-secondary">Back</button>
                <button type="submit" :disabled="followUpForm.processing" class="btn-primary">
                  Save Follow-up
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>

      <!-- Add Note Modal -->
      <div
        v-if="showNoteModal"
        class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-3 sm:p-4"
      >
        <div class="app-card w-full max-w-md max-h-[90vh] overflow-y-auto p-4 sm:p-6">
          <h3 class="text-base font-bold text-[#242424] mb-1">Add Activity Note</h3>
          <p class="text-xs text-[#6B6578] mb-4">Record a call summary, meeting update, or internal note.</p>
          <form @submit.prevent="submitNote" class="space-y-4">
            <div>
              <textarea
                v-model="noteForm.note"
                rows="3"
                required
                placeholder="Write your note here..."
                class="app-input"
              ></textarea>
            </div>
            <div class="flex flex-wrap justify-end gap-2">
              <button type="button" @click="showNoteModal = false" class="btn-secondary">Cancel</button>
              <button type="submit" :disabled="noteForm.processing" class="btn-primary">Save Note</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Mark as Lost Modal -->
      <div
        v-if="showLostModal"
        class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-3 sm:p-4"
      >
        <div class="app-card w-full max-w-md max-h-[90vh] overflow-y-auto p-4 sm:p-6">
          <h3 class="text-base font-bold text-[#242424] mb-1">Mark Deal as Lost</h3>
          <p class="text-xs text-[#6B6578] mb-4">Please record why this opportunity did not close.</p>
          <form @submit.prevent="submitLostStage" class="space-y-4">
            <div>
              <label class="block text-xs font-medium text-[#242424] mb-1.5">Reason for Loss</label>
              <textarea
                v-model="stageForm.lost_reason"
                rows="3"
                required
                placeholder="e.g. Budget constraints, chose competitor, project delayed..."
                class="app-input"
              ></textarea>
            </div>
            <div class="flex flex-wrap justify-end gap-2">
              <button type="button" @click="showLostModal = false" class="btn-secondary">Cancel</button>
              <button type="submit" class="btn-primary">Confirm Lost</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
