<template>
  <div class="min-h-screen bg-background">
    <!-- Search Bar Hero Section -->
    <CourseSearchBar @search="handleSearch" />

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 py-12">
      <!-- Page Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-text-dark mb-2">
          {{ searchQuery ? `Search Results for "${searchQuery}"` : 'All Courses' }}
        </h1>
        <p class="text-text-muted">
          {{ searchQuery ? `${courses.length} courses found` : 'Discover courses to advance your career' }}
        </p>
      </div>

      <!-- Filters and Controls -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
        <!-- Filters -->
        <div class="flex flex-wrap gap-4">
          <select
            v-model="filters.status"
            @change="fetchCourses"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-primary focus:border-brand-primary"
          >
            <option value="">All Status</option>
            <option value="PUBLISHED">Published</option>
            <option value="DRAFT">Draft</option>
          </select>

          <select
            v-model="filters.level"
            @change="fetchCourses"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-primary focus:border-brand-primary"
          >
            <option value="">All Levels</option>
            <option value="BEGINNER">Beginner</option>
            <option value="INTERMEDIATE">Intermediate</option>
            <option value="ADVANCED">Advanced</option>
            <option value="EXPERT">Expert</option>
          </select>
        </div>

        <!-- Clear Search -->
        <button
          v-if="searchQuery"
          @click="clearSearch"
          class="px-4 py-2 text-brand-primary hover:text-brand-secondary font-medium transition-colors"
        >
          Clear Search
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-brand-primary"></div>
      </div>

      <!-- Courses Grid -->
      <div v-else-if="courses.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 items-stretch">
        <NuxtLink
          v-for="course in courses"
          :key="course.id"
          :to="`/courses/${course.id}`"
        >
          <CourseCard :course="mapCourseForCard(course)" />
        </NuxtLink>
      </div>

      <!-- Empty State -->
      <div v-else class="text-center py-12">
        <svg class="mx-auto h-24 w-24 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
        </svg>
        <h3 class="text-lg font-medium text-text-dark mb-2">
          {{ searchQuery ? 'No courses found' : 'No courses available' }}
        </h3>
        <p class="text-text-muted">
          {{ searchQuery ? 'Try adjusting your search terms or filters' : 'Check back later for new courses' }}
        </p>
      </div>

      <!-- Load More Button (if using pagination) -->
      <div v-if="hasNextPage" class="text-center mt-12">
        <button
          @click="loadMore"
          :disabled="loading"
          class="px-6 py-3 bg-brand-primary hover:bg-brand-secondary text-white font-semibold rounded-lg transition-colors duration-200 disabled:opacity-50"
        >
          Load More Courses
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { Course } from '../../types/course'
import CourseCard from '../../components/ui/CourseCard.vue'

const { getCourses, searchCourses } = useCourses()

// State
const courses = ref<Course[]>([])
const loading = ref(false)
const searchQuery = ref('')
const hasNextPage = ref(false)

// Filters
const filters = ref({
  status: '',
  level: ''
})

// Search handler
const handleSearch = async (query: string) => {
  searchQuery.value = query
  await performSearch(query)
}

// Clear search
const clearSearch = () => {
  searchQuery.value = ''
  filters.value = { status: '', level: '' }
  fetchCourses()
}

// Perform search
const performSearch = async (query: string) => {
  loading.value = true
  try {
    const result = await searchCourses(query)
    if (result) {
      courses.value = result.data
    }
  } catch (error) {
    console.error('Search failed:', error)
    courses.value = []
  } finally {
    loading.value = false
  }
}

// Fetch courses with filters
const fetchCourses = async () => {
  loading.value = true
  try {
    let result: Course[] = []

    if (searchQuery.value) {
      // Use search API when there's a search query
      const searchResult = await searchCourses(searchQuery.value)
      result = searchResult?.data || []
    } else {
      // Use regular courses API for browsing
      const coursesResult: any = await getCourses({
        status: filters.value.status || undefined,
        level: filters.value.level || undefined
      })
      result = Array.isArray(coursesResult) ? coursesResult : coursesResult?.data || []
    }

    courses.value = result
    hasNextPage.value = false // No pagination for now
  } catch (error) {
    console.error('Failed to fetch courses:', error)
    courses.value = []
  } finally {
    loading.value = false
  }
}

// Load more courses (for pagination)
const loadMore = async () => {
  // Implementation for pagination would go here
  console.log('Load more courses...')
}

// Map course data for CourseCard component
const mapCourseForCard = (course: Course) => {
  return {
    thumbnail: course.thumbnail,
    duration: typeof course.duration === 'number' ? `${course.duration}h` : (course.duration || '2h 30m'),
    name: course.title,
    description: course.description,
    author: `${course.teacher?.first_name || ''} ${course.teacher?.last_name || ''}`.trim() || 'Unknown Author',
    price: course.price
  }
}

// Initial load
onMounted(() => {
  fetchCourses()
})

// Page meta
definePageMeta({
  layout: 'default'
})

// SEO
useHead({
  title: 'Courses - CertChain',
  meta: [
    { name: 'description', content: 'Browse and search for courses on CertChain platform' }
  ]
})
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  line-clamp: 2;
}

.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
  line-clamp: 3;
}
</style>