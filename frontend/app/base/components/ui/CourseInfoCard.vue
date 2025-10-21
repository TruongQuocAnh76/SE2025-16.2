<template>
  <div class="bg-neutral-0 border border-neutral-50 rounded-lg p-6 shadow-sm">
    <div class="flex items-start space-x-4">
      <!-- Thumbnail -->
      <img :src="thumbnail" :alt="name" class="w-20 h-20 object-cover rounded-lg flex-shrink-0" />

      <!-- Course Info -->
      <div class="flex-1 min-w-0">
        <h4 class="text-h4 text-neutral-900 mb-2 truncate">{{ name }}</h4>
        <p class="text-body-sm text-neutral-600 mb-4">Last accessed: {{ lastAccessed }}</p>

        <!-- Progress Bar -->
        <div class="w-full bg-neutral-50 rounded-full h-2 mb-4">
          <div
            class="bg-primary-500 h-2 rounded-full transition-all duration-300"
            :style="{ width: progress + '%' }"
          ></div>
        </div>
        <p class="text-caption text-neutral-700">{{ progress }}% complete</p>
      </div>
    </div>

    <!-- Resume Button -->
    <div class="mt-6">
      <NuxtLink
        :to="`/courses/${courseId}`"
        class="inline-flex items-center px-6 py-3 bg-neutral-900 text-neutral-0 text-body font-medium rounded-lg hover:bg-neutral-700 transition-colors duration-200"
      >
        Resume Learning
      </NuxtLink>
    </div>
  </div>
</template>

<script setup lang="ts">
interface Props {
  thumbnail: string
  name: string
  lastAccessed: string
  progress: number
  courseId: string
}

const props = defineProps<Props>()
</script>
