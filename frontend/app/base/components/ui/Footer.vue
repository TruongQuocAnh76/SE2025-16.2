<template>
  <footer class="text-white w-full" style="background-color: #282C43;">
    <div class="w-full px-4 sm:px-6 lg:px-8 py-12">
      <!-- Top Section -->
      <div class="flex flex-row justify-center items-center mb-12 gap-8">
        <div class="flex items-center">
          <img
            src="/logo2.svg"
            :width="128"
            :height="128"
            alt="CertChain Logo"
            class="brightness-0 invert flex-shrink-0"
          />
        </div>
        <div class="h-16" style="width: 2px; background-color: #6B7280;"></div>
        <div class="text-center">
          <p class="text-white text-h4 font-semibold">Learn Anywhere. Prove Everywhere.</p>
        </div>
      </div>

      <!-- Newsletter Section -->
      <div class="text-center mb-12">
        <h3 class="text-base font-semibold mb-4" style="color: #6B7280;">Subscribe to get our Newsletter</h3>
        <div class="flex flex-col sm:flex-row justify-center items-center gap-4 max-w-md mx-auto">
          <input
            type="email"
            placeholder="Your Email"
            class="flex-1 px-4 py-2 bg-gray-800 border border-gray-700 rounded-md text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-white focus:border-transparent"
          />
          <button class="px-6 py-2 bg-white text-gray-900 font-semibold rounded-md hover:bg-gray-100 transition-colors duration-200">
            Subscribe
          </button>
        </div>
      </div>

      <!-- Bottom Section -->
      <div class="border-t border-gray-800 pt-8">
        <div class="flex flex-col md:flex-row justify-between items-center">
          <div class="flex space-x-6 mb-4 md:mb-0">
            <NuxtLink to="/careers" class="hover:text-white text-base transition-colors duration-200" style="color: #6B7280;">
              Career
            </NuxtLink>
            <NuxtLink to="/privacy" class="hover:text-white text-base transition-colors duration-200" style="color: #6B7280;">
              Privacy Policy
            </NuxtLink>
            <NuxtLink to="/terms" class="hover:text-white text-base transition-colors duration-200" style="color: #6B7280;">
              Terms & Condition
            </NuxtLink>
          </div>
          <div class="text-base" style="color: #6B7280;">
            © 2025 CertChain Technologies Inc.
          </div>
        </div>
      </div>
    </div>
  </footer>
</template>

<script setup lang="ts">
// Footer component - no reactive data needed
</script>
