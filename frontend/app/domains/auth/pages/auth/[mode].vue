<template>
  <div class="min-h-screen flex p-8">
    <!-- Left Side - Image -->
    <div class="hidden lg:flex lg:w-1/2 ">
      <img :src="mode === 'register' ? '/register.png' : '/signin.png'" alt="Sign In" class="w-full h-full object-cover rounded-3xl" />
    </div>

    <!-- Right Side - Form -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-8 bg-gray-50">
      <div class="w-full max-w-md space-y-8">
        <!-- Welcome Text -->
        <div class="text-center">
          <h1 class="text-3xl font-bold text-gray-900">Welcome to Certchain</h1>
        </div>

        <!-- Toggle Button -->
        <div class="relative bg-brand-primary/50 rounded-full p-4">
          <div class="flex">
            <button
              @click="mode = 'login'"
              class="flex-1 py-2 px-4 rounded-full text-sm font-medium transition-colors duration-200"
              :class="mode === 'login' ? 'bg-brand-primary text-white' : 'bg-transparent text-brand-primary'"
            >
              Sign In
            </button>
            <button
              @click="mode = 'register'"
              class="flex-1 py-2 px-4 rounded-full text-sm font-medium transition-colors duration-200"
              :class="mode === 'register' ? 'bg-brand-primary text-white' : 'bg-transparent text-brand-primary'"
            >
              Sign Up
            </button>
          </div>
        </div>

        <!-- Description -->
        <p class="text-center text-gray-600 text-sm">
          Certchain is an online learning and certification platform that helps learners access courses, verify achievements, and build professional credibility easily.
        </p>

        <!-- Login Form -->
        <form v-if="mode === 'login'" @submit.prevent="handleLogin" class="space-y-6">
          <div>
            <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
            <input
              id="username"
              v-model="loginForm.username"
              type="text"
              required
              class="mt-1 block w-full px-3 py-2 border border-brand-primary rounded-3xl shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
          <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
            <div class="relative">
              <input
                id="password"
                v-model="loginForm.password"
                :type="showPassword ? 'text' : 'password'"
                required
                class="mt-1 block w-full px-3 py-2 pr-10 border border-brand-primary rounded-3xl shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700"
              >
                <svg v-if="showPassword" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0012 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178-.07-.207-.07-.431 0-.639a10.477 10.477 0 013.98-3.597z" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.99902 3L20.999 21M9.8433 9.91364C9.32066 10.4536 8.99902 11.1892 8.99902 12C8.99902 13.6569 10.3422 15 12 15C12.8215 15 13.5667 14.669 14.1086 14.133M6.49902 6.64715C4.59972 7.90034 3.15305 9.78394 2.45703 12C3.73128 16.0571 7.52159 19 12 19C13.9881 19 15.8414 18.4194 17.3988 17.4184M10.999 5.04939C11.328 5.01673 11.6617 5 11.999 5C16.4784 5 20.2687 7.94291 21.5429 12C21.2607 12.894 20.8577 13.7338 20.3522 14.5" />
                </svg>
              </button>
            </div>
          </div>
          <div class="flex items-center justify-between">
            <NuxtLink to="/forgot-password" class="text-sm text-blue-600 hover:text-blue-500">
              Forgot Password?
            </NuxtLink>
          </div>
          <button
            type="submit"
            class="w-full flex justify-center py-2 px-4 border bg-brand-primary rounded-3xl shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            Sign In
          </button>
        </form>

        <!-- Register Form -->
        <form v-else @submit.prevent="handleRegister" class="space-y-6">
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input
              id="email"
              v-model="registerForm.email"
              type="email"
              required
              class="mt-1 block w-full px-3 py-2 border border-brand-primary rounded-3xl shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
          <div>
            <label for="reg-username" class="block text-sm font-medium text-gray-700">Username</label>
            <input
              id="reg-username"
              v-model="registerForm.username"
              type="text"
              required
              class="mt-1 block w-full px-3 py-2 border border-brand-primary rounded-3xl shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
          <div>
            <label for="reg-password" class="block text-sm font-medium text-gray-700">Password</label>
            <div class="relative">
              <input
                id="reg-password"
                v-model="registerForm.password"
                :type="showPassword ? 'text' : 'password'"
                required
                class="mt-1 block w-full px-3 py-2 pr-10 border border-brand-primary rounded-3xl shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700"
              >
                <svg v-if="showPassword" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0012 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178-.07-.207-.07-.431 0-.639a10.477 10.477 0 013.98-3.597z" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.99902 3L20.999 21M9.8433 9.91364C9.32066 10.4536 8.99902 11.1892 8.99902 12C8.99902 13.6569 10.3422 15 12 15C12.8215 15 13.5667 14.669 14.1086 14.133M6.49902 6.64715C4.59972 7.90034 3.15305 9.78394 2.45703 12C3.73128 16.0571 7.52159 19 12 19C13.9881 19 15.8414 18.4194 17.3988 17.4184M10.999 5.04939C11.328 5.01673 11.6617 5 11.999 5C16.4784 5 20.2687 7.94291 21.5429 12C21.2607 12.894 20.8577 13.7338 20.3522 14.5" />
                </svg>
              </button>
            </div>
          </div>
          <button
            type="submit"
            class="w-full flex justify-center py-2 px-4 border bg-brand-primary rounded-3xl shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            Sign Up
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'auth'
});

const route = useRoute()
const mode = ref<'login' | 'register'>(route.params.mode === 'signup' ? 'register' : 'login')

watch(() => route.params.mode, (newMode) => {
  mode.value = newMode === 'signup' ? 'register' : 'login'
})

const showPassword = ref(false)

const auth = useAuth()

const loginForm = reactive({
  username: '',
  password: ''
})

const registerForm = reactive({
  email: '',
  username: '',
  password: ''
})

const handleLogin = async () => {
  try {
    await auth.login(loginForm.username, loginForm.password)
    await navigateTo(`/s/${auth.user.value?.username}`)
  } catch (error) {
    console.error('Login failed:', error)
  }
}

const handleRegister = async () => {
  try {
    await auth.register(registerForm.email, registerForm.username, registerForm.password)
    await navigateTo(`/s/${auth.user.value?.username}`)
  } catch (error) {
    console.error('Register failed:', error)
  }
}
</script>

<style scoped>
/* Additional styles if needed */
</style>