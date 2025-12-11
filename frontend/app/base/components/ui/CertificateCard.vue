<template>
  <div 
    class="bg-neutral-0 border border-neutral-50 rounded-lg p-6 shadow-md cursor-pointer hover:shadow-lg transition-shadow duration-200"
    @click="$emit('click')"
  >
    <div class="flex items-center space-x-4">
      <!-- Certificate Icon -->
      <div class="flex-shrink-0">
        <svg class="w-12 h-12 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
        </svg>
      </div>

      <!-- Certificate Info -->
      <div class="flex-1 min-w-0">
        <h4 class="text-h4 text-neutral-900 mb-1">{{ courseName }}</h4>
        <p class="text-body-sm text-neutral-600">Issued {{ dateIssued }}</p>
      </div>

      <!-- View indicator -->
      <div class="flex-shrink-0">
        <svg class="w-5 h-5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
        </svg>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
interface Props {
  courseName: string
  dateIssued: string
  certificateId?: string
  pdfUrl?: string
}

const props = defineProps<Props>()

defineEmits<{
  click: []
}>()
</script>
