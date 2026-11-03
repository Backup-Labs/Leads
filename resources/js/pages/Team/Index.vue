<script setup lang="ts">
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { UserCheck, UserPlus, Mail, Shield, Trash2, Clock } from 'lucide-vue-next';

const props = defineProps<{
    members: Array<any>;
    invitations: Array<any>;
    roles: Array<{ value: string; label: string }>;
}>();

const inviteModalOpen = ref(false);
const inviteForm = useForm({
    email: '',
    role: 'sales',
});

const submitInvite = () => {
    inviteForm.post('/team/invite', {
        onSuccess: () => {
            inviteModalOpen.value = false;
            inviteForm.reset();
        }
    });
};

const removeMember = (member: any) => {
    if (confirm(`Remove ${member.user?.name} from this organization workspace?`)) {
        router.delete(`/team/members/${member.id}`);
    }
};
</script>

<template>
    <AppLayout>
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-extrabold text-[#280047]">Team & Workspace Access</h1>
                <p class="text-xs text-gray-500 mt-1">Manage employee memberships, operational roles, and team invitations</p>
            </div>

            <button
                @click="inviteModalOpen = true"
                class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold rounded-xl text-white bg-[#F74D01] hover:bg-[#d94200] shadow-sm transition-all"
            >
                <UserPlus class="w-4 h-4" />
                <span>Invite Employee</span>
            </button>
        </div>

        <!-- Active Members Table -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mb-8">
            <div class="p-5 border-b border-gray-100">
                <h2 class="text-sm font-bold text-[#280047]">Active Workspace Members ({{ members.length }})</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-gray-50/70 border-b border-gray-100 text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                            <th class="py-3 px-4">Member Name</th>
                            <th class="py-3 px-4">Role & Capabilities</th>
                            <th class="py-3 px-4">Joined Date</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="m in members" :key="m.id" class="hover:bg-gray-50/50">
                            <td class="py-3 px-4 flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-[#280047] text-white flex items-center justify-center font-bold text-xs">
                                    {{ m.user?.name?.charAt(0) || 'U' }}
                                </div>
                                <div>
                                    <span class="font-bold text-[#280047] block">{{ m.user?.name }}</span>
                                    <span class="text-[11px] text-gray-400">{{ m.user?.email }}</span>
                                </div>
                            </td>

                            <td class="py-3 px-4">
                                <span
                                    class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-md"
                                    :class="{
                                        'bg-purple-100 text-[#280047]': m.role === 'owner',
                                        'bg-blue-100 text-blue-800': m.role === 'admin',
                                        'bg-amber-100 text-amber-800': m.role === 'manager',
                                        'bg-gray-100 text-gray-700': m.role === 'sales'
                                    }"
                                >
                                    {{ m.role }}
                                </span>
                            </td>

                            <td class="py-3 px-4 text-gray-500">
                                {{ new Date(m.created_at).toLocaleDateString() }}
                            </td>

                            <td class="py-3 px-4 text-right">
                                <button
                                    v-if="m.role !== 'owner'"
                                    @click="removeMember(m)"
                                    class="text-gray-400 hover:text-red-600 p-1.5 transition-colors"
                                    title="Remove from organization"
                                >
                                    <Trash2 class="w-4 h-4" />
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pending Invitations Table -->
        <div v-if="invitations.length > 0" class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-gray-100">
                <h2 class="text-sm font-bold text-[#280047]">Pending Invitations ({{ invitations.length }})</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-gray-50/70 border-b border-gray-100 text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                            <th class="py-3 px-4">Invited Email</th>
                            <th class="py-3 px-4">Target Role</th>
                            <th class="py-3 px-4">Expires</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="inv in invitations" :key="inv.id">
                            <td class="py-3 px-4 font-semibold text-gray-700">{{ inv.email }}</td>
                            <td class="py-3 px-4 uppercase text-[10px] font-bold text-gray-500">{{ inv.role }}</td>
                            <td class="py-3 px-4 text-gray-400">{{ new Date(inv.expires_at).toLocaleDateString() }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Invite Modal -->
        <div v-if="inviteModalOpen" class="fixed inset-0 bg-black/40 flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl space-y-4">
                <h3 class="text-sm font-bold text-[#280047]">Invite Team Member</h3>

                <div>
                    <label class="block text-xs font-semibold text-gray-700">Employee Email Address *</label>
                    <input
                        v-model="inviteForm.email"
                        type="email"
                        required
                        placeholder="colleague@company.com"
                        class="mt-1 block w-full px-3 py-2 text-xs bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#F74D01] outline-none"
                    />
                    <p v-if="inviteForm.errors.email" class="mt-1 text-xs text-rose-600">{{ inviteForm.errors.email }}</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700">Role & Access Level</label>
                    <select
                        v-model="inviteForm.role"
                        class="mt-1 block w-full px-3 py-2 text-xs bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#F74D01] outline-none"
                    >
                        <option v-for="r in roles" :key="r.value" :value="r.value">{{ r.label }}</option>
                    </select>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button
                        @click="inviteModalOpen = false"
                        class="px-3 py-1.5 text-xs text-gray-500 hover:text-gray-700"
                    >
                        Cancel
                    </button>
                    <button
                        @click="submitInvite"
                        :disabled="inviteForm.processing"
                        class="px-4 py-2 text-xs font-bold text-white bg-[#F74D01] hover:bg-[#d94200] rounded-xl transition-all"
                    >
                        Send Invitation
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
