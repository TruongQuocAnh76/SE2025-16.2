<template>
  <div class="min-h-screen bg-background p-8">
    <div class="max-w-7xl mx-auto">
      <!-- Header -->
      <div class="flex items-center justify-between mb-8">
        <div>
          <h1 class="text-h2 font-bold text-text-dark">All Courses</h1>
          <p class="text-body text-text-muted mt-1">Manage all courses in the system</p>
        </div>
        <div class="text-body text-text-muted">
          Total: <span class="font-semibold text-text-dark">{{ total }}</span>
        </div>
      </div>

      <!-- Courses Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div 
          v-for="course in courses" 
          :key="course.id"
          class="bg-white rounded-2xl shadow-md overflow-hidden hover:shadow-lg transition-shadow cursor-pointer"
          @click="openCourseModal(course)"
        >
          <div class="h-48 bg-gradient-to-br from-accent-blue to-brand-primary"></div>
          <div class="p-6">
            <h3 class="text-h5 font-semibold text-text-dark mb-2">{{ course.title }}</h3>
            <p class="text-body-sm text-text-muted mb-4 line-clamp-2">{{ course.description || 'No description' }}</p>
            <div class="flex items-center gap-2 mb-3">
              <span class="text-caption text-text-muted">By:</span>
              <span class="text-body-sm font-medium text-text-dark">{{ course.teacher?.first_name }} {{ course.teacher?.last_name }}</span>
            </div>
            <div class="flex items-center justify-between">
              <span 
                class="px-3 py-1 text-caption font-medium rounded-full"
                :class="{
                  'bg-brand-primary/10 text-brand-primary': course.status === 'PUBLISHED',
                  'bg-accent-orange/10 text-accent-orange': course.status === 'PENDING',
                  'bg-gray-100 text-text-muted': course.status === 'DRAFT'
                }"
              >
                {{ course.status }}
              </span>
              <span class="text-caption text-text-muted">{{ formatDate(course.created_at) }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div class="mt-8 flex items-center justify-between">
        <div class="text-body-sm text-text-muted">
          Showing {{ from }} to {{ to }} of {{ total }} courses
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

    <!-- Course Detail Modal -->
    <CourseDetailModal
      :isOpen="courseModalOpen"
      :course="selectedCourse"
      @close="closeCourseModal"
    />
  </div>
</template>

<script setup lang="ts">
import { useAdminList } from '../../composables/useAdminList'
import CourseDetailModal from '../../components/ui/CourseDetailModal.vue'

const { listCourses } = useAdminList()

const courses = ref<any[]>([])
const currentPage = ref(1)
const lastPage = ref(1)
const total = ref(0)
const from = ref(0)
const to = ref(0)
const loading = ref(true)

const courseModalOpen = ref(false)
const selectedCourse = ref<any>(null)

const openCourseModal = (course: any) => {
  selectedCourse.value = course
  courseModalOpen.value = true
}

const closeCourseModal = () => {
  courseModalOpen.value = false
  selectedCourse.value = null
}

const fetchCourses = async (page = 1) => {
  loading.value = true
  const response = await listCourses(page, 20)
  if (response) {
    courses.value = response.data
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
    fetchCourses(page)
  }
}

const formatDate = (dateStr: string): string => {
  const date = new Date(dateStr)
  return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
}

onMounted(() => {
  fetchCourses()
})
</script>
