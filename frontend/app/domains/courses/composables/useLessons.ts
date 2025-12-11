import { ref } from 'vue'
import type { Lesson, Module, Progress } from '../types/course'

export const useLessons = () => {
  const config = useRuntimeConfig()
  // Remove $fetch from useNuxtApp since it should be auto-imported

  // Reactive state
  const lessons = ref<Lesson[]>([])
  const currentLesson = ref<Lesson | null>(null)
  const lessonProgress = ref<Progress | null>(null)
  const loading = ref(false)
  const error = ref<Error | null>(null)

  // Fetch lesson by ID
  const getLessonById = async (lessonId: string): Promise<Lesson | null> => {
    try {
      loading.value = true
      error.value = null

      const response = await $fetch<any>(`/api/lessons/${lessonId}`, {
        baseURL: config.public.backendUrl as string,
        headers: {
          'Accept': 'application/json',
          'Authorization': `Bearer ${useCookie('auth_token').value}`
        }
      })

      if (response.success) {
        currentLesson.value = response.data
        return response.data
      } else {
        throw new Error(response.message || 'Failed to fetch lesson')
      }
    } catch (err: any) {
      error.value = err
      console.error('Error fetching lesson:', err)
      return null
    } finally {
      loading.value = false
    }
  }

  // Fetch lessons by module ID
  const getLessonsByModule = async (moduleId: string): Promise<Lesson[]> => {
    try {
      loading.value = true
      error.value = null

      const response = await $fetch<any>(`/api/modules/${moduleId}/lessons`, {
        baseURL: config.public.backendUrl as string,
        headers: {
          'Accept': 'application/json',
          'Authorization': `Bearer ${useCookie('auth_token').value}`
        }
      })

      if (response.success) {
        lessons.value = response.data || []
        return response.data || []
      } else {
        throw new Error(response.message || 'Failed to fetch lessons')
      }
    } catch (err: any) {
      error.value = err
      console.error('Error fetching lessons by module:', err)
      return []
    } finally {
      loading.value = false
    }
  }

  // Get course modules with lessons for navigation
  const getCourseModulesWithLessons = async (courseId: string): Promise<any> => {
    try {
      const response = await $fetch<any>(`/api/courses/${courseId}/modules`, {
        baseURL: config.public.backendUrl as string,
        headers: {
          'Accept': 'application/json',
          'Authorization': `Bearer ${useCookie('auth_token').value}`
        }
      })

      return response
    } catch (err: any) {
      console.error('Error fetching course modules:', err)
      return { modules: [] }
    }
  }

  // Mark lesson as completed
  const markLessonCompleted = async (lessonId: string): Promise<boolean> => {
    try {
      const response = await $fetch<any>(`/api/learning/lesson/${lessonId}/complete`, {
        method: 'POST',
        baseURL: config.public.backendUrl as string,
        headers: {
          'Accept': 'application/json',
          'Authorization': `Bearer ${useCookie('auth_token').value}`
        }
      })

      if (response.success || response.message) {
        return true
      } else {
        throw new Error('Failed to mark lesson as completed')
      }
    } catch (err: any) {
      console.error('Error marking lesson as completed:', err)
      error.value = err
      return false
    }
  }

  // Update time spent on lesson
  const updateLessonTimeSpent = async (lessonId: string, timeSpent: number): Promise<boolean> => {
    try {
      const response = await $fetch<any>(`/api/learning/lesson/${lessonId}/time`, {
        method: 'POST',
        baseURL: config.public.backendUrl as string,
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${useCookie('auth_token').value}`
        },
        body: JSON.stringify({
          time_spent: Math.floor(timeSpent)
        })
      })

      return response.success || true
    } catch (err: any) {
      console.error('Error updating time spent:', err)
      return false
    }
  }

  // Get lesson progress for a student
  const getLessonProgress = async (studentId: string, lessonId: string): Promise<Progress | null> => {
    try {
      const response = await $fetch<any>(`/api/learning/lesson/${lessonId}/progress`, {
        baseURL: config.public.backendUrl as string,
        headers: {
          'Accept': 'application/json',
          'Authorization': `Bearer ${useCookie('auth_token').value}`
        }
      })

      if (response.success) {
        lessonProgress.value = response.data
        return response.data
      }
      return null
    } catch (err: any) {
      console.error('Error fetching lesson progress:', err)
      return null
    }
  }

  // Get course progress with lesson completion status
  const getCourseProgress = async (courseId: string): Promise<any> => {
    try {
      const response = await $fetch<any>(`/api/learning/course/${courseId}`, {
        baseURL: config.public.backendUrl as string,
        headers: {
          'Accept': 'application/json',
          'Authorization': `Bearer ${useCookie('auth_token').value}`
        }
      })

      return response
    } catch (err: any) {
      console.error('Error fetching course progress:', err)
      return null
    }
  }

  // Get next and previous lessons in course
  const getAdjacentLessons = async (courseId: string, currentLessonId: string): Promise<{ prev: Lesson | null, next: Lesson | null }> => {
    try {
      const courseData = await getCourseModulesWithLessons(courseId)
      const modules = courseData.modules || []
      
      // Flatten all lessons from all modules
      const allLessons: Lesson[] = []
      modules.forEach((module: any) => {
        if (module.lessons) {
          allLessons.push(...module.lessons.sort((a: any, b: any) => a.order_index - b.order_index))
        }
      })

      const currentIndex = allLessons.findIndex((lesson: any) => lesson.id === currentLessonId)
      
      return {
        prev: currentIndex > 0 ? allLessons[currentIndex - 1] || null : null,
        next: currentIndex < allLessons.length - 1 ? allLessons[currentIndex + 1] || null : null
      }
    } catch (err: any) {
      console.error('Error getting adjacent lessons:', err)
      return { prev: null, next: null }
    }
  }

  // Check if user can access lesson (enrollment check)
  const canAccessLesson = async (courseId: string, lessonId: string): Promise<boolean> => {
    try {
      const lesson = await getLessonById(lessonId)
      if (!lesson) return false

      // If lesson is free, anyone can access it
      if (lesson.is_free) return true

      // Check enrollment
      const enrollmentResponse = await $fetch<any>(`/api/courses/${courseId}/enrollment/check`, {
        baseURL: config.public.backendUrl as string,
        headers: {
          'Accept': 'application/json',
          'Authorization': `Bearer ${useCookie('auth_token').value}`
        }
      })

      return enrollmentResponse.enrolled || false
    } catch (err: any) {
      console.error('Error checking lesson access:', err)
      return false
    }
  }

  // Check video processing status for HLS streaming
  const checkVideoProcessingStatus = async (lessonId: string): Promise<{ status: string; hls_url?: string } | null> => {
    try {
      const response = await $fetch<any>(`/api/courses/lesson/${lessonId}/hls-status`, {
        baseURL: config.public.backendUrl as string,
        headers: {
          'Accept': 'application/json',
          'Authorization': `Bearer ${useCookie('auth_token').value}`
        }
      })

      return response
    } catch (err: any) {
      console.error('Error checking video processing status:', err)
      return null
    }
  }

  // Format lesson duration
  const formatDuration = (seconds: number): string => {
    if (!seconds) return '0s'
    
    const hours = Math.floor(seconds / 3600)
    const minutes = Math.floor((seconds % 3600) / 60)
    const remainingSeconds = seconds % 60

    if (hours > 0) {
      return `${hours}h ${minutes}m ${remainingSeconds}s`
    } else if (minutes > 0) {
      return `${minutes}m ${remainingSeconds}s`
    } else {
      return `${remainingSeconds}s`
    }
  }

  // Get lesson content type icon
  const getContentTypeIcon = (contentType: string): string => {
    switch (contentType) {
      case 'VIDEO':
        return 'M15 8a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z'
      case 'PDF':
      case 'DOCUMENT':
        return 'M9 2a1 1 0 000 2h2a1 1 0 100-2H9z M4 5a2 2 0 012-2v1a1 1 0 00-1 1v6a1 1 0 001 1v1a2 2 0 01-2-2V5zM14 5a2 2 0 012 2v6a2 2 0 01-2 2v-1a1 1 0 001-1V6a1 1 0 00-1-1V5z'
      case 'TEXT':
        return 'M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4z M3 8h14v7a1 1 0 01-1 1H4a1 1 0 01-1-1V8z'
      case 'LINK':
        return 'M12.586 4.586a2 2 0 112.828 2.828l-3 3a2 2 0 01-2.828 0 1 1 0 00-1.414 1.414 4 4 0 005.656 0l3-3a4 4 0 00-5.656-5.656l-1.5 1.5a1 1 0 101.414 1.414l1.5-1.5zm-5 5a2 2 0 012.828 0 1 1 0 101.414-1.414 4 4 0 00-5.656 0l-3 3a4 4 0 105.656 5.656l1.5-1.5a1 1 0 10-1.414-1.414l-1.5 1.5a2 2 0 11-2.828-2.828l3-3z'
      default:
        return 'M9 2a1 1 0 000 2h2a1 1 0 100-2H9z M4 5a2 2 0 012-2v1a1 1 0 00-1 1v6a1 1 0 001 1v1a2 2 0 01-2-2V5z'
    }
  }

  return {
    // State
    lessons: readonly(lessons),
    currentLesson: readonly(currentLesson),
    lessonProgress: readonly(lessonProgress),
    loading: readonly(loading),
    error: readonly(error),
    
    // Methods
    getLessonById,
    getLessonsByModule,
    getCourseModulesWithLessons,
    markLessonCompleted,
    updateLessonTimeSpent,
    getLessonProgress,
    getCourseProgress,
    getAdjacentLessons,
    canAccessLesson,
    checkVideoProcessingStatus,
    
    // Utilities
    formatDuration,
    getContentTypeIcon
  }
}