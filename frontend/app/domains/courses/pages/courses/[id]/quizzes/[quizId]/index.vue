<template>
  <div class="min-h-screen bg-background">
    <div v-if="loading" class="flex justify-center items-center min-h-[60vh]">
      <div class="animate-spin rounded-full h-16 w-16 border-b-4 border-brand-primary"></div>
    </div>

    <div v-else-if="error" class="text-center py-20">
      <h2 class="text-h2 font-bold text-red-600 mb-4">Unable to load quiz</h2>
      <p class="text-text-muted">{{ error.message }}</p>
      <NuxtLink :to="`/courses/${courseId}`" class="mt-4 inline-block text-brand-primary hover:underline">
        Back to course
      </NuxtLink>
    </div>

    <div v-else-if="quiz" class="max-w-4xl mx-auto px-4 py-8">
      <!-- Breadcrumb Navigation -->
      <nav class="flex items-center space-x-2 text-body-sm text-text-muted mb-6">
        <NuxtLink to="/courses" class="hover:text-brand-primary">Courses</NuxtLink>
        <span>›</span>
        <NuxtLink :to="`/courses/${courseId}`" class="hover:text-brand-primary">{{ course?.title }}</NuxtLink>
        <span>›</span>
        <span class="text-text-dark">{{ quiz.title }}</span>
      </nav>

      <!-- Quiz Header -->
      <div class="bg-white rounded-lg shadow p-8 mb-8">
        <div class="flex items-start justify-between">
          <div class="flex-1">
            <h1 class="text-h1 font-bold text-text-dark mb-4">{{ quiz.title }}</h1>
            <p v-if="quiz.description" class="text-body text-text-muted mb-6">{{ quiz.description }}</p>
            
            <!-- Quiz Meta Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
              <div class="flex items-center">
                <div class="w-12 h-12 bg-accent-purple/10 rounded-lg flex items-center justify-center mr-4">
                  <svg class="w-6 h-6 text-accent-purple" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" clip-rule="evenodd"/>
                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2v1a1 1 0 00-1 1v5a1 1 0 001 1v1a2 2 0 01-2-2V5zM14 5a2 2 0 012 2v5a2 2 0 01-2 2v-1a1 1 0 001-1V6a1 1 0 00-1-1V5z" clip-rule="evenodd"/>
                    <path fill-rule="evenodd" d="M5 5a1 1 0 011-1h8a1 1 0 110 2v6h1a1 1 0 110 2H6a1 1 0 110-2h1V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                  </svg>
                </div>
                <div>
                  <div class="text-caption font-semibold text-text-muted">Type</div>
                  <div class="text-body font-medium">{{ formatQuizType(quiz.quiz_type) }}</div>
                </div>
              </div>
              
              <div class="flex items-center">
                <div class="w-12 h-12 bg-accent-blue/10 rounded-lg flex items-center justify-center mr-4">
                  <svg class="w-6 h-6 text-accent-blue" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.414-1.414L11 9.586V6z" clip-rule="evenodd"/>
                  </svg>
                </div>
                <div>
                  <div class="text-caption font-semibold text-text-muted">Time Limit</div>
                  <div class="text-body font-medium">{{ quiz.time_limit ? `${quiz.time_limit} mins` : 'No limit' }}</div>
                </div>
              </div>
              
              <div class="flex items-center">
                <div class="w-12 h-12 bg-accent-star/10 rounded-lg flex items-center justify-center mr-4">
                  <svg class="w-6 h-6 text-accent-star" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
                  </svg>
                </div>
                <div>
                  <div class="text-caption font-semibold text-text-muted">Passing Score</div>
                  <div class="text-body font-medium">{{ quiz.passing_score }}%</div>
                </div>
              </div>
              
              <div class="flex items-center">
                <div class="w-12 h-12 bg-accent-red/10 rounded-lg flex items-center justify-center mr-4">
                  <svg class="w-6 h-6 text-accent-red" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
                  </svg>
                </div>
                <div>
                  <div class="text-caption font-semibold text-text-muted">Remaining Attempts</div>
                  <div class="text-body font-medium">
                    <template v-if="!quiz.max_attempts">Unlimited</template>
                    <template v-else>
                      {{ Math.max(0, quiz.max_attempts - (previousAttempts?.length || 0)) }}
                    </template>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="ml-8 text-right">

            <NuxtLink 
              v-if="isTeacher"
              :to="`/courses/${courseId}/manage/quizzes/${quizId}/attempts`"
              class="inline-block px-4 py-2 bg-teal-600 text-white rounded hover:bg-teal-700 transition shadow-sm text-sm font-medium"
            >
              Manage Attempts
            </NuxtLink>
          </div>
        </div>
      </div>

      <!-- Quiz State: Not Started -->
      <div v-if="!currentAttempt && !attemptFinished" class="bg-white rounded-lg shadow-sm p-8 text-center">
        <div class="max-w-2xl mx-auto">
          <div class="w-20 h-20 bg-accent-purple/10 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-accent-purple" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h3a1 1 0 110 2h-4a1 1 0 01-1-1V4a1 1 0 011-1z" clip-rule="evenodd"/>
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm0-2a6 6 0 100-12 6 6 0 000 12z" clip-rule="evenodd"/>
            </svg>
          </div>
          
          <h2 class="text-h2 font-bold text-text-dark mb-4">Ready to Start Quiz?</h2>
          <p class="text-body text-text-muted mb-8">
            Make sure you have enough time to complete the quiz. 
            {{ quiz.time_limit ? `You have ${quiz.time_limit} minutes to finish.` : 'There is no time limit.' }}
          </p>
          
          <!-- Previous Attempts -->
          <div v-if="previousAttempts && previousAttempts.length > 0" class="bg-gray-50 rounded-lg p-6 mb-8">
            <h3 class="text-h4 font-semibold mb-4">Previous Attempts</h3>
            <div class="space-y-2">
              <div 
                v-for="attempt in previousAttempts" 
                :key="attempt.id"
                class="flex justify-between items-center text-body-sm"
              >
                <span>{{ new Date(attempt.submitted_at).toLocaleDateString() }}</span>
                <div class="flex items-center space-x-4">
                  <span :class="attempt.score >= quiz.passing_score ? 'text-green-600' : 'text-red-600'">
                    {{ attempt.score }}%
                  </span>
                  <span 
                    :class="{
                      'text-green-600': attempt.is_passed && attempt.grading_status !== 'pending_manual',
                      'text-red-600': !attempt.is_passed && attempt.grading_status !== 'pending_manual',
                      'text-yellow-600': attempt.grading_status === 'pending_manual'
                    }"
                  >
                    {{ attempt.grading_status === 'pending_manual' ? 'Pending Grading' : (attempt.is_passed ? 'Passed' : 'Failed') }}
                  </span>
                  <div>
                    <NuxtLink 
                      :to="`/courses/${route.params.id}/quizzes/${route.params.quizId}/review/${attempt.id}`"
                      class="text-teal-600 hover:text-teal-800 text-sm font-semibold ml-4"
                    >
                      Review
                    </NuxtLink>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <button
            @click="startQuiz"
            :disabled="startingQuiz || Boolean(quiz.max_attempts && previousAttempts && previousAttempts.length >= (quiz.max_attempts || 0))"
            class="px-8 py-4 bg-brand-primary text-white rounded-lg hover:bg-brand-secondary transition disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ startingQuiz ? 'Starting Quiz...' : 'Start Quiz' }}
          </button>
          
          <div v-if="quiz.max_attempts && previousAttempts && previousAttempts.length >= quiz.max_attempts" class="mt-4 text-red-600 text-body-sm">
            You have reached the maximum number of attempts for this quiz.
          </div>
        </div>
      </div>

      <!-- Quiz State: In Progress -->
      <div v-else-if="currentAttempt && !attemptFinished" class="space-y-8">
        <!-- Quiz Timer -->
        <div v-if="quiz.time_limit" class="bg-white rounded-lg shadow-sm p-6">
          <div class="flex items-center justify-between">
            <span class="text-h4 font-semibold">Time Remaining:</span>
            <div class="text-h2 font-bold" :class="timeRemaining <= 300 ? 'text-red-600' : 'text-brand-primary'">
              {{ formatTime(timeRemaining) }}
            </div>
          </div>
          <div class="w-full bg-gray-200 rounded-full h-2 mt-4">
            <div
              class="h-2 rounded-full transition-all"
              :class="timeRemaining <= 300 ? 'bg-red-500' : 'bg-brand-primary'"
              :style="{ width: `${(timeRemaining / (quiz.time_limit * 60)) * 100}%` }"
            ></div>
          </div>
        </div>

        <!-- Progress Bar -->
        <div class="bg-white rounded-lg shadow-sm p-6">
          <div class="flex justify-between text-body-sm mb-2">
            <span>Progress</span>
            <span>{{ answeredQuestions }} of {{ questions.length }} questions answered</span>
          </div>
          <div class="w-full bg-gray-200 rounded-full h-2">
            <div
              class="bg-brand-primary h-2 rounded-full transition-all"
              :style="{ width: `${(answeredQuestions / questions.length) * 100}%` }"
            ></div>
          </div>
        </div>

        <!-- Questions -->
        <div v-if="questions" class="space-y-8">
          <div
            v-for="(question, index) in questions"
            :key="question.id || index"
            class="bg-white rounded-lg shadow p-6"
          >
            <div class="flex items-start justify-between mb-6">
              <h3 class="text-h3 font-semibold text-text-dark">
                Question {{ index + 1 }}
                <span class="text-body text-text-muted ml-2">({{ question.points }} points)</span>
              </h3>
              <span 
                v-if="question.id && answers[question.id]"
                class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-caption"
              >
                Answered
              </span>
            </div>
            
            <div class="mb-6 text-gray-800 bg-gray-50 p-4 rounded text-lg" v-html="question.question_text"></div>
            
            <!-- Multiple Choice & True/False Questions -->
            <div v-if="['MULTIPLE_CHOICE', 'TRUE_FALSE'].includes(question.question_type) && question.id" class="space-y-3">
              <label
                v-for="(option, optionIndex) in question.options"
                :key="optionIndex"
                class="flex items-center p-4 border rounded-lg transition-colors relative overflow-hidden cursor-pointer hover:bg-gray-50"
                :class="answers[question.id] === getOptionValue(option) ? 'border-brand-primary' : 'border-gray-200'"
              >
                <input
                  type="radio"
                  :name="`question_${question.id}`"
                  :value="getOptionValue(option)"
                  v-model="answers[question.id]"
                  class="sr-only"
                />
                <!-- Selection Indicator -->
                <div class="mr-4 flex-shrink-0">
                  <div 
                    class="w-6 h-6 flex items-center justify-center rounded-full border-2 transition-colors"
                    :class="answers[question.id] === getOptionValue(option) ? 'border-brand-primary' : 'border-gray-300'"
                  >
                    <div v-if="answers[question.id] === getOptionValue(option)" class="w-3 h-3 bg-brand-primary rounded-full"></div>
                  </div>
                </div>
                <span class="text-gray-800" :class="{'font-semibold': answers[question.id] === getOptionValue(option)}">{{ getOptionText(option) }}</span>
              </label>
            </div>
            
            <!-- Checkbox Questions -->
            <div v-else-if="question.question_type === 'CHECKBOX' && question.id" class="space-y-3">
              <label
                v-for="(option, optionIndex) in question.options"
                :key="optionIndex"
                class="flex items-center p-4 border rounded-lg transition-colors relative overflow-hidden cursor-pointer hover:bg-gray-50"
                :class="(Array.isArray(answers[question.id]) && answers[question.id].includes(getOptionValue(option))) ? 'border-brand-primary' : 'border-gray-200'"
              >
                <input
                  type="checkbox"
                  :value="getOptionValue(option)"
                  v-model="answers[question.id]"
                  class="sr-only"
                />
                <!-- Selection Indicator -->
                <div class="mr-4 flex-shrink-0">
                  <div 
                    class="w-6 h-6 flex items-center justify-center rounded border-2 transition-colors"
                     :class="(Array.isArray(answers[question.id]) && answers[question.id].includes(getOptionValue(option))) ? 'border-brand-primary bg-brand-primary text-white' : 'border-gray-300'"
                  >
                    <svg v-if="Array.isArray(answers[question.id]) && answers[question.id].includes(getOptionValue(option))" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                  </div>
                </div>
                <span class="text-gray-800" :class="{'font-semibold': Array.isArray(answers[question.id]) && answers[question.id].includes(getOptionValue(option))}">{{ getOptionText(option) }}</span>
              </label>
            </div>
            
            <!-- Short Answer & Essay Questions -->
            <div v-else-if="['SHORT_ANSWER', 'ESSAY'].includes(question.question_type) && question.id">
              <textarea
                v-model="answers[question.id]"
                rows="4"
                class="w-full p-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-primary focus:border-brand-primary"
                placeholder="Enter your answer here..."
              ></textarea>
            </div>
          </div>
        </div>

        <!-- Submit Quiz -->
        <div class="bg-white rounded-lg shadow-sm p-8 text-center">
          <div class="max-w-md mx-auto">
            <h3 class="text-h3 font-semibold mb-4">Ready to Submit?</h3>
            <p class="text-body text-text-muted mb-6">
              You have answered {{ answeredQuestions }} of {{ questions.length }} questions.
              {{ answeredQuestions < questions.length ? 'You can still answer the remaining questions.' : 'All questions have been answered.' }}
            </p>
            
            <button
              @click="submitQuiz(false)"
              :disabled="submittingQuiz"
              class="px-8 py-4 bg-accent-red text-white rounded-lg hover:bg-red-600 transition disabled:opacity-50"
            >
              {{ submittingQuiz ? 'Submitting...' : 'Submit Quiz' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Quiz State: Finished -->
      <!-- Quiz State: Finished -->
      <div v-else-if="attemptFinished && lastAttempt" class="bg-white rounded-lg shadow p-6 text-center">
        <div class="max-w-2xl mx-auto">
          <!-- Icon -->
          <div class="w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6"
               :class="{
                 'bg-green-100': lastAttempt.grading_status !== 'pending_manual' && lastAttempt.passed,
                 'bg-red-100': lastAttempt.grading_status !== 'pending_manual' && !lastAttempt.passed,
                 'bg-yellow-100': lastAttempt.grading_status === 'pending_manual'
               }">
            <!-- Passed Icon -->
            <svg v-if="lastAttempt.grading_status !== 'pending_manual' && lastAttempt.passed" class="w-10 h-10 text-green-600" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <!-- Failed Icon -->
            <svg v-else-if="lastAttempt.grading_status !== 'pending_manual' && !lastAttempt.passed" class="w-10 h-10 text-red-600" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            <!-- Pending Icon -->
            <svg v-else class="w-10 h-10 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.414-1.414L11 9.586V6z" clip-rule="evenodd"/>
            </svg>
          </div>
          
          <!-- Title -->
          <h2 class="text-h2 font-bold mb-4" 
              :class="{
                'text-green-600': lastAttempt.grading_status !== 'pending_manual' && lastAttempt.passed,
                'text-red-600': lastAttempt.grading_status !== 'pending_manual' && !lastAttempt.passed,
                'text-yellow-600': lastAttempt.grading_status === 'pending_manual'
              }">
            {{ lastAttempt.grading_status === 'pending_manual' ? 'Submission Received' : (lastAttempt.passed ? 'Quiz Passed!' : 'Quiz Failed') }}
          </h2>
          
          <div class="grid grid-cols-2 gap-6 mb-8">
            <div class="text-center">
              <div class="text-h1 font-bold" 
                   :class="{
                     'text-green-600': lastAttempt.grading_status !== 'pending_manual' && lastAttempt.passed,
                     'text-red-600': lastAttempt.grading_status !== 'pending_manual' && !lastAttempt.passed,
                     'text-yellow-600': lastAttempt.grading_status === 'pending_manual'
                   }">
                {{ lastAttempt.grading_status === 'pending_manual' ? 'Pending' : Math.round(lastAttempt.score) + '%' }}
              </div>
              <div class="text-caption text-text-muted">Your Score</div>
            </div>
            <div class="text-center">
              <div class="text-h1 font-bold text-text-muted">{{ quiz.passing_score }}%</div>
              <div class="text-caption text-text-muted">Passing Score</div>
            </div>
          </div>
          
          <div class="flex justify-center space-x-4">
            <NuxtLink
              :to="`/courses/${courseId}`"
              class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition"
            >
              Back to Course
            </NuxtLink>

            <NuxtLink
              v-if="currentAttempt && currentAttempt.id"
              :to="`/courses/${courseId}/quizzes/${quizId}/review/${currentAttempt.id}`"
              class="px-6 py-3 bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition shadow-sm font-medium"
            >
              Review Quiz
            </NuxtLink>
            
            <button
              v-if="lastAttempt.grading_status !== 'pending_manual' && !lastAttempt.passed && canRetry"
              @click="resetQuiz"
              class="px-6 py-3 bg-brand-primary text-white rounded-lg hover:bg-brand-secondary transition"
            >
              Try Again
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">

