<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50">
    <div class="text-center">
      <div class="animate-spin rounded-full h-16 w-16 border-b-4 border-teal-500 mx-auto mb-4"></div>
      <p class="text-gray-600 text-lg">Logging you in...</p>
      <p v-if="error" class="text-red-500 mt-4">{{ error }}</p>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'auth'
})

const route = useRoute()
const nuxtApp = useNuxtApp() as any
const toast = nuxtApp?.$toast ?? { error: () => {}, success: () => {} }
const error = ref('')

onMounted(async () => {
  try {
    const query = route.query ?? {}
    const token = query.token ?? ''
    const access_token = query.access_token ?? ''
    const user = query.user ?? ''
    const errorParam = query.error ?? ''

    if (errorParam) {
      error.value = String(errorParam)
      toast.error(error.value)
      setTimeout(() => {
        navigateTo('/auth/login')
      }, 2000)
      return
    }

    // Backend trả về 'token', không phải 'access_token'
    const authToken = token || access_token

    if (authToken && user) {
      // Store token in cookie
      const tokenCookie = useCookie('auth_token')
      tokenCookie.value = String(authToken)

      // Parse and store user data
      let userData = {} as any
      try {
        userData = JSON.parse(decodeURIComponent(String(user)))
      } catch (err) {
        error.value = 'User data is invalid.'
        toast.error(error.value)
        setTimeout(() => {
          navigateTo('/auth/login')
        }, 2000)
        return
      }
      const userCookie = useCookie('user_data')
      userCookie.value = JSON.stringify(userData)

      toast.success?.(`Welcome back, ${userData.first_name || 'user'}!`)

      // Redirect to user profile
      await navigateTo(`/s/${userData.username || ''}`)
    } else {
      error.value = 'Authentication failed. No token or user data received.'
      toast.error(error.value)
      setTimeout(() => {
        navigateTo('/auth/login')
      }, 2000)
    }
  } catch (e) {
    error.value = 'Authentication failed. Please try again.'
    console.error('OAuth callback error:', e)
    toast.error(error.value)
    setTimeout(() => {
      navigateTo('/auth/signin')
    }, 2000)
  }
})
</script>
