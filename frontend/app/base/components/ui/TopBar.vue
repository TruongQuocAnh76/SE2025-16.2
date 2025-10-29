<template>
  <header class="bg-brand-primary absolute top-0 left-0 right-0 z-60">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-16">
        <!-- Left: Logo -->
        <div class="flex-shrink-0">
          <NuxtLink to="/" class="flex items-center">
            <img src="/logo2.svg" alt="" class="h-8 w-auto" />
          </NuxtLink>
        </div>

        <!-- Middle: Navigation -->
        <nav class="hidden lg:flex lg:absolute lg:left-1/2 lg:transform lg:-translate-x-1/2 space-x-10">
          <NuxtLink
            to="/"
            class="text-white hover:text-accent-star px-4 py-2 text-sm font-medium transition-colors duration-200 relative group whitespace-nowrap cursor-pointer"
            :class="{ 'text-accent-star': $route.path === '/' }"
          >
            Home
            <span
              class="absolute bottom-0 left-0 w-full h-0.5 bg-accent-star transform scale-x-0 group-hover:scale-x-100 transition-transform duration-200"
              :class="{ 'scale-x-100': $route.path === '/' }"
            ></span>
          </NuxtLink>
          <NuxtLink
            to="/courses"
            class="text-white hover:text-accent-star px-4 py-2 text-sm font-medium transition-colors duration-200 relative group whitespace-nowrap cursor-pointer"
            :class="{ 'text-accent-star': $route.path === '/courses' }"
          >
            Courses
            <span
              class="absolute bottom-0 left-0 w-full h-0.5 bg-accent-star transform scale-x-0 group-hover:scale-x-100 transition-transform duration-200"
              :class="{ 'scale-x-100': $route.path === '/courses' }"
            ></span>
          </NuxtLink>
          <NuxtLink
            to="/membership"
            class="text-white hover:text-accent-star px-4 py-2 text-sm font-medium transition-colors duration-200 relative group whitespace-nowrap cursor-pointer"
            :class="{ 'text-accent-star': $route.path === '/membership' }"
          >
            Membership
            <span
              class="absolute bottom-0 left-0 w-full h-0.5 bg-accent-star transform scale-x-0 group-hover:scale-x-100 transition-transform duration-200"
              :class="{ 'scale-x-100': $route.path === '/membership' }"
            ></span>
          </NuxtLink>
          <NuxtLink
            to="/blog"
            class="text-white hover:text-accent-star px-4 py-2 text-sm font-medium transition-colors duration-200 relative group whitespace-nowrap cursor-pointer"
            :class="{ 'text-accent-star': $route.path === '/blog' }"
          >
            Blogs
            <span
              class="absolute bottom-0 left-0 w-full h-0.5 bg-accent-star transform scale-x-0 group-hover:scale-x-100 transition-transform duration-200"
              :class="{ 'scale-x-100': $route.path === '/blog' }"
            ></span>
          </NuxtLink>
          <NuxtLink
            to="/about"
            class="text-white hover:text-accent-star px-4 py-2 text-sm font-medium transition-colors duration-200 relative group whitespace-nowrap cursor-pointer"
            :class="{ 'text-accent-star': $route.path === '/about' }"
          >
            About us
            <span
              class="absolute bottom-0 left-0 w-full h-0.5 bg-accent-star transform scale-x-0 group-hover:scale-x-100 transition-transform duration-200"
              :class="{ 'scale-x-100': $route.path === '/about' }"
            ></span>
          </NuxtLink>
        </nav>

        <!-- Right: Auth Buttons -->
        <div class="flex items-center space-x-4">
          <template v-if="isAuthenticated">
            <!-- User dropdown -->
            <div class="relative dropdown">
              <button
                @click="isDropdownOpen = !isDropdownOpen"
                class="flex items-center space-x-2 text-white hover:text-accent-star px-3 py-2 rounded-md transition-colors duration-200"
              >
                <div class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center text-sm font-medium">
                  {{ userInitials }}
                </div>
                <svg
                  class="w-4 h-4 transition-transform duration-200"
                  :class="{ 'rotate-180': isDropdownOpen }"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>
              <div
                v-if="isDropdownOpen"
                class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-50 border border-gray-200"
              >
                <div class="py-1">
                  <div class="px-4 py-2 text-sm text-gray-700 border-b border-gray-100">
                    {{ user?.username }}
                  </div>
                  <button
                    @click="logout"
                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-200"
                  >
                    Logout
                  </button>
                </div>
              </div>
            </div>
          </template>
          <template v-else>
            <Button
              variant="transparent"
              @click="navigateTo('/auth/signin')"
              class="bg-white hidden sm:flex text-text-dark hover:text-accent-star border-white/20 hover:border-accent-star/50 bg-background rounded-3xl cursor-pointer p-4"
            >
              Login
            </Button>
            <Button
              @click="navigateTo('/auth/signup')"
              class="bg-white/30 hover:bg-white/40 text-text-dark rounded-3xl p-4"
            >
              Sign up
            </Button>
          </template>
        </div>

        <!-- Mobile menu button -->
        <div class="md:hidden">
          <button
            @click="isMobileMenuOpen = !isMobileMenuOpen"
            class="text-white hover:text-accent-star p-2"
          >
            <svg
              class="w-6 h-6"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                v-if="!isMobileMenuOpen"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M4 6h16M4 12h16M4 18h16"
              />
              <path
                v-else
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M6 18L18 6M6 6l12 12"
              />
            </svg>
          </button>
        </div>
      </div>

      <!-- Mobile Navigation Menu -->
      <div
        v-if="isMobileMenuOpen"
        class="md:hidden bg-white/95 backdrop-blur-sm border-t border-white/20"
      >
        <div class="px-2 pt-2 pb-3 space-y-1">
          <NuxtLink
            to="/"
            class="block px-3 py-2 text-base font-medium text-text-dark hover:text-accent-star hover:bg-accent-star/10 rounded-md"
            :class="{ 'text-accent-star bg-accent-star/10': $route.path === '/' }"
            @click="isMobileMenuOpen = false"
          >
            Home
          </NuxtLink>
          <NuxtLink
            to="/courses"
            class="block px-3 py-2 text-base font-medium text-text-dark hover:text-accent-star hover:bg-accent-star/10 rounded-md"
            :class="{ 'text-accent-star bg-accent-star/10': $route.path === '/courses' }"
            @click="isMobileMenuOpen = false"
          >
            Courses
          </NuxtLink>
          <NuxtLink
            to="/membership"
            class="block px-3 py-2 text-base font-medium text-text-dark hover:text-accent-star hover:bg-accent-star/10 rounded-md"
            :class="{ 'text-accent-star bg-accent-star/10': $route.path === '/membership' }"
            @click="isMobileMenuOpen = false"
          >
            Membership
          </NuxtLink>
          <NuxtLink
            to="/blog"
            class="block px-3 py-2 text-base font-medium text-text-dark hover:text-accent-star hover:bg-accent-star/10 rounded-md"
            :class="{ 'text-accent-star bg-accent-star/10': $route.path === '/blog' }"
            @click="isMobileMenuOpen = false"
          >
            Blogs
          </NuxtLink>
          <NuxtLink
            to="/about"
            class="block px-3 py-2 text-base font-medium text-text-dark hover:text-accent-star hover:bg-accent-star/10 rounded-md"
            :class="{ 'text-accent-star bg-accent-star/10': $route.path === '/about' }"
            @click="isMobileMenuOpen = false"
          >
            About us
          </NuxtLink>
          <div class="pt-4 pb-2 border-t border-gray-200">
            <template v-if="isAuthenticated">
              <div class="px-3 py-2 text-base font-medium text-text-dark">
                {{ user?.username }}
              </div>
              <Button
                size="sm"
                @click="logout; isMobileMenuOpen = false"
                class="w-full justify-center bg-accent-star hover:bg-accent-star/80 text-text-dark"
              >
                Logout
              </Button>
            </template>
            <template v-else>
              <Button
                variant="transparent"
                size="sm"
                @click="navigateTo('/auth/signin'); isMobileMenuOpen = false"
                class="w-full justify-center mb-2 text-text-dark"
              >
                Login
              </Button>
              <Button
                size="sm"
                @click="navigateTo('/auth/signup'); isMobileMenuOpen = false"
                class="w-full justify-center bg-accent-star hover:bg-accent-star/80 text-text-dark"
              >
                Sign up
              </Button>
            </template>
          </div>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup lang="ts">
import Button from './Button.vue'
import { useAuth } from '~/domains/auth/composables/useAuth'

const isMobileMenuOpen = ref(false)

const { isAuthenticated, user, logout: authLogout, getUser } = useAuth()
const isDropdownOpen = ref(false)

const userInitials = computed(() => {
  if (user.value?.username) {
    return user.value.username.split(' ').map(n => n[0]).join('').toUpperCase()
  }
  return ''
})

function logout() {
  authLogout()
  isDropdownOpen.value = false
  navigateTo('/')
}

// Initialize user data if authenticated
onMounted(async () => {
  if (isAuthenticated.value && !user.value) {
    try {
      await getUser()
    } catch (err) {
      // Handle error if needed
    }
  }

  // Close dropdown on outside click
  document.addEventListener('click', (e) => {
    if (!(e.target as HTMLElement)?.closest('.dropdown')) {
      isDropdownOpen.value = false
    }
  })
})

// Close mobile menu when route changes
watch(() => useRoute().path, () => {
  isMobileMenuOpen.value = false
  isDropdownOpen.value = false
})
</script>
