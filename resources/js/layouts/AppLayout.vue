<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import CustomSelect from '@/components/CustomSelect.vue';
import { CURRENCY_OPTIONS } from '@/utils/currency';

const page = usePage();
const auth = computed(() => page.props.auth as any);
const flash = computed(() => page.props.flash as any);

const mobileMenuOpen = ref(false);
const orgMenuOpen = ref(false);

const currentCurrency = computed(() => auth.value?.currentOrganization?.currency || 'UGX');

const navigation = [
  { name: 'Dashboard', href: '/dashboard', icon: 'fa-solid fa-chart-pie' },
  { name: 'Leads', href: '/leads', icon: 'fa-solid fa-user-group' },
  { name: 'Pipeline', href: '/pipeline', icon: 'fa-solid fa-table-columns' },
  { name: 'Follow-ups', href: '/follow-ups', icon: 'fa-solid fa-calendar-check' },
  { name: 'Quotations', href: '/quotations', icon: 'fa-solid fa-file-invoice-dollar' },
  { name: 'Revenue Recovery', href: '/recovery', icon: 'fa-solid fa-bolt', highlight: true },
  { name: 'Analytics', href: '/analytics', icon: 'fa-solid fa-chart-line' },
  { name: 'Team', href: '/team', icon: 'fa-solid fa-users-gear' },
  { name: 'Settings', href: '/settings', icon: 'fa-solid fa-gear' },
];

const isCurrent = (url: string) => {
  return page.url.startsWith(url);
};

const switchOrg = (orgId: number) => {
  router.post(
    `/organizations/${orgId}/switch`,
    {},
    {
      onFinish: () => {
        orgMenuOpen.value = false;
      },
    }
  );
};

const switchCurrency = (newCurrency: string) => {
  if (!newCurrency || newCurrency === currentCurrency.value) return;
  router.patch('/settings/currency', { currency: newCurrency }, { preserveScroll: true });
};

const logout = () => {
  router.post('/logout');
};
</script>

