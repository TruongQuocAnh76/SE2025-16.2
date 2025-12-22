<template>
  <div class="min-h-screen bg-background">
    <!-- Main Content -->
    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <!-- Blockchain Trust Indicator -->
      <div class="mb-8 text-center">
        <div class="inline-flex items-center space-x-2 bg-green-50 border border-green-200 rounded-full px-4 py-2">
          <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
          <span class="text-green-800 text-sm font-medium">Secured by Blockchain</span>
        </div>
      </div>

      <!-- Verification Form -->
      <VerificationForm />

      <!-- Features Section -->
      <div class="mt-16 grid md:grid-cols-3 gap-8">
        <div class="text-center space-y-3">
          <div class="w-16 h-16 bg-accent-blue/10 rounded-full flex items-center justify-center mx-auto">
            <svg class="w-8 h-8 text-accent-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
          </div>
          <h3 class="text-h6 font-semibold text-text-dark">Blockchain Security</h3>
          <p class="text-text-muted">
            Every certificate is secured on blockchain, making it impossible to forge or tamper with.
          </p>
        </div>

        <div class="text-center space-y-3">
          <div class="w-16 h-16 bg-accent-purple/10 rounded-full flex items-center justify-center mx-auto">
            <svg class="w-8 h-8 text-accent-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                    d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
          </div>
          <h3 class="text-h6 font-semibold text-text-dark">Instant Verification</h3>
          <p class="text-text-muted">
            Get immediate verification results with detailed certificate information.
          </p>
        </div>

        <div class="text-center space-y-3">
          <div class="w-16 h-16 bg-accent-orange/10 rounded-full flex items-center justify-center mx-auto">
            <svg class="w-8 h-8 text-accent-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
          </div>
          <h3 class="text-h6 font-semibold text-text-dark">Privacy Protected</h3>
          <p class="text-text-muted">
            We don't store your files. All verification happens securely using cryptographic hashes.
          </p>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
// Set page metadata
useHead({
  title: 'Certificate Verification - Certchain',
  meta: [
    {
      name: 'description',
      content: 'Verify the authenticity of certificates issued through Certchain using our secure blockchain verification system.'
    }
  ]
})
</script>