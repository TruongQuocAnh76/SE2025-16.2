<template>
  <div class="min-h-screen bg-gray-50">
    <TeacherHome v-if="role === 'TEACHER'" />
    <AdminHome v-else-if="role === 'ADMIN'" />
    <StudentHome v-else />
  </div>
</template>

<script setup lang="ts">
import StudentHome from '../../components/templates/StudentHome.vue'
import TeacherHome from '../../components/templates/TeacherHome.vue'
import AdminHome from '../../components/templates/AdminHome.vue'

const auth = useAuth()
const role = computed(() => auth.user?.value?.role || 'STUDENT')
</script>