<template>
  <div class="h-screen overflow-hidden bg-[#FAFAFB] flex flex-col lg:flex-row font-sans text-[#242424]">
    <!-- Static Non-Scrolling Sidebar -->
    <aside
      class="hidden lg:flex lg:flex-col w-60 h-screen bg-[#280047] text-white flex-shrink-0 border-r border-[#280047] select-none overflow-hidden"
    >
      <!-- Brand Header -->
      <div class="h-14 flex items-center justify-between px-5 border-b border-white/10 flex-shrink-0">
        <Link href="/dashboard" class="flex items-center gap-2.5">
          <div class="w-7 h-7 rounded-md bg-[#F74D01] flex items-center justify-center text-white font-bold text-sm">
            L
          </div>
          <span class="text-base font-bold tracking-tight text-white">Leads</span>
        </Link>
        <span class="text-[10px] font-mono-num font-semibold px-1.5 py-0.5 rounded bg-[#FDFEBA] text-[#280047]">
          {{ currentCurrency }}
        </span>
      </div>

      <!-- Company Switcher -->
      <div class="p-3 border-b border-white/10 flex-shrink-0 relative">
        <button
          type="button"
          @click="orgMenuOpen = !orgMenuOpen"
          class="w-full flex items-center justify-between px-3 py-2 rounded-md bg-white/8 hover:bg-white/12 text-left transition-colors cursor-pointer"
        >
          <div class="min-w-0">
            <p class="text-xs font-semibold text-white truncate">{{ auth.currentOrganization?.name || 'Company' }}</p>
            <p class="text-[11px] text-[#C3B6D3] truncate">{{ auth.currentMember?.role_label || 'Member' }}</p>
          </div>
          <i
            class="fa-solid fa-chevron-down text-[10px] text-[#C3B6D3] ml-2 transition-transform"
            :class="{ 'rotate-180': orgMenuOpen }"
          ></i>
        </button>

        <!-- Company Dropdown -->
        <div
          v-if="orgMenuOpen"
          class="absolute left-3 right-3 top-14 mt-1 bg-white rounded-md shadow-lg border border-[#C3B6D3] py-1 z-50 text-[#242424]"
        >
          <div class="px-3 py-1 text-[10px] font-semibold text-gray-500 uppercase tracking-wider border-b border-gray-100">
            Switch Company
          </div>
          <button
            v-for="org in auth.organizations"
            :key="org.id"
            type="button"
            @click="switchOrg(org.id)"
            class="w-full text-left px-3 py-2 text-xs hover:bg-gray-50 flex items-center justify-between cursor-pointer"
            :class="{ 'font-bold text-[#280047] bg-[#C3B6D3]/20': org.id === auth.currentOrganization?.id }"
          >
            <span class="truncate">{{ org.name }}</span>
            <i v-if="org.id === auth.currentOrganization?.id" class="fa-solid fa-check text-[#F74D01] text-[10px]"></i>
          </button>
        </div>
      </div>

      <!-- Static Navigation Links (No Scroll) -->
      <nav class="flex-1 px-3 py-3 space-y-0.5 overflow-hidden">
        <Link
          v-for="item in navigation"
          :key="item.name"
          :href="item.href"
          class="flex items-center gap-3 px-3 py-2 rounded-md text-xs font-medium transition-colors"
          :class="[
            isCurrent(item.href)
              ? 'bg-[#F74D01] text-white font-semibold'
              : item.highlight
                ? 'text-[#FDFEBA] hover:bg-white/10'
                : 'text-[#C3B6D3] hover:text-white hover:bg-white/8',
          ]"
        >
          <i :class="item.icon" class="w-4 text-center text-xs"></i>
          <span class="truncate">{{ item.name }}</span>
        </Link>
      </nav>

      <!-- User Footer -->
      <div class="p-3 border-t border-white/10 flex items-center justify-between flex-shrink-0">
        <div class="flex items-center gap-2.5 min-w-0">
          <div
            class="w-7 h-7 rounded-md bg-[#C3B6D3] text-[#280047] flex items-center justify-center font-bold text-xs flex-shrink-0"
          >
            {{ auth.user?.name?.charAt(0) || 'U' }}
          </div>
          <div class="min-w-0">
            <p class="text-xs font-semibold text-white truncate">{{ auth.user?.name }}</p>
            <p class="text-[10px] text-[#C3B6D3] truncate">{{ auth.user?.email }}</p>
          </div>
        </div>
        <button
          type="button"
          @click="logout"
          title="Sign Out"
          class="w-7 h-7 rounded-md hover:bg-white/10 text-[#C3B6D3] hover:text-[#FDFEBA] flex items-center justify-center transition-colors cursor-pointer flex-shrink-0"
        >
          <i class="fa-solid fa-right-from-bracket text-xs"></i>
        </button>
      </div>
    </aside>

    <!-- Mobile Topbar -->
    <header class="lg:hidden bg-[#280047] text-white h-14 flex-shrink-0 flex items-center justify-between px-4 z-40">
      <Link href="/dashboard" class="flex items-center gap-2">
        <div class="w-7 h-7 rounded-md bg-[#F74D01] flex items-center justify-center text-white font-bold text-sm">L</div>
        <span class="font-bold text-base">Leads</span>
      </Link>
      <button type="button" @click="mobileMenuOpen = !mobileMenuOpen" class="p-2 text-white">
        <i :class="mobileMenuOpen ? 'fa-solid fa-xmark' : 'fa-solid fa-bars'" class="text-base"></i>
      </button>
    </header>

    <!-- Mobile Menu -->
    <div
      v-if="mobileMenuOpen"
      class="lg:hidden bg-[#280047] text-white px-4 py-3 space-y-1 border-b border-white/10 z-30 flex-shrink-0"
    >
      <Link
        v-for="item in navigation"
        :key="item.name"
        :href="item.href"
        @click="mobileMenuOpen = false"
        class="flex items-center gap-3 px-3 py-2 rounded-md text-xs font-medium"
        :class="isCurrent(item.href) ? 'bg-[#F74D01] text-white' : 'text-[#C3B6D3]'"
      >
        <i :class="item.icon" class="w-4 text-center"></i>
        <span>{{ item.name }}</span>
      </Link>
      <div class="pt-2 mt-2 border-t border-white/10 flex justify-between items-center">
        <span class="text-xs text-[#C3B6D3]">{{ auth.user?.email }}</span>
        <button type="button" @click="logout" class="text-xs text-[#FDFEBA] font-semibold">Sign Out</button>
      </div>
    </div>

    <!-- Main Content Area (Static Header + Scrollable Page Body) -->
    <div class="flex-1 flex flex-col h-screen overflow-hidden min-w-0">
      <!-- Static Desktop Header -->
      <header class="hidden lg:flex items-center justify-between h-14 px-8 bg-white border-b border-[#E5E0EC] flex-shrink-0">
        <div class="flex items-center gap-2.5 text-xs">
          <span class="font-semibold text-[#280047]">{{ auth.currentOrganization?.name }}</span>
          <span class="text-[#C3B6D3]">/</span>
          <span class="text-gray-500">{{ auth.currentMember?.role_label || 'Member' }}</span>
        </div>

        <div class="flex items-center gap-3">
          <!-- Custom Currency Switcher with Auto-Conversion -->
          <div class="w-48">
            <CustomSelect
              :model-value="currentCurrency"
              :options="CURRENCY_OPTIONS"
              size="sm"
              align="right"
              icon="fa-solid fa-coins"
              @change="switchCurrency"
            />
          </div>

          <Link href="/quotations/create" class="btn-secondary inline-flex items-center gap-1.5">
            <i class="fa-solid fa-file-invoice text-[#280047]"></i>
            <span>New Quotation</span>
          </Link>

          <Link href="/leads/create" class="btn-primary inline-flex items-center gap-1.5">
            <i class="fa-solid fa-plus"></i>
            <span>New Lead</span>
          </Link>
        </div>
      </header>

      <!-- Scrollable Page Body -->
      <main class="flex-1 p-6 lg:p-8 overflow-y-auto">
        <!-- Flash Messages -->
        <div v-if="flash.success || flash.error || flash.warning" class="mb-5">
          <div
            v-if="flash.success"
            class="flex items-center gap-2.5 p-3 text-xs font-medium text-[#280047] bg-[#FDFEBA] border border-[#280047]/20 rounded-md"
          >
            <i class="fa-solid fa-circle-check text-[#280047]"></i>
            <span>{{ flash.success }}</span>
          </div>
          <div
            v-if="flash.error"
            class="flex items-center gap-2.5 p-3 text-xs font-medium text-white bg-[#F74D01] rounded-md"
          >
            <i class="fa-solid fa-circle-exclamation"></i>
            <span>{{ flash.error }}</span>
          </div>
          <div
            v-if="flash.warning"
            class="flex items-center gap-2.5 p-3 text-xs font-medium text-[#280047] bg-[#FDFEBA] border border-[#F74D01] rounded-md"
          >
            <i class="fa-solid fa-triangle-exclamation text-[#F74D01]"></i>
            <span>{{ flash.warning }}</span>
          </div>
        </div>

        <slot />
      </main>
    </div>
  </div>
</template>
