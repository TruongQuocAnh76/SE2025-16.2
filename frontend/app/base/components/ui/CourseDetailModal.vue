<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="isOpen && course" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="$emit('close')"></div>
        
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto">
          <button 
            @click="$emit('close')"
            class="absolute top-4 right-4 z-10 w-8 h-8 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 transition-colors"
          >
            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>

          <div class="h-64 bg-gradient-to-br from-accent-blue to-brand-primary"></div>

          <div class="p-8 space-y-6">
            <div>
              <h2 class="text-2xl font-bold text-text-dark">{{ course.title }}</h2>
              <p class="text-body text-text-muted mt-2">{{ course.description }}</p>
            </div>

            <section>
              <h3 class="text-lg font-semibold text-text-dark mb-4">Course Information</h3>
              <div class="bg-gray-50 rounded-xl p-4 space-y-3">
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <p class="text-caption text-text-muted">Instructor</p>
                    <p class="text-body font-medium text-text-dark">{{ course.teacher?.first_name }} {{ course.teacher?.last_name }}</p>
                  </div>
                  <div>
                    <p class="text-caption text-text-muted">Status</p>
                    <span 
                      class="inline-block px-3 py-1 text-caption font-medium rounded-full"
                      :class="{
                        'bg-brand-primary/10 text-brand-primary': course.status === 'PUBLISHED',
                        'bg-accent-orange/10 text-accent-orange': course.status === 'PENDING',
                        'bg-gray-100 text-text-muted': course.status === 'DRAFT'
                      }"
                    >
                      {{ course.status }}
                    </span>
                  </div>
                  <div v-if="course.level">
                    <p class="text-caption text-text-muted">Level</p>
                    <p class="text-body font-medium text-text-dark">{{ course.level }}</p>
                  </div>
                  <div v-if="course.duration">
                    <p class="text-caption text-text-muted">Duration</p>
                    <p class="text-body font-medium text-text-dark">{{ course.duration }} hours</p>
                  </div>
                  <div v-if="course.price !== null && course.price !== undefined">
                    <p class="text-caption text-text-muted">Price</p>
                    <p class="text-body font-medium text-text-dark">${{ course.price }}</p>
                  </div>
                </div>
              </div>
            </section>

            <section>
              <h3 class="text-lg font-semibold text-text-dark mb-4">Metadata</h3>
              <div class="bg-gray-50 rounded-xl p-4">
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <p class="text-caption text-text-muted">Course ID</p>
                    <p class="text-body font-mono text-sm font-medium text-text-dark">{{ course.id }}</p>
                  </div>
                  <div>
                    <p class="text-caption text-text-muted">Created</p>
                    <p class="text-body font-medium text-text-dark">{{ formatDate(course.created_at) }}</p>
                  </div>
                  <div v-if="course.published_at">
                    <p class="text-caption text-text-muted">Published</p>
                    <p class="text-body font-medium text-text-dark">{{ formatDate(course.published_at) }}</p>
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
  course: any
}

defineProps<Props>()
defineEmits<{ close: [] }>()

const formatDate = (dateStr: string): string => {
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
