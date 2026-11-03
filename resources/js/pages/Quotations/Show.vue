<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { Quotation } from '@/types';

const props = defineProps<{
  quotation: Quotation;
  publicUrl: string;
}>();

const copied = ref(false);

const copyShareLink = () => {
  navigator.clipboard.writeText(props.publicUrl);
  copied.value = true;
  setTimeout(() => (copied.value = false), 2000);
};

const markAsSent = () => {
  router.post(`/quotations/${props.quotation.id}/send`, {}, { preserveScroll: true });
};

const formatMoney = (amount: number) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: props.quotation.currency || 'UGX',
    maximumFractionDigits: 0,
  }).format(amount || 0);
};
</script>

<template>
  <Head :title="quotation.quotation_number" />
  <AppLayout>
    <div class="max-w-4xl mx-auto space-y-5">
      <!-- Top Bar -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-2 text-xs text-[#6B6578]">
          <Link href="/quotations" class="hover:text-[#280047] font-medium flex items-center gap-1.5">
            <i class="fa-solid fa-arrow-left text-[10px]"></i> Quotations
          </Link>
          <span>/</span>
          <span class="font-mono-num font-bold text-[#242424]">{{ quotation.quotation_number }}</span>
        </div>

        <div class="flex flex-wrap items-center gap-2">
          <button
            v-if="quotation.status === 'draft'"
            type="button"
            @click="markAsSent"
            class="btn-indigo text-xs"
          >
            <i class="fa-regular fa-paper-plane"></i> Mark as Sent
          </button>
          <a
            :href="`/quotations/${quotation.id}/preview`"
            target="_blank"
            class="btn-secondary text-xs"
          >
            <i class="fa-solid fa-print"></i> Print / PDF
          </a>
          <a
            :href="publicUrl"
            target="_blank"
            class="btn-primary text-xs"
          >
            <i class="fa-solid fa-arrow-up-right-from-square"></i> Client View
          </a>
        </div>
      </div>

      <!-- Shareable Link Box -->
      <div class="app-card p-4 bg-[#F8F6FA] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-3 min-w-0">
          <div class="w-8 h-8 rounded-md bg-[#280047] text-[#FDFEBA] flex items-center justify-center shrink-0">
            <i class="fa-solid fa-link text-xs"></i>
          </div>
          <div class="min-w-0">
            <p class="text-xs font-bold text-[#242424]">Client Shareable Quotation Link</p>
            <p class="text-[11px] text-[#6B6578] font-mono-num truncate">{{ publicUrl }}</p>
          </div>
        </div>
        <button type="button" @click="copyShareLink" class="btn-secondary text-xs shrink-0">
          <i :class="copied ? 'fa-solid fa-check text-emerald-600' : 'fa-regular fa-copy'"></i>
          {{ copied ? 'Copied!' : 'Copy Link' }}
        </button>
      </div>

      <!-- Quotation Document Card -->
      <div class="app-card p-6 sm:p-8 space-y-6 border-t-4 border-t-[#280047]">
        <!-- Document Header -->
        <div class="flex flex-col sm:flex-row justify-between gap-4 pb-6 border-b border-[#E5E0EC]">
          <div>
            <span class="text-[11px] font-bold uppercase tracking-wider text-[#F74D01]">Official Quotation</span>
            <h1 class="text-2xl font-bold text-[#280047] font-mono-num mt-0.5">{{ quotation.quotation_number }}</h1>
            <p class="text-xs text-[#6B6578] mt-1">
              Prepared by <strong class="text-[#242424]">{{ quotation.created_by?.name || 'Sales Team' }}</strong>
            </p>
          </div>

          <div class="sm:text-right">
            <span class="inline-block px-2.5 py-1 rounded text-xs font-bold uppercase bg-[#FDFEBA] text-[#280047] border border-[#C3B6D3]">
              {{ quotation.status }}
            </span>
            <p v-if="quotation.created_at" class="text-xs text-[#6B6578] font-mono-num mt-2">
              Issued: {{ new Date(quotation.created_at).toLocaleDateString() }}
            </p>
            <p v-if="quotation.validity_date" class="text-xs text-[#6B6578] font-mono-num">
              Valid Until: {{ new Date(quotation.validity_date).toLocaleDateString() }}
            </p>
          </div>
        </div>

        <!-- Client Information -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
          <div class="p-4 rounded-md bg-[#FAFAFB] border border-[#E5E0EC]">
            <p class="text-[11px] font-bold uppercase tracking-wider text-[#6B6578] mb-1">Prepared For</p>
            <p class="text-sm font-bold text-[#242424]">{{ quotation.customer_name }}</p>
            <p class="text-[#6B6578]">{{ quotation.company_name || 'Individual Client' }}</p>
            <p class="font-mono-num text-[#242424] mt-1">{{ quotation.customer_phone }}</p>
          </div>

          <div class="p-4 rounded-md bg-[#FAFAFB] border border-[#E5E0EC] flex flex-col justify-center sm:text-right">
            <p class="text-[11px] font-bold uppercase tracking-wider text-[#6B6578] mb-1">Total Payable</p>
            <p class="text-2xl font-bold text-[#280047] font-mono-num">
              {{ formatMoney(Number(quotation.total_amount)) }}
            </p>
            <p class="text-[11px] text-[#6B6578]">Includes {{ quotation.tax_rate }}% VAT</p>
          </div>
        </div>

        <!-- Items Table -->
        <div class="overflow-x-auto border border-[#E5E0EC] rounded-md">
          <table class="w-full text-left border-collapse text-xs">
            <thead>
              <tr class="bg-[#F8F6FA] border-b border-[#E5E0EC] text-[11px] font-semibold uppercase text-[#6B6578]">
                <th class="px-4 py-2.5">#</th>
                <th class="px-4 py-2.5">Description</th>
                <th class="px-4 py-2.5 text-right">Qty</th>
                <th class="px-4 py-2.5 text-right">Unit Price</th>
                <th class="px-4 py-2.5 text-right">Line Total</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#E5E0EC]">
              <tr v-for="(item, index) in quotation.items" :key="item.id || index">
                <td class="px-4 py-3 font-mono-num text-[#6B6578]">{{ index + 1 }}</td>
                <td class="px-4 py-3 font-medium text-[#242424]">{{ item.description }}</td>
                <td class="px-4 py-3 text-right font-mono-num">{{ item.quantity }}</td>
                <td class="px-4 py-3 text-right font-mono-num">{{ formatMoney(Number(item.unit_price)) }}</td>
                <td class="px-4 py-3 text-right font-mono-num font-semibold text-[#242424]">
                  {{ formatMoney(Number(item.total_price)) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Totals & Notes -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-2">
          <div>
            <p class="text-xs font-bold text-[#242424] mb-1">Terms & Conditions</p>
            <p class="text-xs text-[#6B6578] whitespace-pre-line leading-relaxed">
              {{ quotation.terms_and_conditions || quotation.notes || 'Standard payment terms apply.' }}
            </p>
          </div>

          <div class="space-y-2 text-xs">
            <div class="flex justify-between py-1">
              <span class="text-[#6B6578]">Subtotal</span>
              <span class="font-mono-num font-semibold text-[#242424]">{{ formatMoney(Number(quotation.subtotal)) }}</span>
            </div>
            <div v-if="Number(quotation.discount_amount) > 0" class="flex justify-between py-1">
              <span class="text-[#6B6578]">Discount</span>
              <span class="font-mono-num font-semibold text-[#F74D01]">- {{ formatMoney(Number(quotation.discount_amount)) }}</span>
            </div>
            <div class="flex justify-between py-1">
              <span class="text-[#6B6578]">VAT ({{ quotation.tax_rate }}%)</span>
              <span class="font-mono-num font-semibold text-[#242424]">{{ formatMoney(Number(quotation.tax_amount)) }}</span>
            </div>
            <div class="flex justify-between py-2.5 border-t border-[#E5E0EC] text-sm font-bold text-[#280047]">
              <span>Grand Total</span>
              <span class="font-mono-num">{{ formatMoney(Number(quotation.total_amount)) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
