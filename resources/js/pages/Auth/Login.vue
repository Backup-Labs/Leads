<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { LogIn } from 'lucide-vue-next';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <AuthLayout>
        <div class="mb-6">
            <h2 class="text-xl font-bold text-[#280047]">Sign in to your workspace</h2>
            <p class="text-xs text-gray-500 mt-1">Enter your credentials to access your sales leads</p>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <label for="email" class="block text-xs font-semibold text-gray-700">Business Email</label>
                <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    required
                    autocomplete="email"
                    placeholder="you@company.com"
                    class="mt-1 block w-full px-3.5 py-2.5 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#F74D01] focus:bg-white outline-none transition-all"
                />
                <p v-if="form.errors.email" class="mt-1 text-xs text-rose-600">{{ form.errors.email }}</p>
            </div>

            <div>
                <div class="flex items-center justify-between">
                    <label for="password" class="block text-xs font-semibold text-gray-700">Password</label>
                </div>
                <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                    class="mt-1 block w-full px-3.5 py-2.5 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#F74D01] focus:bg-white outline-none transition-all"
                />
                <p v-if="form.errors.password" class="mt-1 text-xs text-rose-600">{{ form.errors.password }}</p>
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center">
                    <input
                        v-model="form.remember"
                        type="checkbox"
                        class="rounded border-gray-300 text-[#F74D01] focus:ring-[#F74D01]"
                    />
                    <span class="ml-2 text-xs text-gray-600">Remember me</span>
                </label>
            </div>

            <button
                type="submit"
                :disabled="form.processing"
                class="w-full flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl text-sm font-bold text-white bg-[#F74D01] hover:bg-[#d94200] disabled:opacity-50 shadow-sm transition-all"
            >
                <LogIn class="w-4 h-4" />
                <span>{{ form.processing ? 'Signing in...' : 'Sign In' }}</span>
            </button>
        </form>

        <div class="mt-6 text-center text-xs text-gray-500">
            Don't have an account?
            <Link href="/register" class="font-bold text-[#280047] hover:underline ml-1">Create workspace</Link>
        </div>
    </AuthLayout>
</template>
