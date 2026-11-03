<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Target, TrendingUp, ShieldCheck, Zap } from 'lucide-vue-next';
</script>

<template>
    <div class="min-h-screen bg-[#FAFAFC] flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-[#280047] shadow-lg mb-4">
                <Target class="w-8 h-8 text-[#F74D01]" />
            </div>
            <h1 class="text-3xl font-extrabold text-[#280047] tracking-tight">Leads</h1>
            <p class="mt-2 text-sm text-gray-600 font-medium">Turn Every Inquiry Into a Potential Customer</p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-8 px-6 shadow-sm border border-gray-100 sm:rounded-2xl sm:px-10">
                <slot />
            </div>

            <!-- Value props banner -->
            <div class="mt-8 grid grid-cols-3 gap-2 text-center text-xs text-gray-500 font-medium">
                <div class="flex flex-col items-center gap-1">
                    <TrendingUp class="w-4 h-4 text-[#F74D01]" />
                    <span>Pipeline Control</span>
                </div>
                <div class="flex flex-col items-center gap-1">
                    <Zap class="w-4 h-4 text-[#F74D01]" />
                    <span>Revenue Recovery</span>
                </div>
                <div class="flex flex-col items-center gap-1">
                    <ShieldCheck class="w-4 h-4 text-[#F74D01]" />
                    <span>Multi-Tenant Secure</span>
                </div>
            </div>
        </div>
    </div>
</template>
