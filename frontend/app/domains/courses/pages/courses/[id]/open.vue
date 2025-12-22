<template>
  <div class="min-h-screen bg-gray-100">
    <div v-if="loading" class="flex justify-center items-center min-h-[60vh]">
      <div class="animate-spin rounded-full h-16 w-16 border-b-4 border-teal-500"></div>
    </div>

    <div v-else-if="error" class="text-center py-20">
      <h2 class="text-2xl font-bold text-red-600 mb-4">Unable to load course</h2>
      <p class="text-gray-500">{{ error }}</p>
      <NuxtLink to="/courses" class="mt-4 inline-block text-teal-500 hover:underline">
        Back to courses
      </NuxtLink>
    </div>

    <div v-else class="flex">
      <!-- Left Sidebar -->
      <div class="w-64 bg-gray-200 min-h-screen p-4 flex-shrink-0">
        <!-- Back Button -->
        <button @click="goBack" class="w-10 h-10 bg-teal-500 text-white rounded flex items-center justify-center mb-4 hover:bg-teal-600">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
        </button>

        <!-- Lessons Section -->
        <div class="mb-6">
          <div class="flex items-center justify-between mb-3">
            <h3 class="font-bold text-gray-800">Lesson</h3>
            <button 
              v-if="isTeacher" 
              @click="showAddLesson = true"
              class="bg-green-500 text-white text-xs px-2 py-1 rounded flex items-center hover:bg-green-600"
            >
              <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
              </svg>
              Add Lesson
            </button>
          </div>
          
          <div class="space-y-2">
            <div 
              v-for="(lesson, index) in lessons" 
              :key="lesson.id"
              @click="selectLesson(lesson)"
              :class="[
                'flex items-center justify-between p-2 rounded cursor-pointer text-sm',
                selectedLesson?.id === lesson.id ? 'bg-teal-500 text-white' : 
                  lessonProgress[lesson.id] ? 'bg-green-500 text-white hover:bg-green-600' : 'bg-teal-400 text-white hover:bg-teal-500'
              ]"
            >
              <div class="flex items-center flex-1 min-w-0">
                <!-- Checkmark icon for completed lessons -->
                <svg v-if="lessonProgress[lesson.id]" class="w-4 h-4 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <!-- Play icon for incomplete lessons -->
                <svg v-else class="w-4 h-4 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/>
                </svg>
                <span class="truncate">{{ lesson.title }}</span>
              </div>
              <span class="ml-2 text-xs flex-shrink-0">{{ lesson.duration || 30 }} mins</span>
              <button 
                v-if="isTeacher" 
                @click.stop="openLessonMenu(lesson)"
                class="ml-2 p-1 hover:bg-teal-600 rounded"
              >
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Practice Quiz Section -->
        <div>
          <div class="flex items-center justify-between mb-3">
            <h3 class="font-bold text-gray-800">PRACTICE QUIZ</h3>
            <button 
              v-if="isTeacher" 
              @click="showAddQuiz = true"
              class="bg-green-500 text-white text-xs px-2 py-1 rounded flex items-center hover:bg-green-600"
            >
              <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
              </svg>
              Add Quiz
            </button>
          </div>
          
          <div class="space-y-2">
            <div 
              v-for="quiz in quizzes" 
              :key="quiz.id"
              @click="goToQuiz(quiz)"
              :class="[
                'flex items-center justify-between p-2 rounded cursor-pointer text-sm',
                'bg-yellow-400 text-gray-800 hover:bg-yellow-500'
              ]"
            >
              <div class="flex items-center flex-1 min-w-0">
                <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/>
                </svg>
                <span class="truncate">{{ quiz.title }}</span>
              </div>
              <span class="ml-2 text-xs flex-shrink-0">{{ quiz.time_limit || 30 }} mins</span>
              <button 
                v-if="isTeacher" 
                @click.stop="openQuizMenu(quiz)"
                class="ml-2 p-1 hover:bg-yellow-600 rounded"
              >
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Content Area -->
      <div class="flex-1 p-6">
        <!-- Course Header -->
        <div class="bg-teal-500 text-white rounded-lg p-6 mb-6">
          <h1 class="text-2xl font-bold mb-2">{{ course?.title }}</h1>
          <p class="text-teal-100 mb-4">{{ course?.description }}</p>
          <div class="flex items-center text-sm">
            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.414-1.414L11 9.586V6z" clip-rule="evenodd"/>
            </svg>
            <span>{{ totalDuration }}</span>
          </div>
        </div>

        <!-- Dynamic Content View -->
        <div v-if="currentView === 'lesson' && selectedLesson">
          <LessonView 
            :lesson="selectedLesson" 
            :course="course"
            :is-teacher="isTeacher"
            @edit="editLesson"
            @delete="deleteLesson"
            @completed="onLessonCompleted"
          />
        </div>

        <div v-else-if="currentView === 'add-lesson'">
          <AddLessonForm 
            :course-id="courseId"
            :modules="modules"
            @saved="onLessonSaved"
            @cancel="closeForm"
            @close="closeForm"
          />
        </div>

        <div v-else-if="currentView === 'edit-lesson' && editingLesson">
          <AddLessonForm 
            :course-id="courseId"
            :modules="modules"
            :lesson="editingLesson"
            @saved="onLessonSaved"
            @cancel="closeForm"
            @close="closeForm"
          />
        </div>

        <div v-else-if="currentView === 'add-quiz'">
          <AddQuizForm 
            :course-id="courseId"
            @saved="onQuizSaved"
            @cancel="closeForm"
          />
        </div>

        <div v-else-if="currentView === 'edit-quiz' && editingQuiz">
          <AddQuizForm 
            :course-id="courseId"
            :quiz="editingQuiz"
            @saved="onQuizSaved"
            @cancel="closeForm"
          />
        </div>

        <!-- Default: Course Overview -->
        <div v-else class="bg-white rounded-lg shadow p-6">
          <h2 class="text-xl font-bold mb-4">Course Overview</h2>
          <p class="text-gray-600 mb-6">{{ course?.long_description || course?.description }}</p>
          
          <div class="grid grid-cols-3 gap-4">
            <div class="bg-gray-50 rounded-lg p-4 text-center">
              <div class="text-2xl font-bold text-teal-600">{{ lessons.length }}</div>
              <div class="text-sm text-gray-500">Lessons</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-4 text-center">
              <div class="text-2xl font-bold text-yellow-600">{{ quizzes.length }}</div>
              <div class="text-sm text-gray-500">Quizzes</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-4 text-center">
              <div class="text-2xl font-bold text-purple-600">{{ totalDuration }}</div>
              <div class="text-sm text-gray-500">Duration</div>
            </div>
          </div>

          <div v-if="lessons.length > 0" class="mt-6">
            <button 
              @click="selectLesson(lessons[0])"
              class="w-full bg-teal-500 text-white py-3 rounded-lg font-semibold hover:bg-teal-600 transition"
            >
              Start Learning
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Add Lesson Modal Trigger -->
    <Teleport to="body">
      <AddLessonForm 
        v-if="showAddLesson"
        :course-id="courseId"
        :modules="modules"
        @saved="onLessonSaved"
        @close="showAddLesson = false"
      />
    </Teleport>

    <!-- Add Quiz Modal Trigger -->
    <Teleport to="body">
      <AddQuizForm 
        v-if="showAddQuiz"
        :course-id="courseId"
        @saved="onQuizSaved"
        @close="showAddQuiz = false"
      />
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import LessonView from '../../../components/open-course/LessonView.vue'
import AddLessonForm from '../../../components/open-course/AddLessonForm.vue'
import AddQuizForm from '../../../components/open-course/AddQuizForm.vue'

