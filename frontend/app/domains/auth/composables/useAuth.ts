import { ref, computed } from 'vue'
import type { User, LoginRequest, RegisterRequest, LoginResponse, RegisterResponse, AuthError } from '../types/auth'
import { validateLoginForm, validateRegisterForm } from '../utils/validation'

export const useAuth = () => {
  const config = useRuntimeConfig()
  const nuxtApp = useNuxtApp() as any
  const $toast = nuxtApp.$toast
  const user = ref<User | null>(null)
  const isLoading = ref(false)
  const error = ref<string | null>(null)
  const validationErrors = ref<Record<string, string>>({})

  const login = async (login: string, password: string) => {
    isLoading.value = true
    error.value = null
    validationErrors.value = {}

    // Client-side validation
    const validation = validateLoginForm(login, password)
    if (!validation.isValid) {
      validationErrors.value = validation.errors
      isLoading.value = false
      if ($toast) $toast.error('Please fix the validation errors')
      throw new Error('Validation failed')
    }

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

      if ($toast) $toast.success('Login successful! Welcome back.')

      return response
    } catch (err: any) {
      const errorMessage = err.data?.message || 'Login failed. Please try again.'
      error.value = errorMessage
      if ($toast) $toast.error(errorMessage)
      
      // Handle backend validation errors
      if (err.data?.errors) {
        validationErrors.value = err.data.errors
      }
      
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const register = async (email: string, username: string, password: string, confirmPassword: string, firstName: string, lastName: string) => {
    isLoading.value = true
    error.value = null
    validationErrors.value = {}

    // Client-side validation
    const validation = validateRegisterForm(email, username, password, confirmPassword, firstName, lastName)
    if (!validation.isValid) {
      validationErrors.value = validation.errors
      isLoading.value = false
      if ($toast) $toast.error('Please fix the validation errors')
      throw new Error('Validation failed')
    }

    try {
      const response = await $fetch('/api/auth/register', {
        baseURL: config.public.backendUrl,
        method: 'POST',
        body: {
          email,
          username,
          password,
          first_name: firstName,
          last_name: lastName
        }
      }) as RegisterResponse

      user.value = response.user

      if ($toast) $toast.success('Registration successful! Welcome to Certchain.')

      return response
    } catch (err: any) {
      const errorMessage = err.data?.message || 'Registration failed. Please try again.'
      error.value = errorMessage
      if ($toast) $toast.error(errorMessage)
      
      // Handle backend validation errors
      if (err.data?.errors) {
        const backendErrors: Record<string, string> = {}
        Object.keys(err.data.errors).forEach(key => {
          let mappedKey = key
          if (key === 'first_name') mappedKey = 'firstName'
          if (key === 'last_name') mappedKey = 'lastName'
          backendErrors[mappedKey] = err.data.errors[key][0] || err.data.errors[key]
        })
        validationErrors.value = backendErrors
      }
      
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

      if ($toast) $toast.success('Logged out successfully')
    } catch (err: any) {
      const errorMessage = err.data?.message || 'Logout failed'
      error.value = errorMessage
      if ($toast) $toast.error(errorMessage)
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

  const clearErrors = () => {
    error.value = null
    validationErrors.value = {}
  }

  return {
    user,
    isLoading,
    error,
    validationErrors,
    login,
    register,
    logout,
    getUser,
    isAuthenticated,
    clearErrors
  }
}
