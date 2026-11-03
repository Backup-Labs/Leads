<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
</script>

<template>
  <div class="min-h-screen bg-[#FAFAFB] flex flex-col lg:flex-row font-sans text-brand-dark">
    <!-- Left Brand Panel -->
    <div class="hidden lg:flex lg:w-5/12 bg-brand-indigo text-white flex-col justify-between p-12">
      <div class="flex items-center justify-between">
        <Link href="/" class="flex items-center gap-3">
          <div class="w-8 h-8 rounded-md bg-brand-orange flex items-center justify-center text-white font-bold text-base">
            L
          </div>
          <span class="text-xl font-bold tracking-tight">Leads</span>
        </Link>

        <Link
          href="/"
          class="text-xs text-brand-purple hover:text-white transition-colors flex items-center gap-1.5"
        >
          <i class="fa-solid fa-arrow-left text-[10px]"></i> Back to Website
        </Link>
      </div>

      <div class="space-y-6 my-auto">
        <span class="inline-block text-[11px] font-semibold uppercase tracking-wider px-2.5 py-1 rounded bg-brand-yellow text-brand-indigo">
          Lead Management & Sales Platform
        </span>

        <h1 class="text-3xl font-bold leading-tight text-white">
          Turn Every Customer Inquiry Into a Paying Client.
        </h1>

        <p class="text-sm text-indigo-purple leading-relaxed">
          Organize inquiries from WhatsApp, Instagram, phone calls, and your website in one place. Track quotations, schedule follow-ups, and never lose a deal.
        </p>

        <div class="grid grid-cols-2 gap-3 pt-2">
          <div class="p-3.5 rounded-md bg-white/6 border border-white/10">
            <i class="fa-solid fa-table-columns text-brand-yellow text-sm mb-1.5 block"></i>
            <p class="text-xs font-semibold text-white">Clear Sales Pipeline</p>
            <p class="text-[11px] text-brand-purple mt-0.5">Track every inquiry from start to close.</p>
          </div>
          <div class="p-3.5 rounded-md bg-white/6 border border-white/10">
            <i class="fa-solid fa-file-invoice-dollar text-brand-yellow text-sm mb-1.5 block"></i>
            <p class="text-xs font-semibold text-white">Fast Quotations</p>
            <p class="text-[11px] text-brand-purple mt-0.5">Send professional quotes and track replies.</p>
          </div>
        </div>
      </div>

      <div class="text-xs text-brand-purple border-t border-white/10 pt-5 flex items-center justify-between">
        <span>Secure Multi-Company Workspace</span>
        <span>Leads Platform</span>
      </div>
    </div>

    <!-- Right Form Area -->
    <div class="flex-1 flex flex-col justify-center py-6 sm:py-10 px-3 sm:px-6 lg:px-12">
      <div class="mx-auto w-full max-w-md space-y-5">
        <!-- Mobile / Tablet Header -->
        <div class="lg:hidden flex items-center justify-between gap-2">
          <Link href="/" class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-md bg-brand-indigo flex items-center justify-center text-white font-bold">L</div>
            <span class="text-xl font-bold text-brand-indigo">Leads</span>
          </Link>
          <Link href="/" class="text-xs font-semibold text-brand-indigo hover:text-brand-orange flex items-center gap-1.5">
            <i class="fa-solid fa-arrow-left text-[10px]"></i> Back to Website
          </Link>
        </div>

        <div class="app-card p-4 sm:p-7 md:p-8">
          <slot />
        </div>

        <!-- Mobile / Tablet Brand Context Banner (Preserves Left Panel Content on < 1024px) -->
        <div class="lg:hidden rounded-lg bg-brand-indigo text-white p-4 sm:p-5 space-y-3">
          <div class="flex flex-wrap items-center justify-between gap-2">
            <span class="inline-block text-[10px] font-semibold uppercase tracking-wider px-2 py-0.5 rounded bg-brand-yellow text-brand-indigo">
              Lead Management & Sales Platform
            </span>
            <span class="text-[10px] text-brand-purple">Secure Multi-Company Workspace</span>
          </div>
          <p class="text-sm font-bold text-white leading-snug">
            Turn Every Customer Inquiry Into a Paying Client.
          </p>
          <p class="text-xs text-brand-purple leading-relaxed">
            Organize inquiries from WhatsApp, Instagram, phone calls, and your website in one place. Track quotations, schedule follow-ups, and never lose a deal.
          </p>
          <div class="grid grid-cols-1 min-[360px]:grid-cols-2 gap-2.5 pt-1">
            <div class="p-3 rounded-md bg-white/6 border border-white/10">
              <i class="fa-solid fa-table-columns text-brand-yellow text-xs mb-1 block"></i>
              <p class="text-xs font-semibold text-white">Clear Sales Pipeline</p>
              <p class="text-[11px] text-brand-purple mt-0.5">Track every inquiry from start to close.</p>
            </div>
            <div class="p-3 rounded-md bg-white/6 border border-white/10">
              <i class="fa-solid fa-file-invoice-dollar text-brand-yellow text-xs mb-1 block"></i>
              <p class="text-xs font-semibold text-white">Fast Quotations</p>
              <p class="text-[11px] text-brand-purple mt-0.5">Send professional quotes and track replies.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
