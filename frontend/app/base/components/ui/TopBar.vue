<template>
  <header class="bg-white shadow-sm border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-16">
        <!-- Left: Logo (closer to left edge) -->
        <div class="flex-shrink-0 -ml-2">
          <NuxtLink to="/" class="flex items-center">
            <CertchainLogo variant="horizontal" :size="32" />
          </NuxtLink>
        </div>

        <!-- Middle: Navigation -->
        <nav class="hidden md:flex space-x-8">
          <NuxtLink
            to="/courses"
            class="text-gray-700 hover:text-blue-600 px-3 py-2 text-sm font-medium transition-colors duration-200 relative group"
            :class="{ 'text-blue-600': $route.path === '/courses' }"
          >
            Courses
            <span
              class="absolute bottom-0 left-0 w-full h-0.5 bg-blue-600 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-200"
              :class="{ 'scale-x-100': $route.path === '/courses' }"
            ></span>
          </NuxtLink>
          <NuxtLink
            to="/pricing"
            class="text-gray-700 hover:text-blue-600 px-3 py-2 text-sm font-medium transition-colors duration-200 relative group"
            :class="{ 'text-blue-600': $route.path === '/pricing' }"
          >
            Pricing
            <span
              class="absolute bottom-0 left-0 w-full h-0.5 bg-blue-600 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-200"
              :class="{ 'scale-x-100': $route.path === '/pricing' }"
            ></span>
          </NuxtLink>
        </nav>

        <!-- Right: Auth Buttons or User Menu -->
        <div class="flex items-center space-x-4">
          <!-- If logged in -->
          <div v-if="isLoggedIn" class="relative">
            <button
              @click="isDropdownOpen = !isDropdownOpen"
              class="flex items-center space-x-2 text-gray-700 hover:text-blue-600 transition-colors duration-200"
            >
              <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center text-sm font-medium text-gray-700">
                {{ userInitials }}
              </div>
              <span class="text-sm font-medium hidden sm:block">{{ userName }}</span>
              <svg
                class="w-4 h-4"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M19 9l-7 7-7-7"
                />
              </svg>
            </button>
            <!-- Dropdown -->
            <div
              v-if="!isDropdownOpen"
              class="absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-md shadow-lg z-10"
            >
              <div class="px-4 py-2 text-sm font-medium text-gray-700 border-b border-gray-200">
                My Account
              </div>
              <div class="py-1">
                <NuxtLink
                  to="/@me/profile"
                  class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                  @click="isDropdownOpen = false"
                >
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                  Profile
                </NuxtLink>
                <NuxtLink
                  to="/settings"
                  class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                  @click="isDropdownOpen = false"
                >
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                  Settings
                </NuxtLink>
                <button
                  @click="logout"
                  class="flex items-center w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                >
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                  </svg>
                  Logout
                </button>
              </div>
            </div>
          </div>
          <!-- If not logged in -->
          <div v-else class="flex items-center space-x-4">
            <Button
              variant="transparent"
              size="sm"
              @click="navigateTo('/signin')"
              class="hidden sm:flex"
            >
              Sign In
            </Button>
            <Button
              size="sm"
              @click="navigateTo('/signup')"
            >
              Get Started
            </Button>
          </div>
        </div>

        <!-- Mobile menu button -->
        <div class="md:hidden">
          <button
            @click="isMobileMenuOpen = !isMobileMenuOpen"
            class="text-gray-700 hover:text-blue-600 p-2"
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
        class="md:hidden border-t border-gray-200 bg-white"
      >
        <div class="px-2 pt-2 pb-3 space-y-1">
          <NuxtLink
            to="/courses"
            class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-blue-600 hover:bg-gray-50 rounded-md"
            :class="{ 'text-blue-600 bg-blue-50': $route.path === '/courses' }"
            @click="isMobileMenuOpen = false"
          >
            Courses
          </NuxtLink>
          <NuxtLink
            to="/pricing"
            class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-blue-600 hover:bg-gray-50 rounded-md"
            :class="{ 'text-blue-600 bg-blue-50': $route.path === '/pricing' }"
            @click="isMobileMenuOpen = false"
          >
            Pricing
          </NuxtLink>
          <div class="pt-4 pb-2 border-t border-gray-200">
            <!-- If logged in -->
            <div v-if="isLoggedIn" class="px-3 py-2">
              <div class="text-sm font-medium text-gray-700 mb-2">{{ userName }}</div>
              <Button
                variant="transparent"
                size="sm"
                @click="logout; isMobileMenuOpen = false"
                class="w-full justify-center"
              >
                Logout
              </Button>
            </div>
            <!-- If not logged in -->
            <div v-else>
              <Button
                variant="transparent"
                size="sm"
                @click="navigateTo('/signin'); isMobileMenuOpen = false"
                class="w-full justify-center mb-2"
              >
                Sign In
              </Button>
              <Button
                size="sm"
                @click="navigateTo('/signup'); isMobileMenuOpen = false"
                class="w-full justify-center"
              >
                Get Started
              </Button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup lang="ts">
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
