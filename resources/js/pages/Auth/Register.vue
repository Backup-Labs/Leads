<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { UserPlus } from 'lucide-vue-next';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    organization_name: '',
    currency: 'RWF',
});

const submit = () => {
    form.post('/register', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <AuthLayout>
        <div class="mb-6">
            <h2 class="text-xl font-bold text-[#280047]">Create your workspace</h2>
            <p class="text-xs text-gray-500 mt-1">Start recovering revenue and organizing inquiries in minutes</p>
        </div>

        <form @submit.prevent="submit" class="space-y-3.5">
            <div>
                <label for="org_name" class="block text-xs font-semibold text-gray-700">Company / Business Name</label>
                <input
                    id="org_name"
                    v-model="form.organization_name"
                    type="text"
                    required
                    placeholder="e.g. Kigali Design Agency"
                    class="mt-1 block w-full px-3.5 py-2.5 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#F74D01] focus:bg-white outline-none transition-all"
                />
                <p v-if="form.errors.organization_name" class="mt-1 text-xs text-rose-600">{{ form.errors.organization_name }}</p>
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div class="col-span-2">
                    <label for="name" class="block text-xs font-semibold text-gray-700">Your Full Name</label>
                    <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        required
                        placeholder="Jean Claude"
                        class="mt-1 block w-full px-3.5 py-2.5 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#F74D01] focus:bg-white outline-none transition-all"
                    />
                    <p v-if="form.errors.name" class="mt-1 text-xs text-rose-600">{{ form.errors.name }}</p>
                </div>
                <div>
                    <label for="currency" class="block text-xs font-semibold text-gray-700">Currency</label>
                    <select
                        id="currency"
                        v-model="form.currency"
                        class="mt-1 block w-full px-3 py-2.5 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#F74D01] focus:bg-white outline-none transition-all"
                    >
                        <option value="RWF">RWF</option>
                        <option value="USD">USD ($)</option>
                        <option value="EUR">EUR (€)</option>
                        <option value="GBP">GBP (£)</option>
                        <option value="KES">KES</option>
                        <option value="UGX">UGX</option>
                    </select>
                </div>
            </div>

            <div>
                <label for="email" class="block text-xs font-semibold text-gray-700">Email Address</label>
                <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    required
                    placeholder="owner@company.com"
                    class="mt-1 block w-full px-3.5 py-2.5 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#F74D01] focus:bg-white outline-none transition-all"
                />
                <p v-if="form.errors.email" class="mt-1 text-xs text-rose-600">{{ form.errors.email }}</p>
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold text-gray-700">Password</label>
                <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    required
                    placeholder="Minimum 8 characters"
                    class="mt-1 block w-full px-3.5 py-2.5 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#F74D01] focus:bg-white outline-none transition-all"
                />
                <p v-if="form.errors.password" class="mt-1 text-xs text-rose-600">{{ form.errors.password }}</p>
            </div>

            <div>
                <label for="password_confirmation" class="block text-xs font-semibold text-gray-700">Confirm Password</label>
                <input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    required
                    placeholder="Repeat password"
                    class="mt-1 block w-full px-3.5 py-2.5 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#F74D01] focus:bg-white outline-none transition-all"
                />
            </div>

            <button
                type="submit"
                :disabled="form.processing"
                class="w-full flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl text-sm font-bold text-white bg-[#F74D01] hover:bg-[#d94200] disabled:opacity-50 shadow-sm transition-all mt-2"
            >
                <UserPlus class="w-4 h-4" />
                <span>{{ form.processing ? 'Creating Workspace...' : 'Create Free 14-Day Trial' }}</span>
            </button>
        </form>

        <div class="mt-6 text-center text-xs text-gray-500">
            Already have an account?
            <Link href="/login" class="font-bold text-[#280047] hover:underline ml-1">Sign in</Link>
        </div>
    </AuthLayout>
</template>
