<template>
  <div class="min-h-screen bg-background py-12">
    <div class="max-w-4xl mx-auto px-4">
      <!-- Header -->
      <div class="mb-8">
        <NuxtLink 
          :to="`/courses/${courseId}/manage/quizzes/${quizId}/attempts`"
          class="text-teal-600 hover:text-teal-800 flex items-center mb-4"
        >
          &larr; Back to Attempts
        </NuxtLink>
        <div class="flex justify-between items-start">
          <div>
            <h1 class="text-3xl font-bold text-text-dark">Grade Attempt</h1>
            <p v-if="attemptData" class="text-gray-600 mt-2">
              Student: <span class="font-semibold">{{ attemptData.student.name }}</span> | 
              Submitted: {{ formatDate(attemptData.attempt.submitted_at) }}
            </p>
          </div>
          <div v-if="attemptData" class="text-right">
            <div class="text-3xl font-bold text-teal-600">
              {{ attemptData.attempt.score }}%
            </div>
            <div :class="attemptData.attempt.is_passed ? 'text-green-600' : 'text-red-600'">
              {{ attemptData.attempt.is_passed ? 'Passed' : 'Failed' }}
            </div>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="text-center py-12">
        <p class="text-gray-500">Loading attempt details...</p>
      </div>

      <!-- Content -->
      <div v-else-if="attemptData" class="space-y-8">
        <div 
          v-for="(answer, index) in attemptData.answers" 
          :key="answer.id" 
          class="bg-white rounded-lg shadow p-6"
        >
          <div class="flex justify-between items-start mb-4">
            <h3 class="text-lg font-semibold text-gray-800">
              Question {{ index + 1 }}
              <span class="text-sm font-normal text-gray-500 ml-2">
                ({{ formatQuestionType(answer.question.question_type) }})
              </span>
            </h3>
            <div class="text-sm font-medium">
              <span :class="getScoreColor(answer)">
                {{ answer.points_awarded }} / {{ answer.question.points }} Points
              </span>
            </div>
          </div>

          <!-- Question Text -->
          <div class="mb-4 text-gray-800 bg-gray-50 p-4 rounded">
            {{ answer.question.question_text }}
          </div>

          <!-- Student Answer -->
          <div class="mb-4">
            <p class="text-sm font-medium text-gray-500 mb-1">Student Answer:</p>
            <div class="p-3 border rounded-md" :class="{'bg-green-50 border-green-200': answer.is_correct === true, 'bg-red-50 border-red-200': answer.is_correct === false}">
              {{ answer.answer_text || '(No answer)' }}
            </div>
          </div>

          <!-- Grading Interface for Manual Grading -->
          <div v-if="isManualGradingNeeded(answer.question.question_type)" class="mt-6 border-t pt-4">
            <h4 class="text-md font-semibold text-teal-700 mb-3">Manual Grading</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Score (Max {{ answer.question.points }})</label>
                <input 
                  type="number" 
                  v-model.number="tempGrades[answer.id].points"
                  :max="answer.question.points"
                  min="0"
                  class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-teal-500 focus:border-teal-500"
                />
              </div>
              <div class="flex items-end text-sm text-gray-500 italic">
                (Enter score above)
              </div>
            </div>
            
          </div>

           <!-- Correct Answer Display (for auto-graded questions) -->
           <div v-else class="mt-2">
             <p class="text-sm font-medium text-gray-500">Correct Answer:</p>
             <p class="text-sm text-gray-800">{{ answer.question.correct_answer }}</p>
           </div>
        </div>

        <!-- Bulk Submit Actions -->
        <div class="sticky bottom-4 bg-white p-4 rounded-lg shadow-lg border border-teal-100 flex justify-between items-center z-10">
          <div class="text-sm text-gray-600">
            Review all grades before submitting.
          </div>
          <div class="flex items-center space-x-4">
            <button 
              @click="$router.push(`/courses/${courseId}/manage/quizzes/${quizId}/attempts`)"
              class="px-4 py-2 text-gray-600 hover:text-gray-800"
            >
              Cancel
            </button>
            <button 
              @click="submitAllGrades"
              :disabled="gradingLoading === 'all'"
              class="px-6 py-2 bg-teal-600 text-white font-bold rounded hover:bg-teal-700 disabled:opacity-50 shadow-md"
            >
              {{ gradingLoading === 'all' ? 'Submitting...' : 'Submit All Grades' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
const route = useRoute()
const router = useRouter()
const config = useRuntimeConfig()
const nuxtApp = useNuxtApp() as any
const $toast = nuxtApp.$toast


const courseId = route.params.id as string
const quizId = route.params.quizId as string
const attemptId = route.params.attemptId as string

const loading = ref(true)
const attemptData = ref<any>(null)
const tempGrades = ref<Record<string, { points: number }>>({})
const gradingLoading = ref<string | null>(null)

// Initialize detailed view
onMounted(async () => {
  await fetchAttemptReview()
})

const fetchAttemptReview = async () => {
  try {
    loading.value = true
    const response = await $fetch<any>(`/api/grading/attempts/${attemptId}/review`, {
      baseURL: config.public.backendUrl as string,
      headers: {
        'Accept': 'application/json',
        'Authorization': `Bearer ${useCookie('auth_token').value}`
      }
    })

    if (response.success) {
      attemptData.value = response.data
      // Initialize temp grades
      response.data.answers.forEach((ans: any) => {
        if (isManualGradingNeeded(ans.question.question_type)) {
          tempGrades.value[ans.id] = {
            points: ans.points_awarded || 0
          }
        }
      })
    }
  } catch (error) {
    console.error('Error fetching attempt review:', error)
  } finally {
    loading.value = false
  }
}

const submitAllGrades = async () => {
  // Validate all grades
  const gradesToSubmit = []
  
  for (const answerId in tempGrades.value) {
    const grade = tempGrades.value[answerId]
    const answer = attemptData.value.answers.find((a: any) => a.id === answerId)
    
    if (!answer) continue
    
    // Find max points from the answer object (it has question embedded)
    // Note: answer.question.points might be nested differently based on API response structure.
    // In fetchAttemptReview, we saw: 'question' => $answer->question->toReviewArray()
    // Let's verify structure logic matches template `answer.question.points`
    const maxPoints = answer.question.points
    
    if (grade.points < 0 || grade.points > maxPoints) {
      if ($toast) $toast.warning(`Invalid score for Question ${answer.question.question_text.substring(0, 20)}... (Must be between 0 and ${maxPoints})`)
      return
    }

    gradesToSubmit.push({
      answer_id: answerId,
      points_awarded: grade.points,
      is_correct: grade.points === maxPoints // Simple logic
    })
  }

  if (gradesToSubmit.length === 0) {
    if ($toast) $toast.info("No manual grades to submit.")
    return
  }

  try {
    gradingLoading.value = 'all'
    await $fetch(`/api/grading/attempts/${attemptId}/bulk-grade`, {
      method: 'POST',
      baseURL: config.public.backendUrl as string,
      headers: {
        'Accept': 'application/json',
        'Authorization': `Bearer ${useCookie('auth_token').value}`
      },
      body: {
        grades: gradesToSubmit
      }
    })
    
    if ($toast) $toast.success('All grades submitted successfully!')
    router.push(`/courses/${courseId}/manage/quizzes/${quizId}/attempts`)
    
  } catch (error) {
    console.error('Error submitting grades:', error)
    if ($toast) $toast.error(`Failed to save grades: ${error instanceof Error ? error.message : String(error)}`)
  } finally {
    gradingLoading.value = null
  }
}

const isManualGradingNeeded = (type: string) => {
  return ['SHORT_ANSWER', 'ESSAY'].includes(type)
}

const formatQuestionType = (type: string) => {
  return type.replace('_', ' ').toLowerCase().replace(/\b\w/g, l => l.toUpperCase())
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleString()
}

const getScoreColor = (answer: any) => {
  if (answer.is_correct === true) return 'text-green-600'
  if (answer.is_correct === false) return 'text-red-600'
  return 'text-yellow-600' // Partial or pending
}
</script>
