<template>
  <section class="bg-background py-16 lg:py-24 relative overflow-hidden">
    <!-- Background Decorative Elements -->
    <div class="absolute inset-0 pointer-events-none">
      <!-- Floating quote marks -->
      <div class="absolute top-16 left-8 text-accent-purple/20 text-6xl font-serif animate-pulse">"</div>
      <div class="absolute bottom-16 right-8 text-accent-blue/20 text-6xl font-serif animate-pulse delay-500">"</div>
      <!-- Floating stars -->
      <div class="absolute top-32 right-16 w-4 h-4 text-accent-star animate-bounce">
        <svg fill="currentColor" viewBox="0 0 20 20">
          <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
        </svg>
      </div>
      <div class="absolute bottom-32 left-16 w-3 h-3 text-accent-orange animate-pulse delay-300">
        <svg fill="currentColor" viewBox="0 0 20 20">
          <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
        </svg>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
        <!-- Left Content -->
        <div class="space-y-8">
          <!-- Dash line with TESTIMONIAL text -->
          <div class="relative">
            <div class="flex items-center space-x-4">
              <div class="flex-1 h-px bg-gradient-to-r from-accent-purple to-accent-blue"></div>
              <span class="text-caption text-accent-purple font-inter font-medium tracking-wider">TESTIMONIAL</span>
              <div class="flex-1 h-px bg-gradient-to-l from-accent-purple to-accent-blue"></div>
            </div>
          </div>

          <!-- Header -->
          <div class="space-y-4">
            <h2 class="text-h2 text-text-dark font-inter">
              What They <span class="text-brand-primary">Say?</span>
            </h2>
            <p class="text-body text-text-muted font-inter leading-relaxed">
              CertChain has got many positive ratings from our users around the world. Some of the students and teachers were greatly helped by the Skilline. Are you too? Please give your assessment
            </p>
          </div>

          <!-- Write Assessment Button -->
          <button class="group inline-flex items-center gap-3 bg-white hover:bg-brand-primary text-text-dark hover:text-white px-8 py-4 rounded-3xl text-h6 font-inter transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1 border border-brand-primary/20">
            <span>Write your assessment</span>
            <div class="relative">
              <svg class="w-6 h-6 transition-transform duration-300 group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
              </svg>
              <!-- Circle border around arrow -->
              <div class="absolute -inset-1 border-2 border-current rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            </div>
          </button>
        </div>

        <!-- Right Content - Image with Testimonial Overlay -->
        <div class="relative group">
          <div class="relative overflow-hidden rounded-3xl  hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
            <img src="../../public/images/testimonial.png" alt="User Testimonial" class="w-full h-96 object-contain">

            <!-- Testimonial Overlay Card -->
            <div class="absolute bottom-6 right-6 bg-white/95 backdrop-blur-sm rounded-2xl p-6 shadow-xl max-w-sm">
              <!-- Quote -->
              <blockquote class="mb-4">
                <p class="text-body-sm text-text-dark font-inter italic leading-relaxed">
                  "Thank you so much for your help. It's exactly what I've been looking for. You won't regret it. It really saves me time and effort. CertChain is exactly what I need."
                </p>
              </blockquote>

              <!-- Author and Rating -->
              <div class="flex items-center justify-between">
                <h4 class="text-h6 text-text-dark font-inter font-medium">
                  Mai Hoàng Anh
                </h4>
                <!-- 5 Star Rating -->
                <div class="flex space-x-1">
                  <svg
                    v-for="star in 5"
                    :key="star"
                    class="w-4 h-4 text-accent-star"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                  >
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                  </svg>
                </div>
              </div>
            </div>

            <!-- Decorative elements -->
            <div class="absolute top-4 left-4 w-3 h-3 bg-accent-purple rounded-full animate-pulse"></div>
            <div class="absolute top-8 right-8 w-2 h-2 bg-accent-blue rounded-full animate-pulse delay-300"></div>
          </div>

          <!-- Floating decorative elements around the image -->
          <div class="absolute -z-10 top-8 -left-8 w-24 h-24 bg-gradient-to-r from-accent-purple/10 to-accent-blue/10 rounded-full blur-xl animate-float"></div>
          <div class="absolute -z-10 -bottom-8 -right-8 w-32 h-32 bg-gradient-to-r from-accent-star/10 to-accent-orange/10 rounded-full blur-xl animate-float-delay"></div>
        </div>
      </div>
    </div>

    <!-- Bottom decorative wave -->
    <div class="absolute bottom-0 left-0 right-0 overflow-hidden">
      <svg class="w-full h-12" viewBox="0 0 1200 120" preserveAspectRatio="none">
        <path d="M0,0 C150,60 350,120 600,60 C850,0 1050,60 1200,0 L1200,120 L0,120 Z"
              fill="url(#testimonial-gradient)" opacity="0.1"/>
        <defs>
          <linearGradient id="testimonial-gradient" x1="0%" y1="0%" x2="100%" y2="0%">
            <stop offset="0%" style="stop-color:#00A89A"/>
            <stop offset="50%" style="stop-color:#6366F1"/>
            <stop offset="100%" style="stop-color:#FFC107"/>
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
