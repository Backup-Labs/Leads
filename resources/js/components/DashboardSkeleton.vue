<template>
  <div class="animate-pulse space-y-6">
    <!-- Top Action & Title Skeleton -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2">
      <div class="space-y-2">
        <div class="h-6 w-40 bg-gray-200 rounded-md"></div>
        <div class="h-3.5 w-64 bg-gray-100 rounded-md"></div>
      </div>
      <div class="flex items-center gap-2">
        <div class="h-8 w-32 bg-gray-200 rounded-md"></div>
        <div class="h-8 w-24 bg-gray-200 rounded-md"></div>
      </div>
    </div>

    <!-- 4 Metrics KPI Skeleton Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div v-for="i in 4" :key="i" class="app-card p-4 space-y-3 bg-white">
        <div class="flex items-center justify-between">
          <div class="h-3 w-24 bg-gray-200 rounded"></div>
          <div class="w-5 h-5 bg-gray-100 rounded-full"></div>
        </div>
        <div class="h-7 w-32 bg-gray-200 rounded"></div>
        <div class="pt-2 border-t border-gray-100 flex items-center justify-between">
          <div class="h-3 w-16 bg-gray-100 rounded"></div>
          <div class="h-3 w-12 bg-gray-100 rounded"></div>
        </div>
      </div>
    </div>

    <!-- Main Grid Section (Pipeline & Table Skeleton) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2 app-card p-5 bg-white space-y-4">
        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
          <div class="h-4 w-44 bg-gray-200 rounded"></div>
          <div class="h-7 w-20 bg-gray-100 rounded"></div>
        </div>
        <div v-for="i in 4" :key="i" class="space-y-2">
          <div class="flex justify-between">
            <div class="h-3 w-28 bg-gray-200 rounded"></div>
            <div class="h-3 w-16 bg-gray-100 rounded"></div>
          </div>
          <div class="h-2 w-full bg-gray-100 rounded-full"></div>
        </div>
      </div>

      <div class="app-card p-5 bg-white space-y-4">
        <div class="h-4 w-36 bg-gray-200 rounded border-b border-gray-100 pb-3"></div>
        <div v-for="i in 3" :key="i" class="p-3 rounded-lg border border-gray-100 space-y-2">
          <div class="h-3 w-28 bg-gray-200 rounded"></div>
          <div class="h-2.5 w-40 bg-gray-100 rounded"></div>
        </div>
      </div>
    </div>
  </div>
</template>

