<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-3xl max-h-[90vh] overflow-y-auto">
      <!-- Header -->
      <div class="bg-teal-500 text-white px-6 py-4 rounded-t-lg flex justify-between items-center">
        <h2 class="text-xl font-bold">{{ isEditing ? 'Edit Quiz' : 'Add New Quiz' }}</h2>
        <button @click="$emit('close')" class="text-white hover:text-gray-200">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <!-- Form -->
      <form @submit.prevent="handleSubmit" class="p-6 space-y-6">
        <!-- Quiz Info Section -->
        <div class="bg-gray-50 rounded-lg p-4">
          <h3 class="font-semibold text-gray-700 mb-4">Quiz Information</h3>
          
          <!-- Title -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Quiz Title *</label>
            <input
              v-model="form.title"
              type="text"
              placeholder="Enter quiz title"
              class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
              required
            />
          </div>

          <!-- Description -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
            <textarea
              v-model="form.description"
              placeholder="Quiz description"
              rows="2"
              class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
            ></textarea>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <!-- Time Limit -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Time Limit (minutes)</label>
              <input
                v-model.number="form.time_limit"
                type="number"
                min="1"
                placeholder="30"
                class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
              />
            </div>

            <!-- Passing Score -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Passing Score (%)</label>
              <input
                v-model.number="form.passing_score"
                type="number"
                min="0"
                max="100"
                placeholder="70"
                class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
              />
            </div>
          </div>
        </div>

        <!-- Questions Section -->
        <div>
          <div class="flex justify-between items-center mb-4">
            <h3 class="font-semibold text-gray-700">Questions</h3>
            <div class="flex gap-2">
              <button
                type="button"
                @click="addQuestion('MULTIPLE_CHOICE')"
                class="bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600"
              >
                + Multiple Choice
              </button>
              <button
                type="button"
                @click="addQuestion('SHORT_ANSWER')"
                class="bg-green-500 text-white px-3 py-1 rounded text-sm hover:bg-green-600"
              >
                + Short Answer
              </button>
              <button
                type="button"
                @click="addQuestion('CHECKBOX')"
                class="bg-purple-500 text-white px-3 py-1 rounded text-sm hover:bg-purple-600"
              >
                + Checkboxes
              </button>
            </div>
          </div>

          <div v-if="form.questions.length === 0" class="text-center py-8 bg-gray-50 rounded-lg">
            <p class="text-gray-500">No questions added yet. Click a button above to add questions.</p>
          </div>

          <div v-else class="space-y-4">
            <div 
              v-for="(question, qIndex) in form.questions" 
              :key="qIndex"
              class="bg-pink-50 rounded-lg p-4"
            >
              <div class="flex justify-between items-start mb-3">
                <span class="text-sm font-medium text-gray-500">
                  Question {{ Number(qIndex) + 1 }} 
                  <span class="text-xs text-teal-600">({{ formatQuestionType(question.question_type) }})</span>
                </span>
                <button
                  type="button"
                  @click="removeQuestion(Number(qIndex))"
                  class="text-red-500 hover:text-red-700"
                >
                  <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                  </svg>
                </button>
              </div>

              <!-- Question Text -->
              <input
                v-model="question.question_text"
                type="text"
                placeholder="Enter your question"
                class="w-full border rounded-lg px-4 py-2 mb-3 focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
              />

              <!-- Points -->
              <div class="flex items-center gap-2 mb-3">
                <label class="text-sm text-gray-600">Points:</label>
                <input
                  v-model.number="question.points"
                  type="number"
                  min="1"
                  class="w-20 border rounded px-2 py-1 text-sm"
                />
              </div>

              <!-- Options for Multiple Choice / Checkbox -->
              <div v-if="question.question_type !== 'SHORT_ANSWER'" class="space-y-2">
                <div 
                  v-for="(option, oIndex) in question.options" 
                  :key="oIndex"
                  class="flex items-center gap-2"
                >
                  <input
                    :type="question.question_type === 'CHECKBOX' ? 'checkbox' : 'radio'"
                    :checked="option.is_correct"
                    @change="setCorrectAnswer(Number(qIndex), Number(oIndex), question.question_type)"
                    class="w-4 h-4 text-teal-500"
                  />
                  <input
                    v-model="option.option_text"
                    type="text"
                    placeholder="Option text"
                    class="flex-1 border rounded px-3 py-1 text-sm focus:ring-2 focus:ring-teal-500"
                  />
                  <button
                    type="button"
                    @click="removeOption(Number(qIndex), Number(oIndex))"
                    class="text-red-500 hover:text-red-700"
                  >
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                  </button>
                </div>
                <button
                  type="button"
                  @click="addOption(Number(qIndex))"
                  class="text-teal-500 hover:text-teal-700 text-sm"
                >
                  + Add Option
                </button>
              </div>

              <!-- Correct Answer for Short Answer -->
              <div v-else>
                <label class="text-sm text-gray-600">Expected Answer (for auto-grading):</label>
                <input
                  v-model="question.correct_answer"
                  type="text"
                  placeholder="Enter expected answer"
                  class="w-full border rounded-lg px-4 py-2 mt-1 focus:ring-2 focus:ring-teal-500"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Buttons -->
        <div class="flex justify-end gap-3 pt-4 border-t">
          <button
            type="button"
            @click="$emit('close')"
            class="px-6 py-2 border rounded-lg hover:bg-gray-50"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="submitting"
            class="px-6 py-2 bg-teal-500 text-white rounded-lg hover:bg-teal-600 disabled:opacity-50"
          >
            {{ submitting ? 'Saving...' : (isEditing ? 'Update Quiz' : 'Create Quiz') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed } from 'vue'

interface QuizOption {
  option_text: string
  is_correct: boolean
}

interface QuizQuestion {
  question_text: string
  question_type: 'MULTIPLE_CHOICE' | 'SHORT_ANSWER' | 'CHECKBOX'
  points: number
  options: QuizOption[]
  correct_answer?: string
}

const props = defineProps<{
  courseId: number | string
  quiz?: any
}>()

const emit = defineEmits(['close', 'saved'])

const config = useRuntimeConfig()
const token = useCookie('auth_token')

const isEditing = computed(() => !!props.quiz?.id)
const submitting = ref(false)

const form = reactive({
  title: props.quiz?.title || '',
  description: props.quiz?.description || '',
  time_limit: props.quiz?.time_limit || 30,
  passing_score: props.quiz?.passing_score || 70,
  questions: props.quiz?.questions?.map((q: any) => ({
    question_text: q.question_text,
    question_type: q.question_type,
    points: q.points || 1,
    options: q.options || [],
    correct_answer: q.correct_answer || ''
  })) || [] as QuizQuestion[]
})

const formatQuestionType = (type: string) => {
  switch (type) {
    case 'MULTIPLE_CHOICE': return 'Multiple Choice'
    case 'SHORT_ANSWER': return 'Short Answer'
    case 'CHECKBOX': return 'Checkboxes'
    default: return type
  }
}

const addQuestion = (type: 'MULTIPLE_CHOICE' | 'SHORT_ANSWER' | 'CHECKBOX') => {
  const question: QuizQuestion = {
    question_text: '',
    question_type: type,
    points: 1,
    options: type !== 'SHORT_ANSWER' ? [
      { option_text: '', is_correct: false },
      { option_text: '', is_correct: false }
    ] : [],
    correct_answer: ''
  }
  form.questions.push(question)
}

const removeQuestion = (index: number) => {
  form.questions.splice(index, 1)
}

const addOption = (questionIndex: number) => {
  form.questions[questionIndex].options.push({
    option_text: '',
    is_correct: false
  })
}

const removeOption = (questionIndex: number, optionIndex: number) => {
  form.questions[questionIndex].options.splice(optionIndex, 1)
}

const setCorrectAnswer = (qIndex: number, oIndex: number, type: string) => {
  if (type === 'MULTIPLE_CHOICE') {
    // Only one correct answer for multiple choice
    form.questions[qIndex].options.forEach((opt: QuizOption, i: number) => {
      opt.is_correct = i === oIndex
    })
  } else {
    // Toggle for checkbox
    form.questions[qIndex].options[oIndex].is_correct = 
      !form.questions[qIndex].options[oIndex].is_correct
  }
}

const handleSubmit = async () => {
  if (!form.title.trim()) {
    alert('Please enter a quiz title')
    return
  }

  if (form.questions.length === 0) {
    alert('Please add at least one question')
    return
  }

  // Validate questions
  for (let i = 0; i < form.questions.length; i++) {
    const q = form.questions[i]
    if (!q.question_text.trim()) {
      alert(`Please enter text for question ${i + 1}`)
      return
    }
    if (q.question_type !== 'SHORT_ANSWER' && q.options.length < 2) {
      alert(`Question ${i + 1} needs at least 2 options`)
      return
    }
  }

  submitting.value = true

  try {
    const payload = {
      title: form.title,
      description: form.description,
      time_limit: form.time_limit,
      passing_score: form.passing_score,
      questions: form.questions.map((q: QuizQuestion) => ({
        question_text: q.question_text,
        question_type: q.question_type,
        points: q.points,
        options: q.question_type !== 'SHORT_ANSWER' ? q.options : undefined,
        correct_answer: q.question_type === 'SHORT_ANSWER' ? q.correct_answer : undefined
      }))
    }

    const url = isEditing.value 
      ? `/api/quizzes/${props.quiz.id}`
      : `/api/quizzes/course/${props.courseId}`
    
    const method = isEditing.value ? 'PUT' : 'POST'

    const response = await $fetch(url, {
      baseURL: config.public.backendUrl as string,
      method: method,
      headers: {
        'Authorization': `Bearer ${token.value}`,
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      },
      body: payload
    })

    emit('saved', response)
    emit('close')
  } catch (err: any) {
    console.error('Failed to save quiz:', err)
    alert(err?.data?.message || 'Failed to save quiz')
  } finally {
    submitting.value = false
  }
}
</script>
