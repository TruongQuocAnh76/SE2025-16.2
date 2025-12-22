<template>
  <div class="min-h-screen bg-background py-12">
    <div class="max-w-4xl mx-auto px-4">
      <!-- Header -->
      <div class="mb-8">
        <NuxtLink 
          :to="`/courses/${courseId}/quizzes/${quizId}`"
          class="text-teal-600 hover:text-teal-800 flex items-center mb-4"
        >
          &larr; Back to Quiz
        </NuxtLink>
        <div class="flex justify-between items-start">
          <div>
            <h1 class="text-3xl font-bold text-text-dark">Attempt Review</h1>
            <p v-if="attemptData" class="text-gray-600 mt-2">
              Submitted: {{ formatDate(attemptData.attempt.submitted_at) }}
            </p>
          </div>
          <div v-if="attemptData" class="text-right">
            <div class="text-3xl font-bold text-teal-600">
              {{ attemptData.attempt.grading_status === 'pending_manual' ? 'Pending' : attemptData.attempt.score + '%' }}
            </div>
            <div 
              class="font-semibold"
              :class="{
                'text-green-600': attemptData.attempt.is_passed && attemptData.attempt.grading_status !== 'pending_manual',
                'text-red-600': !attemptData.attempt.is_passed && attemptData.attempt.grading_status !== 'pending_manual',
                'text-yellow-600': attemptData.attempt.grading_status === 'pending_manual'
              }"
            >
              {{ attemptData.attempt.grading_status === 'pending_manual' ? 'Pending Grading' : (attemptData.attempt.is_passed ? 'Passed' : 'Failed') }}
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
          <div class="mb-6 text-gray-800 bg-gray-50 p-4 rounded text-lg" v-html="answer.question.question_text"></div>

          <!-- Options Display (Multiple Choice / Checkbox) -->
          <div v-if="['MULTIPLE_CHOICE', 'CHECKBOX'].includes(answer.question.question_type?.toUpperCase())" class="space-y-3 mb-4">
             <div 
               v-for="(option, optIndex) in answer.question.options" 
               :key="optIndex"
               class="flex items-center p-4 border rounded-lg transition-colors relative overflow-hidden"
               :class="getOptionClass(answer, option)"
             >
                <!-- Selection Indicator -->
                <div class="mr-4 flex-shrink-0">
                   <!-- Selected and Correct -->
                   <div v-if="isOptionSelected(answer, option) && isOptionCorrect(answer, option)" class="w-6 h-6 flex items-center justify-center rounded-full border-2 border-green-500 text-green-500 bg-white">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                   </div>
                   <!-- Selected and Wrong -->
                   <div v-else-if="isOptionSelected(answer, option) && !isOptionCorrect(answer, option)" class="w-6 h-6 flex items-center justify-center rounded-full border-2 border-red-500 text-red-500 bg-white">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                   </div>
                   <!-- Not Selected but Correct -->
                   <div v-else-if="!isOptionSelected(answer, option) && isOptionCorrect(answer, option)" class="w-6 h-6 flex items-center justify-center rounded-full border-2 border-green-500 text-green-500 bg-white">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                   </div>
                   <!-- Normal/Unselected -->
                   <div v-else class="w-6 h-6 border-2 border-gray-300 rounded-full"></div>
                </div>
                
                <span class="text-gray-800" :class="{'font-semibold': isOptionCorrect(answer, option)}">{{ getOptionText(option) }}</span>
             </div>
          </div>

          <!-- Text Answer Display (Short Answer / Essay) -->
          <div v-else class="mb-4">
            <p class="text-sm font-medium text-gray-500 mb-1">Your Answer:</p>
            <div class="p-4 border rounded-md bg-white shadow-sm" :class="getAnswerClass(answer)">
              {{ answer.answer_text || '(No answer)' }}
            </div>
            
            <!-- Correct Answer for Short Answer / Essay -->
             <div v-if="answer.question.correct_answer && !answer.is_correct && ['SHORT_ANSWER', 'ESSAY'].includes(answer.question.question_type) && attemptData.attempt.grading_status === 'graded'" class="mt-4 p-4 bg-green-50 border border-green-200 rounded-md">
                 <p class="text-sm font-bold text-green-800 mb-1">Correct Answer:</p>
                 <p class="text-gray-800">{{ answer.question.correct_answer }}</p>
             </div>
          </div>

          <!-- Grading Feedback (if any) -->
          <div v-if="answer.feedback" class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-md">
            <p class="text-sm font-bold text-yellow-800 mb-1">Teacher Feedback:</p>
            <p class="text-gray-800 whitespace-pre-line">{{ answer.feedback }}</p>
          </div>
          
          <!-- Explanation (if available) -->
          <div v-if="answer.question.explanation" class="mt-4 bg-blue-50 p-4 rounded-md border border-blue-100">
            <p class="text-sm font-bold text-blue-800 mb-1">Explanation:</p> 
            <p class="text-blue-900">{{ answer.question.explanation }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
const route = useRoute()
const config = useRuntimeConfig()

const courseId = route.params.id as string
const quizId = route.params.quizId as string
const attemptId = route.params.attemptId as string

const loading = ref(true)
const attemptData = ref<any>(null)

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
    }
  } catch (error) {
    console.error('Error fetching attempt review:', error)
  } finally {
    loading.value = false
  }
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

