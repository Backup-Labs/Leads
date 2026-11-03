<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import {
    LayoutDashboard,
    Users,
    Kanban,
    CalendarCheck,
    FileText,
    Zap,
    BarChart3,
    UserCheck,
    Settings,
    LogOut,
    Plus,
    Building2,
    ChevronDown,
    Menu,
    X,
    Bell,
    CheckCircle2,
    AlertCircle,
    AlertTriangle
} from 'lucide-vue-next';

const page = usePage();
const auth = computed(() => page.props.auth as any);
const flash = computed(() => page.props.flash as any);

const mobileMenuOpen = ref(false);
const orgMenuOpen = ref(false);
const userMenuOpen = ref(false);

const navigation = [
    { name: 'Overview', href: '/dashboard', route: 'dashboard', icon: LayoutDashboard },
    { name: 'Leads', href: '/leads', route: 'leads.index', icon: Users },
    { name: 'Pipeline', href: '/pipeline', route: 'pipeline.index', icon: Kanban },
    { name: 'Follow-ups', href: '/follow-ups', route: 'follow-ups.index', icon: CalendarCheck },
    { name: 'Quotations', href: '/quotations', route: 'quotations.index', icon: FileText },
    { name: 'Revenue Recovery', href: '/recovery', route: 'recovery.index', icon: Zap, highlight: true },
    { name: 'Analytics', href: '/analytics', route: 'analytics.index', icon: BarChart3 },
    { name: 'Team', href: '/team', route: 'team.index', icon: UserCheck },
    { name: 'Settings', href: '/settings', route: 'settings.index', icon: Settings },
];

const isCurrent = (url: string) => {
    return page.url.startsWith(url);
};

const switchOrg = (orgId: number) => {
    router.post(`/organizations/${orgId}/switch`, {}, {
        onFinish: () => { orgMenuOpen.value = false; }
    });
};

const logout = () => {
    router.post('/logout');
};
</script>

