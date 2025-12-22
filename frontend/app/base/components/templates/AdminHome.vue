<template>
  <div class="p-8 bg-background min-h-screen">
    <!-- Header Section -->
    <div class="bg-white p-6 rounded-2xl shadow-md mb-8">
      <h2 class="text-h3 text-text-dark">Welcome Back, {{ displayName }}</h2>
      <p class="text-body-sm text-text-muted mt-1">Here is an overview of your system management</p>
    </div>

    <!-- KPI Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <!-- Total Users -->
      <div class="bg-white p-6 rounded-2xl shadow-md hover:shadow-lg transition-shadow">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-body-sm text-text-muted mb-1">Total Users</p>
            <p class="text-h3 text-text-dark font-bold">{{ formatNumber(stats.total_users) }}</p>
          </div>
          <div class="w-12 h-12 bg-accent-purple/10 rounded-xl flex items-center justify-center">
            <svg class="w-6 h-6 text-accent-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
            </svg>
          </div>
        </div>

        <div class="mt-4">
          <button @click="goToApplications" class="w-full text-center text-body-sm text-text-muted hover:text-text-dark transition-colors py-2">
            Show more...
          </button>
        </div>
      </div>

      <!-- Total Courses -->
      <div class="bg-white p-6 rounded-2xl shadow-md hover:shadow-lg transition-shadow">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-body-sm text-text-muted mb-1">Total Courses</p>
            <p class="text-h3 text-text-dark font-bold">{{ formatNumber(stats.total_courses) }}</p>
          </div>
          <div class="w-12 h-12 bg-accent-blue/10 rounded-xl flex items-center justify-center">
            <svg class="w-6 h-6 text-accent-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
          </div>
        </div>
      </div>

      <!-- Certificates Issued -->
      <div class="bg-white p-6 rounded-2xl shadow-md hover:shadow-lg transition-shadow">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-body-sm text-text-muted mb-1">Certificates Issued</p>
            <p class="text-h3 text-text-dark font-bold">{{ formatNumber(stats.certificates_issued) }}</p>
          </div>
          <div class="w-12 h-12 bg-brand-primary/10 rounded-xl flex items-center justify-center">
            <svg class="w-6 h-6 text-brand-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
            </svg>
          </div>
        </div>
      </div>

      <!-- Pending Actions -->
      <div class="bg-white p-6 rounded-2xl shadow-md hover:shadow-lg transition-shadow">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-body-sm text-text-muted mb-1">Pending Actions</p>
            <p class="text-h3 text-text-dark font-bold">{{ formatNumber(stats.pending_actions) }}</p>
          </div>
          <div class="w-12 h-12 bg-accent-orange/10 rounded-xl flex items-center justify-center">
            <svg class="w-6 h-6 text-accent-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Left Column -->
      <div class="lg:col-span-2 space-y-8">
        <!-- Application Section -->
        <div>
          <h3 class="text-h5 text-text-dark mb-6">Applications</h3>
          <div class="grid grid-cols-1 gap-6">
            <template v-if="teacherApplications.length > 0">
              <!-- Teacher Application Card -->
              <div 
                v-for="app in teacherApplications.slice(0, 3)" 
                :key="app.id"
                class="bg-white p-6 rounded-2xl shadow-md"
              >
              <div class="flex items-center justify-between mb-4">
                <span class="px-3 py-1 bg-accent-purple/10 text-accent-purple text-caption font-medium rounded-full">
                  {{ app.status === 'PENDING' ? 'Pending Review' : app.status }}
                </span>
              </div>
              <div class="flex items-start gap-4 mb-4">
                <!-- Avatar -->
                <div class="w-16 h-16 rounded-full overflow-hidden flex-shrink-0 bg-gray-200">
                  <img v-if="app.avatar_url" :src="app.avatar_url" alt="Avatar" class="w-full h-full object-cover" />
                  <div v-else class="w-full h-full flex items-center justify-center text-gray-600 font-semibold">
                    {{ getInitials(app.full_name) }}
                  </div>
                </div>
                <!-- Personal Info -->
                <div class="flex-1 space-y-2">
                  <p class="text-body font-semibold text-text-dark">{{ app.full_name }}</p>
                  <p class="text-body-sm text-text-muted">{{ app.email }}</p>
                  <div v-if="app.bio" class="text-caption text-text-muted line-clamp-2">{{ app.bio }}</div>
                  <div class="flex flex-wrap gap-2 text-caption text-text-muted">
                    <span v-if="app.country">📍 {{ app.country }}</span>
                    <span v-if="app.phone">📞 {{ app.phone }}</span>
                    <span v-if="app.gender">{{ app.gender }}</span>
                  </div>
                </div>
              </div>
              <!-- Certificate Info -->
              <div class="bg-gray-50 p-3 rounded-lg space-y-1 mb-4">
                <p class="text-body-sm font-medium text-text-dark">📜 {{ app.certificate_title }}</p>
                <p class="text-caption text-text-muted">Issued by: {{ app.issuer }}</p>
                <p class="text-caption text-text-muted">Date: {{ app.issue_date }}</p>
                <p v-if="app.expiry_date" class="text-caption text-text-muted">Expires: {{ app.expiry_date }}</p>
              </div>
              <p class="text-caption text-text-muted mb-4">Submitted: {{ new Date(app.created_at).toLocaleDateString() }}</p>
              <div class="flex items-center gap-2 flex-wrap">
                <button 
                  @click="openTeacherModal(app)"
                  class="px-3 py-1.5 text-brand-primary border border-brand-primary text-caption font-medium rounded-lg hover:bg-brand-primary/5 transition-colors"
                >
                  View Details
                </button>
                <button 
                  @click="handleApproveTeacher(app.id)"
                  class="px-3 py-1.5 bg-brand-primary text-white text-caption font-medium rounded-lg hover:bg-teal-600 transition-colors"
                >
                  Approve
                </button>
                <button 
                  @click="handleRejectTeacher(app.id)"
                  class="px-3 py-1.5 border border-accent-red text-accent-red text-caption font-medium rounded-lg hover:bg-accent-red/5 transition-colors"
                >
                  Reject
                </button>
              </div>
              </div>
            </template>
            <template v-else>
              <div class="bg-white p-6 rounded-2xl shadow-md text-center text-text-muted">
                No applications yet.
              </div>
            </template>

            <!-- (Course applications removed - teacher applications occupy full width) -->
          </div>
        </div>

        <!-- Certificates Overview Section -->
        <div>
          <h3 class="text-h5 text-text-dark mb-6">Certificates Overview</h3>
          <div class="grid grid-cols-3 gap-4">
            <div class="bg-white p-4 rounded-xl shadow-md text-center">
              <p class="text-h4 font-bold text-brand-primary">{{ formatNumber(certsOverview.issued) }}</p>
              <p class="text-caption text-text-muted">Issued</p>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-md text-center">
              <p class="text-h4 font-bold text-accent-blue">{{ formatNumber(certsOverview.verified) }}</p>
              <p class="text-caption text-text-muted">Verified</p>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-md text-center">
              <p class="text-h4 font-bold text-accent-orange">{{ formatNumber(certsOverview.pending) }}</p>
              <p class="text-caption text-text-muted">Pending</p>
            </div>
          </div>
        </div>

        <!-- Recent Certificates Section -->
        <div class="bg-white p-6 rounded-2xl shadow-md">
          <h3 class="text-h5 text-text-dark mb-6">Recent Certificates</h3>
          <div class="space-y-4">
            <template v-if="recentCertificates.length > 0">
              <div 
                v-for="cert in recentCertificates.slice(0, 5)" 
                :key="cert.id"
                class="flex items-center justify-between p-4 border border-gray-100 rounded-xl hover:shadow-sm transition-shadow"
              >
              <div class="flex items-center gap-4">
                <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center overflow-hidden">
                  <img v-if="cert.student_avatar" :src="cert.student_avatar" alt="Avatar" class="w-full h-full object-cover" />
                  <span v-else class="text-body-sm font-semibold text-text-dark">{{ getInitials(cert.student_name) }}</span>
                </div>
                <div>
                  <p class="text-body font-medium text-text-dark">{{ cert.student_name }}</p>
                  <p class="text-caption text-text-muted">{{ cert.course_name }}</p>
                </div>
              </div>
              <div class="flex items-center gap-4">
                <span 
                  class="px-2 py-1 text-caption font-medium rounded-full"
                  :class="cert.is_verified ? 'bg-brand-primary/10 text-brand-primary' : 'bg-gray-100 text-text-muted'"
                >
                  {{ cert.is_verified ? 'Verified' : 'Pending' }}
                </span>
                <p class="text-caption text-text-muted">{{ cert.issued_at }}</p>
                <button @click="openCertificateModal(cert.id)" class="text-brand-primary hover:text-teal-600 text-body-sm font-medium transition-colors">
                  View Certificate
                </button>
              </div>
              </div>
            </template>
            <template v-else>
              <div class="text-center py-8 text-gray-500">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                </svg>
                <p class="text-sm">No recent certificates</p>
                <p class="text-xs text-gray-400">No certificates have been issued recently.</p>
              </div>
            </template>
          </div>
          <button @click="goToCertificates" class="w-full mt-4 text-center text-body-sm text-text-muted hover:text-text-dark transition-colors py-2">
            Show more...
          </button>
        </div>
      </div>

      <!-- Right Column - System Log -->
      <div class="space-y-8">
        <div class="bg-white p-6 rounded-2xl shadow-md">
          <h3 class="text-h5 text-text-dark mb-6">System Log</h3>
          <div class="space-y-4">
            <template v-if="systemLogs.length > 0">
              <div 
                v-for="log in systemLogs.slice(0, 8)" 
                :key="log.id"
                class="border-b border-gray-100 pb-4 last:border-0 last:pb-0"
              >
              <div class="flex items-start justify-between">
                <div class="flex-1">
                  <p class="text-body font-medium text-text-dark">{{ log.message }}</p>
                  <div class="text-caption text-text-muted mt-1 space-y-0.5">
                    <p v-if="log.context?.course_title">Course: {{ log.context.course_title }}</p>
                    <p v-if="log.context?.teacher_name">Instructor: {{ log.context.teacher_name }}</p>
                    <p v-if="log.context?.student_name">Student: {{ log.context.student_name }}</p>
                    <p v-if="log.context?.reason">Reason: {{ log.context.reason }}</p>
                    <p>Action by: {{ log.action_by }}</p>
                  </div>
                </div>
                <p class="text-caption text-text-muted whitespace-nowrap ml-4">{{ log.timestamp }}</p>
              </div>
              </div>
            </template>
            <template v-else>
              <div class="text-center py-8 text-text-muted">
                No system logs yet.
              </div>
            </template>
          </div>
          <button @click="goToAuditLog" class="w-full mt-4 text-center text-body-sm text-text-muted hover:text-text-dark transition-colors py-2">
            Show more...
          </button>
        </div>
      </div>
    </div>
  </div>
  <!-- Certificate Modal -->
  <CertificateModal
    :isOpen="certificateModalOpen"
    :certificateId="selectedCertificateId || undefined"
    @close="closeCertificateModal"
  />
  
  <!-- Teacher Application Modal -->
  <TeacherApplicationModal
    :isOpen="teacherModalOpen"
    :application="selectedApplication"
    @close="closeTeacherModal"
    @approve="handleModalApprove"
    @reject="handleModalReject"
  />
