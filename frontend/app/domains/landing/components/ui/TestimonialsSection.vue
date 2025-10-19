<template>
  <section class="bg-neutral-0 py-12">
    <div class="max-w-7xl mx-auto px-6">
      <!-- Section Header -->
      <div class="text-center mb-12">
        <h2 class="text-h2 text-neutral-900 mb-4 font-inter">
          What Our Users Say
        </h2>
        <p class="text-body text-neutral-600 max-w-3xl mx-auto font-inter">
          Trusted by students, educators, and employers worldwide.
        </p>
      </div>

      <!-- Testimonials Carousel -->
      <div class="relative">
        <!-- Carousel Container -->
        <div class="overflow-hidden">
          <div 
            class="flex transition-transform duration-500 ease-in-out"
            :style="{ transform: `translateX(-${currentIndex * 100}%)` }"
          >
            <!-- Testimonial Cards -->
            <div 
              v-for="testimonial in testimonials" 
              :key="testimonial.id"
              class="flex-shrink-0 w-full px-6"
            >
              <div class="bg-neutral-50 rounded-2xl p-12 h-full shadow-lg max-w-4xl mx-auto">
                <!-- Star Rating -->
                <div class="flex justify-center mb-8">
                  <div class="flex space-x-1">
                    <svg 
                      v-for="star in testimonial.rating" 
                      :key="star"
                      class="w-6 h-6 text-warning-500" 
                      fill="currentColor" 
                      viewBox="0 0 20 20"
                    >
                      <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                  </div>
                </div>

                <!-- Quote -->
                <blockquote class="text-center mb-8">
                  <p class="text-h4 text-neutral-600 font-inter italic leading-relaxed max-w-3xl mx-auto">
                    "{{ testimonial.quote }}"
                  </p>
                </blockquote>

                <!-- Author Info -->
                <div class="text-center">
                  <h4 class="text-h3 text-neutral-900 mb-2 font-inter">
                    {{ testimonial.name }}
                  </h4>
                  <p class="text-body text-neutral-600 font-inter">
                    {{ testimonial.role }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Dots Indicator -->
      <div class="flex justify-center mt-8 space-x-2">
        <button
          v-for="(dot, index) in testimonials.length"
          :key="index"
          @click="goToSlide(index)"
          class="w-3 h-3 rounded-full transition-colors duration-200"
          :class="currentIndex === index ? 'bg-primary-500' : 'bg-neutral-600'"
        />
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'

// Testimonials data
const testimonials = [
  {
    id: 1,
    quote: "The blockchain-verified certificates have helped me stand out in job applications. Employers trust the authenticity.",
    name: "Sarah Johnson",
    role: "Software Developer",
    rating: 5
  },
  {
    id: 2,
    quote: "Creating and managing courses is intuitive. The automatic certificate generation saves me hours of work.",
    name: "Michael Chen",
    role: "Online Instructor", 
    rating: 5
  },
  {
    id: 3,
    quote: "We can instantly verify candidate certificates. It's transformed our hiring process and reduced fraud.",
    name: "Emily Rodriguez",
    role: "HR Manager",
    rating: 5
  },
  {
    id: 4,
    quote: "The course quality is exceptional. I've gained valuable skills that directly apply to my career.",
    name: "David Kim",
    role: "Data Analyst",
    rating: 4
  },
  {
    id: 5,
    quote: "As an educator, I love how easy it is to track student progress and issue verified certificates.",
    name: "Lisa Thompson",
    role: "University Professor",
    rating: 5
  },
  {
    id: 6,
    quote: "The platform is user-friendly and the blockchain verification gives me confidence in the certificates.",
    name: "James Wilson",
    role: "Marketing Manager",
    rating: 4
  }
]

// Carousel state
const currentIndex = ref(0)

// Carousel methods
const goToSlide = (index: number) => {
  currentIndex.value = index
}

// Auto-play functionality
let autoplayInterval: number | null = null

const startAutoplay = () => {
  autoplayInterval = setInterval(() => {
    if (currentIndex.value >= testimonials.length - 1) {
      currentIndex.value = 0
    } else {
      currentIndex.value++
    }
  }, 4000) // Change slide every 4 seconds
}

const stopAutoplay = () => {
  if (autoplayInterval) {
    clearInterval(autoplayInterval)
  }
}

// Lifecycle hooks
onMounted(() => {
  startAutoplay()
})

onUnmounted(() => {
  stopAutoplay()
})
</script>
