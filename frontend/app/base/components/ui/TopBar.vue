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

        <!-- Right: Auth Buttons -->
        <div class="flex items-center space-x-4">
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
  </header>
</template>

<script setup lang="ts">
const isMobileMenuOpen = ref(false)

// Close mobile menu when route changes
watch(() => useRoute().path, () => {
  isMobileMenuOpen.value = false
})
</script>