const getAnswerClass = (answer: any) => {
  if (answer.is_correct === true) return 'border-green-500'
  if (answer.is_correct === false) return 'border-red-500'
  return 'border-gray-200 bg-white'
}

// Option Helpers
const getOptionText = (option: any): string => {
  let val = option
  if (typeof option === 'string') {
    try {
      const parsed = JSON.parse(option)
      val = parsed
    } catch {
      val = option
    }
  }
  return String(val?.text || val?.value || val).trim()
}

const getOptionValue = (option: any): string => {
  let val = option
  if (typeof option === 'string') {
    try {
      const parsed = JSON.parse(option)
      val = parsed
    } catch {
      val = option
    }
  }
  // Prioritize value, then text, then raw
  return String(val?.value !== undefined ? val.value : (val?.text || val)).trim()
}

const isOptionSelected = (answer: any, option: any) => {
  const userVal = answer.answer_text
  if (userVal === undefined || userVal === null) return false
  
  const optVal = getOptionValue(option)
  const optText = getOptionText(option)
  
  const userString = String(userVal)
  
  // Check against both value and text to be safe
  const check = (v: string) => v === optVal || v === optText

  // For CHECKBOX
  if (answer.question.question_type === 'CHECKBOX') {
      // Try JSON array first
      try {
          const parsed = JSON.parse(userString)
          if (Array.isArray(parsed)) {
              return parsed.some(v => check(String(v).trim()))
          }
      } catch {}

      // Try comma-separated
      if (userString.includes(',')) {
         return userString.split(',').map(s => s.trim()).some(v => check(v))
      }
      return check(userString.trim())
  }
  
  return check(userString.trim())
}

const isOptionCorrect = (answer: any, option: any) => {
   const correct = answer.question.correct_answer
   if (!correct) return false
   
   const optVal = getOptionValue(option)
   const optText = getOptionText(option)
   
   const check = (v: string) => {
       const s = String(v).trim()
       return s === optVal || s === optText
   }
   
   // Usually stored as JSON for Checkbox, String for MC
   try {
       const parsedView = JSON.parse(correct)
       if (Array.isArray(parsedView)) {
           return parsedView.some(v => check(v))
       }
       return check(String(parsedView))
   } catch {
       return check(String(correct))
   }
}

const getOptionClass = (answer: any, option: any) => {
    const selected = isOptionSelected(answer, option)
    const correct = isOptionCorrect(answer, option)
    
    if (correct) {
        return 'border-green-500' // Correct Answer -> Always Green Border
    }
    if (selected) {
        return 'border-red-500' // Selected but Not Correct -> Red Border
    }
    
    return 'border-gray-200 hover:bg-gray-50'
}
</script>