const route = useRoute()
const router = useRouter()
const config = useRuntimeConfig()
const token = useCookie('auth_token')

const courseId = route.params.id as string

// State
const loading = ref(true)
const error = ref<string | null>(null)
const course = ref<any>(null)
const modules = ref<any[]>([])
const lessons = ref<any[]>([])
const quizzes = ref<any[]>([])
const currentUser = ref<any>(null)
const lessonProgress = ref<Record<string, boolean>>({})  // Track lesson completion status

// View state
const currentView = ref<'overview' | 'lesson' | 'add-lesson' | 'edit-lesson' | 'add-quiz' | 'edit-quiz'>('overview')
const selectedLesson = ref<any>(null)
const editingLesson = ref<any>(null)
const editingQuiz = ref<any>(null)
const showAddLesson = ref(false)
const showAddQuiz = ref(false)

// Computed
const isTeacher = computed(() => {
  if (!currentUser.value || !course.value) return false
  return currentUser.value.id === course.value.teacher_id || currentUser.value.role === 'ADMIN'
})

const totalDuration = computed(() => {
  const totalMins = lessons.value.reduce((sum, l) => sum + (l.duration || 30), 0)
  if (totalMins >= 60) {
    const hours = Math.floor(totalMins / 60)
    const mins = totalMins % 60
    return `${hours}h ${mins}m`
  }
  return `${totalMins} mins`
})

// Methods
const goBack = () => {
  router.push(`/courses/${courseId}`)
}

const onLessonCompleted = (lessonId: string) => {
  lessonProgress.value[lessonId] = true
}

const selectLesson = (lesson: any) => {
  selectedLesson.value = lesson
  currentView.value = 'lesson'
}

// Navigate to quiz page (existing quiz page in dev)
const goToQuiz = (quiz: any) => {
  router.push(`/courses/${courseId}/quizzes/${quiz.id}`)
}

const editLesson = (lesson: any) => {
  editingLesson.value = lesson
  currentView.value = 'edit-lesson'
}

