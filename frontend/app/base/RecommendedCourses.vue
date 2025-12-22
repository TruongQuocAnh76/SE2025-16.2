<template>
  <section v-if="!error" class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4">
      <!-- Header -->
      <div class="mb-12">
        <h2 class="text-4xl font-bold text-gray-900 mb-3">
          {{ isAuthenticated ? 'Recommended for You' : 'Popular Courses' }}
        </h2>
        <p class="text-lg text-gray-600">
          {{ isAuthenticated ? 'Personalized courses based on your learning history' : 'Explore our most popular courses' }}
        </p>
      </div>

      <!-- Loading State -->
      <div v-if="pending" class="flex justify-center py-16">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-brand-primary"></div>
      </div>

      <!-- Courses Grid -->
      <div v-else-if="courses && courses.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 items-stretch">
        <NuxtLink
          v-for="course in courses"
          :key="course.id"
          :to="`/courses/${course.id}`"
        >
          <CourseCard :course="mapCourseForCard(course)" />
        </NuxtLink>
      </div>

      <!-- Empty State -->
      <div v-else class="text-center py-16">
        <svg class="mx-auto h-24 w-24 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
        </svg>
        <h3 class="text-xl font-semibold text-gray-900 mb-2">
          Start learning to get personalized recommendations
        </h3>
        <p class="text-gray-600">
          Enroll in courses to receive tailored course suggestions
        </p>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed, watch, ref, onMounted } from 'vue'
import { useCourses } from '../domains/courses/composables/useCourses'
import CourseCard from '../domains/courses/components/ui/CourseCard.vue'

const { getRecommendations, getCourses } = useCourses()
const token = useCookie('auth_token')

// Reactive state
const data = ref<any>(null)
const pending = ref(false)
const error = ref<any>(null)
const isAuthenticated = computed(() => !!token.value)

// Fetch recommendations (authenticated) or popular courses (guest)
const fetchRecommendations = async () => {
  pending.value = true
  error.value = null
  try {
    let courses: any[] = []
    
    console.log('isAuthenticated:', isAuthenticated.value, 'token:', token.value ? 'exists' : 'null')
    
    if (isAuthenticated.value) {
      // User logged in - get personalized recommendations
      try {
        courses = await getRecommendations()
        console.log('Recommendations fetched:', courses)
        
        // If no recommendations, fallback to published courses
        if (!courses || courses.length === 0) {
          console.log('No recommendations, falling back to published courses')
          const allCourses = await getCourses({ status: 'PUBLISHED' })
          courses = allCourses.slice(0, 8)
        }
      } catch (recError) {
        console.error('Error fetching recommendations, falling back to published courses:', recError)
        // Fallback to published courses if recommendations fail
        const allCourses = await getCourses({ status: 'PUBLISHED' })
        courses = allCourses.slice(0, 8)
      }
    } else {
      // Guest - get popular/published courses
      const allCourses = await getCourses({ status: 'PUBLISHED' })
      courses = allCourses.slice(0, 8) // Limit to 8 courses
    }
    
    data.value = { success: true, data: courses }
  } catch (err) {
    error.value = err
    console.error('Failed to fetch recommendations:', err)
  } finally {
    pending.value = false
  }
}

// Fetch on mount
onMounted(() => {
  fetchRecommendations()
})

// Debug logging
watch(() => data.value, (newData) => {
  console.log('RecommendedCourses data updated:', newData)
}, { deep: true })

watch(() => error.value, (newError) => {
  if (newError) {
    console.error('RecommendedCourses error:', newError)
  }
})

const courses = computed(() => {
  const rawData = data.value as any
  console.log('Computing courses from:', rawData)
  
  // Nếu API trả về format { success, data: [...] }
  if (rawData?.data && Array.isArray(rawData.data)) {
    return rawData.data
  }
  
  // Nếu API trả về trực tiếp array
  if (Array.isArray(rawData)) {
    return rawData
  }
  
  return []
})

// Map course data for CourseCard component
const mapCourseForCard = (course: any) => {
  let thumbnailUrl = '/placeholder-course.jpg'
  if (course.thumbnail) {
    thumbnailUrl = course.thumbnail
  }
  return {
    thumbnail: thumbnailUrl,
    duration: typeof course.duration === 'number' ? `${course.duration}h` : (course.duration || '2h 30m'),
    name: course.title,
    description: course.description,
    author: `${course.teacher?.first_name || ''} ${course.teacher?.last_name || ''}`.trim() || 'Unknown Author',
    price: course.price
  }
}
</script>

<style scoped>
.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>