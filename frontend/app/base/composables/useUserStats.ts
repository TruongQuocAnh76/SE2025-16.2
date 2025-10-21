import type { Enrollment, Certificate, CourseTimeSpent, CourseProgress } from '../types/userStats'

export const useUserStats = () => {
  
  // Get current user enrollments
  const getUserEnrollments = async (userId: string): Promise<Enrollment[]> => {
    try {
      const token = useCookie('auth_token').value
      const data = await $fetch<Enrollment[]>(`/api/users/${userId}/enrollments`, {
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
  const getUserCertificates = async (userId: string): Promise<Certificate[]> => {
    try {
      const token = useCookie('auth_token').value
      const data = await $fetch<Certificate[]>(`/api/users/${userId}/certificates`, {
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

  // Get total learning hours for user
  const getUserLearningHours = async (userId: string): Promise<number> => {
    try {
      const token = useCookie('auth_token').value
      const data = await $fetch<CourseTimeSpent[]>(`/api/learning/student/${userId}/courses/time-spent`, {
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

  // Get average progress across all enrolled courses
  const getAverageCourseProgress = async (userId: string): Promise<number> => {
    try {
      const enrollments = await getUserEnrollments(userId)
      if (enrollments.length === 0) return 0
      
      const totalProgress = enrollments.reduce((sum, enrollment) => sum + enrollment.progress, 0)
      return Math.round(totalProgress / enrollments.length)
    } catch (error) {
      console.error('Failed to fetch average course progress:', error)
      return 0
    }
  }

  // Get courses for continue learning (active enrollments with progress > 0)
  const getContinueLearningCourses = async (userId: string): Promise<Array<{
    courseId: string
    name: string
    thumbnail: string
    lastAccessed: string
    progress: number
  }>> => {
    try {
      const enrollments = await getUserEnrollments(userId)
      return enrollments
        .filter(enrollment => enrollment.status === 'ACTIVE')
        .map(enrollment => ({
          courseId: enrollment.course.id,
          name: enrollment.course.title,
          thumbnail: '/placeholder-course.jpg', // Placeholder thumbnail
          lastAccessed: enrollment.enrolled_at, // Using enrolled_at as last accessed
          progress: enrollment.progress
        }))
    } catch (error) {
      console.error('Failed to fetch continue learning courses:', error)
      return []
    }
  }

  return {
    getUserEnrollments,
    getUserCertificates,
    getUserLearningHours,
    getCourseProgress,
    getAverageCourseProgress,
    getContinueLearningCourses
  }
}
