<template>
  <div class="p-12">
    <div class="welcome-card bg-white p-6 rounded-lg shadow-md flex items-center mb-6">
      <div class="avatar mr-4">
        <img :src="currentUser?.avatar || '/placeholder-avatar.png'" alt="User Avatar" class="w-16 h-16 rounded-full object-cover">
      </div>
      <div class="text">
        <h2 class="text-2xl font-bold text-gray-800">Welcome Back, {{ displayName }}</h2>
        <p class="text-gray-600 mt-1">Here is overview of your course</p>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-12 mt-6">
      <CourseInfoCard
        title="Total Enrollment"
        icon="fas fa-users"
        :data="enrollmentCount"
      />
      <CourseInfoCard
        title="Completed Courses"
        icon="fas fa-check-circle"
        :data="certificateCount"
      />
      <CourseInfoCard
        title="Quiz Attempts"
        icon="fas fa-question-circle"
        :data="quizAttemptsCount"
      />
    </div>

    <div class="grid grid-cols-7 gap-6 mt-8">
      <div class="col-span-5 bg-white p-6 rounded-lg shadow-lg">
        <h3 class="text-xl font-semibold mb-4">Enrolled Courses</h3>
        <div class="space-y-6">
          <template v-if="enrolledCourses.length > 0">
            <NuxtLink 
              v-for="enrollment in enrolledCourses.slice(0, 5)"
              :key="enrollment.id"
              :to="`/courses/${enrollment.course?.id}`"
              class="block"
            >
              <div class="flex gap-4 p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                <img 
                  :src="enrollment.course?.thumbnail || '/placeholder-course.jpg'" 
                  :alt="enrollment.course?.title"
                  class="w-32 h-20 object-cover rounded"
                />
                <div class="flex-1">
                  <h4 class="font-semibold text-lg text-gray-800 mb-1">{{ enrollment.course?.title }}</h4>
                  <p class="text-sm text-gray-600 line-clamp-2 mb-2">{{ enrollment.course?.description }}</p>
                  <div class="flex items-center gap-4 text-sm text-gray-500">
                    <span class="flex items-center gap-1">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                      </svg>
                      {{ enrollment.course?.teacher?.first_name }} {{ enrollment.course?.teacher?.last_name }}
                    </span>
                    <span class="flex items-center gap-1">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                      </svg>
                      {{ enrollment.course?.duration || 'N/A' }} hours
                    </span>
                    <span class="px-2 py-1 text-xs rounded" :class="{
                      'bg-green-100 text-green-800': enrollment.status === 'ACTIVE',
                      'bg-blue-100 text-blue-800': enrollment.status === 'COMPLETED',
                      'bg-gray-100 text-gray-800': enrollment.status === 'EXPIRED'
                    }">
                      {{ enrollment.status }}
                    </span>
                  </div>
                </div>
                <div class="flex flex-col justify-center items-end">
                  <div class="text-sm text-gray-500 mb-2">Progress</div>
                  <div class="text-2xl font-bold text-brand-primary">{{ enrollment.progress || 0 }}%</div>
                </div>
              </div>
            </NuxtLink>
          </template>
          <template v-else>
            <div class="text-center py-12 text-gray-500">
              <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
              </svg>
              <p class="text-lg font-medium mb-2">No enrolled courses yet</p>
              <p class="text-sm text-gray-400 mb-4">Start learning by enrolling in courses</p>
              <NuxtLink to="/courses" class="inline-block px-6 py-2 bg-brand-primary text-white rounded-lg hover:bg-brand-secondary transition">
                Browse Courses
              </NuxtLink>
            </div>
          </template>
        </div>
      </div>
      <div class="col-span-2 bg-white p-6 rounded-lg shadow-lg">
        <h3 class="text-xl font-semibold mb-4">Acquired Certificate</h3>
        <div class="space-y-4">
          <template v-if="recentCertificates.length > 0">
            <CertificateCard
              v-for="cert in recentCertificates"
              :key="cert.id"
              :courseName="cert.courseName"
              :dateIssued="cert.dateIssued"
              :certificateId="cert.id"
              :pdfUrl="cert.pdfUrl"
              @click="openCertificateModal(cert.id)"
            />
          </template>
          <template v-else>
            <div class="text-center py-8 text-gray-500">
              <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
              </svg>
              <p class="text-sm">No certificates yet</p>
              <p class="text-xs text-gray-400">Complete courses to earn certificates</p>
            </div>
          </template>
        </div>
      </div>
    </div>

    <!-- Certificate Modal -->
    <CertificateModal
      :isOpen="certificateModalOpen"
      :certificateId="selectedCertificateId || undefined"
      @close="closeCertificateModal"
    />
  </div>
