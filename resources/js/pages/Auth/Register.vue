<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AuthLayout from '@/layouts/AuthLayout.vue';

const currentStep = ref(1);

const currencies = [
    { code: 'RWF', label: 'Rwandan Franc' },
    { code: 'USD', label: 'US Dollar ($)' },
    { code: 'EUR', label: 'Euro (€)' },
    { code: 'KES', label: 'Kenyan Shilling' },
    { code: 'UGX', label: 'Ugandan Shilling' },
    { code: 'GBP', label: 'British Pound (£)' },
];

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    organization_name: '',
    currency: 'RWF',
});

const canProceedStep1 = computed(() => form.organization_name.trim().length >= 2);

const submit = () => {
    form.post('/register', {
        onError: (errors) => {
            if (errors.organization_name || errors.currency) {
                currentStep.value = 1;
            }
        },
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <AuthLayout>
        <div class="mb-5">
            <div class="flex items-center justify-between mb-1">
                <h2 class="text-xl font-bold text-[#280047]">Create Account</h2>
                <span class="text-xs font-semibold text-[#F74D01]">Step {{ currentStep }} of 2</span>
            </div>
            <p class="text-xs text-gray-500">
                {{ currentStep === 1 ? 'Set up your company name and default currency.' : 'Enter your personal details and password.' }}
            </p>

            <!-- Step Bar -->
            <div class="mt-3 grid grid-cols-2 gap-2">
                <div class="h-1.5 rounded-sm" :class="currentStep >= 1 ? 'bg-[#280047]' : 'bg-gray-200'"></div>
                <div class="h-1.5 rounded-sm" :class="currentStep >= 2 ? 'bg-[#F74D01]' : 'bg-gray-200'"></div>
            </div>
        </div>

        <form @submit.prevent="submit">
            <!-- Step 1: Company Details -->
            <div v-if="currentStep === 1" class="space-y-4">
                <div>
                    <label for="org_name" class="block text-xs font-semibold text-[#242424] mb-1">Company Name *</label>
                    <input
                        id="org_name"
                        v-model="form.organization_name"
                        type="text"
                        required
                        placeholder="e.g. Kigali Solutions Ltd"
                        class="app-input w-full"
                    />
                    <p v-if="form.errors.organization_name" class="mt-1 text-xs text-[#F74D01]">{{ form.errors.organization_name }}</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#242424] mb-1.5">Default Currency</label>
                    <div class="grid grid-cols-3 gap-2">
                        <button
                            v-for="curr in currencies"
                            :key="curr.code"
                            type="button"
                            @click="form.currency = curr.code"
                            class="p-2.5 rounded-md border text-left transition-colors cursor-pointer"
                            :class="form.currency === curr.code
                                ? 'border-[#280047] bg-[#280047] text-white'
                                : 'border-[#D5CCE2] bg-white text-[#242424] hover:border-[#280047]'"
                        >
                            <p class="text-xs font-bold">{{ curr.code }}</p>
                            <p class="text-[10px] opacity-80 truncate">{{ curr.label }}</p>
                        </button>
                    </div>
                </div>

                <button
                    type="button"
                    @click="canProceedStep1 && (currentStep = 2)"
                    :disabled="!canProceedStep1"
                    class="btn-indigo w-full py-2.5 flex items-center justify-center gap-2 disabled:opacity-40 cursor-pointer"
                >
                    <span>Next Step</span>
                    <i class="fa-solid fa-arrow-right text-xs"></i>
                </button>
            </div>

            <!-- Step 2: Account Details -->
            <div v-else class="space-y-4">
                <div>
                    <label for="name" class="block text-xs font-semibold text-[#242424] mb-1">Full Name *</label>
                    <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        required
                        placeholder="e.g. Jean Claude"
                        class="app-input w-full"
                    />
                    <p v-if="form.errors.name" class="mt-1 text-xs text-[#F74D01]">{{ form.errors.name }}</p>
                </div>

                <div>
                    <label for="email" class="block text-xs font-semibold text-[#242424] mb-1">Work Email *</label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        placeholder="you@company.com"
                        class="app-input w-full"
                    />
                    <p v-if="form.errors.email" class="mt-1 text-xs text-[#F74D01]">{{ form.errors.email }}</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="password" class="block text-xs font-semibold text-[#242424] mb-1">Password *</label>
                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            required
                            placeholder="Min. 8 characters"
                            class="app-input w-full"
                        />
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-xs font-semibold text-[#242424] mb-1">Confirm Password *</label>
                        <input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            required
                            placeholder="Repeat password"
                            class="app-input w-full"
                        />
                    </div>
                </div>
                <p v-if="form.errors.password" class="text-xs text-[#F74D01]">{{ form.errors.password }}</p>

                <div class="flex items-center gap-2 pt-1">
                    <button
                        type="button"
                        @click="currentStep = 1"
                        class="btn-secondary py-2.5 px-4 cursor-pointer"
                    >
                        Back
                    </button>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="btn-primary flex-1 py-2.5 flex items-center justify-center gap-2 cursor-pointer"
                    >
                        <span>{{ form.processing ? 'Creating Account...' : 'Create Account' }}</span>
                    </button>
                </div>
            </div>
        </form>

        <div class="mt-5 text-center text-xs text-gray-500">
            Already have an account?
            <Link href="/login" class="font-bold text-[#280047] hover:text-[#F74D01] ml-1">Sign In</Link>
        </div>
    </AuthLayout>
</template>
