<template>
  <div class="bg-neutral-0 border border-neutral-50 rounded-lg p-6 shadow-sm hover:shadow-md transition-shadow duration-200">
    <div class="flex items-center space-x-4">
      <!-- Thumbnail -->
      <img :src="thumbnail" :alt="name" class="w-16 h-16 object-cover rounded-lg flex-shrink-0" />

      <!-- Course Info -->
      <div class="flex-1 min-w-0">
        <h4 class="text-h4 text-neutral-900 mb-1 truncate">{{ name }}</h4>
        <p class="text-body-sm text-neutral-600">by {{ author }}</p>
      </div>

      <!-- Rating and Students -->
      <div class="flex flex-col items-end space-y-1 flex-shrink-0">
        <!-- Rating -->
        <div class="flex items-center space-x-1">
          <svg class="w-4 h-4 text-neutral-700" fill="currentColor" viewBox="0 0 20 20">
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
          </svg>
          <span class="text-body-sm text-neutral-700 font-medium">{{ rating }}</span>
        </div>

        <!-- Students Count -->
        <p class="text-caption text-neutral-600">{{ studentsCount }} students</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
interface Props {
  thumbnail: string
  name: string
  author: string
  rating: number
  studentsCount: number
}

const props = defineProps<Props>()
</script>