<template>
    <div class="min-h-screen bg-[#F8F9FA] flex flex-col lg:flex-row font-sans">
        <!-- Desktop Sidebar -->
        <aside class="hidden lg:flex lg:flex-col w-64 bg-[#280047] text-white flex-shrink-0 min-h-screen border-r border-[#3a0066]">
            <!-- Brand Logo -->
            <div class="h-16 flex items-center px-6 gap-3 border-b border-[#3a0066]">
                <div class="w-9 h-9 rounded-xl bg-[#F74D01] flex items-center justify-center font-bold text-white shadow-sm">
                    L
                </div>
                <div>
                    <span class="text-xl font-extrabold tracking-tight">Leads</span>
                    <span class="ml-1 text-[10px] bg-[#3a0066] text-[#C3B6D3] px-1.5 py-0.5 rounded font-semibold uppercase tracking-wider">SaaS</span>
                </div>
            </div>

            <!-- Organization Switcher -->
            <div class="p-4 border-b border-[#3a0066] relative">
                <button
                    @click="orgMenuOpen = !orgMenuOpen"
                    class="w-full flex items-center justify-between p-2.5 rounded-xl bg-[#320358] hover:bg-[#3d046c] text-left transition-colors"
                >
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-8 h-8 rounded-lg bg-[#4a0882] flex items-center justify-center text-white font-bold flex-shrink-0">
                            <Building2 class="w-4 h-4 text-[#FDFEBA]" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-semibold text-white truncate">{{ auth.currentOrganization?.name || 'Workspace' }}</p>
                            <p class="text-[10px] text-[#C3B6D3] truncate">{{ auth.currentMember?.role_label || 'Member' }}</p>
                        </div>
                    </div>
                    <ChevronDown class="w-4 h-4 text-[#C3B6D3] flex-shrink-0 ml-1" />
                </button>

                <!-- Org Dropdown Menu -->
                <div
                    v-if="orgMenuOpen"
                    class="absolute left-4 right-4 top-16 mt-2 bg-white rounded-xl shadow-xl border border-gray-100 py-1.5 z-50 text-[#242424]"
                >
                    <div class="px-3 py-1.5 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Switch Workspace</div>
                    <button
                        v-for="org in auth.organizations"
                        :key="org.id"
                        @click="switchOrg(org.id)"
                        class="w-full text-left px-3 py-2 text-xs hover:bg-gray-50 flex items-center justify-between font-medium"
                        :class="{'text-[#F74D01] font-bold bg-orange-50/50': org.id === auth.currentOrganization?.id}"
                    >
                        <span>{{ org.name }}</span>
                        <span v-if="org.id === auth.currentOrganization?.id" class="text-[10px] bg-orange-100 text-[#F74D01] px-1.5 py-0.5 rounded">Active</span>
                    </button>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                <Link
                    v-for="item in navigation"
                    :key="item.name"
                    :href="item.href"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all group"
                    :class="[
                        isCurrent(item.href)
                            ? 'bg-[#F74D01] text-white shadow-sm'
                            : item.highlight
                                ? 'text-[#FDFEBA] hover:bg-[#3d046c]'
                                : 'text-[#C3B6D3] hover:text-white hover:bg-[#320358]'
                    ]"
                >
                    <component :is="item.icon" class="w-5 h-5 flex-shrink-0" />
                    <span>{{ item.name }}</span>
                    <span
                        v-if="item.highlight && !isCurrent(item.href)"
                        class="ml-auto text-[10px] bg-[#F74D01] text-white font-bold px-1.5 py-0.5 rounded-full"
                    >
                        Radar
                    </span>
                </Link>
            </nav>

            <!-- User footer -->
            <div class="p-4 border-t border-[#3a0066] flex items-center justify-between">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-8 h-8 rounded-full bg-[#F74D01] flex items-center justify-center font-bold text-xs text-white">
                        {{ auth.user?.name?.charAt(0) || 'U' }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold text-white truncate">{{ auth.user?.name }}</p>
                        <p class="text-[10px] text-[#C3B6D3] truncate">{{ auth.user?.email }}</p>
                    </div>
                </div>
                <button @click="logout" title="Log out" class="text-[#C3B6D3] hover:text-[#F74D01] transition-colors p-1.5">
                    <LogOut class="w-4 h-4" />
                </button>
            </div>
        </aside>

        <!-- Mobile Header -->
        <header class="lg:hidden bg-[#280047] text-white h-16 flex items-center justify-between px-4 z-40">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-[#F74D01] flex items-center justify-center font-bold text-white">
                    L
                </div>
                <span class="font-extrabold text-lg">Leads</span>
            </div>
            <button @click="mobileMenuOpen = !mobileMenuOpen" class="text-white p-2">
                <Menu v-if="!mobileMenuOpen" class="w-6 h-6" />
                <X v-else class="w-6 h-6" />
            </button>
        </header>

        <!-- Mobile Navigation Menu -->
        <div v-if="mobileMenuOpen" class="lg:hidden bg-[#280047] text-white px-4 py-4 space-y-2 border-b border-[#3a0066] z-30">
            <Link
                v-for="item in navigation"
                :key="item.name"
                :href="item.href"
                @click="mobileMenuOpen = false"
                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium"
                :class="isCurrent(item.href) ? 'bg-[#F74D01] text-white' : 'text-[#C3B6D3] hover:bg-[#320358]'"
            >
                <component :is="item.icon" class="w-5 h-5" />
                <span>{{ item.name }}</span>
            </Link>
            <div class="pt-2 border-t border-[#3a0066] flex justify-between items-center">
                <span class="text-xs text-[#C3B6D3]">{{ auth.user?.email }}</span>
                <button @click="logout" class="text-xs text-[#F74D01] font-semibold">Log out</button>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Topbar (Desktop) -->
            <header class="hidden lg:flex items-center justify-between h-16 px-8 bg-white border-b border-gray-100 flex-shrink-0">
                <div class="flex items-center gap-3">
                    <h2 class="text-sm font-semibold text-gray-500">
                        {{ auth.currentOrganization?.name }}
                    </h2>
                    <span class="text-gray-300">/</span>
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-orange-50 text-[#F74D01] border border-orange-100">
                        {{ auth.currentOrganization?.currency }}
                    </span>
                </div>

                <div class="flex items-center gap-4">
                    <Link
                        href="/leads/create"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-[#F74D01] hover:bg-[#d94200] shadow-sm transition-all"
                    >
                        <Plus class="w-4 h-4" />
                        <span>New Lead</span>
                    </Link>
                </div>
            </header>

            <!-- Flash Notifications -->
            <div v-if="flash.success || flash.error || flash.warning" class="px-6 lg:px-8 pt-4">
                <div v-if="flash.success" class="flex items-center gap-2 p-3 text-sm text-emerald-800 bg-emerald-50 border border-emerald-200 rounded-xl">
                    <CheckCircle2 class="w-4 h-4 text-emerald-600 flex-shrink-0" />
                    <span>{{ flash.success }}</span>
                </div>
                <div v-if="flash.error" class="flex items-center gap-2 p-3 text-sm text-rose-800 bg-rose-50 border border-rose-200 rounded-xl">
                    <AlertCircle class="w-4 h-4 text-rose-600 flex-shrink-0" />
                    <span>{{ flash.error }}</span>
                </div>
                <div v-if="flash.warning" class="flex items-center gap-2 p-3 text-sm text-amber-800 bg-amber-50 border border-amber-200 rounded-xl">
                    <AlertTriangle class="w-4 h-4 text-amber-600 flex-shrink-0" />
                    <span>{{ flash.warning }}</span>
                </div>
            </div>

            <!-- Page Body -->
            <main class="flex-1 p-6 lg:p-8 overflow-y-auto">
                <slot />
            </main>
        </div>
    </div>
</template>
