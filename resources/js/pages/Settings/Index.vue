<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Settings, Save, Shield, Sliders } from 'lucide-vue-next';

const props = defineProps<{
    organization: any;
    settings: any;
}>();

const form = useForm({
    name: props.organization.name || '',
    billing_email: props.organization.billing_email || '',
    phone: props.organization.phone || '',
    currency: props.organization.currency || 'RWF',
    default_tax_rate: props.settings?.default_tax_rate ?? 18.00,
    stale_lead_threshold_days: props.settings?.stale_lead_threshold_days ?? 7,
    quote_followup_threshold_days: props.settings?.quote_followup_threshold_days ?? 3,
    high_value_threshold: props.settings?.high_value_threshold ?? 1000000,
    quotation_default_terms: props.settings?.quotation_default_terms || '',
});

const submit = () => {
    form.patch('/settings');
};
</script>

<template>
    <AppLayout>
        <div class="max-w-3xl mx-auto space-y-6">
            <div>
                <h1 class="text-2xl font-extrabold text-[#280047]">Organization & Operational Settings</h1>
                <p class="text-xs text-gray-500 mt-1">Configure workspace currencies, default taxes, and automated revenue recovery triggers</p>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Workspace Profile -->
                <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm space-y-4">
                    <h2 class="text-xs font-bold text-[#280047] uppercase tracking-wider border-b border-gray-100 pb-2">
                        1. Workspace Profile
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700">Organization Name *</label>
                            <input
                                v-model="form.name"
                                type="text"
                                required
                                class="mt-1 block w-full px-3 py-2 text-xs bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#F74D01] outline-none"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700">Primary Currency</label>
                            <select
                                v-model="form.currency"
                                class="mt-1 block w-full px-3 py-2 text-xs bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#F74D01] outline-none"
                            >
                                <option value="RWF">RWF (Rwandan Franc)</option>
                                <option value="USD">USD ($)</option>
                                <option value="EUR">EUR (€)</option>
                                <option value="GBP">GBP (£)</option>
                                <option value="KES">KES (Kenyan Shilling)</option>
                                <option value="UGX">UGX (Ugandan Shilling)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700">Billing Email</label>
                            <input
                                v-model="form.billing_email"
                                type="email"
                                class="mt-1 block w-full px-3 py-2 text-xs bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#F74D01] outline-none"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700">Office Phone</label>
                            <input
                                v-model="form.phone"
                                type="text"
                                class="mt-1 block w-full px-3 py-2 text-xs bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#F74D01] outline-none"
                            />
                        </div>
                    </div>
                </div>

                <!-- Recovery Radar Thresholds -->
                <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm space-y-4">
                    <h2 class="text-xs font-bold text-[#280047] uppercase tracking-wider border-b border-gray-100 pb-2">
                        2. Revenue Recovery Triggers & Automation Rules
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700">Stale Lead Threshold</label>
                            <div class="mt-1 flex items-center gap-1">
                                <input
                                    v-model="form.stale_lead_threshold_days"
                                    type="number"
                                    min="1"
                                    max="60"
                                    class="block w-full px-3 py-2 text-xs bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#F74D01] outline-none"
                                />
                                <span class="text-xs text-gray-400">days</span>
                            </div>
                            <span class="text-[10px] text-gray-400">Flags lead if inactive</span>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700">Unanswered Quote Alert</label>
                            <div class="mt-1 flex items-center gap-1">
                                <input
                                    v-model="form.quote_followup_threshold_days"
                                    type="number"
                                    min="1"
                                    max="30"
                                    class="block w-full px-3 py-2 text-xs bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#F74D01] outline-none"
                                />
                                <span class="text-xs text-gray-400">days</span>
                            </div>
                            <span class="text-[10px] text-gray-400">Flags quote without reply</span>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700">High-Value Threshold</label>
                            <input
                                v-model="form.high_value_threshold"
                                type="number"
                                min="0"
                                class="mt-1 block w-full px-3 py-2 text-xs bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#F74D01] outline-none"
                            />
                            <span class="text-[10px] text-gray-400">Escalates critical risk</span>
                        </div>
                    </div>
                </div>

                <!-- Quotation Defaults & Taxes -->
                <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm space-y-4">
                    <h2 class="text-xs font-bold text-[#280047] uppercase tracking-wider border-b border-gray-100 pb-2">
                        3. Commercial Quotation Defaults
                    </h2>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700">Default Statutory VAT Rate (%)</label>
                        <input
                            v-model="form.default_tax_rate"
                            type="number"
                            step="0.01"
                            class="mt-1 block w-48 px-3 py-2 text-xs bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#F74D01] outline-none"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700">Default Quotation Terms & Conditions</label>
                        <textarea
                            v-model="form.quotation_default_terms"
                            rows="4"
                            class="mt-1 block w-full p-3 text-xs bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#F74D01] outline-none"
                        ></textarea>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex items-center gap-1.5 px-6 py-2.5 rounded-xl text-xs font-bold text-white bg-[#F74D01] hover:bg-[#d94200] disabled:opacity-50 shadow-sm transition-all"
                    >
                        <Save class="w-4 h-4" />
                        <span>{{ form.processing ? 'Saving...' : 'Save Preferences' }}</span>
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
