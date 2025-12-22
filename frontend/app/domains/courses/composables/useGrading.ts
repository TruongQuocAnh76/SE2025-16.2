import { ref, readonly } from 'vue'

export const useGrading = () => {
    const config = useRuntimeConfig()
    const loading = ref(false)
    const error = ref<Error | null>(null)

    // Fetch attempt review data
    const getAttemptReview = async (attemptId: string) => {
        try {
            loading.value = true
            error.value = null

            const response = await $fetch<any>(`/api/grading/attempts/${attemptId}/review`, {
                baseURL: config.public.backendUrl as string,
                headers: {
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${useCookie('auth_token').value}`
                }
            })

            if (response.success) {
                return response.data
            } else {
                throw new Error(response.message || 'Failed to fetch review data')
            }
        } catch (err: any) {
            error.value = err
            console.error('Error fetching review:', err)
            return null
        } finally {
            loading.value = false
        }
    }

    // Bulk submit grades
    const bulkGradeAnswers = async (attemptId: string, grades: any[]) => {
        try {
            loading.value = true
            error.value = null

            const response = await $fetch<any>(`/api/grading/attempts/${attemptId}/bulk-grade`, {
                method: 'POST',
                baseURL: config.public.backendUrl as string,
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${useCookie('auth_token').value}`
                },
                body: JSON.stringify({ grades })
            })

            if (response.success) {
                return response.data
            } else {
                throw new Error(response.message || 'Failed to submit grades')
            }
        } catch (err: any) {
            error.value = err
            console.error('Error submitting grades:', err)
            throw err // Re-throw to handle in component (e.g., showing toast)
        } finally {
            loading.value = false
        }
    }

    return {
        loading: readonly(loading),
        error: readonly(error),
        getAttemptReview,
        bulkGradeAnswers
    }
}
