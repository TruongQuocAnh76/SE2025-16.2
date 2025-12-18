<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="isOpen && application" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="$emit('close')"></div>
        
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
          <button 
            @click="$emit('close')"
            class="absolute top-4 right-4 z-10 w-8 h-8 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 transition-colors"
          >
            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>

          <div class="bg-gradient-to-r from-accent-purple to-accent-blue p-8 rounded-t-2xl">
            <div class="flex items-center gap-6">
              <div class="w-20 h-20 rounded-full bg-white shadow-lg flex items-center justify-center overflow-hidden border-4 border-white">
                <img v-if="application.user?.avatar" :src="application.user.avatar" alt="User" class="w-full h-full object-cover" />
                <span v-else class="text-2xl font-bold text-accent-purple">{{ getInitials(application.user) }}</span>
              </div>
              <div class="text-white">
                <h2 class="text-2xl font-bold">{{ application.user?.first_name }} {{ application.user?.last_name }}</h2>
                <p class="text-white/80">@{{ application.user?.username }}</p>
                <span 
                  class="inline-block mt-2 px-3 py-1 rounded-full text-sm font-medium"
                  :class="{
                    'bg-white/30': application.status === 'APPROVED',
                    'bg-white/20': application.status === 'PENDING',
                    'bg-white/20': application.status === 'REJECTED'
                  }"
                >
                  {{ application.status }}
                </span>
              </div>
            </div>
          </div>

          <div class="p-8 space-y-6">
            <section>
              <h3 class="text-lg font-semibold text-text-dark mb-4">Teaching Certificate</h3>
              <div class="bg-gradient-to-br from-accent-purple/5 to-accent-blue/5 border border-accent-purple/20 rounded-xl p-5">
                <h4 class="text-lg font-semibold text-text-dark">{{ application.certificate_title }}</h4>
                <p class="text-text-muted mt-1">Issued by: <span class="font-medium text-text-dark">{{ application.issuer }}</span></p>
                <div class="flex flex-wrap gap-4 mt-3 text-sm">
                  <div v-if="application.issue_date" class="flex items-center gap-1.5 text-text-muted">
                    <span>Issued: {{ formatDate(application.issue_date) }}</span>
                  </div>
                  <div v-if="application.expiry_date" class="flex items-center gap-1.5 text-text-muted">
                    <span>Expires: {{ formatDate(application.expiry_date) }}</span>
                  </div>
                </div>
              </div>
            </section>

            <section v-if="application.status === 'REJECTED' && application.rejection_reason">
              <h3 class="text-lg font-semibold text-text-dark mb-4">Rejection Reason</h3>
              <div class="bg-accent-red/5 border border-accent-red/20 rounded-xl p-4">
                <p class="text-text-dark">{{ application.rejection_reason }}</p>
              </div>
            </section>

            <section>
              <h3 class="text-lg font-semibold text-text-dark mb-4">Application Details</h3>
              <div class="bg-gray-50 rounded-xl p-4">
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <p class="text-caption text-text-muted">Application ID</p>
                    <p class="text-body font-mono text-sm font-medium text-text-dark">{{ application.id }}</p>
                  </div>
                  <div>
                    <p class="text-caption text-text-muted">Submitted</p>
                    <p class="text-body font-medium text-text-dark">{{ formatDate(application.created_at) }}</p>
                  </div>
                  <div v-if="application.reviewed_at">
                    <p class="text-caption text-text-muted">Reviewed</p>
                    <p class="text-body font-medium text-text-dark">{{ formatDate(application.reviewed_at) }}</p>
                  </div>
                  <div v-if="application.reviewer">
                    <p class="text-caption text-text-muted">Reviewed By</p>
                    <p class="text-body font-medium text-text-dark">{{ application.reviewer.first_name }} {{ application.reviewer.last_name }}</p>
                  </div>
                </div>
              </div>
            </section>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
interface Props {
  isOpen: boolean
  application: any
}

defineProps<Props>()
defineEmits<{ close: [] }>()

const getInitials = (user: any): string => {
  if (!user) return '?'
  const first = user.first_name?.[0] || ''
  const last = user.last_name?.[0] || ''
  return (first + last).toUpperCase() || '?'
}

const formatDate = (dateStr: string | null): string => {
  if (!dateStr) return 'N/A'
  const date = new Date(dateStr)
  return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })
}
</script>

<style scoped>
.modal-enter-active, .modal-leave-active { transition: opacity 0.3s ease; }
.modal-enter-active .relative, .modal-leave-active .relative { transition: transform 0.3s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
.modal-enter-from .relative { transform: scale(0.95) translateY(20px); }
.modal-leave-to .relative { transform: scale(0.95) translateY(20px); }
</style>
