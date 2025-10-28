import type { Enrollment, Certificate, CourseTimeSpent, CourseProgress, User } from '../types/userStats'

export const useUserStats = () => {
  const config = useRuntimeConfig()
  
  // Global variable storing user data
  const currentUser = ref<User | null>(null)
  
  const setCurrentUser = (user: User | null) => {
    currentUser.value = user
  }
  
  // Get current user enrollments
  const getUserEnrollments = async (): Promise<Enrollment[]> => {
    const userId = currentUser.value?.id
    if (!userId) return []
    
    try {
      const token = useCookie('auth_token').value
      const data = await $fetch<Enrollment[]>(`/api/users/${userId}/enrollments`, {
        baseURL: config.public.apiBase as string,
        headers: {
          'Authorization': `Bearer ${token}`
        }
      })
      return data || []
    } catch (error) {
      console.error('Failed to fetch user enrollments:', error)
      return []
    }
  }

  // Get current user certificates
  const getUserCertificates = async (): Promise<Certificate[]> => {
    const userId = currentUser.value?.id
    if (!userId) return []
    
    try {
      const token = useCookie('auth_token').value
      const data = await $fetch<Certificate[]>(`/api/users/${userId}/certificates`, {
        baseURL: config.public.apiBase as string,
        headers: {
          'Authorization': `Bearer ${token}`
        }
      })
      return data || []
    } catch (error) {
      console.error('Failed to fetch user certificates:', error)
      return []
    }
  }

  // Get enrollment count for a specific user
  const getEnrollmentCount = async (): Promise<number> => {
    const userId = currentUser.value?.id
    if (!userId) return 0
    
    try {
      const token = useCookie('auth_token').value
      const enrollments = await getUserEnrollments()
      return enrollments.length
    } catch (error) {
      console.error('Failed to fetch enrollment count:', error)
      return 0
    }
  }

  // Get certificate count for a specific user
  const getCertificateCount = async (): Promise<number> => {
    const userId = currentUser.value?.id
    if (!userId) return 0
    
    try {
      const token = useCookie('auth_token').value
      const certificates = await getUserCertificates()
      return certificates.filter(cert => cert.status === 'ISSUED').length
    } catch (error) {
      console.error('Failed to fetch certificate count:', error)
      return 0
    }
  }

    // Get total learning hours for a specific user
  const getUserLearningHours = async (): Promise<number> => {
    const userId = currentUser.value?.id
    if (!userId) return 0
    
    try {
      const token = useCookie('auth_token').value
      const data = await $fetch<CourseTimeSpent[]>(`/api/learning/student/${userId}/courses/time-spent`, {
        baseURL: config.public.apiBase as string,
        headers: {
          'Authorization': `Bearer ${token}`
        }
      })
      
      // Sum all time spent across all courses and convert seconds to hours
      const totalSeconds = data.reduce((total, course) => total + course.total_time_spent, 0)
      return Math.round(totalSeconds / 3600 * 10) / 10 // Round to 1 decimal place
    } catch (error) {
      console.error('Failed to fetch learning hours:', error)
      return 0
    }
  }

  // Get course progress for a specific course
  const getCourseProgress = async (courseId: string): Promise<number> => {
    try {
      const token = useCookie('auth_token').value
      const data = await $fetch<CourseProgress>(`/api/learning/course/${courseId}`, {
        baseURL: config.public.apiBase as string,
        headers: {
          'Authorization': `Bearer ${token}`
        }
      })
      return data.enrollment?.progress || 0
    } catch (error) {
      console.error('Failed to fetch course progress:', error)
      return 0
    }
  }

  // Get average progress across all enrolled courses for a specific user
  const getAverageCourseProgress = async (): Promise<number> => {
    const userId = currentUser.value?.id
    if (!userId) return 0
    
    try {
      const token = useCookie('auth_token').value
      const data = await $fetch<any[]>('/api/learning/courses/progress', {
        baseURL: config.public.apiBase as string,
        headers: {
          'Authorization': `Bearer ${token}`
        }
      })
      
      if (data.length === 0) return 0
      
      const totalProgress = data.reduce((sum, course) => sum + course.progress, 0)
      return Math.round(totalProgress / data.length)
    } catch (error) {
      console.error('Failed to fetch average course progress:', error)
      return 0
    }
  }

  // Get courses for continue learning (active enrollments with progress > 0) for a specific user
  const getContinueLearningCourses = async (): Promise<Array<{
    courseId: string
    name: string
    thumbnail: string
    lastAccessed: string
    progress: number
  }>> => {
    const userId = currentUser.value?.id
    if (!userId) return []
    
    try {
      const token = useCookie('auth_token').value
      const data = await $fetch<any[]>('/api/learning/courses/progress', {
        baseURL: config.public.apiBase as string,
        headers: {
          'Authorization': `Bearer ${token}`
        }
      })
      
      return data
        .filter(course => course.enrollment_status === 'ACTIVE' && course.progress > 0)
        .map(course => ({
          courseId: course.course_id,
          name: course.course_title,
          thumbnail: '/placeholder-course.jpg', // Placeholder thumbnail
          lastAccessed: new Date(course.enrolled_at).toLocaleDateString(),
          progress: course.progress
        }))
    } catch (error) {
      console.error('Failed to fetch continue learning courses:', error)
      return []
    }
  }

  // Get recent certificates for a specific user
  const getRecentCertificates = async (): Promise<Array<{
    courseName: string
    dateIssued: string
  }>> => {
    const userId = currentUser.value?.id
    if (!userId) return []
    
    try {
      const token = useCookie('auth_token').value
      const certificates = await getUserCertificates()
      
      return certificates
        .filter(cert => cert.status === 'ISSUED')
        .sort((a, b) => new Date(b.issued_at).getTime() - new Date(a.issued_at).getTime())
        .slice(0, 3) // Get only the 3 most recent
        .map(cert => ({
          courseName: cert.course.title,
          dateIssued: new Date(cert.issued_at).toLocaleDateString()
        }))
    } catch (error) {
      console.error('Failed to fetch recent certificates:', error)
      return []
    }
  }

  // Get current user info
  const getCurrentUser = async (): Promise<User | null> => {
    try {
      const token = useCookie('auth_token').value
      const data = await $fetch<User>('/api/auth/me', {
        baseURL: config.public.apiBase as string,
        headers: {
          'Authorization': `Bearer ${token}`
        }
      })
      return data
    } catch (error) {
      console.error('Failed to fetch current user info:', error)
      return null
    }
  }

  return {
    currentUser,
    getUserEnrollments,
    getUserCertificates,
    getUserLearningHours,
    getCourseProgress,
    getAverageCourseProgress,
    getContinueLearningCourses,
    getEnrollmentCount,
    getCertificateCount,
    getRecentCertificates,
    getCurrentUser,
    setCurrentUser
  }
}