import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import type { Quiz, Question, Course } from '../../../../types/course'

// Route parameters
const route = useRoute()
const courseId = route.params.id as string
const quizId = route.params.quizId as string



// Reactive data
const quiz = ref<Quiz | null>(null)
const course = ref<Course | null>(null)
const questions = ref<Question[]>([])
const answers = ref<Record<string, any>>({})
const currentAttempt = ref<any>(null)
const lastAttempt = ref<any>(null)
const previousAttempts = ref<any[]>([])
const attemptFinished = ref(false)
const isTeacher = ref(false)

// Loading states
const loading = ref(true)
const error = ref<Error | null>(null)
const startingQuiz = ref(false)
const submittingQuiz = ref(false)

// Timer
const timeRemaining = ref(0)
const timerInterval = ref<NodeJS.Timeout | null>(null)

// Computed properties
const answeredQuestions = computed(() => {
  return Object.keys(answers.value).filter(questionId => {
    const answer = answers.value[questionId]
    return answer !== undefined && answer !== null && answer !== '' && (Array.isArray(answer) ? answer.length > 0 : true)
  }).length
})

const canRetry = computed(() => {
  if (!quiz.value || !previousAttempts.value) return false
  return !quiz.value.max_attempts || previousAttempts.value.length < quiz.value.max_attempts
})

