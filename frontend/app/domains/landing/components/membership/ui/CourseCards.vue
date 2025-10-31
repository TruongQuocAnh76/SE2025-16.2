<template>
  <section class="py-16 bg-white">
    <div class="container mx-auto px-4 max-w-5xl">
      <div class="grid md:grid-cols-2 gap-8">
        <div 
          v-for="(course, index) in courses" 
          :key="index"
          class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 hover:shadow-lg transition-shadow"
        >
          <div class="w-full h-48 bg-gradient-to-br from-teal-100 to-blue-100 flex items-center justify-center overflow-hidden">
            <img 
              v-if="course.image"
              :src="course.image" 
              :alt="course.title"
              class="w-full h-full object-cover"
            />
            <svg v-else class="w-20 h-20 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
          </div>
          <div class="p-6">
            <h3 class="text-xl font-bold mb-2">{{ course.title }}</h3>
            <p class="text-gray-600 text-sm mb-4">{{ course.description }}</p>
            <NuxtLink 
              :to="course.link"
              class="inline-block bg-teal-400 text-white px-6 py-2 rounded-lg font-medium hover:bg-teal-500 transition-colors"
            >
              {{ course.buttonText }}
            </NuxtLink>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref } from 'vue'

const courses = ref([
  {
    title: 'Become a Teacher',
    description: 'Share your expertise and inspire the next generation of learners. Create courses and help students achieve their goals.',
    buttonText: 'Join as Teacher',
    link: '/teacher/register', 
    image: '/images/membership/teacher.jpg'
  },
  {
    title: 'Become a Student',
    description: 'Start your learning journey today. Access hundreds of courses and earn blockchain-verified certificates.',
    buttonText: 'Join as Student',
    link: 'auth/signup',
    image: '/images/membership/student.jpg'
  }
])
</script>
