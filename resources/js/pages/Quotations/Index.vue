<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { Quotation, PaginatedData, PageProps } from '@/types';

defineProps<{
  quotations: PaginatedData<Quotation>;
  filters: {
    status?: string;
    search?: string;
  };
}>();

const page = usePage<PageProps>();
const currency = computed(() => page.props.auth?.currentOrganization?.currency || 'UGX');

const formatMoney = (amount: number, quoteCurrency?: string) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: quoteCurrency || currency.value,
    maximumFractionDigits: 0,
  }).format(amount || 0);
};

const statusStyle: Record<string, string> = {
  draft: 'bg-[#F3F1F6] text-[#280047] border-[#C3B6D3]',
  sent: 'bg-[#FDFEBA] text-[#280047] border-[#C3B6D3]',
  accepted: 'bg-emerald-50 text-emerald-800 border-emerald-200',
  rejected: 'bg-red-50 text-red-700 border-red-200',
  expired: 'bg-gray-100 text-gray-600 border-gray-200',
};
</script>

<template>
  <Head title="Quotations" />
  <AppLayout>
    <div class="space-y-5">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-xl font-bold text-[#242424]">Quotations</h1>
          <p class="text-xs text-[#6B6578] mt-0.5">Create, share, and track client proposals and quotations.</p>
        </div>
        <Link href="/quotations/create" class="btn-primary text-xs">
          <i class="fa-solid fa-plus"></i> New Quotation
        </Link>
      </div>

      <!-- Quotations Table -->
      <div class="app-card overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-[#F8F6FA] border-b border-[#E5E0EC] text-[11px] font-semibold uppercase tracking-wider text-[#6B6578]">
                <th class="px-4 py-3">Quotation #</th>
                <th class="px-4 py-3">Client</th>
                <th class="px-4 py-3">Subtotal</th>
                <th class="px-4 py-3">VAT</th>
                <th class="px-4 py-3">Total Amount</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">Valid Until</th>
                <th class="px-4 py-3 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#E5E0EC] text-xs">
              <tr v-if="quotations.data.length === 0">
                <td colspan="8" class="px-6 py-12 text-center">
                  <div class="w-10 h-10 rounded-md bg-[#F3F1F6] text-[#280047] flex items-center justify-center mx-auto mb-2.5">
                    <i class="fa-solid fa-file-invoice-dollar text-sm"></i>
                  </div>
                  <p class="text-sm font-semibold text-[#242424]">No quotations generated yet</p>
                  <p class="text-xs text-[#6B6578] mt-0.5 mb-4">Create your first client quotation in seconds.</p>
                  <Link href="/quotations/create" class="btn-primary text-xs">
                    <i class="fa-solid fa-plus"></i> Create Quotation
                  </Link>
                </td>
              </tr>

              <tr
                v-for="q in quotations.data"
                :key="q.id"
                class="hover:bg-[#FAFAFB] transition-colors"
              >
                <td class="px-4 py-3.5 font-mono-num font-bold text-[#280047]">
                  <Link :href="`/quotations/${q.id}`" class="hover:text-[#F74D01]">
                    {{ q.quotation_number }}
                  </Link>
                </td>
                <td class="px-4 py-3.5">
                  <p class="font-semibold text-[#242424]">{{ q.customer_name || q.lead?.customer_name || 'Client' }}</p>
                  <p class="text-[11px] text-[#6B6578]">{{ q.company_name || 'Direct' }}</p>
                </td>
                <td class="px-4 py-3.5 font-mono-num text-[#6B6578]">
                  {{ formatMoney(Number(q.subtotal), q.currency) }}
                </td>
                <td class="px-4 py-3.5 font-mono-num text-[#6B6578]">
                  {{ formatMoney(Number(q.tax_amount), q.currency) }}
                </td>
                <td class="px-4 py-3.5 font-mono-num font-bold text-[#242424]">
                  {{ formatMoney(Number(q.total_amount), q.currency) }}
                </td>
                <td class="px-4 py-3.5">
                  <span
                    class="px-2 py-0.5 rounded border text-[11px] font-semibold uppercase"
                    :class="statusStyle[q.status] || statusStyle.draft"
                  >
                    {{ q.status }}
                  </span>
                </td>
                <td class="px-4 py-3.5 font-mono-num text-[#6B6578]">
                  {{ q.validity_date ? new Date(q.validity_date).toLocaleDateString() : '—' }}
                </td>
                <td class="px-4 py-3.5 text-right">
                  <Link
                    :href="`/quotations/${q.id}`"
                    class="px-2.5 py-1 rounded bg-[#280047] text-white hover:bg-[#3a0066] text-[11px] font-medium"
                  >
                    View
                  </Link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
