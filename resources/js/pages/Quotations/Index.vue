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
          <h1 class="text-xl font-bold text-brand-dark">Quotations</h1>
          <p class="text-xs text-[#6B6578] mt-0.5">Create, share, and track client proposals and quotations.</p>
        </div>
        <div class="flex items-center">
          <Link href="/quotations/create" class="btn-primary text-xs">
            <i class="fa-solid fa-plus"></i> New Quotation
          </Link>
        </div>
      </div>

      <!-- Quotations Table & Responsive Mobile Card List -->
      <div class="app-card overflow-hidden">
        <!-- Empty State -->
        <div v-if="quotations.data.length === 0" class="px-4 sm:px-6 py-12 text-center">
          <div class="w-10 h-10 rounded-md bg-[#F3F1F6] text-brand-indigo flex items-center justify-center mx-auto mb-2.5">
            <i class="fa-solid fa-file-invoice-dollar text-sm"></i>
          </div>
          <p class="text-sm font-semibold text-brand-dark">No quotations generated yet</p>
          <p class="text-xs text-[#6B6578] mt-0.5 mb-4">Create your first client quotation in seconds.</p>
          <Link href="/quotations/create" class="btn-primary text-xs">
            <i class="fa-solid fa-plus"></i> Create Quotation
          </Link>
        </div>

        <template v-else>
          <!-- Mobile Card List (< 768px) — Zero Content Loss (All 8 columns preserved) -->
          <div class="md:hidden divide-y divide-[#E5E0EC]">
            <div
              v-for="q in quotations.data"
              :key="`mob-q-${q.id}`"
              class="p-4 space-y-3 hover:bg-[#FAFAFB] transition-colors"
            >
              <div class="flex items-start justify-between gap-2">
                <div class="min-w-0">
                  <Link
                    :href="`/quotations/${q.id}`"
                    class="text-sm font-bold text-brand-indigo font-mono-num hover:text-brand-orange block"
                  >
                    {{ q.quotation_number }}
                  </Link>
                  <p class="text-xs font-semibold text-brand-dark mt-0.5">
                    {{ q.customer_name || q.lead?.customer_name || 'Client' }}
                  </p>
                  <p class="text-[11px] text-[#6B6578]">{{ q.company_name || 'Direct' }}</p>
                </div>
                <span
                  class="px-2 py-0.5 rounded border text-[10px] font-semibold uppercase shrink-0"
                  :class="statusStyle[q.status] || statusStyle.draft"
                >
                  {{ q.status }}
                </span>
              </div>

              <div class="grid grid-cols-2 min-[360px]:grid-cols-3 gap-2 pt-2 border-t border-[#F0EDF5] text-xs">
                <div>
                  <p class="text-[10px] uppercase tracking-wider text-[#6B6578]">Subtotal</p>
                  <p class="font-mono-num text-[#6B6578] mt-0.5">{{ formatMoney(Number(q.subtotal), q.currency) }}</p>
                </div>
                <div>
                  <p class="text-[10px] uppercase tracking-wider text-[#6B6578]">VAT</p>
                  <p class="font-mono-num text-[#6B6578] mt-0.5">{{ formatMoney(Number(q.tax_amount), q.currency) }}</p>
                </div>
                <div class="col-span-2 min-[360px]:col-span-1">
                  <p class="text-[10px] uppercase tracking-wider text-[#6B6578]">Total Amount</p>
                  <p class="font-mono-num font-bold text-brand-dark mt-0.5">{{ formatMoney(Number(q.total_amount), q.currency) }}</p>
                </div>
              </div>

              <div class="flex flex-wrap items-center justify-between gap-2 pt-2 border-t border-[#F0EDF5]">
                <span class="text-[11px] text-[#6B6578] font-mono-num">
                  Valid Until: <strong class="text-brand-dark">{{ q.validity_date ? new Date(q.validity_date).toLocaleDateString() : '—' }}</strong>
                </span>
                <Link
                  :href="`/quotations/${q.id}`"
                  class="px-2.5 py-1 rounded bg-brand-indigo text-white hover:bg-[#3a0066] text-[11px] font-medium"
                >
                  View
                </Link>
              </div>
            </div>
          </div>

          <!-- Desktop & Tablet Table (>= 768px) -->
          <div class="hidden md:block overflow-x-auto">
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
                <tr
                  v-for="q in quotations.data"
                  :key="q.id"
                  class="hover:bg-[#FAFAFB] transition-colors"
                >
                  <td class="px-4 py-3.5 font-mono-num font-bold text-brand-indigo">
                    <Link :href="`/quotations/${q.id}`" class="hover:text-brand-orange">
                      {{ q.quotation_number }}
                    </Link>
                  </td>
                  <td class="px-4 py-3.5">
                    <p class="font-semibold text-brand-dark">{{ q.customer_name || q.lead?.customer_name || 'Client' }}</p>
                    <p class="text-[11px] text-[#6B6578]">{{ q.company_name || 'Direct' }}</p>
                  </td>
                  <td class="px-4 py-3.5 font-mono-num text-[#6B6578]">
                    {{ formatMoney(Number(q.subtotal), q.currency) }}
                  </td>
                  <td class="px-4 py-3.5 font-mono-num text-[#6B6578]">
                    {{ formatMoney(Number(q.tax_amount), q.currency) }}
                  </td>
                  <td class="px-4 py-3.5 font-mono-num font-bold text-brand-dark">
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
                      class="px-2.5 py-1 rounded bg-brand-indigo text-white hover:bg-[#3a0066] text-[11px] font-medium"
                    >
                      View
                    </Link>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div
            v-if="quotations.last_page > 1"
            class="px-4 py-3 border-t border-[#E5E0EC] bg-[#FAFAFB] flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs"
          >
            <span class="text-[#6B6578]">
              Showing <strong class="text-brand-dark">{{ quotations.from }}</strong> to
              <strong class="text-brand-dark">{{ quotations.to }}</strong> of
              <strong class="text-brand-dark">{{ quotations.total }}</strong> quotations
            </span>
            <div class="flex flex-wrap items-center gap-1">
              <template v-for="(link, idx) in quotations.links" :key="idx">
                <Link
                  v-if="link.url"
                  :href="link.url"
                  v-html="link.label"
                  class="px-2.5 py-1 rounded border text-xs transition-colors"
                  :class="
                    link.active
                      ? 'bg-brand-indigo text-white border-brand-indigo font-semibold'
                      : 'bg-white text-brand-dark border-[#E5E0EC] hover:bg-[#F3F1F6]'
                  "
                />
              </template>
            </div>
          </div>
        </template>
      </div>
    </div>
  </AppLayout>
</template>
