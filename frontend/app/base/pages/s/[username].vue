<template>
  <div>
    <StudentHome v-if="role === 'STUDENT'" />
    <TeacherHome v-else-if="role === 'TEACHER'" />
    <div v-else class="p-12 bg-white rounded-lg shadow-md">
      <h2 class="text-2xl font-bold">Admin Dashboard</h2>
      <p class="text-gray-600 mt-1">Admin UI coming soon</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, watch } from 'vue'
import StudentHome from '../../components/templates/StudentHome.vue'
import TeacherHome from '../../components/templates/TeacherHome.vue'
import { useUserStats } from '../../composables/useUserStats'

const route = useRoute()
const username = (route.params.username as string) || ''

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
  const fallback = user ? (user.username || user.email || '') : ''
  return getDisplayName(user, fallback)
})
const role = computed(() => currentUser.value?.role || '')
</script>