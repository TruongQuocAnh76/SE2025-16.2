<template>
  <section class="bg-background py-16 lg:py-24 relative overflow-hidden">
    <!-- Background Decorative Elements -->
    <div class="absolute inset-0 pointer-events-none">
      <!-- Floating circles -->
      <div class="absolute top-20 left-10 w-20 h-20 bg-accent-purple/10 rounded-full animate-pulse"></div>
      <div class="absolute top-40 right-16 w-16 h-16 bg-accent-blue/10 rounded-full animate-pulse delay-300"></div>
      <div class="absolute bottom-32 left-20 w-12 h-12 bg-accent-orange/10 rounded-full animate-pulse delay-700"></div>
      <div class="absolute bottom-20 right-32 w-24 h-24 bg-accent-star/10 rounded-full animate-pulse delay-1000"></div>
    </div>

    <div class="w-full px-4 sm:px-6 lg:px-8 relative z-10">
      <!-- Header Section -->
      <div class="text-center mb-16">
        <h2 class="text-h2 text-text-dark font-inter mb-6">
          What is <span class="text-brand-primary">CertChain</span>?
        </h2>
        <p class="text-body text-text-muted max-w-4xl mx-auto font-inter">
          CertChain is an online learning and certification platform that ensures every achievement is securely stored and verified on the blockchain. We aim to build a transparent and tamper-proof ecosystem for modern education.
        </p>
      </div>

      <!-- Two Image Cards Section -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-20">
        <!-- For Instructors Card -->
        <div class="relative group">
          <div class="relative overflow-hidden rounded-3xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2">
            <img src="../../public/images/overview1.png" alt="For Instructors" class="w-full h-64 object-cover">
            <!-- Overlay -->
            <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent"></div>
            <!-- Content Overlay -->
            <div class="absolute bottom-0 left-0 right-0 p-6 text-white">
              <h3 class="text-h4 font-inter mb-3 text-accent-star">FOR INSTRUCTORS</h3>
              <p class="text-body font-inter">
                Create and manage your courses effortlessly. Track student progress, issue verified blockchain certificates, and enhance teaching efficiency.
              </p>
            </div>
            <!-- Decorative element -->
            <div class="absolute top-4 right-4 w-8 h-8 bg-accent-purple rounded-full flex items-center justify-center">
              <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.84L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.84l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"/>
              </svg>
            </div>
          </div>
        </div>

        <!-- For Students Card -->
        <div class="relative group">
          <div class="relative overflow-hidden rounded-3xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2">
            <img src="../../public/images/overview2.png" alt="For Students" class="w-full h-64 object-cover">
            <!-- Overlay -->
            <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent"></div>
            <!-- Content Overlay -->
            <div class="absolute bottom-0 left-0 right-0 p-6 text-white">
              <h3 class="text-h4 font-inter mb-3 text-accent-star">FOR STUDENTS</h3>
              <p class="text-body font-inter">
                Learn anytime, anywhere. Access interactive lessons, complete assessments, and receive digital certificates that are permanently verified on the blockchain.
              </p>
            </div>
            <!-- Decorative element -->
            <div class="absolute top-4 right-4 w-8 h-8 bg-accent-blue rounded-full flex items-center justify-center">
              <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Bottom Section with Text and Video -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
        <!-- Left Content -->
        <div class="space-y-6">
          <div class="relative">
            <!-- Decorative line -->
            <div class="absolute -left-4 top-0 w-1 h-full bg-gradient-to-b from-brand-primary to-brand-secondary rounded-full"></div>
            <h3 class="text-h3 text-text-dark font-inter mb-4">
              Everything you can do in a physical classroom, you can do with <span class="text-brand-primary">CertChain</span>
            </h3>
          </div>
          <p class="text-body text-text-muted font-inter leading-relaxed">
            CertChain empowers educators and learners to create, manage, and verify courses and achievements seamlessly on a secure blockchain-based platform.
          </p>
          
        </div>

        <!-- Right Video/Image Section -->
        <div class="relative group">
          <div class="relative overflow-hidden rounded-3xl shadow-lg hover:shadow-xl transition-all duration-300">
            <img src="../../public/images/overview3.png" alt="CertChain Platform Demo" class="w-full h-80 object-cover">
            
            <!-- Decorative elements around the image -->
            <div class="absolute -top-4 -right-4 w-8 h-8 bg-accent-star rounded-full animate-bounce"></div>
            <div class="absolute -bottom-4 -left-4 w-6 h-6 bg-accent-red rounded-full animate-pulse"></div>
            
            <!-- Floating stats -->
          </div>

          <!-- Additional decorative floating elements -->
          <div class="absolute -z-10 top-8 -right-8 w-32 h-32 bg-gradient-to-r from-brand-primary/10 to-brand-secondary/10 rounded-full blur-xl"></div>
          <div class="absolute -z-10 -bottom-8 -left-8 w-24 h-24 bg-gradient-to-r from-accent-purple/10 to-accent-blue/10 rounded-full blur-xl"></div>
        </div>
      </div>
    </div>

    <!-- Bottom decorative wave -->
    <div class="absolute bottom-0 left-0 right-0 overflow-hidden">
      <svg class="w-full h-12" viewBox="0 0 1200 120" preserveAspectRatio="none">
        <path d="M0,0 C150,120 350,0 600,60 C850,120 1050,0 1200,60 L1200,120 L0,120 Z" 
              fill="url(#gradient)" opacity="0.1"/>
        <defs>
          <linearGradient id="gradient" x1="0%" y1="0%" x2="100%" y2="0%">
            <stop offset="0%" style="stop-color:#00A89A"/>
            <stop offset="100%" style="stop-color:#00B9D6"/>
          </linearGradient>
        </defs>
      </svg>
    </div>
  </section>
</template>

<script setup lang="ts">
</script>

<style scoped>
@keyframes float {
  0%, 100% { transform: translateY(0px); }
  50% { transform: translateY(-10px); }
}

.animate-float {
  animation: float 3s ease-in-out infinite;
}

.animate-float-delay {
  animation: float 3s ease-in-out infinite;
  animation-delay: 1.5s;
}
</style>