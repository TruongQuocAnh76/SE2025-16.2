<template>
  <div class="p-8 bg-background min-h-screen">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
      <!-- Greeting Card -->
      <div class="bg-white p-6 rounded-2xl shadow-md flex-1">
        <h2 class="text-h3 text-text-dark">Welcome Back, {{ displayName }}</h2>
        <p class="text-body-sm text-text-muted mt-1">Here is overview of your teaching activities</p>
      </div>
      
      <!-- Create New Course Button -->
      <button 
        class="bg-brand-primary hover:bg-teal-600 text-white px-6 py-3 rounded-full font-semibold transition-all duration-200 flex items-center gap-2 shadow-lg hover:shadow-xl"
        @click="navigateToCreateCourse"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Create New Course
      </button>
    </div>

    <!-- KPI Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <!-- Total Students -->
      <div class="bg-white p-6 rounded-2xl shadow-md hover:shadow-lg transition-shadow">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-body-sm text-text-muted mb-1">Total Students</p>
            <p class="text-h3 text-text-dark font-bold">{{ formatNumber(statistics.unique_students) }}</p>
          </div>
          <div class="w-12 h-12 bg-accent-purple/10 rounded-xl flex items-center justify-center">
            <svg class="w-6 h-6 text-accent-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
            </svg>
          </div>
        </div>
      </div>

      <!-- Courses -->
      <div class="bg-white p-6 rounded-2xl shadow-md hover:shadow-lg transition-shadow">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-body-sm text-text-muted mb-1">Courses</p>
            <p class="text-h3 text-text-dark font-bold">{{ statistics.total_courses }}</p>
          </div>
          <div class="w-12 h-12 bg-accent-blue/10 rounded-xl flex items-center justify-center">
            <svg class="w-6 h-6 text-accent-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
          </div>
        </div>
      </div>

      <!-- Submissions -->
      <div class="bg-white p-6 rounded-2xl shadow-md hover:shadow-lg transition-shadow">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-body-sm text-text-muted mb-1">Submissions</p>
            <p class="text-h3 text-text-dark font-bold">{{ formatNumber(statistics.total_enrollments) }}</p>
          </div>
          <div class="w-12 h-12 bg-accent-orange/10 rounded-xl flex items-center justify-center">
            <svg class="w-6 h-6 text-accent-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
        </div>
      </div>

      <!-- Earnings -->
      <div class="bg-white p-6 rounded-2xl shadow-md hover:shadow-lg transition-shadow">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-body-sm text-text-muted mb-1">Earnings</p>
            <p class="text-h3 text-text-dark font-bold">{{ formatCurrency(totalEarnings) }}</p>
          </div>
          <div class="w-12 h-12 bg-brand-primary/10 rounded-xl flex items-center justify-center">
            <svg class="w-6 h-6 text-brand-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content - Two Columns -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Left Column (Primary) -->
      <div class="lg:col-span-2 space-y-8">
        <!-- Recent Courses Section -->
        <div class="bg-white p-6 rounded-2xl shadow-md">
          <h3 class="text-h5 text-text-dark mb-6">Recent Courses</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div
              v-for="course in recentCourses.slice(0, 4)"
              :key="course.id"
              class="border border-gray-100 rounded-xl p-4 hover:shadow-md transition-shadow cursor-pointer"
              @click="navigateToCourse(course.id)"
            >
              <div class="flex items-start gap-4">
                <div class="w-16 h-16 bg-gradient-to-br from-brand-primary to-brand-secondary rounded-xl flex items-center justify-center flex-shrink-0">
                  <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                  </svg>
                </div>
                <div class="flex-1 min-w-0">
                  <h4 class="text-h6 text-text-dark truncate">{{ course.title }}</h4>
                  <div class="flex items-center gap-4 mt-2 text-caption text-text-muted">
                    <span class="flex items-center gap-1">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197" />
                      </svg>
                      {{ course.enrollments_count }} Students
                    </span>
                    <span class="flex items-center gap-1">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                      </svg>
                      {{ course.duration || 8 }} minutes
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Pending Submissions Section -->
        <div class="bg-white p-6 rounded-2xl shadow-md">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-h5 text-text-dark">Pending Submissions</h3>
            <div class="flex items-center gap-3">
              <select class="text-body-sm text-text-muted border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-primary">
                <option>All</option>
                <option>AI Engineer</option>
                <option>Cloud Engineer</option>
              </select>
              <button class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                <svg class="w-5 h-5 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </button>
            </div>
          </div>
          
          <div class="space-y-4">
            <div
              v-for="submission in pendingSubmissions"
              :key="submission.id"
              class="flex items-center justify-between p-4 border border-gray-100 rounded-xl hover:shadow-sm transition-shadow"
            >
              <div class="flex items-center gap-4">
                <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center">
                  <span class="text-body-sm font-semibold text-text-dark">{{ getInitials(submission.student_name) }}</span>
                </div>
                <div>
                  <p class="text-body font-medium text-text-dark">{{ submission.student_name }}</p>
                  <p class="text-caption text-text-muted">{{ formatRelativeTime(submission.submitted_at) }}</p>
                </div>
              </div>
              <div class="flex items-center gap-4">
                <span class="px-3 py-1 bg-amber-100 text-amber-700 text-caption font-medium rounded-full">
                  {{ submission.status }}
                </span>
                <div class="text-right">
                  <p class="text-body-sm text-text-dark">{{ submission.assignment_title }}</p>
                </div>
                <button class="text-brand-primary hover:text-teal-600 text-body-sm font-medium transition-colors">
                  Review Now
                </button>
              </div>
            </div>
          </div>
          
          <button class="w-full mt-4 text-center text-body-sm text-text-muted hover:text-text-dark transition-colors py-2">
            Show more...
          </button>
        </div>

        <!-- Certificates Pending Section -->
        <div class="bg-white p-6 rounded-2xl shadow-md">
          <h3 class="text-h5 text-text-dark mb-6">Certificates Pending</h3>
          
          <div class="space-y-4">
            <div
              v-for="cert in pendingCertificates"
              :key="cert.id"
              class="flex items-center justify-between p-4 border border-gray-100 rounded-xl hover:shadow-sm transition-shadow"
            >
              <div>
                <p class="text-body font-medium text-text-dark">
                  {{ cert.student_name }} – <span class="text-brand-primary">{{ cert.course_title }}</span>
                </p>
                <p class="text-caption text-text-muted mt-1">Student has completed all requirements</p>
              </div>
              <div class="flex items-center gap-3">
                <button class="px-4 py-2 bg-brand-primary hover:bg-teal-600 text-white text-body-sm font-medium rounded-lg transition-colors">
                  Issue Certificate
                </button>
                <button class="px-4 py-2 border border-accent-red text-accent-red hover:bg-accent-red hover:text-white text-body-sm font-medium rounded-lg transition-colors">
                  Deny
                </button>
              </div>
            </div>
          </div>
          
          <button class="w-full mt-4 text-center text-body-sm text-text-muted hover:text-text-dark transition-colors py-2">
            Show more...
          </button>
        </div>
      </div>

      <!-- Right Column (Sidebar) -->
      <div class="space-y-8">
        <!-- Teacher Courses List -->
        <div class="bg-white p-6 rounded-2xl shadow-md">
          <h3 class="text-h5 text-text-dark mb-6">Your Courses</h3>
          <div class="space-y-3">
            <div
              v-for="course in allCourses.slice(0, 5)"
              :key="course.id"
              class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 cursor-pointer transition-colors"
              @click="navigateToCourse(course.id)"
            >
              <div class="w-10 h-10 bg-gradient-to-br from-brand-primary to-brand-secondary rounded-lg flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
              </div>
              <span class="text-body text-text-dark truncate">{{ course.title }}</span>
            </div>
          </div>
        </div>

        <!-- Teacher Overview Stats -->
        <div class="bg-white p-6 rounded-2xl shadow-md">
          <h3 class="text-h5 text-text-dark mb-6">Teacher Overview</h3>
          <div class="space-y-4">
            <div class="flex items-center justify-between py-2 border-b border-gray-100">
              <span class="text-body-sm text-text-muted">Active Courses</span>
              <span class="text-body font-semibold text-text-dark">{{ statistics.published_courses }}</span>
            </div>
            <div class="flex items-center justify-between py-2 border-b border-gray-100">
              <span class="text-body-sm text-text-muted">Draft Courses</span>
              <span class="text-body font-semibold text-text-dark">{{ statistics.draft_courses }}</span>
            </div>
            <div class="flex items-center justify-between py-2 border-b border-gray-100">
              <span class="text-body-sm text-text-muted">Total Students</span>
              <span class="text-body font-semibold text-text-dark">{{ formatNumber(statistics.unique_students) }}</span>
            </div>
            <div class="flex items-center justify-between py-2 border-b border-gray-100">
              <span class="text-body-sm text-text-muted">New Students (7d)</span>
              <span class="text-body font-semibold text-brand-primary">+{{ newStudentsWeekly }}</span>
            </div>
            <div class="flex items-center justify-between py-2 border-b border-gray-100">
              <span class="text-body-sm text-text-muted">Avg Completion</span>
              <span class="text-body font-semibold text-text-dark">{{ avgCompletion }}%</span>
            </div>
            <div class="flex items-center justify-between py-2 border-b border-gray-100">
              <span class="text-body-sm text-text-muted">Drop-off Rate</span>
              <span class="text-body font-semibold text-accent-red">{{ dropOffRate }}%</span>
            </div>
            <div class="flex items-center justify-between py-2 border-b border-gray-100">
              <span class="text-body-sm text-text-muted">Avg Rating</span>
              <div class="flex items-center gap-1">
                <svg class="w-4 h-4 text-accent-star" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
                <span class="text-body font-semibold text-text-dark">{{ statistics.average_rating || 0 }}</span>
              </div>
            </div>
            <div class="flex items-center justify-between py-2">
              <span class="text-body-sm text-text-muted">Top Course</span>
              <span class="text-body font-semibold text-brand-primary truncate max-w-32">{{ topCourse?.title || 'N/A' }}</span>
            </div>
            <div v-if="topCourse" class="mt-2">
              <div class="flex items-center justify-between text-caption text-text-muted mb-1">
                <span>Completion</span>
                <span>{{ topCourseCompletion }}%</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div 
                  class="bg-brand-primary h-2 rounded-full transition-all duration-300"
                  :style="{ width: `${topCourseCompletion}%` }"
                ></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useUserStats } from '../../composables/useUserStats'