const deleteLesson = async (lesson: any) => {
  if (!confirm(`Delete lesson "${lesson.title}"?`)) return
  
  try {
    await $fetch(`/api/courses/${courseId}/lessons/${lesson.id}`, {
      baseURL: config.public.backendUrl as string,
      method: 'DELETE',
      headers: {
        'Authorization': `Bearer ${token.value}`,
        'Accept': 'application/json'
      }
    })
    lessons.value = lessons.value.filter(l => l.id !== lesson.id)
    if (selectedLesson.value?.id === lesson.id) {
      selectedLesson.value = null
      currentView.value = 'overview'
    }
  } catch (err) {
    console.error('Failed to delete lesson:', err)
    alert('Failed to delete lesson')
  }
}

const editQuiz = (quiz: any) => {
  editingQuiz.value = quiz
  currentView.value = 'edit-quiz'
}

const deleteQuiz = async (quiz: any) => {
  if (!confirm(`Delete quiz "${quiz.title}"?`)) return
  
  try {
    await $fetch(`/api/quizzes/${quiz.id}`, {
      baseURL: config.public.backendUrl as string,
      method: 'DELETE',
      headers: {
        'Authorization': `Bearer ${token.value}`,
        'Accept': 'application/json'
      }
    })
    quizzes.value = quizzes.value.filter(q => q.id !== quiz.id)
  } catch (err) {
    console.error('Failed to delete quiz:', err)
    alert('Failed to delete quiz')
  }
}

const openLessonMenu = (lesson: any) => {
  // Simple menu - could be enhanced with dropdown
  const action = prompt('Enter action: edit or delete')
  if (action === 'edit') editLesson(lesson)
  else if (action === 'delete') deleteLesson(lesson)
}

const openQuizMenu = (quiz: any) => {
  const action = prompt('Enter action: edit or delete')
  if (action === 'edit') editQuiz(quiz)
  else if (action === 'delete') deleteQuiz(quiz)
}

const onLessonSaved = async () => {
  showAddLesson.value = false
  editingLesson.value = null
  currentView.value = 'overview'
  await fetchCourseData()
}

const onQuizSaved = async () => {
  showAddQuiz.value = false
  editingQuiz.value = null
  currentView.value = 'overview'
  await fetchCourseData()
}

const closeForm = () => {
  showAddLesson.value = false
  showAddQuiz.value = false
  editingLesson.value = null
  editingQuiz.value = null
  currentView.value = 'overview'
}

// Fetch data
const fetchCourseData = async () => {
  try {
    // Fetch course details
    const courseResponse = await $fetch<any>(`/api/courses/${courseId}`, {
      baseURL: config.public.backendUrl as string,
      headers: { 'Accept': 'application/json' }
    })
    course.value = courseResponse.course || courseResponse

    // Fetch modules with lessons
    const modulesResponse = await $fetch<any>(`/api/courses/${courseId}/modules`, {
      baseURL: config.public.backendUrl as string,
      headers: {
        'Authorization': `Bearer ${token.value}`,
        'Accept': 'application/json'
      }
    })
    modules.value = modulesResponse.modules || modulesResponse || []
    
    // Flatten lessons from modules
    lessons.value = modules.value.flatMap((m: any) => m.lessons || [])

    // Fetch quizzes
    try {
      const quizzesResponse = await $fetch<any>(`/api/quizzes/course/${courseId}`, {
        baseURL: config.public.backendUrl as string,
        headers: {
          'Authorization': `Bearer ${token.value}`,
          'Accept': 'application/json'
        }
      })
      // API returns {success: true, data: [...]}
      quizzes.value = quizzesResponse.data || quizzesResponse.quizzes || quizzesResponse || []
    } catch {
      quizzes.value = []
    }

  } catch (err: any) {
    error.value = err.message || 'Failed to load course'
    console.error('Error fetching course:', err)
  }
}

const fetchCurrentUser = async () => {
  try {
    const response = await $fetch<any>('/api/auth/me', {
      baseURL: config.public.backendUrl as string,
      headers: {
        'Authorization': `Bearer ${token.value}`,
        'Accept': 'application/json'
      }
    })
    currentUser.value = response.user || response
  } catch {
    currentUser.value = null
  }
}

const fetchCourseProgress = async () => {
  try {
    const response = await $fetch<any>(`/api/learning/course/${courseId}`, {
      baseURL: config.public.backendUrl as string,
      headers: {
        'Authorization': `Bearer ${token.value}`,
        'Accept': 'application/json'
      }
    })
    
    // Build progress map from response
    const progressMap: Record<string, boolean> = {}
    if (response.modules) {
      for (const module of response.modules) {
        if (module.lessons) {
          for (const lesson of module.lessons) {
            progressMap[lesson.id] = lesson.is_completed === true || lesson.is_completed === 1
          }
        }
      }
    }
    lessonProgress.value = progressMap
  } catch (err) {
    console.error('Failed to fetch course progress:', err)
  }
}

onMounted(async () => {
  loading.value = true
  await Promise.all([fetchCurrentUser(), fetchCourseData()])
  // Fetch progress after course data is loaded (only for students)
  if (currentUser.value && currentUser.value.role !== 'TEACHER') {
    await fetchCourseProgress()
  }
  loading.value = false
})
</script>
