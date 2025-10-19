<template>
  <section class="bg-gradient-to-br from-neutral-50 to-neutral-50 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center">
        <!-- Shield Icon with Check -->
        <div class="flex justify-center mb-8">
          <div class="relative">
            <div class="w-20 h-20 bg-primary-600 rounded-full flex items-center justify-center shadow-lg animate-chain-pulse">
              <svg class="w-12 h-12 text-neutral-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
            </div>
            <!-- Decorative rings -->
            <div class="absolute inset-0 rounded-full border-4 border-primary-500 animate-ping"></div>
            <div class="absolute inset-0 rounded-full border-2 border-primary-500 animate-pulse"></div>
          </div>
        </div>

        <!-- Main Heading -->
        <h1 class="text-h1 text-neutral-900 mb-6 font-inter">
          <span class="text-primary-600">Blockchain-Verified</span><br>
          Learning Platform
        </h1>

        <!-- Subheading -->
        <h2 class="text-h2 text-neutral-700 mb-2 font-inter">
          Learn, Earn & Verify
        </h2>
        <h3 class="text-h4 text-neutral-600 mb-8 font-inter">
          Certificates on Blockchain
        </h3>

        <!-- Description -->
        <p class="text-body text-neutral-600 mb-12 max-w-3xl mx-auto font-inter">
          Take courses from expert instructors, pass quizzes, and receive tamper-proof certificates verified on the blockchain. Your achievements, secured forever.
        </p>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-12">
          <button class="w-full sm:w-auto bg-primary-500 hover:bg-primary-600 text-neutral-0 px-8 py-4 rounded-lg text-h6 font-inter transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
            Explore Courses
          </button>
          <button 
            @click="scrollToHowItWorks" 
            class="w-full sm:w-auto bg-neutral-0 hover:bg-neutral-50 text-primary-600 border-2 border-primary-600 px-8 py-4 rounded-lg text-h6 font-inter transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-1"
          >
            How it Works
          </button>
        </div>

        <!-- Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-4xl mx-auto">
          <!-- Students -->
          <div class="bg-neutral-0 rounded-2xl p-8 shadow-lg hover:shadow-xl transition-shadow duration-300">
            <div class="text-h1 text-primary-600 mb-2 font-inter">
              50K+
            </div>
            <div class="text-h5 text-neutral-600 font-inter">
              Students
            </div>
            <div class="mt-2 text-caption text-neutral-600 font-inter">
              Learning and growing with us
            </div>
          </div>

          <!-- Courses -->
          <div class="bg-neutral-0 rounded-2xl p-8 shadow-lg hover:shadow-xl transition-shadow duration-300">
            <div class="text-h1 text-primary-600 mb-2 font-inter">
              500+
            </div>
            <div class="text-h5 text-neutral-600 font-inter">
              Courses
            </div>
            <div class="mt-2 text-caption text-neutral-600 font-inter">
              Expert-created content
            </div>
          </div>

          <!-- Verified -->
          <div class="bg-neutral-0 rounded-2xl p-8 shadow-lg hover:shadow-xl transition-shadow duration-300">
            <div class="text-h1 text-primary-600 mb-2 font-inter">
              100%
            </div>
            <div class="text-h5 text-neutral-600 font-inter">
              Verified
            </div>
            <div class="mt-2 text-caption text-neutral-600 font-inter">
              Blockchain-secured certificates
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
// Smooth scroll to "How it Works" section
const scrollToHowItWorks = () => {
  const element = document.getElementById('how-it-works')
  if (element) {
    element.scrollIntoView({ 
      behavior: 'smooth',
      block: 'start'
    })
  }
}
</script>

<style scoped>
/* Additional custom animations for the hero section */
@keyframes float {
  0%, 100% {
    transform: translateY(0px);
  }
  50% {
    transform: translateY(-10px);
  }
}

.hero-float {
  animation: float 3s ease-in-out infinite;
}
</style>