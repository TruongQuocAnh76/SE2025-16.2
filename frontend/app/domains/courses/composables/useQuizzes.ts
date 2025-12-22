import { ref } from 'vue'
import type { Quiz, Question, QuizAttempt, QuizAnswer, QuizStats } from '../types/course'

export const useQuizzes = () => {
  const config = useRuntimeConfig()


  // Reactive state
  const quizzes = ref<Quiz[]>([])
  const currentQuiz = ref<Quiz | null>(null)
  const currentAttempt = ref<QuizAttempt | null>(null)
  const questions = ref<Question[]>([])
  const userAnswers = ref<Record<string, any>>({})
  const attempts = ref<QuizAttempt[]>([])
  const quizStats = ref<QuizStats | null>(null)
  const loading = ref(false)
  const error = ref<Error | null>(null)

  // Fetch quizzes by course ID
  const getQuizzesByCourse = async (courseId: string): Promise<Quiz[]> => {
    try {
      loading.value = true
      error.value = null

      const response = await $fetch<any>(`/api/quizzes/course/${courseId}`, {
        baseURL: config.public.backendUrl as string,
        headers: {
          'Accept': 'application/json',
          'Authorization': `Bearer ${useCookie('auth_token').value}`
        }
      })

      if (response.success) {
        quizzes.value = response.data || []
        return response.data || []
      } else {
        throw new Error(response.message || 'Failed to fetch quizzes')
      }
    } catch (err: any) {
      error.value = err
      console.error('Error fetching quizzes:', err)
      return []
    } finally {
      loading.value = false
    }
  }

  // Fetch quiz by ID
  const getQuizById = async (quizId: string): Promise<Quiz | null> => {
    try {
      loading.value = true
      error.value = null

      const response = await $fetch<any>(`/api/quizzes/${quizId}`, {
        baseURL: config.public.backendUrl as string,
        headers: {
          'Accept': 'application/json',
          'Authorization': `Bearer ${useCookie('auth_token').value}`
        }
      })

      if (response.success) {
        currentQuiz.value = response.data
        return response.data
      } else {
        throw new Error(response.message || 'Failed to fetch quiz')
      }
    } catch (err: any) {
      error.value = err
      console.error('Error fetching quiz:', err)
      return null
    } finally {
      loading.value = false
    }
  }

  // Start a quiz attempt
  const startQuizAttempt = async (quizId: string): Promise<{ attempt: QuizAttempt, questions: Question[] } | null> => {
    try {
      loading.value = true
      error.value = null

      const response = await $fetch<any>(`/api/quizzes/${quizId}/start`, {
        method: 'POST',
        baseURL: config.public.backendUrl as string,
        headers: {
          'Accept': 'application/json',
          'Authorization': `Bearer ${useCookie('auth_token').value}`
        }
      })

      if (response.success) {
        currentAttempt.value = response.data.attempt
        questions.value = response.data.questions || []
        userAnswers.value = {}

        return {
          attempt: response.data.attempt,
          questions: response.data.questions || []
        }
      } else {
        throw new Error(response.message || 'Failed to start quiz')
      }
    } catch (err: any) {
      error.value = err
      console.error('Error starting quiz:', err)
      return null
    } finally {
      loading.value = false
    }
  }

  // Submit quiz attempt
  const submitQuizAttempt = async (
    attemptId: string,
    answers: string[],
    timeSpent?: number
  ): Promise<{ score: number, passed: boolean } | null> => {
    try {
      loading.value = true
      error.value = null

      const response = await $fetch<any>(`/api/quizzes/attempt/${attemptId}/submit`, {
        method: 'POST',
        baseURL: config.public.backendUrl as string,
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${useCookie('auth_token').value}`
        },
        body: JSON.stringify({
          answers,
          time_spent: timeSpent
        })
      })

      if (response.success) {
        // Clear current attempt state
        currentAttempt.value = null
        userAnswers.value = {}

        return {
          score: response.data.score,
          passed: response.data.passed
        }
      } else {
        throw new Error(response.message || 'Failed to submit quiz')
      }
    } catch (err: any) {
      error.value = err
      console.error('Error submitting quiz:', err)
      return null
    } finally {
      loading.value = false
    }
  }

  // Get quiz attempts history
  const getQuizAttempts = async (quizId: string): Promise<QuizAttempt[]> => {
    try {
      const response = await $fetch<any>(`/api/quizzes/${quizId}/attempts`, {
        baseURL: config.public.backendUrl as string,
        headers: {
          'Accept': 'application/json',
          'Authorization': `Bearer ${useCookie('auth_token').value}`
        }
      })

      if (response.success) {
        attempts.value = response.data || []
        return response.data || []
      } else {
        attempts.value = []
        return []
      }
    } catch (err: any) {
      console.error('Error fetching quiz attempts:', err)
      attempts.value = []
      return []
    }
  }

  // Get all attempts for a quiz (Teacher)
  const fetchAllAttempts = async (quizId: string): Promise<QuizAttempt[]> => {
    try {
      const response = await $fetch<any>(`/api/quizzes/${quizId}/all-attempts`, {
        baseURL: config.public.backendUrl as string,
        headers: {
          'Accept': 'application/json',
          'Authorization': `Bearer ${useCookie('auth_token').value}`
        }
      })

      if (response.success) {
        attempts.value = response.data || []
        return response.data || []
      } else {
        attempts.value = []
        return []
      }
    } catch (err: any) {
      console.error('Error fetching all attempts:', err)
      attempts.value = []
      return []
    }
  }

  // Get quiz statistics for current user
  const getQuizStats = async (quizId: string): Promise<QuizStats | null> => {
    try {
      const response = await $fetch<any>(`/api/quizzes/${quizId}/stats`, {
        baseURL: config.public.backendUrl as string,
        headers: {
          'Accept': 'application/json',
          'Authorization': `Bearer ${useCookie('auth_token').value}`
        }
      })

      if (response.success) {
        quizStats.value = response.data
        return response.data
      } else {
        return null
      }
    } catch (err: any) {
      console.error('Error fetching quiz stats:', err)
      return null
    }
  }

  // Get questions for a quiz
  const getQuizQuestions = async (quizId: string): Promise<Question[]> => {
    try {
      const response = await $fetch<any>(`/api/quizzes/${quizId}/questions`, {
        baseURL: config.public.backendUrl as string,
        headers: {
          'Accept': 'application/json',
          'Authorization': `Bearer ${useCookie('auth_token').value}`
        }
      })

      if (response.success) {
        questions.value = response.data || []
        return response.data || []
      } else {
        return []
      }
    } catch (err: any) {
      console.error('Error fetching quiz questions:', err)
      return []
    }
  }

  // Save answer for a question
  const saveAnswer = (questionId: string, answer: any) => {
    userAnswers.value[questionId] = answer
  }

  // Get answer for a question
  const getAnswer = (questionId: string): any => {
    return userAnswers.value[questionId]
  }

  // Check if all questions are answered
  const isQuizComplete = (): boolean => {
    if (!questions.value.length) return false

    return questions.value.every(question => {
      if (!question.id) return false
      const answer = userAnswers.value[question.id]
      return answer !== undefined && answer !== null && answer !== '' &&
        (Array.isArray(answer) ? answer.length > 0 : true)
    })
  }

  // Get number of answered questions
  const getAnsweredQuestionsCount = (): number => {
    return Object.keys(userAnswers.value).filter(questionId => {
      const answer = userAnswers.value[questionId]
      return answer !== undefined && answer !== null && answer !== '' &&
        (Array.isArray(answer) ? answer.length > 0 : true)
    }).length
  }

  // Format quiz type for display
  const formatQuizType = (type: string): string => {
    switch (type) {
      case 'PRACTICE': return 'Practice Quiz'
      case 'GRADED': return 'Graded Quiz'
      case 'FINAL': return 'Final Exam'
      default: return type
    }
  }

  // Format time duration
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

  // Check if user can retake quiz
  const canRetakeQuiz = (quiz: Quiz, attempts: QuizAttempt[]): boolean => {
    if (!quiz.max_attempts) return true
    return attempts.length < quiz.max_attempts
  }

  // Get quiz status for user
  const getQuizStatus = (quiz: Quiz, attempts: QuizAttempt[]): 'not_attempted' | 'in_progress' | 'passed' | 'failed' | 'max_attempts_reached' => {
    if (!attempts.length) return 'not_attempted'

    const completedAttempts = attempts.filter(attempt => attempt.submitted_at)
    if (!completedAttempts.length) return 'in_progress'

    const bestScore = Math.max(...completedAttempts.map(attempt => attempt.score || 0))
    const hasPassed = completedAttempts.some(attempt => attempt.passed)

    if (hasPassed) return 'passed'

    if (quiz.max_attempts && attempts.length >= quiz.max_attempts) {
      return 'max_attempts_reached'
    }

    return 'failed'
  }

  // Reset quiz state
  const resetQuizState = () => {
    currentQuiz.value = null
    currentAttempt.value = null
    questions.value = []
    userAnswers.value = {}
    attempts.value = []
    quizStats.value = null
    error.value = null
  }

  // Format answers for submission based on question type
  const formatAnswersForSubmission = (): string[] => {
    return questions.value.map(question => {
      if (!question.id) return ''
      const answer = userAnswers.value[question.id]

      if (question.question_type === 'CHECKBOX' && Array.isArray(answer)) {
        return answer.join(',')
      }

      return answer || ''
    })
  }

  return {
    // State
    quizzes: readonly(quizzes),
    currentQuiz: readonly(currentQuiz),
    currentAttempt: readonly(currentAttempt),
    questions: readonly(questions),
    userAnswers: readonly(userAnswers),
    attempts: readonly(attempts),
    quizStats: readonly(quizStats),
    loading: readonly(loading),
    error: readonly(error),

    // Methods
    getQuizzesByCourse,
    getQuizById,
    startQuizAttempt,
    submitQuizAttempt,
    getQuizAttempts,
    fetchAllAttempts,
    getQuizStats,
    getQuizQuestions,
    saveAnswer,
    getAnswer,
    isQuizComplete,
    getAnsweredQuestionsCount,
    resetQuizState,
    formatAnswersForSubmission,

    // Utilities
    formatQuizType,
    formatTime,
    canRetakeQuiz,
    getQuizStatus
  }
}