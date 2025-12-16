<template>
  <div class="p-12">
    <div class="welcome-card bg-white p-6 rounded-lg shadow-md flex items-center mb-6">
      <div class="avatar mr-4">
        <img src="/placeholder-avatar.png" alt="User Avatar" class="w-16 h-16 rounded-full object-cover">
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
        <h3 class="text-xl font-semibold mb-4">Recent Enrollment Course</h3>
        <div class="space-y-6">
          <RecentCourseCard
            v-for="course in coursesProgress.slice(0, 5)"
            :key="course.course_id"
            :thumbnail="'/placeholder-course.jpg'"
            :name="course.course_title"
            :author="'Instructor'"
            :rating="4.5"
            :studentsCount="100"
          />
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
import CourseInfoCard from '../../components/ui/CourseInfoCard.vue'
import RecentCourseCard from '../../components/ui/RecentCourseCard.vue'
import CertificateCard from '../../components/ui/CertificateCard.vue'
import CertificateModal from '../../components/ui/CertificateModal.vue'
import { useUserStats } from '../../composables/useUserStats'

const route = useRoute()
const username = route.params.username as string

const { currentUser } = useUserStats()


function getDisplayName(user: any, fallback: string) {
  if (!user) {
    // Nếu fallback là email, cắt phần sau @
    if (fallback && fallback.includes('@')) {
      return fallback.split('@')[0]
    }
    return fallback
  }
  if (user.username && !user.username.includes('@')) return user.username
  if (user.first_name || user.last_name) return `${user.first_name || ''} ${user.last_name || ''}`.trim()
  if (user.email && typeof user.email === 'string') return user.email.split('@')[0]
  if (user.username && user.username.includes('@')) return user.username.split('@')[0]
  // Nếu fallback là email, cắt phần sau @
  if (fallback && fallback.includes('@')) {
    return fallback.split('@')[0]
  }
  return fallback
}

const displayName = computed(() => {
  const user = currentUser.value
  return getDisplayName(user, username)
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
    getRecentCertificates,
    getQuizAttemptsCount,
    getCoursesProgress
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

// Set page title
useSeoMeta({
  title: `${displayName.value} - Dashboard`
})
</script>