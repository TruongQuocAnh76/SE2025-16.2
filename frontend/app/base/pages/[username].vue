<template>
  <div class="min-h-screen bg-gray-50">
    <!-- User Profile Header -->
    <div class="bg-white shadow-sm">
      <div class="max-w-7xl mx-auto px-4 py-6">
        <div class="flex items-center space-x-4">
          <div class="w-16 h-16 bg-gray-300 rounded-full"></div>
          <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ username }}</h1>
            <p class="text-gray-600">Student Dashboard</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 py-8">
      <!-- Stats Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Courses Enrolled -->
        <MetricCard 
          title="Courses Enrolled" 
          :value="enrollmentCount"
          :loading="loading.enrollments"
        >
          <template #icon>
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
            </svg>
          </template>
        </MetricCard>

        <!-- Certificates Earned -->
        <MetricCard 
          title="Certificates Earned" 
          :value="certificateCount"
          :loading="loading.certificates"
        >
          <template #icon>
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
            </svg>
          </template>
        </MetricCard>

        <!-- Learning Hours -->
        <MetricCard 
          title="Learning Hours" 
          :value="learningHours"
          :loading="loading.hours"
        >
          <template #icon>
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </template>
        </MetricCard>

        <!-- Average Progress -->
        <MetricCard 
          title="Average Progress" 
          :value="averageProgress"
          :loading="loading.progress"
        >
          <template #icon>
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
            </svg>
          </template>
        </MetricCard>
      </div>

      <!-- Continue Learning Section -->
      <div class="mb-8">
        <h2 class="text-h3 text-neutral-900 mb-6">Continue Learning</h2>
        <div v-if="loading.continueLearning" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div v-for="n in 3" :key="n" class="bg-neutral-0 border border-neutral-50 rounded-lg p-6 shadow-sm animate-pulse">
            <div class="flex items-start space-x-4">
              <div class="w-20 h-20 bg-neutral-50 rounded-lg"></div>
              <div class="flex-1">
                <div class="h-4 bg-neutral-50 rounded mb-2"></div>
                <div class="h-3 bg-neutral-50 rounded mb-4"></div>
                <div class="h-2 bg-neutral-50 rounded-full mb-4"></div>
              </div>
            </div>
            <div class="mt-6 h-10 bg-neutral-50 rounded-lg"></div>
          </div>
        </div>
        <div v-else-if="continueLearningCourses.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <CourseInfoCard
            v-for="course in continueLearningCourses"
            :key="course.courseId"
            :thumbnail="course.thumbnail"
            :name="course.name"
            :last-accessed="course.lastAccessed"
            :progress="course.progress"
            :course-id="course.courseId"
          />
        </div>
        <div v-else class="text-center py-12">
          <p class="text-body text-neutral-600">No courses to continue learning. Start a new course to see it here!</p>
        </div>
      </div>

      <!-- Additional Content -->

    </div>
  </div>
</template>

<script setup lang="ts">
import MetricCard from '../components/ui/MetricCard.vue'
import CourseInfoCard from '../components/ui/CourseInfoCard.vue'

const route = useRoute()
const username = route.params.username as string

// Get current user ID - hardcode for now or get from auth
const userId = ref('some-user-id') // You'll need to replace this with actual user ID

// Reactive state
const enrollmentCount = ref(0)
const certificateCount = ref(0)
const learningHours = ref(0)
const averageProgress = ref(0)
const continueLearningCourses = ref<Array<{
  courseId: string
  name: string
  thumbnail: string
  lastAccessed: string
  progress: number
}>>([])

const loading = reactive({
  enrollments: true,
  certificates: true,
  hours: true,
  progress: true,
  continueLearning: true
})

// TODO: Replace mock data with actual API calls
onMounted(async () => {

  // Simulate API calls with mock data
  setTimeout(() => {
    enrollmentCount.value = 5
    loading.enrollments = false
  }, 500)

  setTimeout(() => {
    certificateCount.value = 2
    loading.certificates = false
  }, 700)

  setTimeout(() => {
    learningHours.value = 24.5
    loading.hours = false
  }, 900)

  setTimeout(() => {
    averageProgress.value = 75
    loading.progress = false
  }, 1100)

  setTimeout(async () => {
    continueLearningCourses.value = [
      {
        courseId: '1',
        name: 'Introduction to Web Development',
        thumbnail: '/placeholder-course.jpg',
        lastAccessed: '2025-10-20',
        progress: 65
      },
      {
        courseId: '2',
        name: 'Advanced JavaScript Concepts',
        thumbnail: '/placeholder-course.jpg',
        lastAccessed: '2025-10-19',
        progress: 40
      },
      {
        courseId: '3',
        name: 'React Fundamentals',
        thumbnail: '/placeholder-course.jpg',
        lastAccessed: '2025-10-18',
        progress: 80
      }
    ]
    loading.continueLearning = false
  }, 1300)
})

// Set page title
useSeoMeta({
  title: `${username} - Dashboard`
})
</script>