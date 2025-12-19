<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="isOpen && user" class="fixed inset-0 z-50 flex items-center justify-center p-4">
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
              <div class="w-24 h-24 rounded-full bg-white shadow-lg flex items-center justify-center overflow-hidden border-4 border-white">
                <img v-if="user.avatar" :src="user.avatar" :alt="user.username" class="w-full h-full object-cover" />
                <span v-else class="text-3xl font-bold text-accent-purple">{{ getInitials(user) }}</span>
              </div>
              <div class="text-white">
                <h2 class="text-2xl font-bold">{{ user.first_name }} {{ user.last_name }}</h2>
                <p class="text-white/80 text-lg">@{{ user.username }}</p>
                <span class="inline-block mt-2 px-3 py-1 bg-white/20 rounded-full text-sm font-medium">
                  {{ user.role }}
                </span>
              </div>
            </div>
          </div>

          <div class="p-8 space-y-6">
            <section>
              <h3 class="text-lg font-semibold text-text-dark mb-4">Contact Information</h3>
              <div class="bg-gray-50 rounded-xl p-4 space-y-3">
                <div class="flex items-center gap-3">
                  <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                  </svg>
                  <span class="text-text-dark">{{ user.email }}</span>
                </div>
                <div class="flex items-center gap-3">
                  <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                  <span class="text-text-dark">User ID: {{ user.id }}</span>
                </div>
              </div>
            </section>

            <section v-if="user.bio">
              <h3 class="text-lg font-semibold text-text-dark mb-4">Bio</h3>
              <div class="bg-gray-50 rounded-xl p-4">
                <p class="text-text-muted leading-relaxed">{{ user.bio }}</p>
              </div>
            </section>

            <section>
              <h3 class="text-lg font-semibold text-text-dark mb-4">Account Details</h3>
              <div class="bg-gray-50 rounded-xl p-4">
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <p class="text-caption text-text-muted">Status</p>
                    <p class="text-body font-medium" :class="user.is_active ? 'text-brand-primary' : 'text-accent-red'">
                      {{ user.is_active ? 'Active' : 'Inactive' }}
                    </p>
                  </div>
                  <div>
                    <p class="text-caption text-text-muted">Joined</p>
                    <p class="text-body font-medium text-text-dark">{{ formatDate(user.created_at) }}</p>
                  </div>
                  <div v-if="user.auth_provider">
                    <p class="text-caption text-text-muted">Auth Provider</p>
                    <p class="text-body font-medium text-text-dark">{{ user.auth_provider }}</p>
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
import type { User } from '../../types/user'

interface Props {
  isOpen: boolean
  user: User | null
}

defineProps<Props>()
defineEmits<{ close: [] }>()

const getInitials = (user: User): string => {
  const first = user.first_name?.[0] || ''
  const last = user.last_name?.[0] || ''
  return (first + last).toUpperCase() || '?'
}

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
