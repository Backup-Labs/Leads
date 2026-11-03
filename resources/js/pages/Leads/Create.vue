<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { ArrowLeft, Plus } from 'lucide-vue-next';

const props = defineProps<{
    stages: Array<any>;
    sources: Array<any>;
    team: Array<any>;
    priorities: Array<{ value: string; label: string }>;
}>();

const form = useForm({
    customer_name: '',
    email: '',
    phone: '',
    company_name: '',
    requested_service: '',
    estimated_value: '',
    pipeline_stage_id: props.stages[0]?.id || '',
    lead_source_id: props.sources[0]?.id || '',
    assigned_to_user_id: '',
    priority: 'medium',
    description: '',
});

const submit = () => {
    form.post('/leads');
};
</script>

<template>
    <AppLayout>
        <div class="max-w-3xl mx-auto">
            <!-- Breadcrumb Navigation -->
            <div class="mb-6">
                <Link href="/leads" class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-500 hover:text-[#280047]">
                    <ArrowLeft class="w-4 h-4" />
                    <span>Back to Leads</span>
                </Link>
                <h1 class="text-2xl font-extrabold text-[#280047] mt-2">Capture New Inbound Lead</h1>
                <p class="text-xs text-gray-500">Record customer inquiry details from WhatsApp, phone, web, or physical visit</p>
            </div>

            <!-- Form Card -->
            <form @submit.prevent="submit" class="bg-white p-6 sm:p-8 rounded-2xl border border-gray-100 shadow-sm space-y-6">
                <!-- Section 1: Customer Contact -->
                <div>
                    <h2 class="text-sm font-bold text-[#280047] uppercase tracking-wider mb-4 border-b border-gray-100 pb-2">
                        1. Customer & Company Information
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700">Customer / Contact Name *</label>
                            <input
                                v-model="form.customer_name"
                                type="text"
                                required
                                placeholder="e.g. Marie Claire"
                                class="mt-1 block w-full px-3.5 py-2 text-xs bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#F74D01] focus:bg-white outline-none"
                            />
                            <p v-if="form.errors.customer_name" class="mt-1 text-xs text-rose-600">{{ form.errors.customer_name }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700">Company Name (Optional)</label>
                            <input
                                v-model="form.company_name"
                                type="text"
                                placeholder="e.g. Akagera Business Group"
                                class="mt-1 block w-full px-3.5 py-2 text-xs bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#F74D01] focus:bg-white outline-none"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700">Phone Number (WhatsApp / Calls)</label>
                            <input
                                v-model="form.phone"
                                type="text"
                                placeholder="+250 788 000 000"
                                class="mt-1 block w-full px-3.5 py-2 text-xs bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#F74D01] focus:bg-white outline-none"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700">Email Address</label>
                            <input
                                v-model="form.email"
                                type="email"
                                placeholder="client@company.rw"
                                class="mt-1 block w-full px-3.5 py-2 text-xs bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#F74D01] focus:bg-white outline-none"
                            />
                        </div>
                    </div>
                </div>

                <!-- Section 2: Opportunity Details -->
                <div>
                    <h2 class="text-sm font-bold text-[#280047] uppercase tracking-wider mb-4 border-b border-gray-100 pb-2">
                        2. Opportunity & Deal Attributes
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700">Requested Service / Product</label>
                            <input
                                v-model="form.requested_service"
                                type="text"
                                placeholder="e.g. Commercial Office Cleaning, Web App Development"
                                class="mt-1 block w-full px-3.5 py-2 text-xs bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#F74D01] focus:bg-white outline-none"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700">Estimated Deal Value</label>
                            <input
                                v-model="form.estimated_value"
                                type="number"
                                step="0.01"
                                placeholder="e.g. 1500000"
                                class="mt-1 block w-full px-3.5 py-2 text-xs bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#F74D01] focus:bg-white outline-none"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700">Inquiry Channel / Source</label>
                            <select
                                v-model="form.lead_source_id"
                                class="mt-1 block w-full px-3 py-2 text-xs bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#F74D01] focus:bg-white outline-none text-gray-700"
                            >
                                <option v-for="src in sources" :key="src.id" :value="src.id">{{ src.name }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700">Pipeline Stage</label>
                            <select
                                v-model="form.pipeline_stage_id"
                                class="mt-1 block w-full px-3 py-2 text-xs bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#F74D01] focus:bg-white outline-none text-gray-700"
                            >
                                <option v-for="stg in stages" :key="stg.id" :value="stg.id">{{ stg.name }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700">Assign To Team Member</label>
                            <select
                                v-model="form.assigned_to_user_id"
                                class="mt-1 block w-full px-3 py-2 text-xs bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#F74D01] focus:bg-white outline-none text-gray-700"
                            >
                                <option value="">Unassigned</option>
                                <option v-for="m in team" :key="m.user_id" :value="m.user_id">{{ m.user.name }} ({{ m.role }})</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700">Priority Level</label>
                            <select
                                v-model="form.priority"
                                class="mt-1 block w-full px-3 py-2 text-xs bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#F74D01] focus:bg-white outline-none text-gray-700"
                            >
                                <option v-for="p in priorities" :key="p.value" :value="p.value">{{ p.label }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Notes -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700">Inquiry Notes / Customer Context</label>
                    <textarea
                        v-model="form.description"
                        rows="3"
                        placeholder="Key client requirements, scope discussed, budget range, or specific pain points mentioned..."
                        class="mt-1 block w-full px-3.5 py-2 text-xs bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#F74D01] focus:bg-white outline-none"
                    ></textarea>
                </div>

                <!-- Submit Button -->
                <div class="pt-4 flex items-center justify-end gap-3 border-t border-gray-100">
                    <Link href="/leads" class="px-4 py-2 text-xs font-semibold text-gray-600 hover:text-gray-900">
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-[#F74D01] hover:bg-[#d94200] disabled:opacity-50 shadow-sm transition-all"
                    >
                        <Plus class="w-4 h-4" />
                        <span>{{ form.processing ? 'Capturing...' : 'Capture Lead' }}</span>
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
