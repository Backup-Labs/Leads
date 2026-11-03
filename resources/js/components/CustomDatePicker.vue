<script setup lang="ts">
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';

const props = withDefaults(
  defineProps<{
    modelValue: string;
    mode?: 'date' | 'datetime';
    placeholder?: string;
  }>(),
  {
    mode: 'date',
    placeholder: 'Select date',
  }
);

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void;
}>();

const isOpen = ref(false);
const containerRef = ref<HTMLElement | null>(null);

const parseInputDate = (val: string): Date => {
  if (!val) return new Date();
  const parsed = new Date(val);
  return isNaN(parsed.getTime()) ? new Date() : parsed;
};

const initialDate = parseInputDate(props.modelValue);
const viewYear = ref(initialDate.getFullYear());
const viewMonth = ref(initialDate.getMonth());
const selectedHour = ref(initialDate.getHours() || 10);
const selectedMinute = ref(initialDate.getMinutes() - (initialDate.getMinutes() % 15));

watch(
  () => props.modelValue,
  (newVal) => {
    if (newVal) {
      const d = parseInputDate(newVal);
      viewYear.value = d.getFullYear();
      viewMonth.value = d.getMonth();
      selectedHour.value = d.getHours();
      selectedMinute.value = d.getMinutes();
    }
  }
);

const monthNames = [
  'January',
  'February',
  'March',
  'April',
  'May',
  'June',
  'July',
  'August',
  'September',
  'October',
  'November',
  'December',
];

const weekDays = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'];

const pad = (n: number) => String(n).padStart(2, '0');

const formatOutput = (year: number, month: number, day: number, hour: number, minute: number) => {
  const datePart = `${year}-${pad(month + 1)}-${pad(day)}`;
  if (props.mode === 'datetime') {
    return `${datePart}T${pad(hour)}:${pad(minute)}`;
  }
  return datePart;
};

const displayLabel = computed(() => {
  if (!props.modelValue) return props.placeholder;
  const d = parseInputDate(props.modelValue);
  const dateStr = d.toLocaleDateString('en-US', {
    month: 'short',
    day: '2-digit',
    year: 'numeric',
  });
  if (props.mode === 'datetime') {
    const timeStr = d.toLocaleTimeString('en-US', {
      hour: '2-digit',
      minute: '2-digit',
    });
    return `${dateStr} • ${timeStr}`;
  }
  return dateStr;
});

const calendarDays = computed(() => {
  const firstDayOfMonth = new Date(viewYear.value, viewMonth.value, 1).getDay();
  const daysInMonth = new Date(viewYear.value, viewMonth.value + 1, 0).getDate();
  const daysInPrevMonth = new Date(viewYear.value, viewMonth.value, 0).getDate();

  const cells: { day: number; month: number; year: number; isCurrentMonth: boolean }[] = [];

  // Previous month trailing days
  for (let i = firstDayOfMonth - 1; i >= 0; i--) {
    const pm = viewMonth.value === 0 ? 11 : viewMonth.value - 1;
    const py = viewMonth.value === 0 ? viewYear.value - 1 : viewYear.value;
    cells.push({ day: daysInPrevMonth - i, month: pm, year: py, isCurrentMonth: false });
  }

  // Current month days
  for (let d = 1; d <= daysInMonth; d++) {
    cells.push({ day: d, month: viewMonth.value, year: viewYear.value, isCurrentMonth: true });
  }

  // Next month leading days to complete 42 cells (6 rows)
  const remaining = 42 - cells.length;
  for (let n = 1; n <= remaining; n++) {
    const nm = viewMonth.value === 11 ? 0 : viewMonth.value + 1;
    const ny = viewMonth.value === 11 ? viewYear.value + 1 : viewYear.value;
    cells.push({ day: n, month: nm, year: ny, isCurrentMonth: false });
  }

  return cells;
});

const isSelectedCell = (cell: { day: number; month: number; year: number }) => {
  if (!props.modelValue) return false;
  const d = parseInputDate(props.modelValue);
  return (
    d.getFullYear() === cell.year &&
    d.getMonth() === cell.month &&
    d.getDate() === cell.day
  );
};

const isTodayCell = (cell: { day: number; month: number; year: number }) => {
  const now = new Date();
  return (
    now.getFullYear() === cell.year &&
    now.getMonth() === cell.month &&
    now.getDate() === cell.day
  );
};

