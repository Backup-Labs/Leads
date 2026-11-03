<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';

export interface SelectOption {
  value: string | number;
  label: string;
  sublabel?: string;
  icon?: string;
  badge?: string;
}

const props = withDefaults(
  defineProps<{
    modelValue: string | number | null | undefined;
    options: SelectOption[];
    placeholder?: string;
    icon?: string;
    searchable?: boolean;
    size?: 'sm' | 'md';
    disabled?: boolean;
    align?: 'left' | 'right';
  }>(),
  {
    placeholder: 'Select an option',
    searchable: false,
    size: 'md',
    disabled: false,
    align: 'left',
  }
);

const emit = defineEmits<{
  (e: 'update:modelValue', value: any): void;
  (e: 'change', value: any): void;
}>();

const isOpen = ref(false);
const searchQuery = ref('');
const containerRef = ref<HTMLElement | null>(null);

const selectedOption = computed(() => {
  return props.options.find((opt) => String(opt.value) === String(props.modelValue ?? ''));
});

const showSearch = computed(() => props.searchable || props.options.length > 6);

const filteredOptions = computed(() => {
  if (!searchQuery.value.trim()) return props.options;
  const q = searchQuery.value.toLowerCase();
  return props.options.filter(
    (opt) =>
      opt.label.toLowerCase().includes(q) ||
      (opt.sublabel && opt.sublabel.toLowerCase().includes(q)) ||
      (opt.badge && opt.badge.toLowerCase().includes(q))
  );
});

const toggleDropdown = () => {
  if (props.disabled) return;
  isOpen.value = !isOpen.value;
  if (isOpen.value) {
    searchQuery.value = '';
  }
};

const selectOption = (opt: SelectOption) => {
  emit('update:modelValue', opt.value);
  emit('change', opt.value);
  isOpen.value = false;
};

const handleClickOutside = (event: MouseEvent) => {
  if (containerRef.value && !containerRef.value.contains(event.target as Node)) {
    isOpen.value = false;
  }
};

const handleKeydown = (event: KeyboardEvent) => {
  if (event.key === 'Escape') {
    isOpen.value = false;
  }
};

onMounted(() => {
  document.addEventListener('mousedown', handleClickOutside);
  document.addEventListener('keydown', handleKeydown);
});

onBeforeUnmount(() => {
  document.removeEventListener('mousedown', handleClickOutside);
  document.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
  <div ref="containerRef" class="relative w-full select-none">
    <!-- Trigger Button -->
    <button
      type="button"
      :disabled="disabled"
      :aria-expanded="isOpen"
      aria-haspopup="listbox"
      @click="toggleDropdown"
      class="w-full flex items-center justify-between gap-2 bg-white border rounded-md text-left transition-all cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
      :class="[
        size === 'sm' ? 'px-2.5 py-1.5 text-xs min-h-[34px]' : 'px-3 py-2 text-xs min-h-[38px]',
        isOpen
          ? 'border-[#280047] ring-2 ring-[#280047]/10'
          : 'border-[#D5CCE2] hover:border-[#280047]/60',
      ]"
    >
      <div class="flex items-center gap-2 min-w-0 flex-1">
        <i
          v-if="selectedOption?.icon || icon"
          :class="selectedOption?.icon || icon"
          class="text-[#280047] text-xs w-4 text-center shrink-0"
        ></i>
        <span
          class="truncate"
          :class="selectedOption ? 'text-[#242424] font-medium' : 'text-[#6B6578]'"
        >
          {{ selectedOption ? selectedOption.label : placeholder }}
        </span>
      </div>

      <div class="flex items-center gap-1.5 shrink-0">
        <span
          v-if="selectedOption?.badge"
          class="px-1.5 py-0.5 rounded bg-[#FDFEBA] text-[#280047] font-mono-num text-[10px] font-bold border border-[#C3B6D3]"
        >
          {{ selectedOption.badge }}
        </span>
        <i
          class="fa-solid fa-chevron-down text-[10px] text-[#6B6578] transition-transform duration-150"
          :class="{ 'rotate-180 text-[#280047]': isOpen }"
        ></i>
      </div>
    </button>

    <!-- Dropdown Panel -->
    <div
      v-if="isOpen"
      role="listbox"
      class="absolute top-full mt-1 min-w-full w-full sm:w-max max-w-[min(20rem,calc(100vw-1.5rem))] bg-white border border-[#D5CCE2] rounded-md shadow-lg z-50 overflow-hidden"
      :class="align === 'right' ? 'right-0' : 'left-0'"
    >
      <!-- Optional Search Input -->
      <div v-if="showSearch" class="p-2 border-b border-[#E5E0EC] bg-[#FAFAFB]">
        <div class="relative">
          <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-1/2 -translate-y-1/2 text-[#6B6578] text-[10px]"></i>
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Filter options..."
            class="w-full pl-7 pr-2.5 py-1.5 text-xs bg-white border border-[#D5CCE2] rounded focus:outline-none focus:border-[#280047]"
          />
        </div>
      </div>

      <!-- Options List -->
      <div class="max-h-56 overflow-y-auto py-1 divide-y divide-[#F3F1F6]">
        <button
          v-for="opt in filteredOptions"
          :key="String(opt.value)"
          type="button"
          @click="selectOption(opt)"
          class="w-full px-3 py-2 text-left text-xs flex items-center justify-between gap-3 transition-colors cursor-pointer"
          :class="
            String(opt.value) === String(modelValue ?? '')
              ? 'bg-[#280047] text-white font-semibold'
              : 'text-[#242424] hover:bg-[#F3F1F6]'
          "
        >
          <div class="flex items-center gap-2 min-w-0">
            <i
              v-if="opt.icon"
              :class="[
                opt.icon,
                String(opt.value) === String(modelValue ?? '') ? 'text-[#FDFEBA]' : 'text-[#280047]',
              ]"
              class="w-4 text-center text-xs shrink-0"
            ></i>
            <div class="min-w-0">
              <p class="truncate">{{ opt.label }}</p>
              <p
                v-if="opt.sublabel"
                class="text-[10px] truncate"
                :class="
                  String(opt.value) === String(modelValue ?? '') ? 'text-[#C3B6D3]' : 'text-[#6B6578]'
                "
              >
                {{ opt.sublabel }}
              </p>
            </div>
          </div>

          <div class="flex items-center gap-1.5 shrink-0">
            <span
              v-if="opt.badge"
              class="px-1.5 py-0.5 rounded text-[10px] font-mono-num font-semibold"
              :class="
                String(opt.value) === String(modelValue ?? '')
                  ? 'bg-white/15 text-[#FDFEBA]'
                  : 'bg-[#F3F1F6] text-[#280047]'
              "
            >
              {{ opt.badge }}
            </span>
            <i
              v-if="String(opt.value) === String(modelValue ?? '')"
              class="fa-solid fa-check text-[#F74D01] text-[10px]"
            ></i>
          </div>
        </button>

        <div v-if="filteredOptions.length === 0" class="px-3 py-4 text-center text-xs text-[#6B6578]">
          No matching options
        </div>
      </div>
    </div>
  </div>
</template>

