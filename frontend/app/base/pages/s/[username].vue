<template>
  <div>
    <StudentHome v-if="role === 'STUDENT'" />
    <TeacherHome v-else-if="role === 'TEACHER'" />
    <AdminHome v-else-if="role === 'ADMIN'" />
    <div v-else class="p-12 bg-white rounded-lg shadow-md">
      <h2 class="text-2xl font-bold">Loading...</h2>
      <p class="text-gray-600 mt-1">Please wait while we load your dashboard</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, watch } from 'vue'
import StudentHome from '../../components/templates/StudentHome.vue'
import TeacherHome from '../../components/templates/TeacherHome.vue'
import AdminHome from '../../components/templates/AdminHome.vue'
import { useUserStats } from '../../composables/useUserStats'

const route = useRoute()
const username = (route.params.username as string) || ''

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

// Use auth.user as primary source for role
const role = computed(() => auth.user?.value?.role || currentUser.value?.role || '')
</script>