<template>
  <footer class="bg-gray-900 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-8">
        <!-- Company Info -->
        <div class="lg:col-span-2">
          <div class="flex items-center mb-4">
            <CertchainLogo variant="horizontal" :size="28" class="brightness-0 invert" />
          </div>
          <p class="text-gray-300 text-sm leading-relaxed mb-4">
            Blockchain-verified online learning platform. Secure, transparent, and trusted certificate verification.
          </p>
        </div>

        <!-- Product -->
        <div>
          <h3 class="text-white font-semibold mb-4">Product</h3>
          <ul class="space-y-2">
            <li>
              <NuxtLink to="/how-it-works" class="text-gray-300 hover:text-white text-sm transition-colors duration-200">
                How it Works
              </NuxtLink>
            </li>
            <li>
              <NuxtLink to="/courses" class="text-gray-300 hover:text-white text-sm transition-colors duration-200">
                Courses
              </NuxtLink>
            </li>
            <li>
              <NuxtLink to="/certificates" class="text-gray-300 hover:text-white text-sm transition-colors duration-200">
                Certificates
              </NuxtLink>
            </li>
            <li>
              <NuxtLink to="/pricing" class="text-gray-300 hover:text-white text-sm transition-colors duration-200">
                Pricing
              </NuxtLink>
            </li>
          </ul>
        </div>

        <!-- For Educators -->
        <div>
          <h3 class="text-white font-semibold mb-4">For Educators</h3>
          <ul class="space-y-2">
            <li>
              <NuxtLink to="/teach" class="text-gray-300 hover:text-white text-sm transition-colors duration-200">
                Teach on Certchain
              </NuxtLink>
            </li>
            <li>
              <NuxtLink to="/teacher-resources" class="text-gray-300 hover:text-white text-sm transition-colors duration-200">
                Teacher Resources
              </NuxtLink>
            </li>
            <li>
              <NuxtLink to="/course-guidelines" class="text-gray-300 hover:text-white text-sm transition-colors duration-200">
                Course Guidelines
              </NuxtLink>
            </li>
            <li>
              <NuxtLink to="/support" class="text-gray-300 hover:text-white text-sm transition-colors duration-200">
                Support
              </NuxtLink>
            </li>
          </ul>
        </div>

        <!-- Company -->
        <div>
          <h3 class="text-white font-semibold mb-4">Company</h3>
          <ul class="space-y-2">
            <li>
              <NuxtLink to="/about" class="text-gray-300 hover:text-white text-sm transition-colors duration-200">
                About Us
              </NuxtLink>
            </li>
            <li>
              <NuxtLink to="/blog" class="text-gray-300 hover:text-white text-sm transition-colors duration-200">
                Blog
              </NuxtLink>
            </li>
            <li>
              <NuxtLink to="/careers" class="text-gray-300 hover:text-white text-sm transition-colors duration-200">
                Careers
              </NuxtLink>
            </li>
            <li>
              <NuxtLink to="/contact" class="text-gray-300 hover:text-white text-sm transition-colors duration-200">
                Contact
              </NuxtLink>
            </li>
          </ul>
        </div>

        <!-- Legal -->
        <div>
          <h3 class="text-white font-semibold mb-4">Legal</h3>
          <ul class="space-y-2">
            <li>
              <NuxtLink to="/privacy" class="text-gray-300 hover:text-white text-sm transition-colors duration-200">
                Privacy Policy
              </NuxtLink>
            </li>
            <li>
              <NuxtLink to="/terms" class="text-gray-300 hover:text-white text-sm transition-colors duration-200">
                Terms of Service
              </NuxtLink>
            </li>
            <li>
              <NuxtLink to="/blockchain-policy" class="text-gray-300 hover:text-white text-sm transition-colors duration-200">
                Blockchain Policy
              </NuxtLink>
            </li>
            <li>
              <NuxtLink to="/cookies" class="text-gray-300 hover:text-white text-sm transition-colors duration-200">
                Cookie Policy
              </NuxtLink>
            </li>
          </ul>
        </div>
      </div>

      <!-- Bottom Section -->
      <div class="border-t border-gray-800 mt-8 pt-8">
        <div class="flex flex-col md:flex-row justify-between items-center">
          <div class="text-gray-400 text-sm mb-4 md:mb-0">
            © 2025 Certchain. All rights reserved.
          </div>
          <div class="text-gray-400 text-xs max-w-md text-center md:text-right">
            No personally identifiable information (PII) is stored on the blockchain. Only certificate hashes and minimal metadata are recorded.
          </div>
        </div>
      </div>
    </div>
  </footer>
</template>

<script setup lang="ts">
// Footer component - no reactive data needed
</script>
