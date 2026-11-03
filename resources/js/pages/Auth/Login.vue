<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3';
import AuthLayout from '@/layouts/AuthLayout.vue';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const fillDemo = (email: string) => {
    form.email = email;
    form.password = 'Password123!';
};

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <AuthLayout>
        <div class="mb-6">
            <h2 class="text-xl font-bold text-[#280047]">Sign In</h2>
            <p class="text-xs text-gray-500 mt-1">Enter your email and password to open your workspace.</p>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <label for="email" class="block text-xs font-semibold text-[#242424] mb-1">Email Address</label>
                <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    required
                    autocomplete="email"
                    placeholder="you@company.com"
                    class="app-input w-full"
                />
                <p v-if="form.errors.email" class="mt-1 text-xs text-[#F74D01] font-medium">{{ form.errors.email }}</p>
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold text-[#242424] mb-1">Password</label>
                <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                    class="app-input w-full"
                />
                <p v-if="form.errors.password" class="mt-1 text-xs text-[#F74D01] font-medium">{{ form.errors.password }}</p>
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center cursor-pointer">
                    <input
                        v-model="form.remember"
                        type="checkbox"
                        class="rounded border-[#C3B6D3] text-[#280047] focus:ring-[#280047] w-3.5 h-3.5"
                    />
                    <span class="ml-2 text-xs text-gray-600">Remember me</span>
                </label>
            </div>

            <button
                type="submit"
                :disabled="form.processing"
                class="btn-primary w-full py-2.5 flex items-center justify-center gap-2 cursor-pointer"
            >
                <i class="fa-solid fa-right-to-bracket"></i>
                <span>{{ form.processing ? 'Signing in...' : 'Sign In' }}</span>
            </button>
        </form>

        <!-- Demo Accounts -->
        <div class="mt-6 pt-5 border-t border-gray-100">
            <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-2">Quick Demo Login</p>
            <div class="grid grid-cols-3 gap-2">
                <button
                    type="button"
                    @click="fillDemo('admin@leads.test')"
                    class="p-2 rounded-md border border-[#E5E0EC] hover:border-[#280047] text-left transition-colors cursor-pointer"
                >
                    <p class="text-xs font-bold text-[#280047]">Owner</p>
                    <p class="text-[10px] text-gray-500 truncate">admin@leads.test</p>
                </button>
                <button
                    type="button"
                    @click="fillDemo('diane@leads.test')"
                    class="p-2 rounded-md border border-[#E5E0EC] hover:border-[#280047] text-left transition-colors cursor-pointer"
                >
                    <p class="text-xs font-bold text-[#280047]">Manager</p>
                    <p class="text-[10px] text-gray-500 truncate">diane@leads.test</p>
                </button>
                <button
                    type="button"
                    @click="fillDemo('eric@leads.test')"
                    class="p-2 rounded-md border border-[#E5E0EC] hover:border-[#280047] text-left transition-colors cursor-pointer"
                >
                    <p class="text-xs font-bold text-[#280047]">Sales Rep</p>
                    <p class="text-[10px] text-gray-500 truncate">eric@leads.test</p>
                </button>
            </div>
        </div>

        <div class="mt-5 text-center text-xs text-gray-500">
            Don't have an account?
            <Link href="/register" class="font-bold text-[#280047] hover:text-[#F74D01] ml-1">Create Account</Link>
        </div>
    </AuthLayout>
</template>