</template>

<script setup lang="ts">
import { useUserStats } from '../../composables/useUserStats'
import { useAdminStats, type DashboardStats, type TeacherApplication, type CertificatesOverview, type RecentCertificate, type SystemLogEntry } from '../../composables/useAdminStats'
import CertificateModal from '../../components/ui/CertificateModal.vue'
import TeacherApplicationModal from '../../components/ui/TeacherApplicationModal.vue'

const { currentUser } = useUserStats()
const { 
  getDashboardStats, 
  getTeacherApplications,
  getTeacherApplicationDetail, 
  getCertificatesOverview, 
  getRecentCertificates, 
  getSystemLogs,
  approveTeacher,
  rejectTeacher
} = useAdminStats()

// Computed display name
const displayName = computed(() => {
  const user = currentUser.value
  if (!user) return 'Admin'
  return user.first_name || user.username || 'Admin'
})

// Reactive state
const stats = ref<DashboardStats>({
  total_users: 0,
  total_courses: 0,
  certificates_issued: 0,
  pending_actions: 0
})

const teacherApplications = ref<TeacherApplication[]>([])
const certsOverview = ref<CertificatesOverview>({ issued: 0, verified: 0, pending: 0 })
const recentCertificates = ref<RecentCertificate[]>([])
const systemLogs = ref<SystemLogEntry[]>([])

