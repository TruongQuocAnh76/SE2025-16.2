import { useFetch } from '#app'

export function useRecommendedCourses() {
  const { data, pending, error, refresh } = useFetch('/api/courses/recommendations', {
    method: 'GET',
    credentials: 'include',
    headers: {
      Accept: 'application/json',
    },
  })

  return { data, pending, error, refresh }
}