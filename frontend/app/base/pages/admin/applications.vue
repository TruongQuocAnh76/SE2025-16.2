<template>
  <div class="min-h-screen bg-background p-8">
    <div class="max-w-7xl mx-auto">
      <!-- Header -->
      <div class="flex items-center justify-between mb-8">
        <div>
          <h1 class="text-h2 font-bold text-text-dark">Teacher Applications</h1>
          <p class="text-body text-text-muted mt-1">Manage teacher role applications</p>
        </div>
        <div class="text-body text-text-muted">
          Total: <span class="font-semibold text-text-dark">{{ total }}</span>
        </div>
      </div>

      <!-- Applications Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div 
          v-for="app in applications" 
          :key="app.id"
          class="bg-white rounded-2xl shadow-md p-6 hover:shadow-lg transition-shadow cursor-pointer"
          @click="openAppModal(app)"
        >
          <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center overflow-hidden">
              <img v-if="app.user?.avatar" :src="app.user.avatar" alt="User" class="w-full h-full object-cover" />
              <span v-else class="text-body font-semibold text-text-dark">{{ getInitials(app.user) }}</span>
            </div>
            <div>
              <p class="text-body font-medium text-text-dark">{{ app.user?.first_name }} {{ app.user?.last_name }}</p>
              <p class="text-caption text-text-muted">@{{ app.user?.username }}</p>
            </div>
          </div>
          <div class="space-y-2 mb-4">
            <p class="text-body-sm text-text-muted"><span class="font-medium text-text-dark">Certificate:</span> {{ app.certificate_title }}</p>
            <p class="text-caption text-text-muted">Issued by: {{ app.issuer }}</p>
          </div>
          <div class="flex items-center justify-between">
            <span 
              class="px-3 py-1 text-caption font-medium rounded-full"
              :class="{
                'bg-brand-primary/10 text-brand-primary': app.status === 'APPROVED',
                'bg-accent-orange/10 text-accent-orange': app.status === 'PENDING',
                'bg-accent-red/10 text-accent-red': app.status === 'REJECTED'
              }"
            >
              {{ app.status }}
            </span>
            <span class="text-caption text-text-muted">{{ formatDate(app.created_at) }}</span>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div class="mt-8 flex items-center justify-between">
        <div class="text-body-sm text-text-muted">
          Showing {{ from }} to {{ to }} of {{ total }} applications
        </div>
        <div class="flex items-center gap-2">
          <button 
            @click="goToPage(currentPage - 1)" 
            :disabled="currentPage === 1"
            class="px-4 py-2 text-body-sm font-medium rounded-lg border transition-colors"
            :class="currentPage === 1 ? 'border-gray-200 text-text-muted cursor-not-allowed' : 'border-gray-300 text-text-dark hover:bg-gray-100'"
          >
            Previous
          </button>
          <span class="text-body-sm text-text-dark">Page {{ currentPage }} of {{ lastPage }}</span>
          <button 
            @click="goToPage(currentPage + 1)" 
            :disabled="currentPage === lastPage"
            class="px-4 py-2 text-body-sm font-medium rounded-lg border transition-colors"
            :class="currentPage === lastPage ? 'border-gray-200 text-text-muted cursor-not-allowed' : 'border-gray-300 text-text-dark hover:bg-gray-100'"
          >
            Next
          </button>
        </div>
      </div>
    </div>

    <!-- Application Detail Modal -->
    <ApplicationDetailModal
      :isOpen="appModalOpen"
      :application="selectedApp"
      @close="closeAppModal"
    />
  </div>
</template>

<script setup lang="ts">
import { useAdminList } from '../../composables/useAdminList'
import ApplicationDetailModal from '../../components/ui/ApplicationDetailModal.vue'

const { listApplications } = useAdminList()

const applications = ref<any[]>([])
const currentPage = ref(1)
const lastPage = ref(1)
const total = ref(0)
const from = ref(0)
const to = ref(0)
const loading = ref(true)

const appModalOpen = ref(false)
const selectedApp = ref<any>(null)

const openAppModal = (app: any) => {
  selectedApp.value = app
  appModalOpen.value = true
}

const closeAppModal = () => {
  appModalOpen.value = false
  selectedApp.value = null
}

const fetchApplications = async (page = 1) => {
  loading.value = true
  const response = await listApplications(page, 20)
  if (response) {
    applications.value = response.data
    currentPage.value = response.current_page
    lastPage.value = response.last_page
    total.value = response.total
    from.value = response.from
    to.value = response.to
  }
  loading.value = false
}

const goToPage = (page: number) => {
  if (page >= 1 && page <= lastPage.value) {
    fetchApplications(page)
  }
}

const getInitials = (user: any): string => {
  if (!user) return '?'
  const first = user.first_name?.[0] || ''
  const last = user.last_name?.[0] || ''
  return (first + last).toUpperCase() || '?'
}

const formatDate = (dateStr: string): string => {
  const date = new Date(dateStr)
  return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
}

onMounted(() => {
  fetchApplications()
})
</script>
