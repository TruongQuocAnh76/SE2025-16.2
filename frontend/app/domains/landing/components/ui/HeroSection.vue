<template>
  <section class="bg-brand-primary py-12 lg:py-16 overflow-visible">
    <div class="w-full px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
        <!-- Left Content -->
        <div class="space-y-6">
          <!-- Main Heading -->
          <h1 class="text-h1 text-white font-inter">
            <span class="">CertChain</span> – <span class="text-accent-star">Learn, Earn, and Verify</span> with
            <span class="">
              Blockchain
            </span>
          </h1>

          <!-- Description -->
          <p class="text-body text-white max-w-md font-inter">
            A next-generation e-learning platform that empowers students and instructors with verifiable on-chain
            certificates.
          </p>

          <!-- Action Buttons -->
          <div class="flex flex-col sm:flex-row gap-4">
            <!-- Join for Free Button -->
            <button
              @click="handleJoinClick"
              class="inline-flex items-center justify-center bg-white/80 hover:bg-white text-text-dark px-8 py-4 rounded-3xl text-h6 font-inter transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
              Join for Free
            </button>

            <!-- Explore Courses Button -->
            <NuxtLink to="/courses"
              class="inline-flex items-center justify-center gap-3 bg-white/80 hover:bg-white text-text-dark px-8 py-4 rounded-3xl text-h6 font-inter transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
              <!-- Play Icon -->
              <div
                class="w-6 h-6 bg-gradient-to-r from-brand-primary to-brand-secondary rounded-full flex items-center justify-center">
                <svg class="w-3 h-3 text-white ml-0.5" fill="currentColor" viewBox="0 0 20 20">
                  <path
                    d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z" />
                </svg>
              </div>
              Explore Courses
            </NuxtLink>
          </div>
        </div>

        <!-- Right Image -->
        <div class="relative z-30 mt-[-100px] pointer-events-none">
          <img src="../../public/images/hero.png" alt="CertChain Learning Platform"
            class="w-full h-auto scale-100 pointer-events-none" />
        </div>
      </div>
    </div>
  </section>
  <!-- Curved Bottom Element -->
  <div class="relative z-20 bg-brand-primary">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none"
      class="relative block w-full h-[100px]">
      <path d="M0,0 A600,60 0 0 0 1200,0 L1200,120 L0,120 Z" fill="background" />
    </svg>
  </div>
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router'
import { useCookie } from '#app'

const router = useRouter()
const handleJoinClick = () => {
  const authToken = useCookie('auth_token')
  if (authToken.value) {
    router.push('/courses')
  } else {
    router.push('/auth/signup')
  }
}
</script>

<style scoped>
.bg-clip-text {
  background-clip: text;
  -webkit-background-clip: text;
}

.curve-bg {
  /* background: #00A89A; */
  clip-path: ellipse(100% 300% at 50% 100%);
  height: 150px;
}
</style>