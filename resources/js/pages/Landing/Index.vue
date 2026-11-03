<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { convertCurrencyAmount, formatCurrencyAmount } from '@/utils/currency';
import type { PageProps } from '@/types';

const page = usePage<PageProps>();
const isLoggedIn = computed(() => Boolean(page.props.auth?.user));

const mobileNavOpen = ref(false);
const activeSection = ref('how-it-works');

// ============================================================================
// 1. TYPEWRITER / SELF-WRITING TEXT ANIMATIONS
// ============================================================================
const heroPhrases = [
  'without losing leads',
  'without forgotten quotes',
  'without messy sheets',
  'without micromanaging',
];
const typedHeroText = ref('');
let phraseIndex = 0;
let charIndex = 0;
let isDeleting = false;
let typeTimer: ReturnType<typeof setTimeout> | null = null;

const runTypewriter = () => {
  const currentPhrase = heroPhrases[phraseIndex];

  if (!isDeleting) {
    typedHeroText.value = currentPhrase.slice(0, charIndex + 1);
    charIndex++;

    if (charIndex === currentPhrase.length) {
      isDeleting = true;
      typeTimer = setTimeout(runTypewriter, 2200);
      return;
    }
    typeTimer = setTimeout(runTypewriter, 65);
  } else {
    typedHeroText.value = currentPhrase.slice(0, charIndex - 1);
    charIndex--;

    if (charIndex === 0) {
      isDeleting = false;
      phraseIndex = (phraseIndex + 1) % heroPhrases.length;
      typeTimer = setTimeout(runTypewriter, 320);
      return;
    }
    typeTimer = setTimeout(runTypewriter, 32);
  }
};

// Live self-writing status line inside Hero Diagram
const liveFeedMessages = [
  'Capturing WhatsApp inquiry from Grace Nakato...',
  'Assigning lead to Sales Rep: Diane M...',
  'Generating Quotation #QUO-2026-0042 ($3,200)...',
  'Scheduling follow-up reminder for Tomorrow 10:00 AM...',
];
const typedFeedText = ref('');
let feedIndex = 0;
let feedCharIndex = 0;
let feedTimer: ReturnType<typeof setTimeout> | null = null;

const runFeedWriter = () => {
  const currentMsg = liveFeedMessages[feedIndex];
  if (feedCharIndex < currentMsg.length) {
    typedFeedText.value = currentMsg.slice(0, feedCharIndex + 1);
    feedCharIndex++;
    feedTimer = setTimeout(runFeedWriter, 42);
  } else {
    feedTimer = setTimeout(() => {
      feedCharIndex = 0;
      typedFeedText.value = '';
      feedIndex = (feedIndex + 1) % liveFeedMessages.length;
      runFeedWriter();
    }, 2000);
  }
};

// ============================================================================
// 2. SCROLL REVEAL OBSERVER
// ============================================================================
let observer: IntersectionObserver | null = null;

onMounted(() => {
  runTypewriter();
  runFeedWriter();

  observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
        }
      });
    },
    { threshold: 0.12 }
  );

  document
    .querySelectorAll('.reveal-section, .reveal-slide-left, .reveal-slide-right')
    .forEach((el) => observer?.observe(el));
});

onBeforeUnmount(() => {
  if (typeTimer) clearTimeout(typeTimer);
  if (feedTimer) clearTimeout(feedTimer);
  observer?.disconnect();
});

// ============================================================================
// 3. PRICING & MULTI-CURRENCY CONVERSION
// ============================================================================
const billingCycle = ref<'monthly' | 'annual'>('monthly');
const selectedCurrency = ref<'USD' | 'UGX' | 'KES' | 'RWF'>('USD');

const formatPlanPrice = (usdMonthlyPrice: number) => {
  const effectiveUsd = billingCycle.value === 'annual' ? Math.round(usdMonthlyPrice * 0.8) : usdMonthlyPrice;
  const converted = convertCurrencyAmount(effectiveUsd, 'USD', selectedCurrency.value);
  return formatCurrencyAmount(converted, selectedCurrency.value);
};

// ============================================================================
// 4. SLIDING CHANNELS MARQUEE DATA
// ============================================================================
const marqueeChannels = [
  { label: 'WhatsApp Click-to-Chat', icon: 'fa-brands fa-whatsapp', color: 'text-emerald-600 bg-emerald-500/10' },
  { label: 'Inbound Phone Calls', icon: 'fa-solid fa-phone', color: 'text-[#280047] bg-[#280047]/10' },
  { label: 'Website Lead Forms', icon: 'fa-solid fa-globe', color: 'text-[#F74D01] bg-[#F74D01]/10' },
  { label: 'Client Referrals', icon: 'fa-solid fa-handshake', color: 'text-[#280047] bg-[#280047]/10' },
  { label: 'Shareable Quotations', icon: 'fa-solid fa-file-invoice-dollar', color: 'text-[#F74D01] bg-[#F74D01]/10' },
  { label: 'Follow-up Reminders', icon: 'fa-solid fa-calendar-check', color: 'text-[#280047] bg-[#280047]/10' },
  { label: 'Lost Opportunity Recovery', icon: 'fa-solid fa-bolt', color: 'text-[#F74D01] bg-[#F74D01]/10' },
  { label: 'Multi-Currency Conversion', icon: 'fa-solid fa-coins', color: 'text-[#280047] bg-[#280047]/10' },
];

// ============================================================================
// 5. TESTIMONIALS
// ============================================================================
interface Testimonial {
  quote: string;
  author: string;
  role: string;
  company: string;
  icon: string;
  metric: string;
}

const testimonials: Testimonial[] = [
  {
    quote:
      "Before Leads, we tracked inquiries in notebooks and WhatsApp chats—and lost track of half our quotes. Now every client inquiry has an owner, every quote is tracked, and our close rate has doubled.",
    author: 'David K.',
    role: 'Managing Director',
    company: 'Crest Logistics',
    icon: 'fa-solid fa-truck-fast',
    metric: '2.4x higher quote acceptance',
  },
  {
    quote:
      "The Revenue Recovery page alone paid for our subscription in the first week. It flagged three high-value quotations we had forgotten to follow up on, and we closed two of them the same day.",
    author: 'Sarah N.',
    role: 'Head of Sales',
    company: 'Apex Solar Systems',
    icon: 'fa-solid fa-solar-panel',
    metric: 'UGX 48M recovered in 30 days',
  },
  {
    quote:
      "Our clients love receiving a clean, shareable quotation link on WhatsApp that they can review and approve with one click. No more messy PDFs or miscalculated VAT.",
    author: 'Jean-Paul M.',
    role: 'Operations Lead',
    company: 'Kigali Tech Studio',
    icon: 'fa-solid fa-laptop-code',
    metric: '60-second quote turnaround',
  },
  {
    quote:
      "Having multi-currency conversion built right into our pipeline lets us quote regional clients in USD, RWF, or UGX effortlessly while keeping our management reports accurate.",
    author: 'Grace A.',
    role: 'Commercial Director',
    company: 'PrimeMed Supplies',
    icon: 'fa-solid fa-briefcase-medical',
    metric: '100% pipeline visibility',
  },
  {
    quote:
      "I no longer have to micromanage my sales reps or ask for end-of-week spreadsheets. I can see every lead stage, scheduled follow-up, and team win rate in real time.",
    author: 'Samuel O.',
    role: 'Founder & CEO',
    company: 'BuildRight Contractors',
    icon: 'fa-solid fa-helmet-safety',
    metric: '3x faster lead response',
  },
];

const activeTestimonialIndex = ref(0);
const activeTestimonial = computed(() => testimonials[activeTestimonialIndex.value]);

const prevTestimonial = () => {
  activeTestimonialIndex.value =
    (activeTestimonialIndex.value - 1 + testimonials.length) % testimonials.length;
};

const nextTestimonial = () => {
  activeTestimonialIndex.value = (activeTestimonialIndex.value + 1) % testimonials.length;
};
</script>

