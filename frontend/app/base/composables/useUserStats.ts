import type { Enrollment, Certificate, CourseTimeSpent, CourseProgress, User } from '../types/userStats'

export const useUserStats = () => {
  const config = useRuntimeConfig()
  
  // Use useState for shared state across all components and SSR
  const currentUser = useState<User | null>('user-stats-current-user', () => null)
  
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
        baseURL: config.public.backendUrl as string,
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
    try {
      const token = useCookie('auth_token').value
      console.log('Fetching certificates with token:', token ? 'present' : 'missing')
      const data = await $fetch<Certificate[]>(`/api/certificates/mine`, {
        baseURL: config.public.backendUrl as string,
        headers: {
          'Authorization': `Bearer ${token}`
        }
      })
      console.log('Raw certificates data:', data)
      
      // Handle case where API returns object instead of array
      let certificates: Certificate[] = []
      if (Array.isArray(data)) {
        certificates = data
      } else if (typeof data === 'object' && data !== null) {
        // Convert object with numeric keys to array
        certificates = Object.values(data)
      }
      
      console.log('Processed certificates:', certificates)
      return certificates || []
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
    try {
      const certificates = await getUserCertificates()
      
      // Deduplicate by course_id and count unique certificates
      const uniqueCourseIds = new Set(
        certificates
          .filter(cert => cert.status === 'ISSUED' || cert.status === 'PENDING')
          .map(cert => cert.course_id)
      )
      
      return uniqueCourseIds.size
    } catch (error) {
      console.error('Failed to fetch certificate count:', error)
      return 0
    }
  }

  // Get quiz attempts count for a specific user
  const getQuizAttemptsCount = async (): Promise<number> => {
    const userId = currentUser.value?.id
    if (!userId) return 0
    
    try {
      const token = useCookie('auth_token').value
      const data = await $fetch<{count: number}>(`/api/users/${userId}/quiz-attempts-count`, {
        baseURL: config.public.backendUrl as string,
        headers: {
          'Authorization': `Bearer ${token}`
        }
      })
      return data.count
    } catch (error) {
      console.error('Failed to fetch quiz attempts count:', error)
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
        baseURL: config.public.backendUrl as string,
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

  // Get courses progress
  const getCoursesProgress = async (): Promise<any[]> => {
    try {
      const token = useCookie('auth_token').value
      const data = await $fetch<any[]>('/api/learning/courses/progress', {
        baseURL: config.public.backendUrl as string,
        headers: {
          'Authorization': `Bearer ${token}`
        }
      })
      return data || []
    } catch (error) {
      console.error('Failed to fetch courses progress:', error)
      return []
    }
  }

  // Get average progress across all enrolled courses for a specific user
  const getAverageCourseProgress = async (): Promise<number> => {
    const userId = currentUser.value?.id
    if (!userId) return 0
    
    try {
      const token = useCookie('auth_token').value
      const data = await $fetch<any[]>('/api/learning/courses/progress', {
        baseURL: config.public.backendUrl as string,
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
        baseURL: config.public.backendUrl as string,
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
    id: string
    courseName: string
    dateIssued: string
    pdfUrl?: string
  }>> => {
    try {
      const certificates = await getUserCertificates()
      
      // Deduplicate certificates by course_id (keep only the most recent one per course)
      const uniqueCertificates = certificates
        .filter(cert => cert.status === 'ISSUED' || cert.status === 'PENDING')
        .sort((a, b) => new Date(b.issued_at).getTime() - new Date(a.issued_at).getTime())
        .reduce((acc, cert) => {
          // Only add if we haven't seen this course_id yet
          if (!acc.some(c => c.course_id === cert.course_id)) {
            acc.push(cert)
          }
          return acc
        }, [] as typeof certificates)
      
      return uniqueCertificates
        .slice(0, 3) // Get only the 3 most recent unique certificates
        .map(cert => ({
          id: cert.id,
          courseName: cert.course?.title || 'Unknown Course',
          dateIssued: new Date(cert.issued_at).toLocaleDateString(),
          pdfUrl: cert.pdf_url
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
      const data = await $fetch<any>(`/api/auth/me`, {
        baseURL: config.public.backendUrl as string,
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
    getCoursesProgress,
    getAverageCourseProgress,
    getContinueLearningCourses,
    getEnrollmentCount,
    getCertificateCount,
    getQuizAttemptsCount,
    getRecentCertificates,
    getCurrentUser,
    setCurrentUser
  }
}