const selectDateCell = (cell: { day: number; month: number; year: number }) => {
  viewYear.value = cell.year;
  viewMonth.value = cell.month;
  const formatted = formatOutput(cell.year, cell.month, cell.day, selectedHour.value, selectedMinute.value);
  emit('update:modelValue', formatted);
  if (props.mode === 'date') {
    isOpen.value = false;
  }
};

const applyQuickOffset = (daysOffset: number) => {
  const target = new Date();
  target.setDate(target.getDate() + daysOffset);
  viewYear.value = target.getFullYear();
  viewMonth.value = target.getMonth();
  const formatted = formatOutput(
    target.getFullYear(),
    target.getMonth(),
    target.getDate(),
    selectedHour.value,
    selectedMinute.value
  );
  emit('update:modelValue', formatted);
  if (props.mode === 'date') {
    isOpen.value = false;
  }
};

const updateTime = (hour: number, minute: number) => {
  selectedHour.value = (hour + 24) % 24;
  selectedMinute.value = (minute + 60) % 60;
  const d = parseInputDate(props.modelValue);
  const formatted = formatOutput(
    d.getFullYear(),
    d.getMonth(),
    d.getDate(),
    selectedHour.value,
    selectedMinute.value
  );
  emit('update:modelValue', formatted);
};

const prevMonth = () => {
  if (viewMonth.value === 0) {
    viewMonth.value = 11;
    viewYear.value--;
  } else {
    viewMonth.value--;
  }
};

