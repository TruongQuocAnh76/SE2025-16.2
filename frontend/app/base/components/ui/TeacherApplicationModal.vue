<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="$emit('close')"></div>
        
        <!-- Modal Content -->
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
          <!-- Close Button -->
          <button 
            @click="$emit('close')"
            class="absolute top-4 right-4 z-10 w-8 h-8 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 transition-colors"
          >
            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>

          <!-- CV Header -->
          <div class="bg-gradient-to-r from-accent-purple to-accent-blue p-8 rounded-t-2xl">
            <div class="flex items-center gap-6">
              <!-- Avatar -->
              <div class="w-24 h-24 rounded-full bg-white shadow-lg flex items-center justify-center overflow-hidden border-4 border-white">
                <img 
                  v-if="application?.avatar_url"
                  :src="application.avatar_url"
                  :alt="application.full_name"
                  class="w-full h-full object-cover"
                />
                <span v-else class="text-3xl font-bold text-accent-purple">
                  {{ getInitials(application?.full_name || '') }}
                </span>
              </div>

              <!-- Name & Title -->
              <div class="text-white">
                <h2 class="text-2xl font-bold">{{ application?.full_name }}</h2>
                <p class="text-white/80 text-lg">{{ application?.email }}</p>
                <div class="flex items-center gap-2 mt-2" v-if="application?.phone">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                  </svg>
                  <span class="text-white/90 text-sm">{{ application.phone }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- CV Body -->
          <div class="p-8 space-y-6">
            <!-- Contact Information -->
            <section>
              <h3 class="text-lg font-semibold text-text-dark mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-accent-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                Contact Information
              </h3>
              <div class="bg-gray-50 rounded-xl p-4 space-y-3">
                <div class="flex items-center gap-3">
                  <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                  </svg>
                  <span class="text-text-dark">{{ application?.email }}</span>
                </div>
                <div v-if="application?.phone" class="flex items-center gap-3">
                  <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                  </svg>
                  <span class="text-text-dark">{{ application.phone }}</span>
                </div>
                <div v-if="application?.date_of_birth" class="flex items-center gap-3">
                  <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                  <span class="text-text-dark">Date of Birth: {{ formatDate(application.date_of_birth) }}</span>
                </div>
                <div v-if="application?.country" class="flex items-center gap-3">
                  <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <span class="text-text-dark">{{ application.country }}</span>
                </div>
                <div v-if="application?.gender" class="flex items-center gap-3">
                  <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                  <span class="text-text-dark">{{ application.gender }}</span>
                </div>
                <div class="flex items-center gap-3">
                  <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                  <span class="text-text-dark text-sm">User ID: {{ application?.user_id }}</span>
                </div>
              </div>
            </section>

            <!-- Bio / About -->
            <section v-if="application?.bio">
              <h3 class="text-lg font-semibold text-text-dark mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-accent-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                About
              </h3>
              <div class="bg-gray-50 rounded-xl p-4">
                <p class="text-text-muted leading-relaxed">{{ application.bio }}</p>
              </div>
            </section>

            <!-- Teaching Certificate -->
            <section>
              <h3 class="text-lg font-semibold text-text-dark mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-accent-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                </svg>
                Teaching Certificate
              </h3>
              <div class="bg-gradient-to-br from-accent-purple/5 to-accent-blue/5 border border-accent-purple/20 rounded-xl p-5">
                <div class="flex items-start gap-4">
                  <div class="w-12 h-12 bg-accent-purple/10 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-accent-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                    </svg>
                  </div>
                  <div class="flex-1">
                    <h4 class="text-lg font-semibold text-text-dark">{{ application?.certificate_title }}</h4>
                    <p class="text-text-muted mt-1">Issued by: <span class="font-medium text-text-dark">{{ application?.issuer }}</span></p>
                    <div class="flex flex-wrap gap-4 mt-3 text-sm">
                      <div v-if="application?.issue_date" class="flex items-center gap-1.5 text-text-muted">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>Issued: {{ formatDate(application.issue_date) }}</span>
                      </div>
                      <div v-if="application?.expiry_date" class="flex items-center gap-1.5 text-text-muted">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Expires: {{ formatDate(application.expiry_date) }}</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </section>

            <!-- Certificate Document -->
            <section v-if="application?.certificate_file_path">
              <h3 class="text-lg font-semibold text-text-dark mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-accent-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                Certificate Document
              </h3>
              <div class="bg-gradient-to-br from-blue-50 to-purple-50 border border-blue-200 rounded-xl p-5">
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-white rounded-lg flex items-center justify-center shadow-sm">
                      <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                      </svg>
                    </div>
                    <div>
                      <p class="font-medium text-text-dark">Certificate Document</p>
                      <p class="text-sm text-text-muted">Uploaded certificate file</p>
                    </div>
                  </div>
                  <a 
                    :href="getCertificateUrl(application.id)" 
                    target="_blank"
                    class="px-4 py-2 bg-brand-primary text-white font-medium rounded-lg hover:bg-teal-600 transition-colors flex items-center gap-2"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    View Certificate
                  </a>
                </div>
              </div>
            </section>

            <!-- Application Details -->
            <section>
              <h3 class="text-lg font-semibold text-text-dark mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-accent-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Application Details
              </h3>
              <div class="bg-gray-50 rounded-xl p-4">
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <p class="text-caption text-text-muted">Application ID</p>
                    <p class="text-body font-medium text-text-dark font-mono text-sm">{{ application?.id }}</p>
                  </div>
                  <div>
                    <p class="text-caption text-text-muted">Submitted On</p>
                    <p class="text-body font-medium text-text-dark">{{ formatDate(application?.created_at) }}</p>
                  </div>
                  <div>
                    <p class="text-caption text-text-muted">Status</p>
                    <p class="text-body font-medium" :class="{
                      'text-yellow-600': application?.status === 'PENDING',
                      'text-green-600': application?.status === 'APPROVED',
                      'text-red-600': application?.status === 'REJECTED'
                    }">{{ application?.status }}</p>
                  </div>
                  <div v-if="application?.reviewed_at">
                    <p class="text-caption text-text-muted">Reviewed On</p>
                    <p class="text-body font-medium text-text-dark">{{ formatDate(application.reviewed_at) }}</p>
                  </div>
                </div>
              </div>
            </section>

            <!-- Action Buttons -->
            <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
              <button 
                @click="application?.id && $emit('approve', application.id)"
                class="flex-1 px-6 py-3 bg-brand-primary text-white font-medium rounded-xl hover:bg-teal-600 transition-colors flex items-center justify-center gap-2"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Approve Application
              </button>
              <button 
                @click="application?.id && $emit('reject', application.id)"
                class="flex-1 px-6 py-3 border border-accent-red text-accent-red font-medium rounded-xl hover:bg-accent-red/5 transition-colors flex items-center justify-center gap-2"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                Reject Application
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import type { TeacherApplication } from '../../composables/useAdminStats'

interface Props {
  isOpen: boolean
  application: TeacherApplication | null
}

defineProps<Props>()

defineEmits<{
  close: []
  approve: [id: string]
  reject: [id: string]
}>()

const config = useRuntimeConfig()

const getInitials = (name: string): string => {
  if (!name) return '?'
  return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase()
}

const formatDate = (dateStr: string | null | undefined): string => {
  if (!dateStr) return 'N/A'
  const date = new Date(dateStr)
  return date.toLocaleDateString('en-US', { 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric' 
  })
}

const getCertificateUrl = (applicationId: string): string => {
  const token = useCookie('auth_token').value
  return `${config.public.backendUrl}/api/teacher-applications/${applicationId}/certificate?token=${token}`
}
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-active .relative,
.modal-leave-active .relative {
  transition: transform 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-from .relative {
  transform: scale(0.95) translateY(20px);
}

.modal-leave-to .relative {
  transform: scale(0.95) translateY(20px);
}
</style>