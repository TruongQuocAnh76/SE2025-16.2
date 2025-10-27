<template>
  <section class="bg-white py-16 lg:py-24 relative overflow-hidden">
    <!-- Background Decorative Elements -->
    <div class="absolute inset-0 pointer-events-none">
      <!-- Floating shapes -->
      <div class="absolute top-16 left-8 w-12 h-12 bg-accent-purple/20 rounded-full animate-float"></div>
      <div class="absolute top-32 right-12 w-8 h-8 bg-accent-star/30 rotate-45 animate-float-delay"></div>
      <div class="absolute bottom-24 left-16 w-6 h-6 bg-accent-blue/25 rounded-full animate-pulse"></div>
      <div class="absolute bottom-16 right-20 w-10 h-10 bg-accent-orange/20 rotate-12 animate-float"></div>
      <div class="absolute top-1/2 left-4 w-4 h-4 bg-accent-red/30 rounded-full animate-bounce"></div>
      <div class="absolute top-1/3 right-8 w-14 h-14 bg-brand-primary/10 rounded-full animate-pulse delay-500"></div>

      <!-- Stars -->
      <div class="absolute top-20 right-1/4 w-6 h-6 text-accent-star animate-pulse">
        <svg fill="currentColor" viewBox="0 0 20 20">
          <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
        </svg>
      </div>
      <div class="absolute bottom-32 left-1/3 w-4 h-4 text-accent-purple animate-pulse delay-300">
        <svg fill="currentColor" viewBox="0 0 20 20">
          <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
        </svg>
      </div>

      <!-- Triangles -->
      <div class="absolute top-40 left-1/2 w-8 h-8 text-accent-blue/20 animate-float">
        <svg fill="currentColor" viewBox="0 0 20 20">
          <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.409l-7-14z"/>
        </svg>
      </div>
      <div class="absolute bottom-40 right-1/3 w-6 h-6 text-accent-orange/25 animate-float-delay">
        <svg fill="currentColor" viewBox="0 0 20 20">
          <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.409l-7-14z"/>
        </svg>
      </div>
    </div>

    <div class="w-full px-4 sm:px-6 lg:px-8 relative z-10">
      <!-- Header Section -->
      <div class="text-center mb-16">
        <h2 class="text-h2 text-text-dark font-inter mb-6">
          Our <span class="text-brand-primary">Features</span>
        </h2>
        <p class="text-body text-text-muted max-w-3xl mx-auto font-inter">
          This very extraordinary feature, can make learning activities more efficient
        </p>
      </div>

      <!-- Feature 1: Smart Course Management (Image Left, Text Right) -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center mb-20">
        <div class="relative group">
          <div class="relative overflow-hidden rounded-3xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
            <img src="../../public/images/features1.png" alt="Smart Course Management" class="w-full h-80 object-cover">
            <!-- Decorative overlay -->
            <div class="absolute inset-0 bg-gradient-to-r from-brand-primary/10 to-transparent"></div>
            <!-- Floating elements on image -->
            <div class="absolute top-4 left-4 w-3 h-3 bg-accent-star rounded-full animate-pulse"></div>
            <div class="absolute bottom-4 right-4 w-2 h-2 bg-accent-blue rounded-full animate-pulse delay-300"></div>
          </div>
        </div>
        <div class="space-y-6">
          <div class="relative">
            <div class="absolute -left-4 top-0 w-1 h-full bg-gradient-to-b from-accent-purple to-accent-blue rounded-full"></div>
            <h3 class="text-h3 text-text-dark font-inter mb-4">
              Smart Course <span class="text-accent-purple">Management</span>
            </h3>
          </div>
          <p class="text-body text-text-muted font-inter leading-relaxed">
            Easily browse, enroll in, and complete online courses through an intuitive interface. CertChain provides a seamless learning experience with video lessons, interactive quizzes, and progress tracking — all in one unified platform.
          </p>
        </div>
      </div>

      <!-- Feature 2: Blockchain Verification (Image Right, Text Left) -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center mb-20">
        <div class="space-y-6 lg:order-1">
          <div class="relative">
            <div class="absolute -left-4 top-0 w-1 h-full bg-gradient-to-b from-accent-blue to-accent-star rounded-full"></div>
            <h3 class="text-h3 text-text-dark font-inter mb-4">
              Blockchain <span class="text-accent-blue">Verification</span>
            </h3>
          </div>
          <p class="text-body text-text-muted font-inter leading-relaxed">
            Each certificate issued is securely recorded on the blockchain, ensuring authenticity, transparency, and immutability. Learners can share verifiable achievements that employers or institutions can instantly confirm with a single click.
          </p>
        </div>
        <div class="relative group lg:order-2">
          <div class="relative overflow-hidden rounded-3xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
            <img src="../../public/images/features2.png" alt="Blockchain Verification" class="w-full h-80 object-cover">
            <!-- Decorative overlay -->
            <div class="absolute inset-0 bg-gradient-to-l from-accent-blue/10 to-transparent"></div>
            <!-- Floating elements on image -->
            <div class="absolute top-4 right-4 w-3 h-3 bg-accent-red rounded-full animate-pulse"></div>
            <div class="absolute bottom-4 left-4 w-2 h-2 bg-accent-purple rounded-full animate-pulse delay-300"></div>
            <!-- Chain link icon -->
            <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-12 h-12 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center">
              <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M12.586 4.586a2 2 0 112.828 2.828l-3 3a2 2 0 01-2.828 0 1 1 0 00-1.414 1.414 4 4 0 005.656 0l3-3a4 4 0 00-5.656-5.656l-1.5 1.5a1 1 0 101.414 1.414l1.5-1.5zm-5 5a2 2 0 012.828 0 1 1 0 101.414-1.414 4 4 0 00-5.656 0l-3 3a4 4 0 105.656 5.656l1.5-1.5a1 1 0 10-1.414-1.414l-1.5 1.5a2 2 0 11-2.828-2.828l3-3z" clip-rule="evenodd"/>
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Feature 3: Learning Analytics (Image Left, Text Right) -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center mb-20">
        <div class="relative group">
          <div class="relative overflow-hidden rounded-3xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
            <img src="../../public/images/features3.png" alt="Learning Analytics" class="w-full h-80 object-cover">
            <!-- Decorative overlay -->
            <div class="absolute inset-0 bg-gradient-to-r from-accent-star/10 to-transparent"></div>
            <!-- Floating elements on image -->
            <div class="absolute top-4 left-4 w-3 h-3 bg-accent-orange rounded-full animate-pulse"></div>
            <div class="absolute bottom-4 right-4 w-2 h-2 bg-accent-purple rounded-full animate-pulse delay-300"></div>
            <!-- Chart icon -->
            <div class="absolute top-1/4 right-4 w-8 h-8 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center">
              <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
              </svg>
            </div>
          </div>
        </div>
        <div class="space-y-6">
          <div class="relative">
            <div class="absolute -left-4 top-0 w-1 h-full bg-gradient-to-b from-accent-star to-accent-orange rounded-full"></div>
            <h3 class="text-h3 text-text-dark font-inter mb-4">
              Learning <span class="text-accent-star">Analytics</span>
            </h3>
          </div>
          <p class="text-body text-text-muted font-inter leading-relaxed">
            Monitor individual and overall learning performance with detailed real-time analytics. Instructors can review student engagement, completion rates, and assessment outcomes to continuously enhance teaching quality.
          </p>
        </div>
      </div>

      <!-- Feature 4: Instructor Dashboard (Image Right, Text Left) -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
        <div class="space-y-6 lg:order-1">
          <div class="relative">
            <div class="absolute -left-4 top-0 w-1 h-full bg-gradient-to-b from-accent-orange to-accent-red rounded-full"></div>
            <h3 class="text-h3 text-text-dark font-inter mb-4">
              Instructor <span class="text-accent-orange">Dashboard</span>
            </h3>
          </div>
          <p class="text-body text-text-muted font-inter leading-relaxed">
            Instructors have full control over course creation and management. Upload lectures, add resources, set up quizzes, and track student progress — all supported by insightful dashboards that simplify teaching and feedback.
          </p>
        </div>
        <div class="relative group lg:order-2">
          <div class="relative overflow-hidden rounded-3xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
            <img src="../../public/images/features4.png" alt="Instructor Dashboard" class="w-full h-80 object-cover">
            <!-- Decorative overlay -->
            <div class="absolute inset-0 bg-gradient-to-l from-accent-orange/10 to-transparent"></div>
            <!-- Floating elements on image -->
            <div class="absolute top-4 right-4 w-3 h-3 bg-accent-blue rounded-full animate-pulse"></div>
            <div class="absolute bottom-4 left-4 w-2 h-2 bg-accent-star rounded-full animate-pulse delay-300"></div>
            <!-- Dashboard icon -->
            <div class="absolute top-1/3 left-4 w-10 h-10 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
              </svg>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Bottom decorative elements -->
    <div class="absolute bottom-0 left-0 right-0 overflow-hidden">
      <svg class="w-full h-16" viewBox="0 0 1200 120" preserveAspectRatio="none">
        <path d="M0,60 C300,120 600,0 900,60 C1050,90 1200,30 1200,60 L1200,120 L0,120 Z"
              fill="url(#features-gradient)" opacity="0.05"/>
        <defs>
          <linearGradient id="features-gradient" x1="0%" y1="0%" x2="100%" y2="0%">
            <stop offset="0%" style="stop-color:#00A89A"/>
            <stop offset="25%" style="stop-color:#6366F1"/>
            <stop offset="50%" style="stop-color:#3B82F6"/>
            <stop offset="75%" style="stop-color:#FFC107"/>
            <stop offset="100%" style="stop-color:#F97316"/>
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

@keyframes float-delay {
  0%, 100% { transform: translateY(0px); }
  50% { transform: translateY(-10px); }
}

.animate-float {
  animation: float 3s ease-in-out infinite;
}

.animate-float-delay {
  animation: float-delay 3s ease-in-out infinite;
  animation-delay: 1.5s;
}
</style>