// applications grid is inline in template to keep code concise

// Certificate modal state
const certificateModalOpen = ref(false)
const selectedCertificateId = ref<string | null>(null)

const openCertificateModal = (certificateId: string) => {
  selectedCertificateId.value = certificateId
  certificateModalOpen.value = true
}

const closeCertificateModal = () => {
  certificateModalOpen.value = false
  selectedCertificateId.value = null
}

// Teacher application modal state
const teacherModalOpen = ref(false)
const selectedApplication = ref<TeacherApplication | null>(null)

const openTeacherModal = (application: TeacherApplication) => {
  selectedApplication.value = application
  teacherModalOpen.value = true
}

const closeTeacherModal = () => {
  teacherModalOpen.value = false
  selectedApplication.value = null
}

const handleModalApprove = async (applicationId: string) => {
  closeTeacherModal()
  await handleApproveTeacher(applicationId)
}

const handleModalReject = async (applicationId: string) => {
  closeTeacherModal()
  await handleRejectTeacher(applicationId)
}

const loading = ref(true)

const router = useRouter()

const goToCertificates = () => router.push('/admin/certificates')
const goToAuditLog = () => router.push('/admin/audit-log')
const goToApplications = () => router.push('/admin/applications')

// Helper functions
const formatNumber = (num: number): string => {
  return num.toLocaleString()
}

