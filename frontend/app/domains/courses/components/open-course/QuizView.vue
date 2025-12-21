<template>
  <div class="bg-white rounded-lg shadow">
    <!-- Quiz Header -->
    <div class="bg-teal-500 text-white p-6 rounded-t-lg">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-xl font-bold">{{ quiz.title }}</h2>
          <p class="text-teal-100 text-sm mt-1">{{ quiz.description || 'Course Practice Quiz' }}</p>
        </div>
        <div class="flex items-center gap-4">
          <div class="text-right">
            <div class="text-2xl font-bold">{{ timeLeft }}</div>
            <div class="text-xs text-teal-100">{{ quizStarted ? 'Time Left' : 'Time Limit' }}</div>
          </div>
          <div v-if="isTeacher" class="flex gap-2">
            <button @click="$emit('edit', quiz)" class="bg-white text-teal-600 px-3 py-1 rounded text-sm hover:bg-teal-50">
              Edit
            </button>
            <button @click="$emit('delete', quiz)" class="bg-red-500 text-white px-3 py-1 rounded text-sm hover:bg-red-600">
              Delete
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Quiz Not Started -->
    <div v-if="!quizStarted && !quizFinished" class="p-6">
      <div class="bg-gray-50 rounded-lg p-6 mb-6">
        <h3 class="font-bold text-lg mb-4">Course Practice Quiz</h3>
        <p class="text-gray-600 text-sm mb-4">
          Welcome to your course quiz!<br>
          This quiz is designed to test your understanding of the lessons you've just completed.<br>
          You'll have {{ quiz.time_limit || 30 }} minutes to finish all questions.
        </p>
        <p class="text-gray-600 text-sm mb-2"><strong>Format:</strong></p>
        <ul class="text-gray-600 text-sm list-disc list-inside mb-4">
          <li>Multiple-choice questions: choose the correct answer.</li>
          <li>Short answer questions: type your responses clearly and concisely.</li>
        </ul>
        <p class="text-gray-600 text-sm mb-2"><strong>Note:</strong></p>
        <ul class="text-gray-600 text-sm list-disc list-inside">
          <li>Once you start, the timer will begin counting down.</li>
          <li>You cannot pause or restart the quiz.</li>
          <li>Submissions after the time limit will not be accepted.</li>
        </ul>
      </div>

      <button 
        @click="startQuiz"
        class="w-full bg-teal-500 text-white py-3 rounded-lg font-semibold hover:bg-teal-600 transition"
      >
        Start Quiz
      </button>
    </div>

    <!-- Quiz In Progress -->
    <div v-else-if="quizStarted && !quizFinished" class="p-6">
      <div class="space-y-6">
        <div 
          v-for="(question, index) in questions" 
          :key="question.id"
          class="bg-pink-50 rounded-lg p-6"
        >
          <div class="flex justify-between items-start mb-4">
            <h4 class="font-semibold">{{ index + 1 }}. {{ question.question_text }}</h4>
            <span class="text-sm text-gray-500">{{ question.points }} Points</span>
          </div>

          <!-- Multiple Choice -->
          <div v-if="question.question_type === 'MULTIPLE_CHOICE'" class="space-y-3">
            <label 
              v-for="option in question.options" 
              :key="option.id"
              class="flex items-center gap-3 cursor-pointer"
            >
              <input 
                type="radio" 
                :name="`question-${question.id}`"
                :value="option.id"
                v-model="answers[question.id]"
                class="w-4 h-4 text-teal-500"
              />
              <span>{{ option.option_text }}</span>
            </label>
          </div>

          <!-- Short Answer -->
          <div v-else-if="question.question_type === 'SHORT_ANSWER'">
            <textarea
              v-model="answers[question.id]"
              placeholder="Write your answer"
              class="w-full border rounded-lg p-3 resize-none"
              rows="3"
            ></textarea>
          </div>

          <!-- Checkboxes (Multiple Select) -->
          <div v-else-if="question.question_type === 'CHECKBOX'" class="space-y-3">
            <label 
              v-for="option in question.options" 
              :key="option.id"
              class="flex items-center gap-3 cursor-pointer"
            >
              <input 
                type="checkbox" 
                :value="option.id"
                v-model="checkboxAnswers[question.id]"
                class="w-4 h-4 text-teal-500 rounded"
              />
              <span>{{ option.option_text }}</span>
            </label>
          </div>
        </div>
      </div>

      <button 
        @click="submitQuiz"
        :disabled="submitting"
        class="w-full mt-6 bg-teal-500 text-white py-3 rounded-lg font-semibold hover:bg-teal-600 transition disabled:opacity-50"
      >
        {{ submitting ? 'Submitting...' : 'Submit Quiz' }}
      </button>
    </div>

    <!-- Quiz Finished / Results -->
    <div v-else-if="quizFinished" class="p-6">
      <div class="text-center py-8">
        <div :class="[
          'w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-4',
          passed ? 'bg-green-100' : 'bg-red-100'
        ]">
          <svg v-if="passed" class="w-12 h-12 text-green-500" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
          </svg>
          <svg v-else class="w-12 h-12 text-red-500" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
          </svg>
        </div>
        
        <h3 class="text-2xl font-bold mb-2">{{ passed ? 'Congratulations!' : 'Quiz Completed' }}</h3>
        <p class="text-gray-600 mb-6">
          {{ passed ? 'You passed the quiz!' : 'You did not pass this time. Keep learning!' }}
        </p>

        <div class="bg-gray-50 rounded-lg p-6 max-w-sm mx-auto">
          <div class="text-4xl font-bold text-teal-600 mb-2">{{ score }}%</div>
          <p class="text-gray-500">Your Score</p>
          <p class="text-sm text-gray-400 mt-2">Passing: {{ quiz.passing_score || 70 }}%</p>
        </div>

        <button 
          @click="resetQuiz"
          class="mt-6 bg-teal-500 text-white px-6 py-2 rounded-lg hover:bg-teal-600"
        >
          Try Again
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'

