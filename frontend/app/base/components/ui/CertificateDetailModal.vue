<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="isOpen && certificate" class="fixed inset-0 z-50 flex items-center justify-center p-4">
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

          <div class="bg-gradient-to-r from-brand-primary to-accent-blue p-8 rounded-t-2xl text-white">
            <div class="flex items-center gap-3 mb-2">
              <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
              </svg>
              <h2 class="text-2xl font-bold">Certificate</h2>
            </div>
            <p class="text-white/80 font-mono text-lg">{{ certificate.certificate_number }}</p>
          </div>

          <div class="p-8 space-y-6">
            <section>
              <h3 class="text-lg font-semibold text-text-dark mb-4">Student Information</h3>
              <div class="bg-gray-50 rounded-xl p-4 space-y-3">
                <div>
                  <p class="text-caption text-text-muted">Student Name</p>
                  <p class="text-body font-medium text-text-dark">{{ certificate.student?.first_name }} {{ certificate.student?.last_name }}</p>
                </div>
                <div>
                  <p class="text-caption text-text-muted">Student Email</p>
                  <p class="text-body text-text-dark">{{ certificate.student?.email }}</p>
                </div>
              </div>
            </section>

            <section>
              <h3 class="text-lg font-semibold text-text-dark mb-4">Course Information</h3>
              <div class="bg-gray-50 rounded-xl p-4 space-y-3">
                <div>
                  <p class="text-caption text-text-muted">Course Title</p>
                  <p class="text-body font-medium text-text-dark">{{ certificate.course?.title }}</p>
                </div>
                <div v-if="certificate.final_score">
                  <p class="text-caption text-text-muted">Final Score</p>
                  <p class="text-body font-medium text-text-dark">{{ certificate.final_score }}%</p>
                </div>
              </div>
            </section>

            <section>
              <h3 class="text-lg font-semibold text-text-dark mb-4">Certificate Details</h3>
              <div class="bg-gray-50 rounded-xl p-4">
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <p class="text-caption text-text-muted">Status</p>
                    <span 
                      class="inline-block px-3 py-1 text-caption font-medium rounded-full"
                      :class="{
                        'bg-brand-primary/10 text-brand-primary': certificate.status === 'ISSUED',
                        'bg-accent-orange/10 text-accent-orange': certificate.status === 'PENDING',
                        'bg-accent-red/10 text-accent-red': certificate.status === 'REVOKED'
                      }"
                    >
                      {{ certificate.status }}
                    </span>
                  </div>
                  <div>
                    <p class="text-caption text-text-muted">Issued Date</p>
                    <p class="text-body font-medium text-text-dark">{{ formatDate(certificate.issued_at) }}</p>
                  </div>
                  <div>
                    <p class="text-caption text-text-muted">Certificate ID</p>
                    <p class="text-body font-mono text-sm font-medium text-text-dark">{{ certificate.id }}</p>
                  </div>
                  <div v-if="certificate.pdf_url">
                    <a :href="certificate.pdf_url" target="_blank" class="text-brand-primary hover:text-teal-600 text-body-sm font-medium">
                      Download PDF
                    </a>
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
  certificate: any
}

defineProps<Props>()
defineEmits<{ close: [] }>()

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
