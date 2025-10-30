import type { Course, CreateCourseData } from '../types/course'

export interface CreateCourseResponse {
  message: string
  course: Course
  thumbnail_upload_url?: string
}

export const useCourses = () => {
  const config = useRuntimeConfig()

  const createCourse = async (courseData: CreateCourseData): Promise<{ course: Course; thumbnailUploadUrl?: string } | null> => {
    try {
      const token = useCookie('auth_token').value
      if (!token) {
        throw new Error('No authentication token found')
      }

      const data = await $fetch<CreateCourseResponse>('/api/courses', {
        baseURL: config.public.backendUrl as string,
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json'
        },
        body: courseData
      })

      return {
        course: data.course,
        thumbnailUploadUrl: data.thumbnail_upload_url
      }
    } catch (error) {
      console.error('Failed to create course:', error)
      throw error
    }
  }

  const getCourses = async (params?: { status?: string; level?: string }): Promise<Course[]> => {
    try {
      const token = useCookie('auth_token').value
      if (!token) {
        throw new Error('No authentication token found')
      }

      const queryParams = new URLSearchParams()
      if (params?.status) queryParams.append('status', params.status)
      if (params?.level) queryParams.append('level', params.level)

      const queryString = queryParams.toString()
      const url = `/api/courses${queryString ? `?${queryString}` : ''}`

      const data = await $fetch<Course[]>(url, {
        baseURL: config.public.backendUrl as string,
        headers: {
          'Authorization': `Bearer ${token}`
        }
      })

      return data || []
    } catch (error) {
      console.error('Failed to fetch courses:', error)
      return []
    }
  }

  const getCourse = async (id: string): Promise<Course | null> => {
    try {
      const token = useCookie('auth_token').value
      if (!token) {
        throw new Error('No authentication token found')
      }

      const data = await $fetch<Course>(`/api/courses/${id}`, {
        baseURL: config.public.backendUrl as string,
        headers: {
          'Authorization': `Bearer ${token}`
        }
      })

      return data
    } catch (error) {
      console.error('Failed to fetch course:', error)
      return null
    }
  }

  const updateCourse = async (id: string, courseData: Partial<CreateCourseData>): Promise<Course | null> => {
    try {
      const token = useCookie('auth_token').value
      if (!token) {
        throw new Error('No authentication token found')
      }

      const data = await $fetch<{ message: string; course: Course }>(`/api/courses/${id}`, {
        baseURL: config.public.backendUrl as string,
        method: 'PUT',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json'
        },
        body: courseData
      })

      return data.course
    } catch (error) {
      console.error('Failed to update course:', error)
      throw error
    }
  }

  const deleteCourse = async (id: string): Promise<boolean> => {
    try {
      const token = useCookie('auth_token').value
      if (!token) {
        throw new Error('No authentication token found')
      }

      await $fetch(`/api/courses/${id}`, {
        baseURL: config.public.backendUrl as string,
        method: 'DELETE',
        headers: {
          'Authorization': `Bearer ${token}`
        }
      })

      return true
    } catch (error) {
      console.error('Failed to delete course:', error)
      return false
    }
  }

  const updateCourseThumbnail = async (courseId: string, thumbnailUrl: string): Promise<boolean> => {
    try {
      const token = useCookie('auth_token').value
      if (!token) {
        throw new Error('No authentication token found')
      }

      await $fetch<{ message: string }>(`/api/courses/${courseId}`, {
        baseURL: config.public.backendUrl as string,
        method: 'PUT',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json'
        },
        body: { thumbnail: thumbnailUrl }
      })

      return true
    } catch (error) {
      console.error('Failed to update course thumbnail:', error)
      return false
    }
  }

  const uploadCourseThumbnail = async (uploadUrl: string, file: File): Promise<boolean> => {
    try {
      const response = await fetch(uploadUrl, {
        method: 'PUT',
        body: file,
        headers: {
          'Content-Type': file.type
        }
      })

      return response.ok
    } catch (error) {
      console.error('Failed to upload thumbnail:', error)
      return false
    }
  }

  return {
    createCourse,
    getCourses,
    getCourse,
    updateCourse,
    deleteCourse,
    updateCourseThumbnail,
    uploadCourseThumbnail
  }
}