import { useTeacherStats, type TeacherCourse, type PendingSubmission, type PendingCertificate, type TeacherStatistics } from '../../composables/useTeacherStats'

const router = useRouter()
const { currentUser } = useUserStats()
const { 
  getTeacherCourses, 
  getTeacherStatistics, 
  getPendingSubmissions, 
  getPendingCertificates,
  formatRelativeTime,
  formatCurrency 
} = useTeacherStats()

// Reactive state
const allCourses = ref<TeacherCourse[]>([])
const recentCourses = ref<TeacherCourse[]>([])
const statistics = ref<TeacherStatistics>({
  total_courses: 0,
  published_courses: 0,
  draft_courses: 0,
  total_enrollments: 0,
  unique_students: 0,
  average_rating: 0,
  total_reviews: 0
})
const pendingSubmissions = ref<PendingSubmission[]>([])
const pendingCertificates = ref<PendingCertificate[]>([])
const totalEarnings = ref(1200000) // Mock value - would come from payment API
const newStudentsWeekly = ref(124) // Mock value
const avgCompletion = ref(72) // Mock value
const dropOffRate = ref(18) // Mock value
const topCourseCompletion = ref(81) // Mock value

const loading = ref(true)

// Computed properties
const displayName = computed(() => {
  const user = currentUser.value
  return user ? user.first_name : 'Instructor'
})

