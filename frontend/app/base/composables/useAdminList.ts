import type { User } from '../types/user'

export interface PaginatedResponse<T> {
  current_page: number
  data: T[]
  first_page_url: string
  from: number
  last_page: number
  last_page_url: string
  next_page_url: string | null
  path: string
  per_page: number
  prev_page_url: string | null
  to: number
  total: number
}

export const useAdminList = () => {
  const config = useRuntimeConfig()

  const listUsers = async (page = 1, perPage = 20): Promise<PaginatedResponse<User> | null> => {
    try {
      const token = useCookie('auth_token').value
      const data = await $fetch<PaginatedResponse<User>>('/api/admin/list/users', {
        baseURL: config.public.backendUrl as string,
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json'
        },
        params: { page, per_page: perPage }
      })
      return data
    } catch (error) {
      console.error('Failed to fetch users:', error)
      return null
    }
  }

  const listCourses = async (page = 1, perPage = 20): Promise<PaginatedResponse<any> | null> => {
    try {
      const token = useCookie('auth_token').value
      const data = await $fetch<PaginatedResponse<any>>('/api/admin/list/courses', {
        baseURL: config.public.backendUrl as string,
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json'
        },
        params: { page, per_page: perPage }
      })
      return data
    } catch (error) {
      console.error('Failed to fetch courses:', error)
      return null
    }
  }

  const listCertificates = async (page = 1, perPage = 20): Promise<PaginatedResponse<any> | null> => {
    try {
      const token = useCookie('auth_token').value
      const data = await $fetch<PaginatedResponse<any>>('/api/admin/list/certificates', {
        baseURL: config.public.backendUrl as string,
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json'
        },
        params: { page, per_page: perPage }
      })
      return data
    } catch (error) {
      console.error('Failed to fetch certificates:', error)
      return null
    }
  }

  const listApplications = async (page = 1, perPage = 20): Promise<PaginatedResponse<any> | null> => {
    try {
      const token = useCookie('auth_token').value
      const data = await $fetch<PaginatedResponse<any>>('/api/admin/list/applications', {
        baseURL: config.public.backendUrl as string,
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json'
        },
        params: { page, per_page: perPage }
      })
      return data
    } catch (error) {
      console.error('Failed to fetch applications:', error)
      return null
    }
  }

  const listLogs = async (page = 1, perPage = 50): Promise<PaginatedResponse<any> | null> => {
    try {
      const token = useCookie('auth_token').value
      const data = await $fetch<PaginatedResponse<any>>('/api/admin/list/logs', {
        baseURL: config.public.backendUrl as string,
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json'
        },
        params: { page, per_page: perPage }
      })
      return data
    } catch (error) {
      console.error('Failed to fetch logs:', error)
      return null
    }
  }

  return {
    listUsers,
    listCourses,
    listCertificates,
    listApplications,
    listLogs
  }
}
