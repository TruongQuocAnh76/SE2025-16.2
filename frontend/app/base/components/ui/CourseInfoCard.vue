<template>
  <div class="bg-white p-4 rounded-2xl shadow-lg">
    <div class="flex items-center justify-between mb-2">
      <h3 class="text-lg font-semibold text-gray-800">{{ title }}</h3>
      <i :class="icon" class="text-2xl text-gray-600"></i>
    </div>
    <p class="text-2xl font-bold text-gray-900">{{ data }}</p>
  </div>
</template>

<script setup lang="ts">
interface Props {
  title: string
  icon: string
  data: string | number
}

const props = defineProps<Props>()
</script>
