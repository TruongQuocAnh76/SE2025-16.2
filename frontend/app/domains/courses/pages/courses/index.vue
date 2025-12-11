<template>
  <div class="min-h-screen bg-background">



    <!-- Search Bar Hero Section -->
    <CourseSearchBar @search="handleSearch" />

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 py-12 mb-16">
      <!-- Page Header -->
      <div class="mb-8">
        <h1 class="text-4xl font-bold text-text-dark mb-2">
          {{ searchQuery ? 'Search Results' : 'Browse Courses' }}
        </h1>
        <p class="text-text-muted">
          {{ searchQuery ? `${courses?.length || 0} courses found` : 'Discover courses to advance your career' }}
        </p>
      </div>

      <!-- Filters and Controls -->
      <div v-if="filters" class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
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
      <div v-else-if="courses && courses.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 items-stretch">
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

    <!-- Know about learning -->
    <section class="bg-white py-20">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-16 items-center">
          
          <div class="relative lg:col-span-2">
            <div class="absolute left-0 top-0 h-full w-1.5 bg-teal-400 rounded-full -translate-x-4"></div>
            
            <div class="pl-6">
              <h2 class="text-4xl font-bold text-gray-900 mb-6 leading-tight">
                Know about learning<br>learning platform
              </h2>
              <ul class="space-y-3 mb-8 text-lg text-gray-700">
                <li>Free E-book, video & consolation</li>
                <li>Top instructors from around world</li>
                <li>Top courses from your team</li>
              </ul>
              <button class="bg-teal-500 hover:bg-teal-600 text-white px-8 py-4 rounded-lg font-semibold transition shadow-lg text-lg">
                Start learning now
              </button>
            </div>
          </div>

          <div class="relative min-h-[500px] hidden lg:block lg:col-span-3">
            <div class="absolute inset-x-0 top-0 h-[450px] bg-blue-50 rounded-[40px] shadow-lg"></div>
            
            <div class="absolute top-6 left-8 flex space-x-2 z-10">
              <span class="block w-3.5 h-3.5 bg-red-500 rounded-full border border-red-600/50"></span>
              <span class="block w-3.5 h-3.5 bg-yellow-400 rounded-full border border-yellow-500/50"></span>
              <span class="block w-3.5 h-3.5 bg-green-500 rounded-full border border-green-600/50"></span>
            </div>

            <div class="absolute top-16 left-8 w-56 z-10">
              <div class="relative">
                <img :src="callParticipants.main.img" :alt="callParticipants.main.name" class="w-full h-auto object-cover rounded-2xl shadow-lg border-4 border-white">
                <span class="absolute bottom-2 left-2 text-xs font-semibold text-white bg-black/50 px-2 py-1 rounded-full">{{ callParticipants.main.name }}</span>
              </div>
            </div>

            <div class="absolute bottom-20 left-10 z-20 flex space-x-4">
              <button class="bg-blue-600 text-white px-8 py-3 rounded-full font-semibold shadow-xl hover:bg-blue-700 transition text-lg">Pres</button>
              <button class="bg-pink-500 text-white px-8 py-3 rounded-full font-semibold shadow-xl hover:bg-pink-600 transition text-lg">Cal</button>
            </div>

            <div class="absolute top-1/2 left-[42%] -translate-x-1/2 -translate-y-1/4 w-16 h-16 bg-white rounded-full shadow-lg z-20 border-4 border-blue-50"></div>

            <div class="absolute top-16 right-6 w-[330px] z-10">
              <div class="grid grid-cols-2 gap-4">
                
                <div class="relative w-full">
                    <img :src="callParticipants.p1.img" :alt="callParticipants.p1.name" class="w-full h-40 object-cover rounded-2xl shadow-lg border-4 border-white">
                    <span class="absolute bottom-2 left-2 text-xs font-semibold text-white bg-black/50 px-2 py-1 rounded-full">{{ callParticipants.p1.name }}</span>
                </div>

                <div class="relative w-full">
                    <img :src="callParticipants.p2.img" :alt="callParticipants.p2.name" class="w-full h-40 object-cover rounded-2xl shadow-lg border-4 border-white">
                    <span class="absolute bottom-2 left-2 text-xs font-semibold text-white bg-black/50 px-2 py-1 rounded-full">{{ callParticipants.p2.name }}</span>
                </div>

                <div class="relative w-full">
                    <img :src="callParticipants.p3.img" :alt="callParticipants.p3.name" class="w-full h-40 object-cover rounded-2xl shadow-lg border-4 border-white">
                    <span class="absolute bottom-2 left-2 text-xs font-semibold text-white bg-black/50 px-2 py-1 rounded-full">{{ callParticipants.p3.name }}</span>
                </div>

                <div class="relative w-full">
                    <img :src="callParticipants.p4.img" :alt="callParticipants.p4.name" class="w-full h-40 object-cover rounded-2xl shadow-lg border-4 border-white">
                    <span class="absolute bottom-2 left-2 text-xs font-semibold text-white bg-black/50 px-2 py-1 rounded-full">{{ callParticipants.p4.name }}</span>
                </div>

              </div>
            </div>
            
          </div>
        </div>
      </div>
    </section>

        <!-- Recommended for You Section -->
    <RecommendedCourses />

    <!-- what our students have to say-->
     <section class="py-20 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <h2 class="text-3xl font-bold text-gray-900 mb-12">What our students have to say</h2>

        <div class="relative bg-blue-50 rounded-3xl p-8 md:p-12 overflow-hidden">
          <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
            
            <div class="lg:col-span-5 flex justify-center items-center">
              <div class="relative">
                <div class="absolute w-56 h-56 bg-blue-200 rounded-full top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 opacity-70"></div>
                <div class="absolute w-32 h-32 bg-yellow-200 rounded-full -top-10 left-1/2 z-10 opacity-70"></div>
                <div class="absolute w-40 h-40 bg-green-200 rounded-full -bottom-10 -left-10 z-10 opacity-70"></div>
                <div class="absolute w-36 h-36 bg-orange-200 rounded-full -bottom-10 -right-10 z-10 opacity-70"></div>
                
                <img 
                  :src="mainTestimonial.avatar"
                  :alt="mainTestimonial.name"
                  class="relative z-20 w-80 h-80 object-cover rounded-full shadow-lg border-8 border-blue-50"
                />
              </div>
            </div>

            <div class="lg:col-span-6 z-10">
              <h3 class="text-4xl font-bold text-gray-900 mb-2">{{ mainTestimonial.name }}</h3>
              <p class="text-lg text-gray-600 mb-6">{{ mainTestimonial.email }}</p>
              
              <div class="space-y-4 text-gray-700 text-lg">
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor</p>
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor</p>
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor</p>
              </div>

              <div class="mt-8">
                <a href="#" class="text-teal-500 hover:text-teal-600">
                  <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path fill-rule="evenodd" d="M12.315 2c-4.368 0-4.89 0-6.617.098-1.728.08-2.922.37-3.951.74-1.066.38-1.986.91-2.85 1.77C-1.025 6.42.0 7.34.38 8.406c.37 1.03.66 2.222.74 3.95C1.21 14.078 1.21 14.6 1.21 18.97s0 4.89.098 6.616c.08 1.728.37 2.922.74 3.951.38 1.066.91 1.986 1.77 2.85 1.78 1.78 3.33 2.16 5.8 2.24 1.728.08 2.248.098 6.616.098s4.89 0 6.617-.098c2.47-.08 3.99-.46 5.798-2.24 1.066-.38 1.986-.91 2.85-1.77.86-.86 1.39-1.78 1.77-2.85.37-1.03.66-2.222.74-3.95C22.79 19.922 22.79 19.4 22.79 15.03s0-4.89-.098-6.616c-.08-1.728-.37-2.922-.74-3.951-.38-1.066-.91-1.986-1.77-2.85C18.415.46 16.86.08 14.39.0 12.66 0 12.13 0 7.86 0zm0 2.16c4.305 0 4.78 0 6.468.092 1.57.07 2.44.33 2.97.51.61.21 1.03.49 1.48.94.46.46.73 1.06.94 1.48.18.53.44 1.4.51 2.97.092 1.688.092 2.162.092 6.468s0 4.78-.092 6.468c-.07 1.57-.33 2.44-.51 2.97-.21.61-.49 1.03-.94 1.48-.46.46-1.06.73-1.48.94-.53.18-1.4.44-2.97.51-1.688.092-2.162.092-6.468.092s-4.78 0-6.468-.092c-1.57-.07-2.44-.33-2.97-.51-.61-.21-1.03-.49-1.48-.94-.46-.46-.73-1.06-.94-1.48-.18-.53-.44-1.4-.51-2.97-.092-1.688-.092-2.162-.092-6.468s0-4.78.092-6.468c.07-1.57.33-2.44.51-2.97.21-.61.49-1.03.94-1.48.46-.46 1.06-.73 1.48-.94.53-.18 1.4.44 2.97.51C7.86 2.16 8.33 2.16 12.68 2.16zM12 6.88c-3.14 0-5.71 2.57-5.71 5.71s2.57 5.71 5.71 5.71 5.71-2.57 5.71-5.71-2.57-5.71-5.71-5.71zm0 9.26c-1.96 0-3.55-1.59-3.55-3.55s1.59-3.55 3.55-3.55 3.55 1.59 3.55 3.55-1.59 3.55-3.55 3.55zM18.88 5.42c-.78 0-1.41.63-1.41 1.41s.63 1.41 1.41 1.41 1.41-.63 1.41-1.41-.63-1.41-1.41-1.41z" clip-rule="evenodd" />
                  </svg>
                </a>
              </div>
            </div>

            <div class="lg:col-span-1 flex lg:flex-col justify-center items-center space-x-3 lg:space-x-0 lg:space-y-4 z-10">
              <img v-for="avatar in otherTestimonials" :key="avatar.id" :src="avatar.img" :alt="avatar.name" class="w-14 h-14 rounded-full object-cover shadow-md border-4 border-blue-50">
            </div>

          </div>
        </div>
      </div>
    </section>

    <!-- Become an Instructor Section -->
    <section v-if="isAdminOrTeacher" class="bg-accent-blue bg-opacity-20 py-16 px-8 mt-16 mx-12 rounded-3xl mb-16">
      <div class="max-w-7xl mx-auto px-4">
        <div class="flex items-center justify-between">
          <!-- Text and Button -->
          <div class="max-w-lg">
            <h2 class="text-3xl font-bold text-dark mb-4">Share your knowledge with the world</h2>
            <p class="text-dark text-lg mb-8">
              Create and publish your own courses on Certchain. Upload lessons, videos and quizzes - your students will earn verified blockchain certificates upon completion.
            </p>
            <NuxtLink
              to="/courses/add"
              class="inline-block px-8 py-3 bg-brand-primary text-white font-semibold rounded-lg hover:bg-gray-100 transition-colors"
            >
              Add a Course Now
            </NuxtLink>
          </div>
          <!-- Image -->
          <div class="ml-8">
            <img src="../../public/add_course.png" alt="Add Course" class="max-w-md h-auto" />
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import type { Course } from '../../types/course'
import RecommendedCourses from '../../../../base/RecommendedCourses.vue'
// import CourseCard from '../../components/ui/CourseCard.vue'
import { ref } from 'vue'

