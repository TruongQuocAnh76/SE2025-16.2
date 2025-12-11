import type { Course, CreateCourseData, Review, CreateReviewData, CreateCourseResponse, CourseDetailsResponse, AddReviewResponse, Tag, CreateCourseWithModulesData } from '../types/course'

export const useCourses = () => {
  const config = useRuntimeConfig()
  const token = useCookie('auth_token')

  const getAuthHeaders = () => {
    if (!token.value) {
      throw new Error('No authentication token found')
    }
    return {
      'Authorization': `Bearer ${token.value}`,
      'Accept': 'application/json',
    }
  }


  const createCourse = async (courseData: CreateCourseData | CreateCourseWithModulesData): Promise<CreateCourseResponse | null> => {
    try {
      const data = await $fetch<CreateCourseResponse>('/api/courses', {
        baseURL: config.public.backendUrl as string,
        method: 'POST',
        headers: {
          ...getAuthHeaders(),
          'Content-Type': 'application/json'
        },
        body: courseData
      })
      return data
    } catch (error) {
      console.error('Failed to create course:', error)
      throw error
    }
  }

  const getCourses = async (params?: { status?: string; level?: string }): Promise<Course[]> => {
    try {
      const queryParams = new URLSearchParams()
      if (params?.status) queryParams.append('status', params.status)
      if (params?.level) queryParams.append('level', params.level)

      const queryString = queryParams.toString()
      const url = `/api/courses${queryString ? `?${queryString}` : ''}`

      const data = await $fetch<Course[] | { data: Course[] }>(url, {
        baseURL: config.public.backendUrl as string,
        headers: { 'Accept': 'application/json' }
      })

      return Array.isArray(data) ? data : (data as any).data || []

    } catch (error) {
      console.error('Failed to fetch courses:', error)
      return []
    }
  }

  const searchCourses = async (query: string, limit: number = 10): Promise<{ data: Course[]; total: number; query: string } | null> => {
    try {
      const queryParams = new URLSearchParams({
        q: query,
        limit: limit.toString()
      })

      // Route này cũng là public
      const data = await $fetch<{ data: Course[]; total: number; query: string }>(`/api/courses/search?${queryParams}`, {
        baseURL: config.public.backendUrl as string,
        headers: { 'Accept': 'application/json' }
      })

      return data
    } catch (error) {
      console.error('Failed to search courses:', error)
      throw error
    }
  }

  const getCourseById = async (id: string): Promise<Course | null> => {
    try {
      const config = useRuntimeConfig()

      const response = await $fetch<CourseDetailsResponse>(`/api/courses/${id}`, {
        baseURL: config.public.backendUrl as string,
        headers: { 'Accept': 'application/json' }
      })

      // Gộp 'rating_counts' vào đối tượng 'course'
      if (response.course && response.rating_counts) {
        response.course.rating_counts = response.rating_counts;
      }

      // Trả về chỉ đối tượng 'course' đã được gộp
      return response.course;

    } catch (error) {
      console.error(`Lỗi khi lấy khóa học ${id}:`, error)
      return null
    }
  }

  const updateCourse = async (id: string, courseData: Partial<CreateCourseData>): Promise<CreateCourseResponse | null> => {
    try {
      const data = await $fetch<CreateCourseResponse>(`/api/courses/${id}`, {
        baseURL: config.public.backendUrl as string,
        method: 'PUT',
        headers: {
          ...getAuthHeaders(),
          'Content-Type': 'application/json'
        },
        body: courseData
      })
      return data
    } catch (error) {
      console.error('Failed to update course:', error)
      throw error
    }
  }

  const deleteCourse = async (id: string): Promise<boolean> => {
    try {
      await $fetch(`/api/courses/${id}`, {
        baseURL: config.public.backendUrl as string,
        method: 'DELETE',
        headers: getAuthHeaders()
      })
      return true
    } catch (error) {
      console.error('Failed to delete course:', error)
      return false
    }
  }

  const uploadCourseThumbnail = async (uploadUrl: string, file: File): Promise<boolean> => {
    try {
      const response = await fetch(uploadUrl, {
        method: 'PUT',
        body: file,
        headers: { 'Content-Type': file.type }
      })
      return response.ok
    } catch (error) {
      console.error('Failed to upload thumbnail:', error)
      return false
    }
  }

  const updateCourseThumbnail = async (courseId: string, thumbnailUrl: string): Promise<boolean> => {
    try {
      await $fetch(`/api/courses/${courseId}`, {
        baseURL: config.public.backendUrl as string,
        method: 'PUT',
        headers: {
          ...getAuthHeaders(),
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


  // === HÀM MỚI CHO TÍNH NĂNG REVIEW ===
  const addReview = async (courseId: string, reviewData: CreateReviewData): Promise<AddReviewResponse> => {

    const authToken = token.value
    if (!authToken) {
      throw new Error('Chưa đăng nhập!')
    }

    try {
      // Gọi $fetch và mong đợi kiểu AddReviewResponse
      const response = await $fetch<AddReviewResponse>(`/api/courses/${courseId}/review`, {
        baseURL: config.public.backendUrl as string,
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'Authorization': `Bearer ${authToken}`,
          'Content-Type': 'application/json'
        },
        body: reviewData
      })

      return response

    } catch (error) {
      console.error('Lỗi khi gửi đánh giá:', error)
      throw error
    }
  }

  const getTags = async (): Promise<Tag[]> => {
    try {
      // Route này public, không cần token
      const data = await $fetch<Tag[]>(`/api/tags`, {
        baseURL: config.public.backendUrl as string,
        headers: { 'Accept': 'application/json' }
      })
      return data || []
    } catch (error) {
      console.error('Failed to fetch tags:', error)
      return []
    }
  }

  const createTag = async (tagName: string): Promise<Tag | null> => {
    try {
      const data = await $fetch<{ message: string; tag: Tag }>('/api/tags', {
        baseURL: config.public.backendUrl as string,
        method: 'POST',
        headers: {
          ...getAuthHeaders(),
          'Content-Type': 'application/json'
        },
        body: { name: tagName }
      })
      return data.tag
    } catch (error) {
      console.error('Failed to create tag:', error)
      throw error
    }
  }

  const uploadLessonVideo = async (lessonId: string, uploadUrl: string, videoFile: File, originalVideoPath?: string, hlsBasePath?: string): Promise<boolean> => {
    try {
      // Step 1: Upload video to S3 using pre-signed URL
      const response = await fetch(uploadUrl, {
        method: 'PUT',
        body: videoFile,
        headers: { 'Content-Type': videoFile.type }
      })
      
      if (!response.ok) {
        throw new Error(`Upload failed with status: ${response.status}`)
      }
      
      // Step 2: Notify backend that upload is complete
      const notificationSuccess = await notifyVideoUploadComplete(lessonId, originalVideoPath!, hlsBasePath!, videoFile)
      
      return notificationSuccess
    } catch (error) {
      console.error('Failed to upload lesson video:', error)
      return false
    }
  }

  const notifyVideoUploadComplete = async (lessonId: string, originalVideoPath: string, hlsBasePath: string, videoFile: File): Promise<boolean> => {
    try {
      const data = await $fetch(`/api/courses/videos/${lessonId}/upload-complete`, {
        baseURL: config.public.backendUrl as string,
        method: 'POST',
        headers: {
          ...getAuthHeaders(),
          'Content-Type': 'application/json'
        },
        body: {
          original_video_path: originalVideoPath,
          hls_base_path: hlsBasePath,
          video_size: videoFile.size,
          video_duration: null // We don't have duration info from the file object
        }
      })
      
      console.log('Video upload notification sent successfully:', data)
      return true
    } catch (error) {
      console.error('Failed to notify video upload completion:', error)
      return false
    }
  }

  const checkHlsProcessingStatus = async (lessonId: string): Promise<{ status: string; message: string; hls_url?: string }> => {
    try {
      const data = await $fetch<{ status: string; message: string; hls_url?: string }>(`/api/courses/lesson/${lessonId}/hls-status`, {
        baseURL: config.public.backendUrl as string,
        method: 'GET',
        headers: {
          ...getAuthHeaders(),
          'Accept': 'application/json'
        }
      })
      return data
    } catch (error) {
      console.error('Failed to check HLS processing status:', error)
      return { status: 'error', message: 'Failed to check processing status' }
    }
  }

  const enrollInCourse = async (courseId: string): Promise<{ success: boolean; message: string; enrollment?: any }> => {
    try {
      const data = await $fetch<{ success: boolean; message: string; enrollment?: any }>(`/api/courses/${courseId}/enroll`, {
        baseURL: config.public.backendUrl as string,
        method: 'POST',
        headers: {
          ...getAuthHeaders(),
          'Content-Type': 'application/json'
        }
      })
      return data
    } catch (error: any) {
      console.error('Failed to enroll in course:', error)
      throw error
    }
  }

  const getRecommendations = async (): Promise<Course[]> => {
    try {
      const response = await $fetch<{ success: boolean; data: Course[] }>('/api/recommendations', {
        baseURL: config.public.backendUrl as string,
        method: 'GET',
        headers: getAuthHeaders()
      })
      return response.data || []
    } catch (error) {
      console.error('Failed to fetch recommendations:', error)
      return []
    }
  }


  return {
    createCourse,
    getCourses,
    searchCourses,
    getCourseById,
    updateCourse,
    deleteCourse,
    uploadCourseThumbnail,
    updateCourseThumbnail,
    addReview,
    getTags,
    createTag,
    getRecommendations,
    uploadLessonVideo,
    notifyVideoUploadComplete,
    checkHlsProcessingStatus,
    enrollInCourse,
  }
}