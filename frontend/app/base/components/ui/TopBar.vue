<template>
  <header class="bg-transparent absolute top-0 left-0 right-0 z-60">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-16">
        <!-- Left: Logo -->
        <div class="flex-shrink-0">
          <NuxtLink to="/" class="flex items-center">
            <img src="../../../../public/logo2.svg" alt="" class="h-8 w-auto" />
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
          <Button
            variant="transparent"
            @click="navigateTo('/signin')"
            class="hidden sm:flex text-text-dark hover:text-accent-star border-white/20 hover:border-accent-star/50 bg-background rounded-3xl cursor-pointer p-4"
          >
            Login
          </Button>
          <Button
            @click="navigateTo('/signup')"
            class="bg-white/30 hover:bg-white/40 text-text-dark rounded-3xl p-4"
          >
            Sign up
          </Button>
        </div>

        <!-- Mobile menu button -->
        <!-- <div class="md:hidden">
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
        </div> -->
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
            <Button
              variant="transparent"
              size="sm"
              @click="navigateTo('/signin'); isMobileMenuOpen = false"
              class="w-full justify-center mb-2 text-text-dark"
            >
              Login
            </Button>
            <Button
              size="sm"
              @click="navigateTo('/signup'); isMobileMenuOpen = false"
              class="w-full justify-center bg-accent-star hover:bg-accent-star/80 text-text-dark"
            >
              Sign up
            </Button>
          </div>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup lang="ts">
import Button from './Button.vue'

const isMobileMenuOpen = ref(false)

// Auth state (TODO: integrate with actual auth)
const isLoggedIn = ref(false) // Set to true for testing
const userName = ref('John Doe')
const userInitials = computed(() => userName.value.split(' ').map(n => n[0]).join('').toUpperCase())
const isDropdownOpen = ref(false)

function logout() {
  // TODO: Implement actual logout logic
  isLoggedIn.value = false
  isDropdownOpen.value = false
  navigateTo('/')
}

// Close mobile menu when route changes
watch(() => useRoute().path, () => {
  isMobileMenuOpen.value = false
  isDropdownOpen.value = false
})
</script>
