<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50">
    <div class="text-center">
      <div class="animate-spin rounded-full h-16 w-16 border-b-4 border-teal-500 mx-auto mb-4"></div>
      <p class="text-gray-600 text-lg">Logging you in...</p>
      <p v-if="error" class="text-red-500 mt-4">{{ error }}</p>
    </div>
  </div>
</template>

<script setup>
const route = useRoute()
const router = useRouter()
const error = ref('')

onMounted(async () => {
  try {
    const { token, access_token, user } = route.query
    
    // Backend trả về 'token', không phải 'access_token'
    const authToken = token || access_token
    
    if (authToken) {
      // Store token in localStorage
      localStorage.setItem('auth_token', authToken)
      
      // Parse and store user data
      if (user) {
        const userData = JSON.parse(decodeURIComponent(user))
        localStorage.setItem('user', JSON.stringify(userData))
      }
      
      // Redirect to dashboard or home
      await router.push('/')
    } else {
      error.value = 'Authentication failed. No token received.'
      setTimeout(() => {
        router.push('/auth/login')
      }, 2000)
    }
  } catch (e) {
    error.value = 'Authentication failed. Please try again.'
    console.error('OAuth callback error:', e)
    setTimeout(() => {
      router.push('/auth/login')
    }, 2000)
  }
})
</script>
