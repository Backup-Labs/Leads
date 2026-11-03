<script setup lang="ts">
</script>

<template>
    <div class="min-h-screen bg-[#FAFAFB] flex flex-col lg:flex-row font-sans text-[#242424]">
        <!-- Left Brand Panel -->
        <div class="hidden lg:flex lg:w-5/12 bg-[#280047] text-white flex-col justify-between p-12">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-md bg-[#F74D01] flex items-center justify-center text-white font-bold text-base">
                    L
                </div>
                <span class="text-xl font-bold tracking-tight">Leads</span>
            </div>

            <div class="space-y-6 my-auto">
                <span class="inline-block text-[11px] font-semibold uppercase tracking-wider px-2.5 py-1 rounded bg-[#FDFEBA] text-[#280047]">
                    Lead Management & Sales Platform
                </span>

                <h1 class="text-3xl font-bold leading-tight text-white">
                    Turn Every Customer Inquiry Into a Paying Client.
                </h1>

                <p class="text-sm text-[#C3B6D3] leading-relaxed">
                    Organize inquiries from WhatsApp, Instagram, phone calls, and your website in one place. Track quotations, schedule follow-ups, and never lose a deal.
                </p>

                <div class="grid grid-cols-2 gap-3 pt-2">
                    <div class="p-3.5 rounded-md bg-white/6 border border-white/10">
                        <i class="fa-solid fa-table-columns text-[#FDFEBA] text-sm mb-1.5 block"></i>
                        <p class="text-xs font-semibold text-white">Clear Sales Pipeline</p>
                        <p class="text-[11px] text-[#C3B6D3] mt-0.5">Track every inquiry from start to close.</p>
                    </div>
                    <div class="p-3.5 rounded-md bg-white/6 border border-white/10">
                        <i class="fa-solid fa-file-invoice-dollar text-[#FDFEBA] text-sm mb-1.5 block"></i>
                        <p class="text-xs font-semibold text-white">Fast Quotations</p>
                        <p class="text-[11px] text-[#C3B6D3] mt-0.5">Send professional quotes and track replies.</p>
                    </div>
                </div>
            </div>

            <div class="text-xs text-[#C3B6D3] border-t border-white/10 pt-5 flex items-center justify-between">
                <span>Secure Multi-Company Workspace</span>
                <span>Leads Platform</span>
            </div>
        </div>

        <!-- Right Form Area -->
        <div class="flex-1 flex flex-col justify-center py-10 px-6 sm:px-12">
            <div class="sm:mx-auto sm:w-full sm:max-w-md">
                <div class="lg:hidden flex items-center gap-2.5 mb-6 justify-center">
                    <div class="w-8 h-8 rounded-md bg-[#280047] flex items-center justify-center text-white font-bold">L</div>
                    <span class="text-xl font-bold text-[#280047]">Leads</span>
                </div>

                <div class="app-card p-7 sm:p-8">
                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>
