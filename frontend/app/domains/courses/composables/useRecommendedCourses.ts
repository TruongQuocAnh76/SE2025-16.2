import { useFetch } from '#app'
import { watch, ref } from 'vue'

export function useRecommendedCourses() {
  const config = useRuntimeConfig()
  const backendUrl = config.public.backendUrl
  const url = `${backendUrl}/api/recommendations`
  
  const headers = ref({
    Accept: 'application/json',
    'Content-Type': 'application/json',
  })

  // Get token from cookie safely
  try {
    const tokenCookie = useCookie('auth_token')
    if (tokenCookie.value) {
      (headers.value as any).Authorization = `Bearer ${tokenCookie.value}`
    }
  } catch (e) {
    console.warn('Could not get auth token:', e)
  }

  const { data, pending, error, refresh } = useFetch(url, {
    method: 'GET',
    headers: headers.value,
  })

  watch(error, (newError) => {
    if (newError) {
      console.error('Recommendation API Error:', {
        url,
        error: newError,
        status: (newError as any)?.status,
        statusCode: (newError as any)?.statusCode,
      })
    }
  })

  return { data, pending, error, refresh }
}