const topCourse = computed(() => {
  if (allCourses.value.length === 0) return null
  return allCourses.value.reduce((prev, curr) => 
    (curr.enrollments_count > prev.enrollments_count) ? curr : prev
  )
})

// Helper functions
const formatNumber = (num: number): string => {
  if (num >= 1000) {
    return num.toLocaleString()
  }
  return num.toString()
}

const getInitials = (name: string): string => {
  return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase()
}

const getRandomProgress = (courseId: string): number => {
  // Generate consistent random progress based on course ID
  const hash = courseId.split('').reduce((a, b) => ((a << 5) - a) + b.charCodeAt(0), 0)
  return Math.abs(hash % 70) + 20 // Progress between 20-90%
}

// Navigation functions
const navigateToCreateCourse = () => {
  router.push('/courses/add')
}

const navigateToCourse = (courseId: string) => {
  router.push(`/courses/${courseId}`)
}

// Fetch data on mount
onMounted(async () => {
  try {
    // Fetch teacher courses
    const coursesData = await getTeacherCourses()
    if (coursesData) {
      allCourses.value = coursesData.courses
      recentCourses.value = coursesData.courses.slice(0, 4)
    }

    // Fetch teacher statistics
    const statsData = await getTeacherStatistics()
    if (statsData) {
      statistics.value = statsData.statistics
    }

    // Fetch pending submissions
    pendingSubmissions.value = await getPendingSubmissions()

    // Fetch pending certificates
    pendingCertificates.value = await getPendingCertificates()

  } catch (error) {
    console.error('Failed to load teacher dashboard data:', error)
  } finally {
    loading.value = false
  }
})
</script>
