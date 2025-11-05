<template>
  <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
    <div class="text-sm text-gray-600 mb-2">{{ title }}</div>
    <div class="flex items-center justify-between">
      <div class="text-3xl font-bold text-gray-900">
        <span v-if="loading" class="animate-pulse text-gray-300">--</span>
        <span v-else>{{ value }}</span>
      </div>
      <div class="text-gray-400">
        <slot name="icon"></slot>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
interface Props {
  title: string
  value: number | string
  loading?: boolean
}

defineProps<Props>()
</script>
