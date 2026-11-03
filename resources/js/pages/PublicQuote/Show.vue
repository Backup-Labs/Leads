<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import type { Quotation } from '@/types';

const props = defineProps<{
  quotation: Quotation;
}>();

const respond = (action: 'accept' | 'reject') => {
  router.post(`/quotes/${props.quotation.token}/respond`, { action });
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
  <Head :title="`Quotation ${quotation.quotation_number}`" />
  <div class="min-h-screen bg-[#FAFAFB] text-[#242424] py-6 sm:py-10 px-3 sm:px-4">
    <div class="max-w-3xl mx-auto space-y-5">
      <!-- Top Action Banner -->
      <div class="app-card p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3 min-w-0">
          <div class="w-9 h-9 rounded-md bg-[#280047] text-[#FDFEBA] flex items-center justify-center font-bold shrink-0">
            <i class="fa-solid fa-file-invoice text-sm"></i>
          </div>
          <div class="min-w-0">
            <p class="text-xs font-bold text-[#242424]">
              {{ quotation.organization?.name || 'Official Quotation' }}
            </p>
            <p class="text-[11px] text-[#6B6578] font-mono-num break-all sm:break-normal">
              Reference: {{ quotation.quotation_number }} &bull; Status:
              <strong class="uppercase text-[#280047]">{{ quotation.status }}</strong>
            </p>
          </div>
        </div>

        <div v-if="quotation.status === 'sent' || quotation.status === 'draft'" class="flex flex-wrap items-center gap-2">
          <button type="button" @click="respond('reject')" class="btn-secondary text-xs">
            Decline
          </button>
          <button type="button" @click="respond('accept')" class="btn-primary text-xs">
            <i class="fa-solid fa-check"></i> Accept Quotation
          </button>
        </div>
        <div v-else class="px-3 py-1.5 rounded-md bg-[#FDFEBA] text-[#280047] text-xs font-bold uppercase border border-[#C3B6D3] self-start sm:self-auto">
          {{ quotation.status }}
        </div>
      </div>

      <!-- Quotation Document -->
      <div class="app-card p-4 sm:p-8 space-y-6 border-t-4 border-t-[#280047]">
        <div class="flex flex-col sm:flex-row justify-between gap-4 pb-6 border-b border-[#E5E0EC]">
          <div>
            <h1 class="text-lg sm:text-xl font-bold text-[#280047]">
              {{ quotation.organization?.name || 'Quotation' }}
            </h1>
            <p v-if="quotation.organization?.billing_email" class="text-xs text-[#6B6578] break-all">
              {{ quotation.organization.billing_email }}
            </p>
            <p v-if="quotation.organization?.phone" class="text-xs text-[#6B6578] font-mono-num">
              {{ quotation.organization.phone }}
            </p>
          </div>
          <div class="sm:text-right">
            <p class="text-base sm:text-lg font-bold font-mono-num text-[#242424] break-all">{{ quotation.quotation_number }}</p>
            <p v-if="quotation.created_at" class="text-xs text-[#6B6578] font-mono-num">
              Date: {{ new Date(quotation.created_at).toLocaleDateString() }}
            </p>
            <p v-if="quotation.validity_date" class="text-xs text-[#6B6578] font-mono-num">
              Valid Until: {{ new Date(quotation.validity_date).toLocaleDateString() }}
            </p>
          </div>
        </div>

        <!-- Client Info -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
          <div class="p-4 rounded-md bg-[#FAFAFB] border border-[#E5E0EC]">
            <p class="text-[11px] font-bold uppercase tracking-wider text-[#6B6578] mb-1">Prepared For</p>
            <p class="text-sm font-bold text-[#242424]">{{ quotation.customer_name }}</p>
            <p class="text-[#6B6578]">{{ quotation.company_name || '' }}</p>
            <p class="font-mono-num text-[#242424] mt-1 break-all">{{ quotation.customer_phone }}</p>
          </div>
          <div class="p-4 rounded-md bg-[#FAFAFB] border border-[#E5E0EC] flex flex-col justify-center sm:text-right">
            <p class="text-[11px] font-bold uppercase tracking-wider text-[#6B6578] mb-1">Total Amount</p>
            <p class="text-xl sm:text-2xl font-bold text-[#280047] font-mono-num break-all sm:break-normal">
              {{ formatMoney(Number(quotation.total_amount)) }}
            </p>
          </div>
        </div>

        <!-- Line Items Mobile Cards (< 640px) — Zero Content Loss -->
        <div class="sm:hidden border border-[#E5E0EC] rounded-md divide-y divide-[#E5E0EC] text-xs">
          <div
            v-for="(item, i) in quotation.items"
            :key="`pub-mob-item-${i}`"
            class="p-3.5 space-y-2 bg-white"
          >
            <p class="font-medium text-[#242424]">{{ item.description }}</p>
            <div class="grid grid-cols-2 min-[340px]:grid-cols-3 gap-2 pt-2 border-t border-[#F0EDF5]">
              <div>
                <p class="text-[10px] uppercase tracking-wider text-[#6B6578]">Qty</p>
                <p class="font-mono-num text-[#242424] mt-0.5">{{ item.quantity }}</p>
              </div>
              <div>
                <p class="text-[10px] uppercase tracking-wider text-[#6B6578]">Unit Price</p>
                <p class="font-mono-num text-[#242424] mt-0.5">{{ formatMoney(Number(item.unit_price)) }}</p>
              </div>
              <div class="col-span-2 min-[340px]:col-span-1">
                <p class="text-[10px] uppercase tracking-wider text-[#6B6578]">Total</p>
                <p class="font-mono-num font-semibold text-[#280047] mt-0.5">{{ formatMoney(Number(item.total_price)) }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Line Items Table (>= 640px) -->
        <div class="hidden sm:block overflow-x-auto border border-[#E5E0EC] rounded-md">
          <table class="w-full text-left border-collapse text-xs">
            <thead>
              <tr class="bg-[#F8F6FA] border-b border-[#E5E0EC] text-[11px] font-semibold uppercase text-[#6B6578]">
                <th class="px-4 py-2.5">Description</th>
                <th class="px-4 py-2.5 text-right">Qty</th>
                <th class="px-4 py-2.5 text-right">Unit Price</th>
                <th class="px-4 py-2.5 text-right">Total</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#E5E0EC]">
              <tr v-for="(item, i) in quotation.items" :key="i">
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

        <!-- Summary & Terms -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-2 text-xs">
          <div>
            <p class="font-bold text-[#242424] mb-1">Terms & Notes</p>
            <p class="text-[#6B6578] whitespace-pre-line leading-relaxed">
              {{ quotation.terms_and_conditions || quotation.notes || 'Standard terms and conditions apply.' }}
            </p>
          </div>
          <div class="space-y-2">
            <div class="flex justify-between">
              <span class="text-[#6B6578]">Subtotal</span>
              <span class="font-mono-num font-semibold">{{ formatMoney(Number(quotation.subtotal)) }}</span>
            </div>
            <div v-if="Number(quotation.discount_amount) > 0" class="flex justify-between">
              <span class="text-[#6B6578]">Discount</span>
              <span class="font-mono-num font-semibold text-[#F74D01]">- {{ formatMoney(Number(quotation.discount_amount)) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-[#6B6578]">VAT ({{ quotation.tax_rate }}%)</span>
              <span class="font-mono-num font-semibold">{{ formatMoney(Number(quotation.tax_amount)) }}</span>
            </div>
            <div class="flex justify-between pt-2.5 border-t border-[#E5E0EC] text-sm font-bold text-[#280047]">
              <span>Grand Total</span>
              <span class="font-mono-num">{{ formatMoney(Number(quotation.total_amount)) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
