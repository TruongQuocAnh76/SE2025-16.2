<template>
  <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300">
    <!-- Thumbnail -->
    <div class="relative">
      <img
        :src="course.thumbnail || '/placeholder-course.jpg'"
        :alt="course.name"
        class="w-full h-48 object-cover rounded-t-3xl"
      />
    </div>

    <!-- Content -->
    <div class="p-4">
      <!-- Course Name and Duration -->
      <div class="flex justify-between items-start mb-2">
        <h3 class="text-lg font-semibold text-gray-900 line-clamp-2 flex-1 mr-2">
          {{ course.name }}
        </h3>
        <span class="text-sm text-gray-500 whitespace-nowrap">
          {{ course.duration }}
        </span>
      </div>

      <!-- Description -->
      <p class="text-gray-600 text-sm mb-4 line-clamp-3">
        {{ course.description }}
      </p>

      <!-- Author and Price -->
      <div class="flex justify-between items-center">
        <span class="text-sm text-gray-500">
          By {{ course.author }}
        </span>
        <span class="text-lg font-bold text-brand-primary">
          {{ course.price ? `$${course.price}` : 'Free' }}
        </span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
interface CourseCardProps {
  thumbnail?: string
  duration: string
  name: string
  description: string
  author: string
  price?: number
}

defineProps<{
  course: CourseCardProps
}>()
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
