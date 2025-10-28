import { ref, computed } from 'vue'
import type { User, LoginRequest, RegisterRequest, LoginResponse, RegisterResponse } from '../types/auth'

export const useAuth = () => {
  const config = useRuntimeConfig()
  const user = ref<User | null>(null)
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  const login = async (login: string, password: string) => {
    isLoading.value = true
    error.value = null

    try {
      const response = await $fetch('/api/auth/login', {
        baseURL: config.public.backendUrl,
        method: 'POST',
        body: {
          login,
          password
        }
      }) as LoginResponse

      // Store token in cookie
      const tokenCookie = useCookie('auth_token')
      tokenCookie.value = response.access_token

      // Store user data
      const userCookie = useCookie('user_data')
      userCookie.value = JSON.stringify(response.user)

      user.value = response.user

      return response
    } catch (err: any) {
      error.value = err.data?.message || 'Login failed'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const register = async (email: string, username: string, password: string) => {
    isLoading.value = true
    error.value = null

    try {
      const response = await $fetch('/api/auth/register', {
        baseURL: config.public.backendUrl,
        method: 'POST',
        body: {
          email,
          username,
          password
        }
      }) as RegisterResponse

      user.value = response.user

      return response
    } catch (err: any) {
      error.value = err.data?.message || 'Registration failed'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const logout = async () => {
    isLoading.value = true
    error.value = null

    try {
      await $fetch('/api/auth/logout', {
        baseURL: config.public.backendUrl,
        method: 'POST'
      })

      // Clear cookies
      const tokenCookie = useCookie('auth_token')
      tokenCookie.value = null

      const userCookie = useCookie('user_data')
      userCookie.value = null

      user.value = null
    } catch (err: any) {
      error.value = err.data?.message || 'Logout failed'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const getUser = async () => {
    isLoading.value = true
    error.value = null

    try {
      const response = await $fetch('/api/auth/me', {
        baseURL: config.public.backendUrl
      }) as User

      user.value = response

      return response
    } catch (err: any) {
      error.value = err.data?.message || 'Failed to get user'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const isAuthenticated = computed(() => {
    const tokenCookie = useCookie('auth_token')
    return !!tokenCookie.value
  })

  return {
    user,
    isLoading,
    error,
    login,
    register,
    logout,
    getUser,
    isAuthenticated
  }
}
