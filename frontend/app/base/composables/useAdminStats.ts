import type { DashboardStats, TeacherApplication, CourseApplication, CertificatesOverview, RecentCertificate, SystemLogEntry } from '../types/admin'

export const useAdminStats = () => {
  const config = useRuntimeConfig()

  const getDashboardStats = async (): Promise<DashboardStats | null> => {
    try {
      const token = useCookie('auth_token').value
      const data = await $fetch<DashboardStats>('/api/admin/dashboard-stats', {
        baseURL: config.public.backendUrl as string,
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json'
        }
      })
      return data
    } catch (error) {
      console.error('Failed to fetch dashboard stats:', error)
      return null
    }
  }

  const getTeacherApplications = async (): Promise<TeacherApplication[]> => {
    try {
      const token = useCookie('auth_token').value
      const data = await $fetch<TeacherApplication[]>('/api/admin/teacher-applications', {
        baseURL: config.public.backendUrl as string,
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json'
        }
      })
      return data || []
    } catch (error) {
      console.error('Failed to fetch teacher applications:', error)
      return []
    }
  }

  const getCourseApplications = async (): Promise<CourseApplication[]> => {
    try {
      const token = useCookie('auth_token').value
      const data = await $fetch<CourseApplication[]>('/api/admin/course-applications', {
        baseURL: config.public.backendUrl as string,
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json'
        }
      })
      return data || []
    } catch (error) {
      console.error('Failed to fetch course applications:', error)
      return []
    }
  }

  const getCertificatesOverview = async (): Promise<CertificatesOverview | null> => {
    try {
      const token = useCookie('auth_token').value
      const data = await $fetch<CertificatesOverview>('/api/admin/certificates-overview', {
        baseURL: config.public.backendUrl as string,
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json'
        }
      })
      return data
    } catch (error) {
      console.error('Failed to fetch certificates overview:', error)
      return null
    }
  }

  const getRecentCertificates = async (): Promise<RecentCertificate[]> => {
    try {
      const token = useCookie('auth_token').value
      const data = await $fetch<RecentCertificate[]>('/api/admin/recent-certificates', {
        baseURL: config.public.backendUrl as string,
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json'
        }
      })
      return data || []
    } catch (error) {
      console.error('Failed to fetch recent certificates:', error)
      return []
    }
  }

  const getSystemLogs = async (): Promise<SystemLogEntry[]> => {
    try {
      const token = useCookie('auth_token').value
      const data = await $fetch<SystemLogEntry[]>('/api/admin/system-logs', {
        baseURL: config.public.backendUrl as string,
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json'
        }
      })
      return data || []
    } catch (error) {
      console.error('Failed to fetch system logs:', error)
      return []
    }
  }

  const approveCourse = async (courseId: string): Promise<boolean> => {
    try {
      const token = useCookie('auth_token').value
      await $fetch(`/api/admin/courses/${courseId}/approve`, {
        baseURL: config.public.backendUrl as string,
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json'
        }
      })
      return true
    } catch (error) {
      console.error('Failed to approve course:', error)
      return false
    }
  }

  const rejectCourse = async (courseId: string, reason?: string): Promise<boolean> => {
    try {
      const token = useCookie('auth_token').value
      await $fetch(`/api/admin/courses/${courseId}/reject`, {
        baseURL: config.public.backendUrl as string,
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json'
        },
        body: { reason }
      })
      return true
    } catch (error) {
      console.error('Failed to reject course:', error)
      return false
    }
  }

  const approveTeacher = async (applicationId: string): Promise<boolean> => {
    try {
      const token = useCookie('auth_token').value
      await $fetch(`/api/admin/teacher-applications/${applicationId}/approve`, {
        baseURL: config.public.backendUrl as string,
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json'
        }
      })
      return true
    } catch (error) {
      console.error('Failed to approve teacher application:', error)
      return false
    }
  }

  const rejectTeacher = async (applicationId: string, reason?: string): Promise<boolean> => {
    try {
      const token = useCookie('auth_token').value
      await $fetch(`/api/admin/teacher-applications/${applicationId}/reject`, {
        baseURL: config.public.backendUrl as string,
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json'
        },
        body: { reason }
      })
      return true
    } catch (error) {
      console.error('Failed to reject teacher application:', error)
      return false
    }
  }

  return {
    getDashboardStats,
    getTeacherApplications,
    getCourseApplications,
    getCertificatesOverview,
    getRecentCertificates,
    getSystemLogs,
    approveCourse,
    rejectCourse,
    approveTeacher,
    rejectTeacher
  }
}
