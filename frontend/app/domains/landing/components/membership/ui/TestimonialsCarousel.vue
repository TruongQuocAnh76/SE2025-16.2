<template>
  <section class="py-16 bg-blue-50">
    <div class="container mx-auto px-4">
      <h2 class="text-3xl font-bold text-center mb-12">
        What our students have to say
      </h2>
      <div class="relative max-w-5xl mx-auto">
        <div class="flex items-center justify-center gap-8">
          <!-- Previous Button -->
          <button 
            @click="prevTestimonial"
            :disabled="currentIndex === 0"
            class="w-10 h-10 rounded-full bg-teal-400 text-white flex items-center justify-center hover:bg-teal-500 transition-colors flex-shrink-0 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
          </button>

          <!-- Testimonial Cards -->
          <div class="grid md:grid-cols-4 gap-4 overflow-hidden flex-1">
            <div 
              v-for="(testimonial, index) in visibleTestimonials" 
              :key="index"
              class="bg-white rounded-lg p-6 shadow-md"
            >
              <div class="flex flex-col items-center text-center">
                <img 
                  :src="testimonial.avatar" 
                  :alt="`${testimonial.name} Avatar`"
                  class="w-16 h-16 rounded-full mb-3 object-cover"
                />
                <h3 class="font-bold text-gray-900">{{ testimonial.name }}</h3>
                <p class="text-xs text-gray-500 mb-3">{{ testimonial.role }}</p>
                <p class="text-sm text-gray-600 leading-relaxed">{{ testimonial.text }}</p>
              </div>
            </div>
          </div>

          <!-- Next Button -->
          <button 
            @click="nextTestimonial"
            :disabled="currentIndex >= testimonials.length - 4"
            class="w-10 h-10 rounded-full bg-teal-400 text-white flex items-center justify-center hover:bg-teal-500 transition-colors flex-shrink-0 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </button>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, computed } from 'vue'

const testimonials = ref([
  {
    name: 'Mai Hoang Anh',
    role: 'Student',
    avatar: '/images/membership/student1.jpg',
    text: 'This platform has transformed my learning experience. The courses are well-structured and the blockchain certificates add real value to my credentials.'
  },
  {
    name: 'Tran Thai Dai Duong',
    role: 'Student',
    avatar: '/images/membership/student5.jpg',
    text: 'Amazing quality of content! The instructors are knowledgeable and the interactive lessons make learning enjoyable and effective.'
  },
  {
    name: 'Truong Quoc Anh',
    role: 'Student',
    avatar: '/images/membership/student3.jpg',
    text: 'I\'ve tried many learning platforms, but this one stands out. The premium membership is worth every penny with its comprehensive course library.'
  },
  {
    name: 'Bui Tho An',
    role: 'Student',
    avatar: '/images/membership/student4.jpg',
    text: 'The certificate verification through blockchain is brilliant! It gives me confidence when showcasing my achievements to potential employers.'
  },
  {
    name: 'Nguyen Canh Hoang',
    role: 'Student',
    avatar: '/images/membership/student2.jpg',
    text: 'Excellent value for money. The premium plan gives access to so many quality courses and the support team is always responsive.'
  },
  {
    name: 'Dinh Truong An',
    role: 'Student',
    avatar: '/images/membership/student6.jpg',
    text: 'The user interface is intuitive and the learning path is clear. I\'ve gained practical skills that I can apply immediately in my career.'
  }
])

const currentIndex = ref(0)

const visibleTestimonials = computed(() => {
  return testimonials.value.slice(currentIndex.value, currentIndex.value + 4)
})

const nextTestimonial = () => {
  if (currentIndex.value < testimonials.value.length - 4) {
    currentIndex.value++
  }
}

const prevTestimonial = () => {
  if (currentIndex.value > 0) {
    currentIndex.value--
  }
}
</script>
