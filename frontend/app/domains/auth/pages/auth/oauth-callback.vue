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
const { $toast } = useNuxtApp() as any
const error = ref('')

onMounted(async () => {
  try {
    const query = route.query
    const token = query.token
    const user = query.user
    const errorParam = query.error

    if (errorParam) {
      error.value = String(errorParam)
      if ($toast && $toast.error) {
        $toast.error(error.value)
      }
      setTimeout(() => {
        navigateTo('/auth/login')
      }, 2000)
      return
    }

    if (token && user) {
      let userData = null
      try {
        userData = JSON.parse(decodeURIComponent(String(user)))
      } catch (parseErr) {
        error.value = 'User data is invalid.'
        if ($toast && $toast.error) {
          $toast.error(error.value)
        }
        setTimeout(() => {
          navigateTo('/auth/login')
        }, 2000)
        return
      }
      
      // Store token in cookie
      const tokenCookie = useCookie('auth_token')
      tokenCookie.value = String(token)
      
      // Store user data in cookie
      const userCookie = useCookie('user_data')
      userCookie.value = JSON.stringify(userData)
      
      if ($toast && $toast.success) {
        $toast.success(`Welcome back, ${userData?.first_name || userData?.email || 'User'}!`)
      }
      
      await navigateTo(`/s/${userData?.username || ''}`)
    } else {
      error.value = 'Authentication failed. No token or user data received.'
      if ($toast && $toast.error) {
        $toast.error(error.value)
      }
      setTimeout(() => {
        navigateTo('/auth/login')
      }, 2000)
    }
  } catch (e) {
    error.value = 'Authentication failed. Please try again.'
    console.error('OAuth callback error:', e)
    if ($toast && $toast.error) {
      $toast.error(error.value)
    }
    setTimeout(() => {
      navigateTo('/auth/login')
    }, 2000)
  }
})
</script>