const nextMonth = () => {
  if (viewMonth.value === 11) {
    viewMonth.value = 0;
    viewYear.value++;
  } else {
    viewMonth.value++;
  }
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
      :aria-expanded="isOpen"
      @click="isOpen = !isOpen"
      class="w-full min-h-[38px] px-3 py-2 bg-white border rounded-md flex items-center justify-between gap-2 text-xs transition-all cursor-pointer"
      :class="
        isOpen
          ? 'border-[#280047] ring-2 ring-[#280047]/10'
          : 'border-[#D5CCE2] hover:border-[#280047]/60'
      "
    >
      <div class="flex items-center gap-2 min-w-0">
        <i class="fa-regular fa-calendar text-[#F74D01] text-xs shrink-0"></i>
        <span class="font-mono-num font-medium text-[#242424] truncate">{{ displayLabel }}</span>
      </div>
      <i
        class="fa-solid fa-chevron-down text-[10px] text-[#6B6578] transition-transform"
        :class="{ 'rotate-180 text-[#280047]': isOpen }"
      ></i>
    </button>

    <!-- Custom Calendar Popover -->
    <div
      v-if="isOpen"
      class="absolute left-0 top-full mt-1.5 w-full sm:w-72 max-w-[min(18rem,calc(100vw-1.5rem))] bg-white border border-[#D5CCE2] rounded-lg shadow-xl z-50 p-3 sm:p-3.5 space-y-3"
    >
      <!-- Quick Presets -->
      <div class="grid grid-cols-2 min-[320px]:grid-cols-4 gap-1 pb-2.5 border-b border-[#E5E0EC]">
        <button
          v-for="preset in [
            { label: 'Today', days: 0 },
            { label: 'Tomorrow', days: 1 },
            { label: '+7 Days', days: 7 },
            { label: '+14 Days', days: 14 },
          ]"
          :key="preset.label"
          type="button"
          @click="applyQuickOffset(preset.days)"
          class="px-1.5 py-1 rounded text-[10px] font-semibold bg-[#F3F1F6] text-[#280047] hover:bg-[#280047] hover:text-white transition-colors cursor-pointer text-center"
        >
          {{ preset.label }}
        </button>
      </div>

      <!-- Month / Year Header -->
      <div class="flex items-center justify-between">
        <button
          type="button"
          @click="prevMonth"
          class="w-7 h-7 rounded-md border border-[#E5E0EC] hover:bg-[#F3F1F6] text-[#280047] flex items-center justify-center cursor-pointer"
        >
          <i class="fa-solid fa-chevron-left text-[10px]"></i>
        </button>
        <div class="text-xs font-bold text-[#280047]">
          {{ monthNames[viewMonth] }} <span class="font-mono-num">{{ viewYear }}</span>
        </div>
        <button
          type="button"
          @click="nextMonth"
          class="w-7 h-7 rounded-md border border-[#E5E0EC] hover:bg-[#F3F1F6] text-[#280047] flex items-center justify-center cursor-pointer"
        >
          <i class="fa-solid fa-chevron-right text-[10px]"></i>
        </button>
      </div>

      <!-- Weekday Headers -->
      <div class="grid grid-cols-7 gap-1 text-center">
        <span
          v-for="wd in weekDays"
          :key="wd"
          class="text-[10px] font-bold uppercase text-[#6B6578] py-1"
        >
          {{ wd }}
        </span>
      </div>

      <!-- Calendar Days Grid -->
      <div class="grid grid-cols-7 gap-1">
        <button
          v-for="(cell, idx) in calendarDays"
          :key="idx"
          type="button"
          @click="selectDateCell(cell)"
          class="h-8 rounded-md text-xs font-mono-num flex items-center justify-center transition-colors cursor-pointer relative"
          :class="[
            isSelectedCell(cell)
              ? 'bg-[#280047] text-[#FDFEBA] font-bold'
              : cell.isCurrentMonth
                ? 'text-[#242424] hover:bg-[#F3F1F6] font-medium'
                : 'text-[#C3B6D3] hover:bg-[#FAFAFB]',
            isTodayCell(cell) && !isSelectedCell(cell) ? 'border border-[#F74D01] text-[#F74D01] font-bold' : '',
          ]"
        >
          {{ cell.day }}
        </button>
      </div>

      <!-- Custom Time Selector (for datetime mode) -->
      <div v-if="mode === 'datetime'" class="pt-3 border-t border-[#E5E0EC] space-y-2.5">
        <div class="flex flex-wrap items-center justify-between gap-1.5">
          <span class="text-[11px] font-semibold text-[#280047] flex items-center gap-1.5">
            <i class="fa-regular fa-clock text-[#F74D01]"></i> Time
          </span>

          <div class="flex items-center gap-1.5">
            <!-- Hour Stepper -->
            <div class="flex items-center bg-[#F3F1F6] rounded border border-[#D5CCE2]">
              <button
                type="button"
                @click="updateTime(selectedHour - 1, selectedMinute)"
                class="px-1.5 py-0.5 text-[#280047] hover:bg-[#E5E0EC] text-[10px] font-bold cursor-pointer"
              >
                -
              </button>
              <span class="px-1.5 text-xs font-mono-num font-bold text-[#280047]">
                {{ pad(selectedHour) }}
              </span>
              <button
                type="button"
                @click="updateTime(selectedHour + 1, selectedMinute)"
                class="px-1.5 py-0.5 text-[#280047] hover:bg-[#E5E0EC] text-[10px] font-bold cursor-pointer"
              >
                +
              </button>
            </div>

            <span class="font-bold text-xs text-[#280047]">:</span>

            <!-- Minute Stepper -->
            <div class="flex items-center bg-[#F3F1F6] rounded border border-[#D5CCE2]">
              <button
                type="button"
                @click="updateTime(selectedHour, selectedMinute - 15)"
                class="px-1.5 py-0.5 text-[#280047] hover:bg-[#E5E0EC] text-[10px] font-bold cursor-pointer"
              >
                -
              </button>
              <span class="px-1.5 text-xs font-mono-num font-bold text-[#280047]">
                {{ pad(selectedMinute) }}
              </span>
              <button
                type="button"
                @click="updateTime(selectedHour, selectedMinute + 15)"
                class="px-1.5 py-0.5 text-[#280047] hover:bg-[#E5E0EC] text-[10px] font-bold cursor-pointer"
              >
                +
              </button>
            </div>
          </div>
        </div>

        <!-- Quick Time Presets -->
        <div class="grid grid-cols-2 min-[320px]:grid-cols-4 gap-1">
          <button
            v-for="slot in [
              { label: '09:00', h: 9, m: 0 },
              { label: '11:00', h: 11, m: 0 },
              { label: '14:00', h: 14, m: 0 },
              { label: '16:00', h: 16, m: 0 },
            ]"
            :key="slot.label"
            type="button"
            @click="updateTime(slot.h, slot.m)"
            class="py-1 rounded text-[10px] font-mono-num font-semibold border transition-colors cursor-pointer"
            :class="
              selectedHour === slot.h && selectedMinute === slot.m
                ? 'bg-[#F74D01] text-white border-[#F74D01]'
                : 'bg-white text-[#242424] border-[#E5E0EC] hover:bg-[#F3F1F6]'
            "
          >
            {{ slot.label }}
          </button>
        </div>

        <button
          type="button"
          @click="isOpen = false"
          class="w-full py-1.5 rounded-md bg-[#280047] text-white text-xs font-semibold hover:bg-[#3a0066] transition-colors cursor-pointer"
        >
          Done
        </button>
      </div>
    </div>
  </div>
</template>

