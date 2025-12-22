<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="container mx-auto px-4 max-w-4xl">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">My Teacher Applications</h1>
        <p class="text-gray-600 mt-2">View the status of your teacher registration applications</p>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-brand-primary"></div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4">
        <p class="text-red-800">{{ error }}</p>
      </div>

      <!-- Empty State -->
      <div v-else-if="applications.length === 0" class="bg-white rounded-lg shadow-md p-12 text-center">
        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <h3 class="text-xl font-semibold text-gray-900 mb-2">No Applications Yet</h3>
        <p class="text-gray-600 mb-6">You haven't submitted any teacher applications.</p>
        <NuxtLink 
          to="/teacher/register"
          class="inline-block px-6 py-3 bg-brand-primary text-white font-medium rounded-lg hover:bg-teal-600 transition-colors"
        >
          Apply to Become a Teacher
        </NuxtLink>
      </div>

      <!-- Applications List -->
      <div v-else class="space-y-4">
        <div 
          v-for="application in applications" 
          :key="application.id"
          class="bg-white rounded-lg shadow-md overflow-hidden"
        >
          <div class="p-6">
            <!-- Header -->
            <div class="flex items-start justify-between mb-4">
              <div>
                <h3 class="text-xl font-semibold text-gray-900">{{ application.certificate_title }}</h3>
                <p class="text-sm text-gray-600 mt-1">Issued by: {{ application.issuer }}</p>
              </div>
              <span 
                class="px-3 py-1 rounded-full text-sm font-medium"
                :class="{
                  'bg-yellow-100 text-yellow-800': application.status === 'PENDING',
                  'bg-green-100 text-green-800': application.status === 'APPROVED',
                  'bg-red-100 text-red-800': application.status === 'REJECTED'
                }"
              >
                {{ application.status }}
              </span>
            </div>

            <!-- Application Info -->
            <div class="grid grid-cols-2 gap-4 mb-4">
              <div>
                <p class="text-sm text-gray-600">Full Name</p>
                <p class="font-medium text-gray-900">{{ application.full_name }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-600">Email</p>
                <p class="font-medium text-gray-900">{{ application.email }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-600">Submitted On</p>
                <p class="font-medium text-gray-900">{{ formatDate(application.created_at) }}</p>
              </div>
              <div v-if="application.reviewed_at">
                <p class="text-sm text-gray-600">Reviewed On</p>
                <p class="font-medium text-gray-900">{{ formatDate(application.reviewed_at) }}</p>
              </div>
            </div>

            <!-- Approval Notification -->
            <div v-if="application.status === 'APPROVED'" class="bg-green-50 border border-green-200 rounded-lg p-4 mt-4">
              <div class="flex items-start gap-3">
                <svg class="w-6 h-6 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                  <h4 class="font-semibold text-green-900 mb-1">🎉 Congratulations!</h4>
                  <p class="text-green-800">Your application has been approved! You are now a teacher and can start creating courses.</p>
                  <NuxtLink 
                    to="/teacher/dashboard"
                    class="inline-block mt-3 px-4 py-2 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition-colors text-sm"
                  >
                    Go to Teacher Dashboard
                  </NuxtLink>
                </div>
              </div>
            </div>

            <!-- Rejection Notification -->
            <div v-if="application.status === 'REJECTED'" class="bg-red-50 border border-red-200 rounded-lg p-4 mt-4">
              <div class="flex items-start gap-3">
                <svg class="w-6 h-6 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="flex-1">
                  <h4 class="font-semibold text-red-900 mb-1">Application Rejected</h4>
                  <p class="text-red-800 mb-2">Unfortunately, your application was not approved.</p>
                  <div v-if="application.rejection_reason" class="bg-white rounded p-3 border border-red-200">
                    <p class="text-sm font-medium text-gray-700 mb-1">Reason:</p>
                    <p class="text-gray-900">{{ application.rejection_reason }}</p>
                  </div>
                  <NuxtLink 
                    to="/teacher/register"
                    class="inline-block mt-3 px-4 py-2 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition-colors text-sm"
                  >
                    Submit New Application
                  </NuxtLink>
                </div>
              </div>
            </div>

            <!-- Pending Notification -->
            <div v-if="application.status === 'PENDING'" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mt-4">
              <div class="flex items-start gap-3">
                <svg class="w-6 h-6 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                  <h4 class="font-semibold text-yellow-900 mb-1">Under Review</h4>
                  <p class="text-yellow-800">Your application is currently being reviewed by our admin team. We'll notify you once a decision is made.</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useCookie } from '#app'

interface Application {
  id: string
  user_id: string
  status: 'PENDING' | 'APPROVED' | 'REJECTED'
  full_name: string
  email: string
  certificate_title: string
  issuer: string
  issue_date: string
  expiry_date: string | null
  rejection_reason: string | null
  reviewed_at: string | null
  created_at: string
  updated_at: string
}

const config = useRuntimeConfig()
const applications = ref<Application[]>([])
const loading = ref(true)
const error = ref('')

const formatDate = (dateString: string) => {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const fetchApplications = async () => {
  try {
    loading.value = true
    error.value = ''
    
    const token = useCookie('auth_token')
    
    const response = await $fetch<{ applications: Application[] }>('/api/teacher-applications/my-applications', {
      baseURL: config.public.backendUrl,
      headers: {
        'Authorization': `Bearer ${token.value}`
      }
    })
    
    applications.value = response.applications || []
  } catch (err: any) {
    console.error('Failed to fetch applications:', err)
    error.value = err.data?.message || 'Failed to load applications. Please try again.'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchApplications()
})
</script>