// Methods
const formatQuizType = (type: string): string => {
  switch (type) {
    case 'PRACTICE': return 'Practice Quiz'
    case 'GRADED': return 'Graded Quiz'
    case 'FINAL': return 'Final Exam'
    default: return type
  }
}

const formatTime = (seconds: number): string => {
  const hours = Math.floor(seconds / 3600)
  const minutes = Math.floor((seconds % 3600) / 60)
  const remainingSeconds = seconds % 60

  if (hours > 0) {
    return `${hours}:${minutes.toString().padStart(2, '0')}:${remainingSeconds.toString().padStart(2, '0')}`
  } else {
    return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`
  }
}

// Helper methods to handle options that might be JSON objects or plain strings
const getOptionText = (option: any): string => {
  if (typeof option === 'string') {
    try {
      const parsed = JSON.parse(option)
      return parsed.text || parsed.value || option
    } catch {
      return option
    }
  }
  return option?.text || option?.value || String(option)
}

const getOptionValue = (option: any): string => {
  if (typeof option === 'string') {
    try {
      const parsed = JSON.parse(option)
      return parsed.value || parsed.text || option
    } catch {
      return option
    }
  }
  return option?.value || option?.text || String(option)
}

const fetchQuizData = async () => {
  try {
    loading.value = true
    
    // Fetch quiz details
    const quizResponse = await $fetch<any>(`/api/quizzes/${quizId}`, {
      baseURL: useRuntimeConfig().public.backendUrl as string,
      headers: {
        'Accept': 'application/json',
        'Authorization': `Bearer ${useCookie('auth_token').value}`
      }
    })
    
    quiz.value = quizResponse.data
    
    // Fetch course details
    // Fetch course details
    const courseResponse = await $fetch<any>(`/api/courses/${courseId}`, {
      baseURL: useRuntimeConfig().public.backendUrl as string,
      headers: {
        'Accept': 'application/json',
        'Authorization': `Bearer ${useCookie('auth_token').value}`
      }
    })
    
    course.value = courseResponse.course || courseResponse
    
    // Check if current user is teacher
    const token = useCookie('auth_token').value
    if (token) {
      try {
        const user = await $fetch<any>('/api/auth/me', {
          baseURL: useRuntimeConfig().public.backendUrl as string,
          headers: { 'Authorization': `Bearer ${token}` }
        })
        isTeacher.value = course.value?.teacher_id === user.id
      } catch (e) {
        console.error('Error checking teacher status', e)
      }
    }

    // Fetch previous attempts
    try {
      const attemptsResponse = await $fetch<any>(`/api/quizzes/${quizId}/attempts`, {
        baseURL: useRuntimeConfig().public.backendUrl as string,
        headers: {
          'Accept': 'application/json',
          'Authorization': `Bearer ${useCookie('auth_token').value}`
        }
      })
      
      if (attemptsResponse.success) {
        previousAttempts.value = attemptsResponse.data || []
      }
    } catch (attemptsError) {
      console.log('No previous attempts found')
      previousAttempts.value = []
    }

  } catch (err: any) {
    error.value = err
    console.error('Error fetching quiz data:', err)
  } finally {
    loading.value = false
  }
}

const startQuiz = async () => {
  if (startingQuiz.value) return
  
  try {
    startingQuiz.value = true
    
    const response = await $fetch<any>(`/api/quizzes/${quizId}/start`, {
      method: 'POST',
      baseURL: useRuntimeConfig().public.backendUrl as string,
      headers: {
        'Accept': 'application/json',
        'Authorization': `Bearer ${useCookie('auth_token').value}`
      }
    })
    
    if (response.success) {
      currentAttempt.value = response.data.attempt
      questions.value = response.data.questions || []
      answers.value = {}

      if (response.data.quiz) {
        quiz.value = { ...quiz.value, ...response.data.quiz }
      }
      
      // Initialize answers for checkbox questions
      questions.value.forEach((q: Question) => {
        if (q.question_type === 'CHECKBOX' && q.id) {
          answers.value[q.id] = []
        }
      })
      attemptFinished.value = false
      
      // Start timer if quiz has time limit
      if (quiz.value?.time_limit) {
        timeRemaining.value = quiz.value.time_limit * 60
        startTimer()
      }
    }
    
  } catch (err: any) {
    console.error('Error starting quiz:', err)
    error.value = err
  } finally {
    startingQuiz.value = false
  }
}

const startTimer = () => {
  if (timerInterval.value) {
    clearInterval(timerInterval.value)
  }
  
  timerInterval.value = setInterval(() => {
    timeRemaining.value--
    
    if (timeRemaining.value <= 0) {
      // Auto-submit when time runs out
      submitQuiz(true)
    }
  }, 1000)
}

const submitQuiz = async (isAutoSubmit = false) => {
  if (submittingQuiz.value) return
  
  // Validate completion if not auto-submit (time out)
  if (!isAutoSubmit) {
    // Count answered questions from the reactive answers object
    let answeredCount = 0
    questions.value.forEach((q: Question) => {
      const ans = answers.value[q.id]
      if (q.question_type === 'CHECKBOX') {
        if (Array.isArray(ans) && ans.length > 0) answeredCount++
      } else {
        if (ans !== undefined && ans !== null && ans !== '') answeredCount++
      }
    })

    if (answeredCount < questions.value.length) {
      const { $toast } = useNuxtApp() as any
      if ($toast) $toast.warning('Please answer all questions before submitting.')
      return
    }
  }

  try {
    submittingQuiz.value = true
    
    // Format answers for submission - backend expects answers[question.id] = answer
    const formattedAnswers: Record<string, string> = {}
    
    questions.value.forEach((question: Question) => {
      // Ensure question has an id before proceeding
      if (!question.id) return
      
      const answer = answers.value[question.id]
      
      if (question.question_type === 'CHECKBOX' && Array.isArray(answer)) {
        // For checkbox questions, join selected values with comma
        formattedAnswers[question.id] = answer.join(',')
      } else if (answer !== undefined && answer !== null) {
        // For other question types, use the answer as string
        formattedAnswers[question.id] = String(answer)
      } else {
        // If no answer provided, send empty string
        formattedAnswers[question.id] = ''
      }
    })
    
    const response = await $fetch<any>(`/api/quizzes/attempt/${currentAttempt.value.id}/submit`, {
      method: 'POST',
      baseURL: useRuntimeConfig().public.backendUrl as string,
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${useCookie('auth_token').value}`
      },
      body: JSON.stringify({
        answers: formattedAnswers,
        time_spent: quiz.value?.time_limit ? (quiz.value.time_limit * 60 - timeRemaining.value) : null
      })
    })
    
    if (response.success) {
      lastAttempt.value = {
        score: response.data.score,
        passed: response.data.passed,
        grading_status: response.data.grading_status
      }
      
      currentAttempt.value = null
      attemptFinished.value = true
      
      // Stop timer
      if (timerInterval.value) {
        clearInterval(timerInterval.value)
        timerInterval.value = null
      }
      
      // Refresh attempts list
      await fetchQuizData()
    }
    
  } catch (err: any) {
    console.error('Error submitting quiz:', err)
    error.value = err
  } finally {
    submittingQuiz.value = false
  }
}

const resetQuiz = () => {
  currentAttempt.value = null
  lastAttempt.value = null
  attemptFinished.value = false
  answers.value = {}
  questions.value = []
  timeRemaining.value = 0
  
  if (timerInterval.value) {
    clearInterval(timerInterval.value)
    timerInterval.value = null
  }
}

// Lifecycle
onMounted(() => {
  fetchQuizData()
})

onBeforeUnmount(() => {
  if (timerInterval.value) {
    clearInterval(timerInterval.value)
  }
})

// SEO
useHead({
  title: computed(() => quiz.value ? `${quiz.value.title} - ${course.value?.title}` : 'Loading Quiz...'),
  meta: [
    { name: 'description', content: computed(() => quiz.value?.description || 'Course quiz') }
  ]
})
</script>
