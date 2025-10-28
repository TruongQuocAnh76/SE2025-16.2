<template>
  <div class="min-h-screen bg-gray-50">
    <!-- User Profile Header -->
    <div class="bg-white shadow-sm">
      <div class="max-w-7xl mx-auto px-4 py-6">
        <div class="flex items-center space-x-4">
          <div class="w-16 h-16 bg-gray-300 rounded-full"></div>
          <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ displayName }}</h1>
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

      <!-- Main Dashboard Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Left Section (3/4) -->
        <div class="lg:col-span-3 space-y-8 bg-neutral-0 border border-neutral-50 rounded-lg shadow-sm p-6">
          <!-- Continue Learning Section -->
          <div>
            <h2 class="text-h3 text-neutral-900 mb-6">Continue Learning</h2>
            <div v-if="loading.continueLearning" class="flex flex-col gap-6">
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
            <div v-else-if="continueLearningCourses.length > 0" class="flex flex-col gap-6">
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

          <!-- Recommended for You Section -->
          <div>
            <h2 class="text-h3 text-neutral-900 mb-6">Recommended for You</h2>
            <div class="flex flex-col gap-4">
              <RecommendedCourseCard
                v-for="course in recommendedCourses"
                :key="course.name"
                :thumbnail="course.thumbnail"
                :name="course.name"
                :author="course.author"
                :rating="course.rating"
                :students-count="course.studentsCount"
              />
            </div>
          </div>
        </div>

        <!-- Right Section (1/4) -->
        <div class="lg:col-span-1 space-y-6 ">
          <!-- Recent Certificates Section -->
          <div class="">
            <h3 class="text-h4 text-neutral-900 mb-4">Recent Certificates</h3>
            <div v-if="recentCertificates.length > 0" class="flex flex-col gap-4">
              <CertificateCard
                v-for="certificate in recentCertificates"
                :key="certificate.courseName"
                :course-name="certificate.courseName"
                :date-issued="certificate.dateIssued"
              />
            </div>
            <div v-else class="bg-neutral-0 border border-neutral-50 rounded-lg p-4 shadow-sm">
              <p class="text-body-sm text-neutral-600">Your recent certificates will appear here...</p>
            </div>
          </div>

          <!-- Quick Action Section -->
          <div class="bg-neutral-0 border border-neutral-50 rounded-lg shadow-sm p-6">
            <h3 class="text-h4 text-neutral-900 mb-4">Quick Action</h3>
            <div class="flex flex-col gap-3">
              <NuxtLink
                to="/courses"
                class="inline-flex items-center justify-center px-4 py-3 bg-neutral-900 text-neutral-0 text-body font-medium rounded-lg hover:bg-primary-600 transition-colors duration-200"
              >
                Browse Courses
              </NuxtLink>
              <button class="inline-flex items-center justify-center px-4 py-3 bg-neutral-900 text-neutral-0 text-body font-medium rounded-lg hover:bg-neutral-700 transition-colors duration-200">
                Verify Certificate
              </button>
              <button class="inline-flex items-center justify-center px-4 py-3 bg-neutral-900 text-neutral-0 text-body font-medium rounded-lg hover:bg-neutral-700 transition-colors duration-200">
                Invite Friends
              </button>
            </div>
          </div>

          <!-- Minor Section 2 (Placeholder) -->
          <div>
            <h3 class="text-h4 text-neutral-900 mb-4">Learning Streak</h3>
            <div class="bg-neutral-0 border border-neutral-50 rounded-lg p-4 shadow-sm">
              <p class="text-body-sm text-neutral-600">Your learning streak will appear here...</p>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup lang="ts">
import MetricCard from '../components/ui/MetricCard.vue'
import CourseInfoCard from '../components/ui/CourseInfoCard.vue'
import CertificateCard from '../components/ui/CertificateCard.vue'
import RecommendedCourseCard from '../components/ui/RecommendedCourseCard.vue'
import { useUserStats } from '../../composables/useUserStats'

const route = useRoute()
const username = route.params.username as string

const { currentUser } = useUserStats()

const displayName = computed(() => {
  const user = currentUser.value
  return user ? `${user.first_name} ${user.last_name}` : username
})

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

const recentCertificates = ref<Array<{
  courseName: string
  dateIssued: string
}>>([])

const recommendedCourses = ref<Array<{
  thumbnail: string
  name: string
  author: string
  rating: number
  studentsCount: number
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
  const { 
    getCurrentUser,
    setCurrentUser,
    getEnrollmentCount, 
    getCertificateCount, 
    getUserLearningHours, 
    getAverageCourseProgress, 
    getContinueLearningCourses,
    getRecentCertificates 
  } = useUserStats()

  // Fetch current user and set it
  try {
    const user = await getCurrentUser()
    setCurrentUser(user)
  } catch (error) {
    console.error('Failed to fetch current user:', error)
  }

  // Fetch enrollment count
  try {
    enrollmentCount.value = await getEnrollmentCount()
  } catch (error) {
    console.error('Failed to load enrollment count:', error)
  } finally {
    loading.enrollments = false
  }

  // Fetch certificate count
  try {
    certificateCount.value = await getCertificateCount()
  } catch (error) {
    console.error('Failed to load certificate count:', error)
  } finally {
    loading.certificates = false
  }

  // Fetch learning hours
  try {
    learningHours.value = await getUserLearningHours()
  } catch (error) {
    console.error('Failed to load learning hours:', error)
  } finally {
    loading.hours = false
  }

  // Fetch average progress
  try {
    averageProgress.value = await getAverageCourseProgress()
  } catch (error) {
    console.error('Failed to load average progress:', error)
  } finally {
    loading.progress = false
  }

  // Fetch continue learning courses
  try {
    continueLearningCourses.value = await getContinueLearningCourses()
  } catch (error) {
    console.error('Failed to load continue learning courses:', error)
  } finally {
    loading.continueLearning = false
  }

  // Fetch recent certificates
  try {
    recentCertificates.value = await getRecentCertificates()
  } catch (error) {
    console.error('Failed to load recent certificates:', error)
  }

  // Mock recommended courses (keep as mock for now)
  setTimeout(() => {
    recommendedCourses.value = [
      {
        thumbnail: '/placeholder-course.jpg',
        name: 'Advanced React Patterns',
        author: 'Sarah Johnson',
        rating: 4.8,
        studentsCount: 1250
      },
      {
        thumbnail: '/placeholder-course.jpg',
        name: 'Node.js Backend Development',
        author: 'Mike Chen',
        rating: 4.6,
        studentsCount: 890
      },
      {
        thumbnail: '/placeholder-course.jpg',
        name: 'UI/UX Design Principles',
        author: 'Emma Davis',
        rating: 4.9,
        studentsCount: 2100
      }
    ]
  }, 1500)
})

// Set page title
useSeoMeta({
  title: `${displayName.value} - Dashboard`
})
</script>