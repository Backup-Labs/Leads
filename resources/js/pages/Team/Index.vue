<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { OrganizationMember } from '@/types';

defineProps<{
  members: OrganizationMember[];
  invitations: {
    id: number;
    email: string;
    role: string;
    token: string;
    expires_at: string;
  }[];
  roles: { value: string; label: string }[];
}>();

const showInviteModal = ref(false);

const inviteForm = useForm({
  email: '',
  role: 'sales',
});

const submitInvite = () => {
  inviteForm.post('/team/invite', {
    preserveScroll: true,
    onSuccess: () => {
      showInviteModal.value = false;
      inviteForm.reset();
    },
  });
};

const removeMember = (id: number) => {
  if (confirm('Remove this member from the workspace?')) {
    router.delete(`/team/members/${id}`, { preserveScroll: true });
  }
};
</script>

<template>
  <Head title="Team" />
  <AppLayout>
    <div class="space-y-5">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-xl font-bold text-[#242424]">Team Members</h1>
          <p class="text-xs text-[#6B6578] mt-0.5">Manage workspace access, roles, and pending invitations.</p>
        </div>
        <button type="button" @click="showInviteModal = true" class="btn-primary text-xs">
          <i class="fa-solid fa-user-plus"></i> Invite Team Member
        </button>
      </div>

      <!-- Active Team Table -->
      <div class="app-card overflow-hidden">
        <div class="px-4 sm:px-5 py-4 border-b border-[#E5E0EC]">
          <h2 class="text-sm font-bold text-[#242424]">Active Workspace Members</h2>
        </div>

        <!-- Mobile Member Cards (< 640px) — Zero Content Loss -->
        <div class="sm:hidden divide-y divide-[#E5E0EC] text-xs">
          <div
            v-for="m in members"
            :key="`mob-member-${m.id}`"
            class="p-4 space-y-2.5 hover:bg-[#FAFAFB]"
          >
            <div class="flex items-start justify-between gap-2">
              <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-8 h-8 rounded-md bg-[#280047] text-[#FDFEBA] flex items-center justify-center font-bold text-xs shrink-0">
                  {{ (m.user?.name || 'U').charAt(0).toUpperCase() }}
                </div>
                <div class="min-w-0">
                  <p class="font-semibold text-[#242424] truncate">{{ m.user?.name || 'Member' }}</p>
                  <p class="text-[11px] text-[#6B6578] break-all">{{ m.user?.email || '—' }}</p>
                </div>
              </div>
              <span class="px-2 py-0.5 rounded bg-[#F3F1F6] text-[#280047] text-[10px] font-semibold capitalize shrink-0">
                {{ String(m.role).replace('_', ' ') }}
              </span>
            </div>

            <div class="flex items-center justify-between pt-2 border-t border-[#F0EDF5]">
              <span class="inline-flex items-center gap-1.5 text-emerald-700 font-medium text-[11px]">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span> Active
              </span>
              <button
                v-if="m.role !== 'owner'"
                type="button"
                @click="removeMember(m.id)"
                class="text-xs text-red-600 hover:underline font-medium"
              >
                Remove
              </button>
            </div>
          </div>
        </div>

        <div class="hidden sm:block overflow-x-auto">
          <table class="w-full text-left border-collapse text-xs">
            <thead>
              <tr class="bg-[#F8F6FA] border-b border-[#E5E0EC] text-[11px] font-semibold uppercase text-[#6B6578]">
                <th class="px-4 py-3">Name</th>
                <th class="px-4 py-3">Email</th>
                <th class="px-4 py-3">Role</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#E5E0EC]">
              <tr v-for="m in members" :key="m.id" class="hover:bg-[#FAFAFB]">
                <td class="px-4 py-3.5 font-semibold text-[#242424]">
                  <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-md bg-[#280047] text-[#FDFEBA] flex items-center justify-center font-bold text-xs">
                      {{ (m.user?.name || 'U').charAt(0).toUpperCase() }}
                    </div>
                    <span>{{ m.user?.name || 'Member' }}</span>
                  </div>
                </td>
                <td class="px-4 py-3.5 text-[#6B6578]">{{ m.user?.email || '—' }}</td>
                <td class="px-4 py-3.5">
                  <span class="px-2 py-0.5 rounded bg-[#F3F1F6] text-[#280047] text-[11px] font-semibold capitalize">
                    {{ String(m.role).replace('_', ' ') }}
                  </span>
                </td>
                <td class="px-4 py-3.5">
                  <span class="inline-flex items-center gap-1.5 text-emerald-700 font-medium">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span> Active
                  </span>
                </td>
                <td class="px-4 py-3.5 text-right">
                  <button
                    v-if="m.role !== 'owner'"
                    type="button"
                    @click="removeMember(m.id)"
                    class="text-xs text-red-600 hover:underline font-medium"
                  >
                    Remove
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Pending Invitations -->
      <div v-if="invitations.length > 0" class="app-card overflow-hidden">
        <div class="px-4 sm:px-5 py-4 border-b border-[#E5E0EC]">
          <h2 class="text-sm font-bold text-[#242424]">Pending Invitations</h2>
        </div>
        <div class="divide-y divide-[#E5E0EC] text-xs">
          <div
            v-for="inv in invitations"
            :key="inv.id"
            class="px-4 sm:px-5 py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-2"
          >
            <div>
              <p class="font-semibold text-[#242424] break-all">{{ inv.email }}</p>
              <p class="text-[11px] text-[#6B6578] capitalize">Role: {{ inv.role.replace('_', ' ') }}</p>
            </div>
            <span class="text-[11px] text-[#6B6578] font-mono-num">
              Expires {{ new Date(inv.expires_at).toLocaleDateString() }}
            </span>
          </div>
        </div>
      </div>

      <!-- Invite Modal -->
      <div
        v-if="showInviteModal"
        class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-3 sm:p-4"
      >
        <div class="app-card w-full max-w-md max-h-[90vh] overflow-y-auto p-4 sm:p-6">
          <div class="flex items-center justify-between pb-3 mb-4 border-b border-[#E5E0EC]">
            <h3 class="text-base font-bold text-[#242424]">Invite Team Member</h3>
            <button type="button" @click="showInviteModal = false" class="text-[#6B6578] hover:text-[#242424]">
              <i class="fa-solid fa-xmark"></i>
            </button>
          </div>

          <form @submit.prevent="submitInvite" class="space-y-4">
            <div>
              <label class="block text-xs font-medium text-[#242424] mb-1.5">Email Address</label>
              <input
                v-model="inviteForm.email"
                type="email"
                required
                placeholder="colleague@company.com"
                class="app-input"
              />
            </div>

            <div>
              <label class="block text-xs font-medium text-[#242424] mb-1.5">Role</label>
              <div class="grid grid-cols-1 gap-2">
                <button
                  v-for="r in roles"
                  :key="r.value"
                  type="button"
                  @click="inviteForm.role = r.value"
                  class="p-2.5 rounded-md border text-xs font-medium text-left flex items-center justify-between transition-colors"
                  :class="
                    inviteForm.role === r.value
                      ? 'bg-[#280047] text-white border-[#280047]'
                      : 'bg-white text-[#242424] border-[#E5E0EC] hover:border-[#C3B6D3]'
                  "
                >
                  <span>{{ r.label }}</span>
                  <i v-if="inviteForm.role === r.value" class="fa-solid fa-check text-[#F74D01]"></i>
                </button>
              </div>
            </div>

            <div class="flex flex-wrap justify-end gap-2 pt-3 border-t border-[#E5E0EC]">
              <button type="button" @click="showInviteModal = false" class="btn-secondary">Cancel</button>
              <button type="submit" :disabled="inviteForm.processing" class="btn-primary">
                Send Invitation
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