const { getCourses, searchCourses } = useCourses()
const config = useRuntimeConfig()

// State
const courses = ref<Course[]>([])
const loading = ref(false)
const searchQuery = ref('')
const hasNextPage = ref(false)

// User role check
const { user } = useAuth()
const isAdminOrTeacher = computed(() => true) // TODO: implement proper role check

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
        status: filters.value?.status || undefined,
        level: filters.value?.level || undefined
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
  // TODO: Implement pagination logic
  console.log('Load more courses...')
}

// Map course data for CourseCard component
const mapCourseForCard = (course: Course) => {
  let thumbnailUrl = 'placeholder-course.jpg'
  if (course.thumbnail) {
    thumbnailUrl = `${course.thumbnail}`
  }
  return {
    thumbnail: thumbnailUrl,
    duration: typeof course.duration === 'number' ? `${course.duration}h` : (course.duration || '2h 30m'),
    name: course.title,
    description: course.description,
    author: `${course.teacher?.first_name || ''} ${course.teacher?.last_name || ''}`.trim() || 'Unknown Author',
    price: course.price
  }
}

const callParticipants = ref({
  main: { 
    name: 'Eveny Howard', 
    img: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=224&h=280&fit=crop' 
  },
  p1: { 
    name: 'Tamara Clarke', 
    img: 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=160&h=200&fit=crop' 
  },
  p2: { 
    name: 'Adam Levin', 
    img: 'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?w=192&h=240&fit=crop' 
  },
  p3: { 
    name: 'Humbert Holland', 
    img: 'https://images.unsplash.com/photo-1521119989659-a83eee488004?w=160&h=200&fit=crop' 
  },
  p4: { 
    name: 'Patricia Mendoza', 
    img: 'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=176&h=220&fit=crop' 
  },
});

const mainTestimonial = ref({
  name: 'Savannah Nguyen',
  email: 'tanya.hill@example.com',
  avatar: 'https://images.unsplash.com/photo-1570295999919-56ceb5ecca61?w=320&h=320&fit=crop' // Ảnh Savannah
});

const otherTestimonials = ref([
  { id: 1, name: 'Avatar 1', img: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=56&h=56&fit=crop' },
  { id: 2, name: 'Avatar 2', img: 'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?w=56&h=56&fit=crop' },
  { id: 3, name: 'Avatar 3', img: 'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=56&h=56&fit=crop' },
  { id: 4, name: 'Avatar 4', img: 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=56&h=56&fit=crop' }
]);

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