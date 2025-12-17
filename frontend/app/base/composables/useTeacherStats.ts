import type { User } from '../types/userStats'
import type {
  TeacherCourse,
  TeacherCoursesResponse,
  StudentEnrollment,
  TeacherStudent,
  TeacherStudentsResponse,
  TeacherStatistics,
  TeacherStatisticsResponse,
  PendingSubmission,
  PendingCertificate
} from '../types/teacherStats'

export type { TeacherCourse, TeacherCoursesResponse, StudentEnrollment, TeacherStudent, TeacherStudentsResponse, TeacherStatistics, TeacherStatisticsResponse, PendingSubmission, PendingCertificate }

export const useTeacherStats = () => {
  const config = useRuntimeConfig()
  const { currentUser } = useUserStats()

  // Get teacher's courses
  const getTeacherCourses = async (teacherId?: string, status?: string): Promise<TeacherCoursesResponse | null> => {
    const id = teacherId || currentUser.value?.id
    if (!id) return null

    try {
      const token = useCookie('auth_token').value
      let url = `/api/teachers/${id}/courses`
      if (status) {
        url += `?status=${status}`
      }
      
      const data = await $fetch<TeacherCoursesResponse>(url, {
        baseURL: config.public.backendUrl as string,
        headers: {
          'Authorization': `Bearer ${token}`
        }
      })
      return data
    } catch (error) {
      console.error('Failed to fetch teacher courses:', error)
      return null
    }
  }

  // Get teacher's students
  const getTeacherStudents = async (teacherId?: string, courseId?: string): Promise<TeacherStudentsResponse | null> => {
    const id = teacherId || currentUser.value?.id
    if (!id) return null

    try {
      const token = useCookie('auth_token').value
      let url = `/api/teachers/${id}/students`
      if (courseId) {
        url += `?course_id=${courseId}`
      }
      
      const data = await $fetch<TeacherStudentsResponse>(url, {
        baseURL: config.public.backendUrl as string,
        headers: {
          'Authorization': `Bearer ${token}`
        }
      })
      return data
    } catch (error) {
      console.error('Failed to fetch teacher students:', error)
      return null
    }
  }

  // Get teacher statistics
  const getTeacherStatistics = async (teacherId?: string): Promise<TeacherStatisticsResponse | null> => {
    const id = teacherId || currentUser.value?.id
    if (!id) return null

    try {
      const token = useCookie('auth_token').value
      const data = await $fetch<TeacherStatisticsResponse>(`/api/teachers/${id}/statistics`, {
        baseURL: config.public.backendUrl as string,
        headers: {
          'Authorization': `Bearer ${token}`
        }
      })
      return data
    } catch (error) {
      console.error('Failed to fetch teacher statistics:', error)
      return null
    }
  }

  // Get pending submissions (mock - since API may not be fully implemented)
  // In real implementation, this would call the grading API
  const getPendingSubmissions = async (): Promise<PendingSubmission[]> => {
    // This would normally fetch from the grading API
    // For now, return mock data that matches the UI requirements
    return [
      {
        id: '1',
        student_id: 'student-1',
        student_name: 'Trương Quốc Anh',
        student_avatar: null,
        course_title: 'AI Engineer',
        assignment_title: 'Assignment 3 – AI Engineer',
        submitted_at: new Date(Date.now() - 20 * 60 * 1000).toISOString(),
        status: 'PENDING'
      },
      {
        id: '2',
        student_id: 'student-2',
        student_name: 'Nguyễn Văn Bình',
        student_avatar: null,
        course_title: 'Cloud Engineer',
        assignment_title: 'Assignment 2 – Cloud Engineer',
        submitted_at: new Date(Date.now() - 45 * 60 * 1000).toISOString(),
        status: 'PENDING'
      },
      {
        id: '3',
        student_id: 'student-3',
        student_name: 'Lê Thị Hoa',
        student_avatar: null,
        course_title: 'AI Engineer',
        assignment_title: 'Assignment 2 – AI Engineer',
        submitted_at: new Date(Date.now() - 2 * 60 * 60 * 1000).toISOString(),
        status: 'PENDING'
      }
    ]
  }

  // Get pending certificates (mock - would normally integrate with certificate API)
  const getPendingCertificates = async (): Promise<PendingCertificate[]> => {
    // This would normally fetch pending certificates from the API
    return [
      {
        id: '1',
        student_id: 'student-4',
        student_name: 'Mai Hoàng Anh',
        course_title: 'AI Engineer',
        completed_at: new Date().toISOString(),
        final_score: 92
      },
      {
        id: '2',
        student_id: 'student-5',
        student_name: 'Trần Thái Đại Dương',
        course_title: 'AI Engineer',
        completed_at: new Date().toISOString(),
        final_score: 88
      },
      {
        id: '3',
        student_id: 'student-6',
        student_name: 'Bùi Thảo An',
        course_title: 'AI Engineer',
        completed_at: new Date().toISOString(),
        final_score: 95
      }
    ]
  }

  // Format relative time
  const formatRelativeTime = (dateString: string): string => {
    const date = new Date(dateString)
    const now = new Date()
    const diffMs = now.getTime() - date.getTime()
    const diffMins = Math.floor(diffMs / (1000 * 60))
    const diffHours = Math.floor(diffMs / (1000 * 60 * 60))
    const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24))

    if (diffMins < 1) return 'Just now'
    if (diffMins < 60) return `${diffMins} min ago`
    if (diffHours < 24) return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`
    if (diffDays < 7) return `${diffDays} day${diffDays > 1 ? 's' : ''} ago`
    return date.toLocaleDateString()
  }

  // Format currency
  const formatCurrency = (amount: number): string => {
    if (amount >= 1000000) {
      return `${(amount / 1000000).toFixed(1)}m $`
    }
    if (amount >= 1000) {
      return `${(amount / 1000).toFixed(1)}k $`
    }
    return `${amount} $`
  }

  return {
    getTeacherCourses,
    getTeacherStudents,
    getTeacherStatistics,
    getPendingSubmissions,
    getPendingCertificates,
    formatRelativeTime,
    formatCurrency
  }
}