const props = defineProps<{
  quiz: any
  course: any
  isTeacher: boolean
}>()

const emit = defineEmits(['edit', 'delete'])

const config = useRuntimeConfig()
const token = useCookie('auth_token')

// State
const questions = ref<any[]>([])
const answers = ref<Record<string, any>>({})
const checkboxAnswers = ref<Record<string, string[]>>({})
const quizStarted = ref(false)
const quizFinished = ref(false)
const submitting = ref(false)
const score = ref(0)
const passed = ref(false)
const timeRemaining = ref(0)
let timerInterval: any = null

// Computed
const timeLeft = computed(() => {
  if (!quizStarted.value) {
    return `${props.quiz.time_limit || 30} min`
  }
  const mins = Math.floor(timeRemaining.value / 60)
  const secs = timeRemaining.value % 60
  return `${mins}:${secs.toString().padStart(2, '0')}`
})

// Methods
const startQuiz = async () => {
  try {
    // Start attempt
    const response = await $fetch<any>(`/api/quizzes/${props.quiz.id}/start`, {
      baseURL: config.public.backendUrl as string,
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token.value}`,
        'Accept': 'application/json'
      }
    })

    // Load questions
    const quizData = await $fetch<any>(`/api/quizzes/${props.quiz.id}`, {
      baseURL: config.public.backendUrl as string,
      headers: {
        'Authorization': `Bearer ${token.value}`,
        'Accept': 'application/json'
      }
    })
    
    questions.value = quizData.questions || []
    
    // Initialize checkbox answers
    questions.value.forEach(q => {
      if (q.question_type === 'CHECKBOX') {
        checkboxAnswers.value[q.id] = []
      }
    })

    quizStarted.value = true
    timeRemaining.value = (props.quiz.time_limit || 30) * 60
    
    // Start timer
    timerInterval = setInterval(() => {
      timeRemaining.value--
      if (timeRemaining.value <= 0) {
        submitQuiz()
      }
    }, 1000)

  } catch (err) {
    console.error('Failed to start quiz:', err)
    alert('Failed to start quiz')
  }
}

const submitQuiz = async () => {
  if (submitting.value) return
  submitting.value = true
  
  clearInterval(timerInterval)

  try {
    // Prepare answers
    const formattedAnswers = questions.value.map(q => {
      let answer = answers.value[q.id]
      if (q.question_type === 'CHECKBOX') {
        answer = checkboxAnswers.value[q.id] || []
      }
      return {
        question_id: q.id,
        answer: answer
      }
    })

    const response = await $fetch<any>(`/api/quizzes/attempt/${props.quiz.id}/submit`, {
      baseURL: config.public.backendUrl as string,
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token.value}`,
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      },
      body: { answers: formattedAnswers }
    })

    score.value = Math.round(response.score || 0)
    passed.value = score.value >= (props.quiz.passing_score || 70)
    quizFinished.value = true

  } catch (err) {
    console.error('Failed to submit quiz:', err)
    alert('Failed to submit quiz')
  } finally {
    submitting.value = false
  }
}

const resetQuiz = () => {
  quizStarted.value = false
  quizFinished.value = false
  answers.value = {}
  checkboxAnswers.value = {}
  score.value = 0
  passed.value = false
}

onUnmounted(() => {
  if (timerInterval) clearInterval(timerInterval)
})
</script>