const getInitials = (name: string): string => {
  return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase()
}

// Action handlers
const handleApproveTeacher = async (applicationId: string) => {
  const success = await approveTeacher(applicationId)
  if (success) {
    teacherApplications.value = await getTeacherApplications()
    const newStats = await getDashboardStats()
    if (newStats) stats.value = newStats
    systemLogs.value = await getSystemLogs()
  }
}

const handleRejectTeacher = async (applicationId: string) => {
  const reason = prompt('Please provide a reason for rejection:')
  if (!reason || reason.trim() === '') {
    alert('Rejection reason is required')
    return
  }
  
  const success = await rejectTeacher(applicationId, reason)
  if (success) {
    teacherApplications.value = await getTeacherApplications()
    const newStats = await getDashboardStats()
    if (newStats) stats.value = newStats
    systemLogs.value = await getSystemLogs()
  }
}

// Course approval/rejection handlers removed (no course applications UI)

// Fetch data on mount
onMounted(async () => {
  try {
    const [statsData, teacherApps, certsOverviewData, recentCerts, logs] = await Promise.all([
      getDashboardStats(),
      getTeacherApplications(),
      getCertificatesOverview(),
      getRecentCertificates(),
      getSystemLogs()
    ])

    if (statsData) stats.value = statsData
    teacherApplications.value = teacherApps
    if (certsOverviewData) certsOverview.value = certsOverviewData
    recentCertificates.value = recentCerts
    systemLogs.value = logs
  } catch (error) {
    console.error('Failed to load admin dashboard data:', error)
  } finally {
    loading.value = false
  }
})
</script>