</template>

<script setup lang="ts">
import { computed, watch } from 'vue'
import CourseInfoCard from '../ui/CourseInfoCard.vue'
import CertificateCard from '../ui/CertificateCard.vue'
import CertificateModal from '../ui/CertificateModal.vue'
import { useUserStats } from '../../composables/useUserStats'

const auth = useAuth()
const { currentUser, setCurrentUser } = useUserStats()

// Sync auth user to userStats whenever it changes
watch(() => auth.user?.value, (newUser) => {
  if (newUser && !currentUser.value) {
    setCurrentUser(newUser)
  }
}, { immediate: true })

function getDisplayName(user: any, fallback: string) {
  if (!user) {
    if (fallback && fallback.includes('@')) {
      return fallback.split('@')[0]
    }
    return fallback
  }
  if (user.username && !user.username.includes('@')) return user.username
  if (user.first_name || user.last_name) return `${user.first_name || ''} ${user.last_name || ''}`.trim()
  if (user.email && typeof user.email === 'string') return user.email.split('@')[0]
  if (user.username && user.username.includes('@')) return user.username.split('@')[0]
  if (fallback && fallback.includes('@')) {
    return fallback.split('@')[0]
  }
  return fallback
}

const displayName = computed(() => {
  // Prefer auth.user, fallback to currentUser
  const user = auth.user?.value || currentUser.value
  const fallback = user ? (user.username || user.email || '') : ''
  return getDisplayName(user, fallback)
})

// Reactive state
const enrollmentCount = ref(0)
const certificateCount = ref(0)
const learningHours = ref(0)
const averageProgress = ref(0)
const quizAttemptsCount = ref(0)
const continueLearningCourses = ref<Array<{
  courseId: string
  name: string
  thumbnail: string
  lastAccessed: string
  progress: number
}>>([])

const coursesProgress = ref<Array<any>>([])
const enrolledCourses = ref<Array<any>>([])

const recentCertificates = ref<Array<{
  id: string
  courseName: string
  dateIssued: string
  pdfUrl?: string
}>>([])

const recommendedCourses = ref<Array<{
  thumbnail: string
  name: string
  author: string
  rating: number
  studentsCount: number
}>>([])

// Certificate modal state
const certificateModalOpen = ref(false)
const selectedCertificateId = ref<string | null>(null)

const loading = reactive({
  enrollments: true,
  certificates: true,
  hours: true,
  progress: true,
  continueLearning: true,
  quizAttempts: true
})

// Certificate modal functions
const openCertificateModal = (certificateId: string) => {
  selectedCertificateId.value = certificateId
  certificateModalOpen.value = true
}

const closeCertificateModal = () => {
  certificateModalOpen.value = false
  selectedCertificateId.value = null
}

// Fetch all data on mount
onMounted(async () => {
  const {
    getCurrentUser,
    setCurrentUser,
    getEnrollmentCount,
    getCertificateCount,
    getUserLearningHours,
    getAverageCourseProgress,
    getContinueLearningCourses,
    getRecentCertificates,
    getQuizAttemptsCount,
    getCoursesProgress,
    getUserEnrollments
  } = useUserStats()

  // ⭐ CRITICAL: Fetch current user and set it to global state
  // This ensures other pages (like Course Detail) can access membership_tier for payment logic
  try {
    const user = await getCurrentUser()
    setCurrentUser(user)
    console.log('✅ StudentHome: Current user fetched and set:', {
      id: user?.id,
      membership_tier: user?.membership_tier,
      membership_expires_at: user?.membership_expires_at
    })
  } catch (error) {
    console.error('❌ StudentHome: Failed to fetch current user:', error)
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

  // Fetch quiz attempts count
  try {
    quizAttemptsCount.value = await getQuizAttemptsCount()
  } catch (error) {
    console.error('Failed to load quiz attempts count:', error)
  } finally {
    loading.quizAttempts = false
  }

  // Fetch courses progress
  try {
    coursesProgress.value = await getCoursesProgress()
  } catch (error) {
    console.error('Failed to load courses progress:', error)
  }

  // Fetch enrolled courses
  try {
    enrolledCourses.value = await getUserEnrollments()
    console.log('Enrolled courses:', enrolledCourses.value)
  } catch (error) {
    console.error('Failed to load enrolled courses:', error)
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
    console.log('Fetching recent certificates...')
    const certificates = await getRecentCertificates()
    console.log('Fetched certificates:', certificates)
    recentCertificates.value = certificates
    if (certificates.length === 0) {
      console.log('No certificates found')
    }
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
</script>