<template>
  <Head title="Leads — Close More Deals Without Losing Leads" />

  <div class="min-h-screen landing-side-shade text-brand-dark font-sans selection:bg-brand-indigo selection:text-brand-yellow">
    <!-- =========================================================
         ARCHITECTURAL TOP NAVIGATION BAR (Matching Landing Grid Vibe)
         ========================================================= -->
    <header class="sticky top-0 z-50 landing-side-shade backdrop-blur-md border-b border-[#D5CCE2] px-2 sm:px-6 lg:px-10">
      <div class="max-w-6xl mx-auto border-x border-[#D5CCE2] bg-white/95">
        <div class="min-h-15 sm:h-16 flex items-stretch justify-between">
          <!-- Left Grid Cell: Brand Identity -->
          <div class="flex items-center px-3 sm:px-6 lg:px-8 border-r border-[#E5E0EC] shrink-0">
            <Link href="/" class="flex items-center gap-2 sm:gap-2.5 group">
              <div
                class="w-7 h-7 sm:w-8 sm:h-8 rounded-md bg-brand-indigo flex items-center justify-center text-white font-bold text-xs sm:text-sm shadow-xs group-hover:bg-brand-purple transition-colors shrink-0"
              >
                <span class="text-brand-orange mr-0.5">/</span>L
              </div>
              <div class="flex flex-col">
                <span class="text-xs sm:text-sm font-bold tracking-tight text-brand-indigo leading-none">Leads</span>
                <span class="text-[9px] font-mono-num text-brand-gray mt-0.5">LeadFlow CRM</span>
              </div>
            </Link>
          </div>

          <!-- Center Grid Cell: Segmented Pill Navigation -->
          <nav class="hidden lg:flex items-center justify-center flex-1 px-4">
            <div class="inline-flex items-center gap-1 p-1 rounded-md bg-[#FAFAFB] border border-[#E5E0EC]">
              <a
                v-for="item in [
                  { id: 'how-it-works', label: 'How It Works', icon: 'fa-solid fa-diagram-project' },
                  { id: 'foundations', label: 'Foundations', icon: 'fa-solid fa-layer-group' },
                  { id: 'features', label: 'Features', icon: 'fa-solid fa-bolt' },
                  { id: 'capabilities', label: 'Capabilities', icon: 'fa-solid fa-sliders' },
                  { id: 'pricing', label: 'Pricing', icon: 'fa-solid fa-tag' },
                ]"
                :key="item.id"
                :href="`#${item.id}`"
                @click="activeSection = item.id"
                class="px-3 py-1.5 rounded text-xs font-medium transition-all flex items-center gap-1.5"
                :class="
                  activeSection === item.id
                    ? 'bg-brand-indigo text-white font-semibold shadow-2xs'
                    : 'text-brand-purple hover:text-brand-indigo hover:bg-white'
                "
              >
                <i
                  :class="[
                    item.icon,
                    activeSection === item.id ? 'text-brand-orange' : 'text-brand-purple',
                  ]"
                  class="text-[10px]"
                ></i>
                <span>{{ item.label }}</span>
              </a>
            </div>
          </nav>

          <!-- Right Grid Cell: Workspace Action Buttons -->
          <div class="flex items-center gap-1.5 sm:gap-2.5 px-2.5 sm:px-6 lg:px-8 border-l border-[#E5E0EC] shrink-0">
            <template v-if="isLoggedIn">
              <Link
                href="/dashboard"
                class="inline-flex items-center gap-1.5 sm:gap-2.5 bg-brand-indigo text-white text-[11px] sm:text-xs font-semibold pl-2.5 sm:pl-3.5 pr-1.5 sm:pr-2 py-1.5 sm:py-2 rounded-md hover:bg-brand-purple transition-all"
              >
                <span>Dashboard</span>
                <span class="w-5 h-5 rounded bg-brand-orange text-white flex items-center justify-center shrink-0">
                  <i class="fa-solid fa-arrow-right text-[9px]"></i>
                </span>
              </Link>
            </template>
            <template v-else>
              <Link
                href="/login"
                class="hidden sm:inline-flex items-center gap-1.5 px-3 py-2 rounded-md text-xs font-semibold text-brand-indigo hover:bg-[#F3F1F6] transition-colors"
              >
                Sign In
              </Link>
              <Link
                href="/register"
                class="hidden min-[340px]:inline-flex items-center gap-1.5 sm:gap-2.5 bg-brand-indigo text-white text-[11px] sm:text-xs font-semibold pl-2.5 sm:pl-3.5 pr-1.5 sm:pr-2 py-1.5 sm:py-2 rounded-md hover:bg-brand-purple transition-all shadow-2xs"
              >
                <span>Get Started</span>
                <span class="w-5 h-5 rounded bg-brand-orange text-white flex items-center justify-center shrink-0">
                  <i class="fa-solid fa-arrow-right text-[9px]"></i>
                </span>
              </Link>
            </template>

            <!-- Mobile / Tablet Menu Button -->
            <button
              type="button"
              :aria-expanded="mobileNavOpen"
              aria-label="Toggle site navigation"
              @click="mobileNavOpen = !mobileNavOpen"
              class="lg:hidden w-8 h-8 sm:w-9 sm:h-9 rounded-md border border-[#E5E0EC] bg-[#FAFAFB] hover:bg-[#F3F1F6] flex items-center justify-center text-brand-indigo cursor-pointer"
            >
              <i :class="mobileNavOpen ? 'fa-solid fa-xmark' : 'fa-solid fa-bars'" class="text-xs"></i>
            </button>
          </div>
        </div>

        <!-- Mobile & Tablet Dropdown Drawer -->
        <div v-if="mobileNavOpen" class="lg:hidden border-t border-[#E5E0EC] bg-white px-4 sm:px-6 py-4 space-y-2">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5">
            <a
              v-for="item in [
                { id: 'how-it-works', label: 'How It Works', icon: 'fa-solid fa-diagram-project' },
                { id: 'foundations', label: 'Foundations', icon: 'fa-solid fa-layer-group' },
                { id: 'features', label: 'Features', icon: 'fa-solid fa-bolt' },
                { id: 'capabilities', label: 'Capabilities', icon: 'fa-solid fa-sliders' },
                { id: 'pricing', label: 'Pricing', icon: 'fa-solid fa-tag' },
              ]"
              :key="item.id"
              :href="`#${item.id}`"
              @click="activeSection = item.id; mobileNavOpen = false"
              class="flex items-center gap-2.5 px-3 py-2.5 rounded-md text-xs font-semibold transition-colors"
              :class="
                activeSection === item.id
                  ? 'bg-brand-indigo text-white'
                  : 'text-brand-dark hover:bg-[#F3F1F6]'
              "
            >
              <i
                :class="[item.icon, activeSection === item.id ? 'text-brand-orange' : 'text-brand-indigo']"
                class="w-4 text-center text-xs"
              ></i>
              <span>{{ item.label }}</span>
            </a>
          </div>
          <div v-if="!isLoggedIn" class="pt-3 border-t border-[#E5E0EC] grid grid-cols-1 min-[320px]:grid-cols-2 gap-2">
            <Link href="/login" class="btn-secondary w-full text-center">Sign In</Link>
            <Link href="/register" class="btn-primary w-full text-center">Start Free Workspace</Link>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Architectural Grid Container with Diagonal Hatched Side Shades -->
    <div class="px-2 sm:px-6 lg:px-10">
      <div class="max-w-6xl mx-auto border-x border-[#D5CCE2] bg-white">
      <!-- =========================================================
           1. HERO SECTION (Typewriter Headline + Animated Diagram)
           ========================================================= -->
      <section class="relative overflow-hidden border-b border-[#E5E0EC] px-3.5 sm:px-8 lg:px-12 pt-8 sm:pt-12 pb-12 sm:pb-16 lg:py-20">
        <!-- Subtle Architectural Background Grid Lines -->
        <div
          class="absolute inset-0 pointer-events-none opacity-35"
          style="background-image: radial-gradient(#C3B6D3 1px, transparent 1px); background-size: 24px 24px;"
        ></div>

        <div class="relative z-10 grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-center">
          <!-- Left Column: Copy & CTAs -->
          <div class="lg:col-span-6 space-y-5 sm:space-y-6">
            <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-md bg-[#F8F6FA] border border-[#E5E0EC]">
              <i class="fa-solid fa-bolt text-brand-orange text-xs shrink-0"></i>
              <span class="text-[11px] sm:text-xs font-semibold text-brand-indigo">Lead Management & Revenue Recovery</span>
            </div>

            <div class="space-y-1 min-h-19 sm:min-h-28">
              <h1 class="text-2xl min-[360px]:text-3xl sm:text-5xl font-bold tracking-tight text-brand-indigo leading-[1.15]">
                Close 3X more deals
                <span class="block text-brand-orange font-normal mt-1">
                  {{ typedHeroText }}<span class="typewriter-cursor"></span>
                </span>
              </h1>
            </div>

            <p class="text-xs sm:text-sm text-[#6B6578] max-w-lg leading-relaxed">
              Where every customer inquiry turns into tracked revenue. Capture leads from WhatsApp, phone calls, and your website, send clear quotations in seconds, and recover neglected deals automatically.
            </p>

            <!-- Action Buttons -->
            <div class="flex flex-wrap items-center gap-2.5 sm:gap-3 pt-1">
              <Link
                :href="isLoggedIn ? '/dashboard' : '/register'"
                class="inline-flex items-center justify-center gap-2.5 bg-brand-indigo text-white text-xs font-semibold pl-3.5 pr-2.5 py-2.5 rounded-md hover:bg-[#3a0066] transition-all shadow-sm w-full min-[360px]:w-auto"
              >
                <span>{{ isLoggedIn ? 'Go to Your Workspace' : 'Start Free Workspace' }}</span>
                <span class="w-6 h-6 rounded bg-brand-orange text-white flex items-center justify-center shrink-0">
                  <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </span>
              </Link>

              <a
                href="#pricing"
                class="btn-secondary py-2.5 px-4 text-xs w-full min-[360px]:w-auto"
              >
                View Plans & Pricing
              </a>
            </div>

            <!-- 3 Quick Checkpoints -->
            <ul class="space-y-2 pt-2 text-xs text-brand-dark">
              <li class="flex items-start sm:items-center gap-2.5">
                <span class="w-4 h-4 rounded bg-brand-indigo text-brand-yellow flex items-center justify-center text-[9px] shrink-0 mt-0.5 sm:mt-0">
                  <i class="fa-solid fa-check"></i>
                </span>
                <span>Capture & assign leads from WhatsApp, calls, and web</span>
              </li>
              <li class="flex items-start sm:items-center gap-2.5">
                <span class="w-4 h-4 rounded bg-brand-indigo text-brand-yellow flex items-center justify-center text-[9px] shrink-0 mt-0.5 sm:mt-0">
                  <i class="fa-solid fa-check"></i>
                </span>
                <span>Generate shareable client quotations with automatic VAT</span>
              </li>
              <li class="flex items-start sm:items-center gap-2.5">
                <span class="w-4 h-4 rounded bg-brand-indigo text-brand-yellow flex items-center justify-center text-[9px] shrink-0 mt-0.5 sm:mt-0">
                  <i class="fa-solid fa-check"></i>
                </span>
                <span>Spot stalled deals & overdue follow-ups before revenue is lost</span>
              </li>
            </ul>
          </div>

          <!-- Right Column: Animated Isometric Workflow Diagram -->
          <div class="lg:col-span-6 relative">
            <div class="relative mx-auto max-w-lg bg-[#FAFAFB] border border-[#E5E0EC] rounded-lg p-3.5 sm:p-6 shadow-xs">
              <!-- Live Self-Writing Status Bar -->
              <div class="mb-4 sm:mb-5 px-3 py-2 rounded-md bg-brand-indigo text-white flex items-center justify-between gap-2 text-[11px]">
                <div class="flex items-center gap-2 min-w-0">
                  <span class="w-2 h-2 rounded-full bg-brand-orange animate-ping shrink-0"></span>
                  <span class="font-mono-num truncate text-brand-yellow">{{ typedFeedText }}</span>
                </div>
                <span class="text-[9px] uppercase tracking-wider text-brand-purple shrink-0">Live</span>
              </div>

              <!-- Top Floating Source Node -->
              <div class="flex flex-wrap items-center justify-between gap-2 mb-4 sm:mb-5">
                <div class="inline-flex flex-wrap items-center gap-1.5 sm:gap-2 px-2.5 sm:px-3 py-1.5 rounded-md bg-white border border-[#E5E0EC] shadow-2xs text-xs animate-float">
                  <span class="w-5 h-5 rounded bg-emerald-500 text-white flex items-center justify-center shrink-0">
                    <i class="fa-brands fa-whatsapp text-xs"></i>
                  </span>
                  <span class="font-semibold text-brand-dark">WhatsApp Click-to-Chat</span>
                  <span class="text-[10px] font-mono-num text-emerald-700 font-semibold">Connected</span>
                </div>

                <!-- Floating Status Node -->
                <div class="w-8 h-8 rounded-full bg-white border border-[#E5E0EC] shadow-xs flex items-center justify-center text-brand-orange shrink-0">
                  <i class="fa-solid fa-bolt text-xs"></i>
                </div>
              </div>

              <!-- Central Hub Box (3D Layered Platform Node) -->
              <div class="relative bg-white border-2 border-brand-indigo rounded-lg p-3.5 sm:p-4 shadow-md mb-4 sm:mb-5 transition-transform duration-300 hover:-translate-y-0.5">
                <div class="flex flex-wrap items-start sm:items-center justify-between gap-2 pb-3 mb-3 border-b border-[#E5E0EC]">
                  <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-8 h-8 rounded-md bg-brand-indigo text-brand-yellow flex items-center justify-center font-bold text-xs shrink-0">
                      GN
                    </div>
                    <div class="min-w-0">
                      <p class="text-xs font-bold text-brand-dark wrap-break-word">Grace Nakato &bull; Crest Logistics</p>
                      <p class="text-[11px] text-[#6B6578] wrap-break-word">Annual Fleet Maintenance Contract</p>
                    </div>
                  </div>
                  <span class="px-2 py-0.5 rounded bg-brand-yellow text-brand-indigo border border-brand-purple text-[10px] font-bold uppercase shrink-0">
                    Qualified
                  </span>
                </div>

                <!-- Mini Pipeline Progress Steps -->
                <div class="grid grid-cols-4 gap-1.5 mb-3">
                  <div class="h-1.5 rounded-full bg-brand-indigo"></div>
                  <div class="h-1.5 rounded-full bg-brand-indigo"></div>
                  <div class="h-1.5 rounded-full bg-brand-orange animate-pulse"></div>
                  <div class="h-1.5 rounded-full bg-brand-purple"></div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-1.5 text-xs">
                  <span class="text-[#6B6578]">
                    <i class="fa-regular fa-clock text-brand-orange mr-1"></i>
                    Follow-up: <strong class="text-brand-dark">Tomorrow, 10:00 AM</strong>
                  </span>
                  <span class="font-mono-num font-bold text-brand-indigo">$3,200</span>
                </div>
              </div>

              <!-- Connected Quotation & Recovery Cards -->
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="bg-white border border-[#E5E0EC] rounded-md p-3.5 hover:border-brand-indigo transition-colors">
                  <div class="flex flex-wrap items-center justify-between gap-1 mb-1.5">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-[#6B6578]">Quotation Sent</span>
                    <span class="px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 text-[10px] font-bold">
                      Accepted
                    </span>
                  </div>
                  <p class="text-xs font-bold text-brand-indigo font-mono-num">#QUO-2026-0042</p>
                  <p class="text-[11px] text-[#6B6578] mt-1">Client approved via shareable link</p>
                </div>

                <div class="bg-white border border-brand-orange/40 rounded-md p-3.5 hover:border-brand-orange transition-colors">
                  <div class="flex flex-wrap items-center justify-between gap-1 mb-1.5">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-brand-orange">Recovery Alert</span>
                    <i class="fa-solid fa-bell text-brand-orange text-xs"></i>
                  </div>
                  <p class="text-xs font-bold text-brand-dark">2 Quotes Awaiting Reply</p>
                  <p class="text-[11px] text-[#6B6578] mt-1">Auto-flagged after 3 days of silence</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- =========================================================
           2. SLIDING MARQUEE STRIP & 3-COLUMN WORKFLOW ILLUSTRATIONS
           ========================================================= -->
      <section id="how-it-works" class="border-b border-[#E5E0EC]">
        <!-- Infinite Start-to-End Sliding Channel Ticker -->
        <div class="py-5 border-b border-[#E5E0EC] bg-[#FAFAFB] overflow-hidden">
          <p class="px-3.5 sm:px-8 lg:px-12 text-[11px] font-semibold uppercase tracking-wider text-[#6B6578] mb-3">
            Built to capture and convert leads from every business channel
          </p>
          <div class="relative overflow-hidden">
            <div class="animate-marquee gap-4 px-4">
              <div
                v-for="(ch, index) in [...marqueeChannels, ...marqueeChannels]"
                :key="index"
                class="flex items-center gap-2.5 px-4 py-2 rounded-md bg-white border border-[#E5E0EC] text-xs font-bold text-brand-dark shrink-0 shadow-2xs"
              >
                <span class="w-6 h-6 rounded flex items-center justify-center" :class="ch.color">
                  <i :class="ch.icon"></i>
                </span>
                <span>{{ ch.label }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- 3-Column Architectural Visual Cards -->
        <div class="grid grid-cols-1 lg:grid-cols-3 divide-y lg:divide-y-0 lg:divide-x divide-[#E5E0EC]">
          <!-- Column 1: Instant Lead Capture -->
          <div class="reveal-slide-left p-4 sm:p-8 flex flex-col justify-between space-y-5 sm:space-y-6">
            <div>
              <h3 class="text-sm font-bold text-brand-dark">Centralized Lead Capture</h3>
              <p class="text-xs text-[#6B6578] mt-1 leading-relaxed">
                Every inquiry from WhatsApp, phone calls, walk-ins, and referrals lands in one organized workspace with a clear owner.
              </p>
            </div>

            <!-- Illustration 1: Floating Source Tags Converging -->
            <div class="min-h-44 rounded-lg bg-[#FAFAFB] border border-[#E5E0EC] p-3.5 sm:p-4 flex flex-col justify-between gap-3 relative overflow-hidden">
              <div class="flex flex-wrap gap-1.5">
                <span class="px-2.5 py-1 rounded bg-white border border-[#E5E0EC] text-[11px] font-medium text-brand-dark shadow-2xs">
                  <i class="fa-brands fa-whatsapp text-emerald-600 mr-1"></i> WhatsApp Inquiry
                </span>
                <span class="px-2.5 py-1 rounded bg-white border border-[#E5E0EC] text-[11px] font-medium text-brand-dark shadow-2xs">
                  <i class="fa-solid fa-phone text-brand-indigo mr-1"></i> Inbound Call
                </span>
                <span class="px-2.5 py-1 rounded bg-white border border-[#E5E0EC] text-[11px] font-medium text-brand-dark shadow-2xs">
                  <i class="fa-solid fa-user-plus text-brand-orange mr-1"></i> Referral
                </span>
              </div>

              <div class="flex items-center justify-center my-1">
                <div class="w-9 h-9 rounded-md bg-brand-indigo text-brand-yellow flex items-center justify-center shadow-sm animate-float">
                  <i class="fa-solid fa-layer-group text-xs"></i>
                </div>
              </div>

              <div class="bg-white border border-[#E5E0EC] rounded px-3 py-2 flex flex-wrap items-center justify-between gap-1 text-[11px]">
                <span class="font-semibold text-brand-indigo">Assigned to Sales Rep</span>
                <span class="text-brand-orange font-bold">Instant</span>
              </div>
            </div>
          </div>

          <!-- Column 2: Structured Pipeline & Quotes -->
          <div class="reveal-section p-4 sm:p-8 flex flex-col justify-between space-y-5 sm:space-y-6">
            <div>
              <h3 class="text-sm font-bold text-brand-dark">Structured Pipeline & Quotes</h3>
              <p class="text-xs text-brand-purple mt-1 leading-relaxed">
                Move deals across clear stages, build itemized quotations with VAT, and share a link your client can accept online.
              </p>
            </div>

            <!-- Illustration 2: Connected Workflow Nodes -->
            <div class="min-h-44 rounded-lg bg-[#FAFAFB] border border-[#E5E0EC] p-3.5 sm:p-4 flex flex-col justify-between gap-3">
              <div class="grid grid-cols-1 min-[340px]:grid-cols-3 gap-2 text-center">
                <div class="p-2 rounded bg-white border border-[#E5E0EC]">
                  <p class="text-[10px] text-brand-purple">Stage 1</p>
                  <p class="text-[11px] font-bold text-brand-indigo">Qualified</p>
                </div>
                <div class="p-2 rounded bg-brand-indigo text-white">
                  <p class="text-[10px] text-brand-purple">Stage 2</p>
                  <p class="text-[11px] font-bold text-brand-yellow">Quote Sent</p>
                </div>
                <div class="p-2 rounded bg-white border border-[#E5E0EC]">
                  <p class="text-[10px] text-brand-purple">Stage 3</p>
                  <p class="text-[11px] font-bold text-emerald-700">Deal Won</p>
                </div>
              </div>

              <div class="bg-white border border-[#D5CCE2] rounded-md p-2.5 flex flex-wrap items-center justify-between gap-2 text-xs">
                <div>
                  <p class="font-bold text-brand-dark">Quote #QUO-2026-019</p>
                  <p class="text-[10px] text-brand-purple">18% VAT Calculated Automatically</p>
                </div>
                <span class="px-2 py-0.5 rounded bg-brand-orange text-white text-[10px] font-semibold">
                  Share Link
                </span>
              </div>
            </div>
          </div>

          <!-- Column 3: Revenue Recovery Radar -->
          <div class="reveal-slide-right p-4 sm:p-8 flex flex-col justify-between space-y-5 sm:space-y-6">
            <div>
              <h3 class="text-sm font-bold text-brand-dark">Automatic Revenue Recovery</h3>
              <p class="text-xs text-brand-purple mt-1 leading-relaxed">
                Our recovery engine scans your pipeline for neglected leads and unanswered quotes so you can follow up before they go cold.
              </p>
            </div>

            <!-- Illustration 3: Pinpoint Grid Illustration -->
            <div
              class="min-h-44 rounded-lg bg-[#FAFAFB] border border-[#E5E0EC] p-3.5 sm:p-4 flex flex-col justify-between gap-3 relative"
              style="background-image: linear-gradient(to right, #E5E0EC 1px, transparent 1px), linear-gradient(to bottom, #E5E0EC 1px, transparent 1px); background-size: 28px 28px;"
            >
              <div class="flex flex-wrap items-center justify-between gap-1.5">
                <span class="px-2 py-0.5 rounded bg-brand-orange text-white text-[10px] font-bold">
                  <i class="fa-solid fa-triangle-exclamation mr-1"></i> 4 Days Silent
                </span>
                <span class="px-2 py-0.5 rounded bg-white border border-[#E5E0EC] text-[10px] font-mono-num font-bold text-brand-indigo">
                  $4,800 at risk
                </span>
              </div>

              <div class="my-auto flex items-center justify-around py-2">
                <div class="w-7 h-7 rounded-md bg-brand-indigo text-brand-yellow flex items-center justify-center text-xs shadow animate-float">
                  <i class="fa-solid fa-bell"></i>
                </div>
                <div class="w-7 h-7 rounded-md bg-brand-orange text-white flex items-center justify-center text-xs shadow">
                  <i class="fa-solid fa-phone-volume"></i>
                </div>
              </div>

              <div class="bg-white border border-[#E5E0EC] rounded px-3 py-1.5 text-[11px] font-medium text-brand-dark flex items-center justify-between gap-1">
                <span>Action: Call client today</span>
                <i class="fa-solid fa-arrow-right text-brand-orange text-[10px] shrink-0"></i>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- =========================================================
           3. "BUILT ON STRONG FOUNDATIONS" (2x3 Bento Grid)
           ========================================================= -->
      <section id="foundations" class="reveal-section px-3.5 sm:px-8 lg:px-12 py-10 sm:py-16 border-b border-[#E5E0EC]">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
          <!-- Cell 1: Section Title Block -->
          <div class="p-2 flex flex-col justify-between">
            <div>
              <h2 class="text-xl sm:text-2xl font-bold text-brand-indigo tracking-tight leading-snug">
                Built on strong foundations
              </h2>
              <p class="text-xs text-brand-purple mt-3 leading-relaxed">
                Designed for clarity and speed. Every tool in Leads works together so your sales team spends less time filling forms and more time closing business.
              </p>
            </div>
            <div class="pt-5 sm:pt-6">
              <Link
                :href="isLoggedIn ? '/dashboard' : '/register'"
                class="inline-flex items-center gap-1.5 text-xs font-bold text-brand-orange hover:text-brand-indigo transition-colors"
              >
                <span>Explore the workspace</span>
                <i class="fa-solid fa-arrow-right text-[10px]"></i>
              </Link>
            </div>
          </div>

          <!-- Cell 2 -->
          <div class="bg-[#FAFAFB] border border-[#E5E0EC] rounded-lg p-5 sm:p-6 flex flex-col justify-between min-h-43.75 hover:border-brand-indigo transition-all hover:-translate-y-0.5">
            <div class="w-9 h-9 rounded-md bg-white border border-[#E5E0EC] text-brand-indigo flex items-center justify-center">
              <i class="fa-solid fa-list-check text-sm"></i>
            </div>
            <div class="pt-6 sm:pt-8">
              <h3 class="text-sm font-bold text-brand-dark">Step-by-Step Forms</h3>
              <p class="text-xs text-brand-purple mt-1 leading-relaxed">
                No long, overwhelming pages. Capture leads and create quotations in 3 guided steps with a live summary card.
              </p>
            </div>
          </div>

          <!-- Cell 3 -->
          <div class="bg-[#FAFAFB] border border-[#E5E0EC] rounded-lg p-5 sm:p-6 flex flex-col justify-between min-h-43.75 hover:border-brand-indigo transition-all hover:-translate-y-0.5">
            <div class="w-9 h-9 rounded-md bg-white border border-[#E5E0EC] text-brand-orange flex items-center justify-center">
              <i class="fa-solid fa-coins text-sm"></i>
            </div>
            <div class="pt-6 sm:pt-8">
              <h3 class="text-sm font-bold text-brand-dark">Auto Currency Conversion</h3>
              <p class="text-xs text-brand-purple mt-1 leading-relaxed">
                Switch your workspace between UGX, KES, TZS, RWF, USD, EUR, or GBP and watch all deal values convert automatically.
              </p>
            </div>
          </div>

          <!-- Cell 4 -->
          <div class="bg-[#FAFAFB] border border-[#E5E0EC] rounded-lg p-5 sm:p-6 flex flex-col justify-between min-h-43.75 hover:border-brand-indigo transition-all hover:-translate-y-0.5">
            <div class="w-9 h-9 rounded-md bg-white border border-[#E5E0EC] text-brand-indigo flex items-center justify-center">
              <i class="fa-solid fa-calendar-check text-sm"></i>
            </div>
            <div class="pt-6 sm:pt-8">
              <h3 class="text-sm font-bold text-brand-dark">Follow-up Accountability</h3>
              <p class="text-xs text-brand-purple mt-1 leading-relaxed">
                Schedule calls and WhatsApp check-ins with custom date and time pickers, and log every conversation outcome.
              </p>
            </div>
          </div>

          <!-- Cell 5 -->
          <div class="bg-[#FAFAFB] border border-[#E5E0EC] rounded-lg p-5 sm:p-6 flex flex-col justify-between min-h-43.75 hover:border-brand-indigo transition-all hover:-translate-y-0.5">
            <div class="w-9 h-9 rounded-md bg-white border border-[#E5E0EC] text-brand-orange flex items-center justify-center">
              <i class="fa-solid fa-bolt text-sm"></i>
            </div>
            <div class="pt-6 sm:pt-8">
              <h3 class="text-sm font-bold text-brand-dark">Proactive Deal Recovery</h3>
              <p class="text-xs text-brand-purple mt-1 leading-relaxed">
                Automatically detect high-value deals that have gone quiet and give your team clear next steps to win them back.
              </p>
            </div>
          </div>

          <!-- Cell 6 -->
          <div class="bg-[#FAFAFB] border border-[#E5E0EC] rounded-lg p-5 sm:p-6 flex flex-col justify-between min-h-43.75 hover:border-brand-indigo transition-all hover:-translate-y-0.5">
            <div class="w-9 h-9 rounded-md bg-white border border-[#E5E0EC] text-brand-indigo flex items-center justify-center">
              <i class="fa-solid fa-users-gear text-sm"></i>
            </div>
            <div class="pt-6 sm:pt-8">
              <h3 class="text-sm font-bold text-brand-dark">Multi-Tenant Team Control</h3>
              <p class="text-xs text-brand-purple mt-1 leading-relaxed">
                Invite owners, managers, and sales reps with role-based permissions and track individual conversion performance.
              </p>
            </div>
          </div>
        </div>
      </section>

      <!-- =========================================================
           4. "POWERFUL FEATURES FOR EVERYDAY SALES WORK" (3 UI Cards)
           ========================================================= -->
      <section id="features" class="reveal-section px-3.5 sm:px-8 lg:px-12 py-10 sm:py-16 border-b border-[#E5E0EC]">
        <div class="max-w-xl mb-8 sm:mb-10">
          <h2 class="text-xl sm:text-3xl font-bold text-brand-indigo tracking-tight">
            Powerful features
            <span class="block text-brand-purple font-normal text-lg sm:text-2xl mt-0.5">
              built for everyday sales work
            </span>
          </h2>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 sm:gap-6">
          <!-- Feature Card 1: Visual Pipeline & Stage Control -->
          <div class="border border-[#E5E0EC] rounded-lg bg-[#FAFAFB] p-4 sm:p-5 flex flex-col justify-between space-y-5 hover:border-brand-indigo transition-all">
            <div class="bg-white border border-[#E5E0EC] rounded-md p-3.5 space-y-2.5 shadow-2xs">
              <div class="flex flex-wrap items-center justify-between gap-1 text-xs">
                <span class="font-semibold text-brand-dark">
                  <i class="fa-solid fa-circle-check text-brand-indigo mr-1.5"></i> Qualification Complete
                </span>
                <span class="text-[10px] font-mono-num text-brand-purple">10:15 AM</span>
              </div>
              <div class="flex flex-wrap items-center justify-between gap-1 text-xs">
                <span class="font-semibold text-brand-dark">
                  <i class="fa-solid fa-file-invoice text-brand-orange mr-1.5"></i> Quotation #QUO-042 Sent
                </span>
                <span class="text-[10px] font-mono-num text-brand-purple">10:18 AM</span>
              </div>
              <div class="pt-2 border-t border-[#E5E0EC] flex flex-wrap items-center justify-between gap-1.5">
                <span class="text-[11px] text-brand-purple">Move Lead Stage</span>
                <span class="px-2.5 py-1 rounded bg-brand-indigo text-white text-[10px] font-semibold">
                  Negotiation <i class="fa-solid fa-chevron-down ml-1 text-[8px]"></i>
                </span>
              </div>
            </div>

            <div>
              <h3 class="text-sm font-bold text-brand-dark">Clear Pipeline Progression</h3>
              <p class="text-xs text-brand-purple mt-1 leading-relaxed">
                See exactly where every prospect stands—from New Inquiry to Won—and move deals forward with a single click.
              </p>
            </div>
          </div>

          <!-- Feature Card 2: Revenue Recovery Action Card -->
          <div class="border border-[#E5E0EC] rounded-lg bg-[#FAFAFB] p-4 sm:p-5 flex flex-col justify-between space-y-5 hover:border-brand-orange transition-all">
            <div>
              <h3 class="text-sm font-bold text-brand-dark">Proactive Revenue Recovery</h3>
              <p class="text-xs text-brand-purple mt-1 leading-relaxed">
                Never let a warm lead or sent quotation slip through the cracks. Get daily alerts on deals that need immediate attention.
              </p>
            </div>

            <div class="bg-white border border-[#E5E0EC] rounded-md p-3.5 sm:p-4 space-y-3 shadow-2xs">
              <div class="flex flex-wrap items-center justify-between gap-1.5">
                <div class="flex items-center gap-2">
                  <span class="w-6 h-6 rounded-full bg-brand-orange/10 text-brand-orange flex items-center justify-center text-xs shrink-0">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                  </span>
                  <span class="text-xs font-bold text-brand-dark">High-Value Quote Stalled</span>
                </div>
                <span class="px-2 py-0.5 rounded bg-brand-orange/10 text-brand-orange text-[10px] font-bold">
                  Critical
                </span>
              </div>

              <div>
                <p class="text-[11px] text-brand-purple">Revenue at Risk</p>
                <p class="text-base sm:text-lg font-bold text-brand-orange font-mono-num">UGX 18,500,000</p>
              </div>

              <div class="w-full py-2 px-2 rounded bg-brand-orange text-white text-xs font-semibold text-center">
                Schedule Follow-up Now <i class="fa-solid fa-arrow-right ml-1 text-[10px]"></i>
              </div>
            </div>
          </div>

          <!-- Feature Card 3: Instant Client Quotations -->
          <div class="border border-[#E5E0EC] rounded-lg bg-[#FAFAFB] p-4 sm:p-5 flex flex-col justify-between space-y-5 hover:border-brand-indigo transition-all">
            <div>
              <h3 class="text-sm font-bold text-brand-dark">Shareable Client Quotations</h3>
              <p class="text-xs text-brand-purple mt-1 leading-relaxed">
                Send a clean web link to your client on WhatsApp or email. They can review line items and accept your quote instantly.
              </p>
            </div>

            <div class="bg-white border border-[#E5E0EC] rounded-md p-3.5 sm:p-4 space-y-2.5 shadow-2xs">
              <div class="flex flex-wrap items-center justify-between gap-1.5 pb-2 border-b border-[#E5E0EC]">
                <span class="text-xs font-bold text-brand-indigo font-mono-num">#QUO-2026-0089</span>
                <span class="px-2 py-0.5 rounded bg-brand-yellow text-brand-indigo text-[10px] font-bold">
                  18% VAT Included
                </span>
              </div>
              <div class="flex flex-wrap justify-between gap-1 text-xs">
                <span class="text-brand-purple">Solar Inverter Installation</span>
                <span class="font-mono-num font-semibold text-brand-dark">$4,200</span>
              </div>
              <div class="pt-2 flex flex-wrap items-center justify-between gap-1.5">
                <span class="text-[10px] text-emerald-700 font-semibold">
                  <i class="fa-solid fa-link mr-1"></i> Client Link Active
                </span>
                <span class="px-2.5 py-1 rounded bg-brand-indigo text-white text-[10px] font-semibold">
                  Accept Quote
                </span>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- =========================================================
           5. EDITORIAL TESTIMONIAL + INTERACTIVE COMPANY TABS
           ========================================================= -->
      <section class="reveal-section px-3.5 sm:px-8 lg:px-12 py-10 sm:py-16 border-b border-[#E5E0EC]">
        <div class="max-w-3xl min-h-24">
          <p class="text-lg sm:text-2xl font-medium text-brand-dark leading-relaxed transition-all duration-300">
            "{{ activeTestimonial.quote }}"
          </p>
        </div>

        <div class="mt-6 sm:mt-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-md bg-brand-indigo text-brand-yellow flex items-center justify-center font-bold text-sm shrink-0">
              {{ activeTestimonial.author.charAt(0) }}
            </div>
            <div>
              <p class="text-xs font-bold text-brand-dark">{{ activeTestimonial.author }}</p>
              <p class="text-[11px] text-brand-purple">
                {{ activeTestimonial.role }}, {{ activeTestimonial.company }} &bull;
                <span class="text-brand-orange font-semibold">{{ activeTestimonial.metric }}</span>
              </p>
            </div>
          </div>

          <div class="flex items-center gap-2">
            <button
              type="button"
              aria-label="Previous testimonial"
              @click="prevTestimonial"
              class="w-9 h-9 rounded-md bg-brand-indigo text-white hover:bg-brand-purple flex items-center justify-center cursor-pointer transition-colors"
            >
              <i class="fa-solid fa-arrow-left text-xs"></i>
            </button>
            <button
              type="button"
              aria-label="Next testimonial"
              @click="nextTestimonial"
              class="w-9 h-9 rounded-md bg-brand-indigo text-white hover:bg-brand-purple flex items-center justify-center cursor-pointer transition-colors"
            >
              <i class="fa-solid fa-arrow-right text-xs"></i>
            </button>
          </div>
        </div>

        <!-- Interactive Company Selector Bar -->
        <div class="mt-6 sm:mt-8 grid grid-cols-1 min-[380px]:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 border border-[#E5E0EC] rounded-lg overflow-hidden divide-y min-[380px]:divide-y-0 divide-[#E5E0EC]">
          <button
            v-for="(item, idx) in testimonials"
            :key="item.company"
            type="button"
            @click="activeTestimonialIndex = idx"
            class="px-3.5 py-3.5 text-xs font-semibold flex items-center justify-center gap-2 transition-all cursor-pointer border-b lg:border-b-0 border-r last:border-r-0 border-[#E5E0EC]"
            :class="
              activeTestimonialIndex === idx
                ? 'bg-brand-indigo text-white'
                : 'bg-white text-brand-purple hover:bg-[#FAFAFB] hover:text-brand-dark'
            "
          >
            <i :class="[item.icon, activeTestimonialIndex === idx ? 'text-brand-orange' : 'text-brand-purple']" class="shrink-0"></i>
            <span class="text-center leading-tight">{{ item.company }}</span>
          </button>
        </div>
      </section>

      <!-- =========================================================
           6. "ADVANCED CAPABILITIES. SIMPLE EXPERIENCE." (3 + 2 Bento)
           ========================================================= -->
      <section id="capabilities" class="reveal-section px-3.5 sm:px-8 lg:px-12 py-10 sm:py-16 border-b border-[#E5E0EC] bg-[#FAFAFB]">
        <div class="mb-8 sm:mb-10">
          <div class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-orange mb-1">
            <i class="fa-solid fa-wand-magic-sparkles"></i> Built for Growth
          </div>
          <h2 class="text-xl sm:text-3xl font-bold text-brand-indigo tracking-tight">
            Advanced Capabilities.
            <span class="block text-brand-dark">Simple Experience.</span>
          </h2>
        </div>

        <!-- Top 3 Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-5 mb-4 sm:mb-5">
          <div class="app-card p-5 sm:p-6 flex flex-col justify-between space-y-5 sm:space-y-6">
            <div>
              <h3 class="text-sm font-bold text-brand-dark">Custom Threshold Rules</h3>
              <p class="text-xs text-brand-purple mt-1 leading-relaxed">
                Define how many days a lead or quotation can sit idle before it is flagged for recovery.
              </p>
            </div>
            <div class="p-3.5 rounded-md bg-[#FAFAFB] border border-[#E5E0EC] flex items-center gap-3">
              <div class="w-8 h-8 rounded bg-brand-indigo text-brand-yellow flex items-center justify-center shrink-0">
                <i class="fa-solid fa-sliders text-xs"></i>
              </div>
              <div class="flex-1 text-xs min-w-0">
                <p class="font-bold text-brand-dark">Stale Lead Threshold</p>
                <p class="text-[11px] text-brand-purple">Flag after 3 days without contact</p>
              </div>
            </div>
          </div>

          <div class="app-card p-5 sm:p-6 flex flex-col justify-between space-y-5 sm:space-y-6">
            <div>
              <h3 class="text-sm font-bold text-brand-dark">One-Click CSV Export</h3>
              <p class="text-xs text-brand-purple mt-1 leading-relaxed">
                Export your filtered leads, deal values, stages, and owners to CSV anytime for board or accounting reports.
              </p>
            </div>
            <div class="p-3.5 rounded-md bg-[#FAFAFB] border border-[#E5E0EC] flex items-center justify-center gap-2">
              <span class="px-3 py-1.5 rounded bg-white border border-[#D5CCE2] text-xs font-semibold text-brand-indigo shadow-2xs text-center">
                <i class="fa-solid fa-download text-brand-orange mr-1.5"></i> Export Leads CSV
              </span>
            </div>
          </div>

          <div class="app-card p-5 sm:p-6 flex flex-col justify-between space-y-5 sm:space-y-6">
            <div>
              <h3 class="text-sm font-bold text-brand-dark">Channel ROI Analytics</h3>
              <p class="text-xs text-brand-purple mt-1 leading-relaxed">
                See which lead sources—WhatsApp, calls, referrals, or website—bring in the highest closed-won revenue.
              </p>
            </div>
            <div class="p-3.5 rounded-md bg-[#FAFAFB] border border-[#E5E0EC] flex flex-wrap items-center justify-between gap-1.5 text-xs">
              <span class="font-medium text-brand-dark">
                <i class="fa-solid fa-chart-line text-brand-indigo mr-1.5"></i> Top Source: Referrals
              </span>
              <span class="font-mono-num font-bold text-emerald-700">58% Win Rate</span>
            </div>
          </div>
        </div>

        <!-- Bottom 2 Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-5">
          <div class="app-card p-5 sm:p-6 flex flex-col justify-between space-y-5 sm:space-y-6">
            <div>
              <h3 class="text-sm font-bold text-brand-dark">Complete Team & Activity Audit Trail</h3>
              <p class="text-xs text-brand-purple mt-1 leading-relaxed">
                Every stage update, note, follow-up completion, and quotation status change is logged with a timestamp and team member name.
              </p>
            </div>
            <div class="p-3.5 sm:p-4 rounded-md bg-[#FAFAFB] border border-[#E5E0EC] space-y-2 text-xs">
              <div class="flex flex-wrap items-center justify-between gap-1">
                <span class="font-semibold text-brand-indigo">
                  <i class="fa-solid fa-user-check text-brand-orange mr-1.5"></i> Diane M. completed follow-up call
                </span>
                <span class="text-[10px] font-mono-num text-brand-purple">2m ago</span>
              </div>
              <p class="text-[11px] text-[#6B6578] sm:pl-5">
                "Client approved the revised maintenance scope and requested final invoice."
              </p>
            </div>
          </div>

          <div class="app-card p-5 sm:p-6 flex flex-col justify-between space-y-5 sm:space-y-6">
            <div>
              <h3 class="text-sm font-bold text-brand-dark">Multi-Company Workspace Switching</h3>
              <p class="text-xs text-brand-purple mt-1 leading-relaxed">
                Run multiple businesses or branches? Switch between isolated company workspaces from the sidebar with a single click.
              </p>
            </div>
            <div class="p-3.5 sm:p-4 rounded-md bg-[#FAFAFB] border border-[#E5E0EC] flex flex-wrap items-center justify-between gap-2 text-xs">
              <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-8 h-8 rounded bg-brand-indigo text-brand-yellow flex items-center justify-center font-bold shrink-0">
                  C
                </div>
                <div class="min-w-0">
                  <p class="font-bold text-brand-dark wrap-break-word">Crest Logistics Ltd</p>
                  <p class="text-[10px] text-brand-purple wrap-break-word">Isolated Tenant Workspace &bull; UGX / USD</p>
                </div>
              </div>
              <span class="px-2 py-1 rounded bg-brand-yellow text-brand-indigo border border-brand-purple text-[10px] font-bold shrink-0">
                Active
              </span>
            </div>
          </div>
        </div>
      </section>

      <!-- =========================================================
           7. PRICING & SUBSCRIPTION PLANS (3 Public Plans + Custom Enterprise)
           ========================================================= -->
      <section id="pricing" class="reveal-section px-3.5 sm:px-8 lg:px-12 py-10 sm:py-16 border-b border-[#E5E0EC]">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-8 sm:mb-10">
          <div>
            <span class="text-[11px] font-bold uppercase tracking-wider text-brand-orange">Subscription Packages</span>
            <h2 class="text-xl sm:text-3xl font-bold text-brand-indigo tracking-tight mt-0.5">
              Smarter plans for
              <span class="block text-[#6B6578] font-normal text-lg sm:text-2xl mt-0.5">
                every stage of your sales growth
              </span>
            </h2>
          </div>

          <!-- Currency & Billing Cycle Controls -->
          <div class="flex flex-wrap items-center gap-2.5 sm:gap-3">
            <!-- Currency Selector Pills -->
            <div class="inline-flex flex-wrap bg-[#FAFAFB] p-1 rounded-md border border-[#E5E0EC]">
              <button
                v-for="curr in (['USD', 'UGX', 'KES', 'RWF'] as const)"
                :key="curr"
                type="button"
                @click="selectedCurrency = curr"
                class="px-2.5 py-1 rounded text-xs font-mono-num font-semibold transition-colors cursor-pointer"
                :class="
                  selectedCurrency === curr
                    ? 'bg-brand-indigo text-brand-yellow'
                    : 'text-brand-purple hover:text-brand-dark'
                "
              >
                {{ curr }}
              </button>
            </div>

            <!-- Monthly / Annual Toggle -->
            <div class="inline-flex flex-wrap bg-[#FAFAFB] p-1 rounded-md border border-[#E5E0EC]">
              <button
                type="button"
                @click="billingCycle = 'monthly'"
                class="px-3 py-1 rounded text-xs font-semibold transition-colors cursor-pointer"
                :class="billingCycle === 'monthly' ? 'bg-brand-orange text-white' : 'text-brand-purple'"
              >
                Monthly
              </button>
              <button
                type="button"
                @click="billingCycle = 'annual'"
                class="px-3 py-1 rounded text-xs font-semibold transition-colors cursor-pointer"
                :class="billingCycle === 'annual' ? 'bg-brand-orange text-white' : 'text-brand-purple'"
              >
                Annual (-20%)
              </button>
            </div>
          </div>
        </div>

        <!-- 3 Public Launch Plans (Starter $19, Professional $49, Agency $99) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 sm:gap-6">
          <!-- A. Starter Plan ($19/mo) -->
          <div class="bg-[#FAFAFB] border border-[#E5E0EC] rounded-lg p-5 sm:p-6 flex flex-col justify-between hover:border-brand-purple transition-all">
            <div>
              <div class="flex flex-wrap items-center justify-between gap-1.5 mb-2">
                <div class="flex items-center gap-2">
                  <div class="w-7 h-7 rounded bg-white border border-[#E5E0EC] text-brand-indigo flex items-center justify-center text-xs shrink-0">
                    <i class="fa-solid fa-seedling"></i>
                  </div>
                  <h3 class="text-base font-bold text-brand-dark">Starter</h3>
                </div>
                <span class="text-[11px] font-mono-num font-semibold text-[#6B6578]">1 User &bull; 100 Leads</span>
              </div>
              <p class="text-xs text-[#6B6578] min-h-8.5">
                For individuals, freelancers, and startups organizing customer inquiries and follow-ups.
              </p>

              <div class="my-5 p-3.5 rounded-md bg-white border border-[#E5E0EC] flex flex-wrap items-baseline gap-1.5">
                <span class="text-xl sm:text-2xl font-bold text-brand-indigo font-mono-num">
                  {{ formatPlanPrice(19) }}
                </span>
                <span class="text-xs text-[#6B6578]">/ month</span>
              </div>

              <ul class="space-y-2.5 text-xs text-brand-dark">
                <li class="flex items-start gap-2">
                  <i class="fa-solid fa-check text-brand-indigo text-[10px] mt-1 shrink-0"></i>
                  <span><strong>1 user account</strong> & up to <strong>100 active leads</strong></span>
                </li>
                <li class="flex items-start gap-2">
                  <i class="fa-solid fa-check text-brand-indigo text-[10px] mt-1 shrink-0"></i>
                  <span>Centralized lead profiles & contact info</span>
                </li>
                <li class="flex items-start gap-2">
                  <i class="fa-solid fa-check text-brand-indigo text-[10px] mt-1 shrink-0"></i>
                  <span>Standard sales pipeline & status tracking</span>
                </li>
                <li class="flex items-start gap-2">
                  <i class="fa-solid fa-check text-brand-indigo text-[10px] mt-1 shrink-0"></i>
                  <span>Follow-up scheduling and reminders</span>
                </li>
                <li class="flex items-start gap-2">
                  <i class="fa-solid fa-check text-brand-indigo text-[10px] mt-1 shrink-0"></i>
                  <span>Basic quotation tracking & WhatsApp click-to-chat</span>
                </li>
                <li class="flex items-start gap-2">
                  <i class="fa-solid fa-check text-brand-indigo text-[10px] mt-1 shrink-0"></i>
                  <span>Basic sales dashboard & activity history</span>
                </li>
              </ul>
            </div>

            <div class="pt-6 mt-6 border-t border-[#E5E0EC]">
              <Link :href="isLoggedIn ? '/dashboard' : '/register'" class="btn-secondary w-full py-2.5 text-xs">
                Get Started with Starter
              </Link>
            </div>
          </div>

          <!-- B. Professional Plan ($49/mo) -->
          <div class="bg-[#FAFAFB] border-2 border-brand-indigo rounded-lg p-5 sm:p-6 flex flex-col justify-between relative shadow-sm">
            <span class="absolute -top-3 right-4 sm:right-5 px-2.5 py-0.5 rounded bg-brand-indigo text-brand-yellow text-[10px] font-bold uppercase tracking-wider">
              Most Popular
            </span>

            <div>
              <div class="flex flex-wrap items-center justify-between gap-1.5 mb-2">
                <div class="flex items-center gap-2">
                  <div class="w-7 h-7 rounded bg-brand-indigo text-brand-yellow flex items-center justify-center text-xs shrink-0">
                    <i class="fa-solid fa-briefcase"></i>
                  </div>
                  <h3 class="text-base font-bold text-brand-dark">Professional</h3>
                </div>
                <span class="text-[11px] font-mono-num font-semibold text-brand-indigo">5 Users &bull; 1,000 Leads</span>
              </div>
              <p class="text-xs text-[#6B6578] min-h-8.5">
                For growing SMEs coordinating sales teams, opportunities, and conversion performance.
              </p>

              <div class="my-5 p-3.5 rounded-md bg-white border border-[#E5E0EC] flex flex-wrap items-baseline gap-1.5">
                <span class="text-xl sm:text-2xl font-bold text-brand-orange font-mono-num">
                  {{ formatPlanPrice(49) }}
                </span>
                <span class="text-xs text-[#6B6578]">/ month</span>
              </div>

              <ul class="space-y-2.5 text-xs text-brand-dark">
                <li class="flex items-start gap-2">
                  <i class="fa-solid fa-check text-brand-orange text-[10px] mt-1 shrink-0"></i>
                  <span>Everything in Starter, plus:</span>
                </li>
                <li class="flex items-start gap-2">
                  <i class="fa-solid fa-check text-brand-orange text-[10px] mt-1 shrink-0"></i>
                  <span><strong>Up to 5 users</strong> & <strong>1,000 active leads</strong></span>
                </li>
                <li class="flex items-start gap-2">
                  <i class="fa-solid fa-check text-brand-orange text-[10px] mt-1 shrink-0"></i>
                  <span>Lead assignment to team members</span>
                </li>
                <li class="flex items-start gap-2">
                  <i class="fa-solid fa-check text-brand-orange text-[10px] mt-1 shrink-0"></i>
                  <span>Customizable sales pipeline & advanced follow-ups</span>
                </li>
                <li class="flex items-start gap-2">
                  <i class="fa-solid fa-check text-brand-orange text-[10px] mt-1 shrink-0"></i>
                  <span>Quotation status tracking & source analytics</span>
                </li>
                <li class="flex items-start gap-2">
                  <i class="fa-solid fa-check text-brand-orange text-[10px] mt-1 shrink-0"></i>
                  <span>Sales conversion reports & team activity overview</span>
                </li>
              </ul>
            </div>

            <div class="pt-6 mt-6 border-t border-[#E5E0EC]">
              <Link :href="isLoggedIn ? '/dashboard' : '/register'" class="btn-primary w-full py-2.5 text-xs">
                Start Professional Plan
              </Link>
            </div>
          </div>

          <!-- C. Agency Plan ($99/mo) -->
          <div class="bg-[#FAFAFB] border border-[#E5E0EC] rounded-lg p-5 sm:p-6 flex flex-col justify-between hover:border-brand-purple transition-all">
            <div>
              <div class="flex flex-wrap items-center justify-between gap-1.5 mb-2">
                <div class="flex items-center gap-2">
                  <div class="w-7 h-7 rounded bg-white border border-[#E5E0EC] text-brand-orange flex items-center justify-center text-xs shrink-0">
                    <i class="fa-solid fa-building"></i>
                  </div>
                  <h3 class="text-base font-bold text-brand-dark">Agency</h3>
                </div>
                <span class="text-[11px] font-mono-num font-semibold text-[#6B6578]">15 Users &bull; 5,000 Leads</span>
              </div>
              <p class="text-xs text-[#6B6578] min-h-8.5">
                For agencies & high-volume service companies handling many quotations and sales reps.
              </p>

              <div class="my-5 p-3.5 rounded-md bg-white border border-[#E5E0EC] flex flex-wrap items-baseline gap-1.5">
                <span class="text-xl sm:text-2xl font-bold text-brand-indigo font-mono-num">
                  {{ formatPlanPrice(99) }}
                </span>
                <span class="text-xs text-[#6B6578]">/ month</span>
              </div>

              <ul class="space-y-2.5 text-xs text-brand-dark">
                <li class="flex items-start gap-2">
                  <i class="fa-solid fa-check text-brand-indigo text-[10px] mt-1 shrink-0"></i>
                  <span>Everything in Professional, plus:</span>
                </li>
                <li class="flex items-start gap-2">
                  <i class="fa-solid fa-check text-brand-indigo text-[10px] mt-1 shrink-0"></i>
                  <span><strong>Up to 15 users</strong> & <strong>5,000 active leads</strong></span>
                </li>
                <li class="flex items-start gap-2">
                  <i class="fa-solid fa-check text-brand-indigo text-[10px] mt-1 shrink-0"></i>
                  <span>Advanced team performance & pipeline reports</span>
                </li>
                <li class="flex items-start gap-2">
                  <i class="fa-solid fa-check text-brand-indigo text-[10px] mt-1 shrink-0"></i>
                  <span>Lead ownership, reassignment & custom fields</span>
                </li>
                <li class="flex items-start gap-2">
                  <i class="fa-solid fa-check text-brand-indigo text-[10px] mt-1 shrink-0"></i>
                  <span>Custom quotation templates & lost deal analysis</span>
                </li>
                <li class="flex items-start gap-2">
                  <i class="fa-solid fa-check text-brand-indigo text-[10px] mt-1 shrink-0"></i>
                  <span>Advanced activity tracking & priority support</span>
                </li>
              </ul>
            </div>

            <div class="pt-6 mt-6 border-t border-[#E5E0EC]">
              <Link :href="isLoggedIn ? '/dashboard' : '/register'" class="btn-indigo w-full py-2.5 text-xs">
                Start Agency Plan
              </Link>
            </div>
          </div>
        </div>

        <!-- D. Business — $199/month (Custom / Enterprise Consultation Callout) -->
        <div class="mt-6 p-4 sm:p-6 rounded-lg bg-[#F8F6FA] border border-[#D5CCE2] flex flex-col lg:flex-row lg:items-center justify-between gap-4 sm:gap-6">
          <div class="space-y-1.5 max-w-2xl">
            <div class="flex flex-wrap items-center gap-2">
              <span class="px-2 py-0.5 rounded bg-brand-indigo text-brand-yellow text-[10px] font-bold uppercase tracking-wider">
                Custom / Enterprise
              </span>
              <h3 class="text-sm font-bold text-brand-dark">
                Business Plan — Starting at {{ formatPlanPrice(199) }} / month (Upon Consultation)
              </h3>
            </div>
            <p class="text-xs text-[#6B6578] leading-relaxed">
              Designed for established organizations with larger sales teams (up to 40 users & 20,000 active leads), multiple departments, advanced role-based permissions, audit logs, configurable workflows, and dedicated onboarding.
            </p>
          </div>

          <div class="shrink-0">
            <Link :href="isLoggedIn ? '/settings' : '/register'" class="btn-secondary text-xs py-2.5 px-4 w-full sm:w-auto">
              <i class="fa-regular fa-comments text-brand-indigo"></i> Request Enterprise Consultation
            </Link>
          </div>
        </div>
      </section>

      <!-- =========================================================
           8. DARK INDIGO FRAMED CTA CARD + SLIDING GIANT WORDMARK
           ========================================================= -->
      <section class="bg-brand-indigo p-3.5 sm:p-8 lg:p-12 overflow-hidden">
        <!-- Inner White CTA Card with Connected Nodes -->
        <div class="bg-white rounded-lg p-5 sm:p-10 lg:p-12 text-center relative overflow-hidden">
          <div
            class="absolute inset-0 pointer-events-none opacity-30"
            style="background-image: radial-gradient(#C3B6D3 1px, transparent 1px); background-size: 20px 20px;"
          ></div>

          <div class="relative z-10 max-w-xl mx-auto space-y-4 sm:space-y-5">
            <h2 class="text-xl sm:text-4xl font-bold text-brand-indigo tracking-tight leading-tight">
              Ready to close more deals with
              <span class="text-brand-orange">complete clarity?</span>
            </h2>
            <p class="text-xs sm:text-sm text-[#6B6578] leading-relaxed">
              Set up your company workspace in under 2 minutes. Capture every lead, send fast quotations, and never let revenue slip away again.
            </p>

            <div class="pt-2 flex flex-wrap items-center justify-center gap-2.5 sm:gap-3">
              <Link
                :href="isLoggedIn ? '/dashboard' : '/register'"
                class="inline-flex items-center justify-center gap-2.5 bg-brand-indigo text-white text-xs font-semibold pl-4 pr-2.5 py-2.5 rounded-md hover:bg-brand-purple transition-colors w-full min-[360px]:w-auto"
              >
                <span>{{ isLoggedIn ? 'Open Dashboard' : 'Get Started Now' }}</span>
                <span class="w-6 h-6 rounded bg-brand-orange text-white flex items-center justify-center shrink-0">
                  <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </span>
              </Link>
              <Link
                v-if="!isLoggedIn"
                href="/login"
                class="btn-secondary py-2.5 px-4 text-xs w-full min-[360px]:w-auto"
              >
                Try Demo Account
              </Link>
            </div>

            <!-- Decorative Isometric Mini Diagram at Bottom of Card -->
            <div class="pt-6 sm:pt-8 flex items-center justify-center gap-3 sm:gap-8">
              <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-brand-light border border-brand-border text-brand-orange flex items-center justify-center shadow-2xs animate-float shrink-0">
                <i class="fa-brands fa-whatsapp text-sm"></i>
              </div>
              <div class="h-px w-8 sm:w-20 bg-brand-purple"></div>
              <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-lg bg-brand-indigo text-brand-yellow flex items-center justify-center shadow-md font-bold text-sm shrink-0">
                <i class="fa-solid fa-layer-group"></i>
              </div>
              <div class="h-px w-8 sm:w-20 bg-brand-purple"></div>
              <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-[#FAFAFB] border border-[#E5E0EC] text-emerald-600 flex items-center justify-center shadow-2xs animate-float shrink-0">
                <i class="fa-solid fa-check text-sm"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- Giant Sliding Brand Wordmark Banner -->
        <div class="pt-6 sm:pt-8 pb-2 overflow-hidden select-none">
          <div class="animate-marquee-fast gap-8 items-center">
            <span
              v-for="n in 6"
              :key="n"
              class="text-2xl sm:text-6xl lg:text-7xl font-bold tracking-tight text-white/90 whitespace-nowrap"
            >
              Leads . Platform <span class="text-brand-orange mx-4">&bull;</span>
            </span>
          </div>
        </div>
      </section>

      <!-- =========================================================
           9. CLEAN MINIMAL FOOTER
           ========================================================= -->
      <footer class="px-3.5 sm:px-8 lg:px-12 py-8 sm:py-12 bg-white border-t border-[#E5E0EC]">
        <div class="grid grid-cols-1 min-[340px]:grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-6 sm:gap-8 pb-8 sm:pb-10 border-b border-[#E5E0EC] text-xs">
          <div>
            <p class="font-bold text-brand-dark mb-3">Platform</p>
            <ul class="space-y-2 text-[#6B6578]">
              <li><a href="#how-it-works" class="hover:text-brand-indigo">Lead Capture</a></li>
              <li><a href="#features" class="hover:text-brand-indigo">Sales Pipeline</a></li>
              <li><a href="#features" class="hover:text-brand-indigo">Quotations</a></li>
              <li><a href="#features" class="hover:text-brand-indigo">Revenue Recovery</a></li>
            </ul>
          </div>

          <div>
            <p class="font-bold text-brand-dark mb-3">Capabilities</p>
            <ul class="space-y-2 text-[#6B6578]">
              <li><a href="#foundations" class="hover:text-brand-indigo">Multi-Currency</a></li>
              <li><a href="#foundations" class="hover:text-brand-indigo">Follow-up Schedule</a></li>
              <li><a href="#capabilities" class="hover:text-brand-indigo">Team Analytics</a></li>
              <li><a href="#capabilities" class="hover:text-brand-indigo">CSV Exports</a></li>
            </ul>
          </div>

          <div>
            <p class="font-bold text-brand-dark mb-3">Workspace</p>
            <ul class="space-y-2 text-[#6B6578]">
              <li><Link href="/login" class="hover:text-brand-indigo">Sign In</Link></li>
              <li><Link href="/register" class="hover:text-brand-indigo">Create Account</Link></li>
              <li><a href="#pricing" class="hover:text-brand-indigo">Pricing Plans</a></li>
            </ul>
          </div>

          <div>
            <p class="font-bold text-brand-dark mb-3">Currencies</p>
            <ul class="space-y-2 text-[#6B6578] font-mono-num">
              <li>UGX &bull; KES &bull; TZS</li>
              <li>RWF &bull; USD</li>
              <li>EUR &bull; GBP</li>
            </ul>
          </div>

          <div class="min-[340px]:col-span-2 lg:col-span-2">
            <p class="font-bold text-brand-dark mb-2">Get Sales & Product Updates</p>
            <p class="text-[11px] text-[#6B6578] mb-3">
              Practical tips on closing more B2B deals and recovering stalled quotations.
            </p>
            <form @submit.prevent class="flex flex-col min-[340px]:flex-row items-stretch min-[340px]:items-center gap-2">
              <input
                type="email"
                placeholder="Enter your work email"
                class="app-input text-xs flex-1"
              />
              <button
                type="submit"
                aria-label="Subscribe"
                class="h-9 px-3 rounded-md bg-brand-indigo text-white hover:bg-brand-orange flex items-center justify-center shrink-0 transition-colors cursor-pointer"
              >
                <i class="fa-solid fa-arrow-right text-xs"></i>
              </button>
            </form>
          </div>
        </div>

        <div class="pt-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-[11px] text-[#6B6578] text-center sm:text-left">
          <p>&copy; {{ new Date().getFullYear() }} Leads Platform. All rights reserved.</p>
          <div class="flex flex-wrap items-center justify-center gap-4 sm:gap-6">
            <span>Terms of Service</span>
            <span>Privacy Policy</span>
            <span>Security</span>
          </div>
        </div>
      </footer>
      </div>
    </div>
  </div>
</template>
