<template>
  <div class="min-h-screen bg-gray-50">
    <div v-if="loading" class="flex justify-center items-center min-h-[60vh]">
      <div class="animate-spin rounded-full h-16 w-16 border-b-4 border-brand-primary"></div>
    </div>

    <div v-else-if="error" class="text-center py-20">
      <h2 class="text-2xl font-bold text-red-600 mb-4">Unable to load course</h2>
      <p class="text-text-muted">{{ error.message }}</p>
      <NuxtLink to="/courses" class="mt-4 inline-block text-brand-primary hover:underline">
        Back to courses
      </NuxtLink>
    </div>

    <div v-else-if="course">

    <div 
      class="h-96 bg-cover bg-center" 
      :style="{ backgroundImage: `url(${course.thumbnail || '/placeholder-course.jpg'})` }"
    >
    </div>

      <main class="container mx-auto px-4 grid lg:grid-cols-3 gap-6 py-12">
        
        <div class="lg:col-span-2">
          
          <h1 class="text-4xl font-bold text-text-dark mb-4">{{ course.title }}</h1>
          <p class="text-lg text-text-muted mb-6">{{ course.description }}</p>
          
          <div class="flex space-x-2 mb-6 border-b border-gray-200">
            <button 
              v-for="tab in tabs" 
              :key="tab.id"
              @click="activeTab = tab.id" 
              
              :class="[
                'px-6 py-3 font-semibold transition-colors duration-200 focus:outline-none',
                activeTab === tab.id
                  ? 'border-b-2 border-brand-primary text-brand-primary'
                  : 'text-gray-500 hover:text-gray-700'
              ]"
            >
              {{ tab.name }}
            </button>
          </div>

          <div class="bg-white rounded-lg shadow-sm p-8 min-h-[400px]">
            <div v-if="activeTab === 'description'">
              <h2 class="text-2xl font-bold mb-4">Detailed Description</h2>
              <div v-html="course.description || 'No detailed description available.'"></div>
            </div>
              <div v-if="activeTab === 'review'">
                <h2 class="text-2xl font-bold mb-4">Reviews</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
  <div class="flex flex-col items-center justify-center bg-gray-50 p-6 rounded-lg">
    <div class="text-6xl font-bold text-brand-primary">
      {{ (Number(course.average_rating) || 0).toFixed(1) }}
    </div>
    <div class="flex text-yellow-400 text-2xl my-2">
      <span v-for="i in Math.round(Number(course.average_rating) || 0)" :key="`star-${i}`">★</span>
      <span v-for="i in (5 - Math.round(Number(course.average_rating) || 0))" :key="`empty-${i}`" class="text-gray-300">★</span>
    </div>
    <div class="text-gray-600">
      ({{ course.review_count || 0 }} reviews)
    </div>
  </div>
  
  <div class="flex flex-col justify-center">
    <div v-for="star in [5, 4, 3, 2, 1]" :key="`bar-${star}`" class="flex items-center gap-3 mb-2">
      <span class="w-16 text-sm font-medium text-gray-700">{{ star }} Stars</span>
      <div class="flex-1 bg-gray-200 rounded-full h-2.5">
        <div class="bg-brand-primary h-2.5 rounded-full" :style="{ width: `${ratingPercentages[star]}%` }"></div>
      </div>
      <span class="w-10 text-sm text-gray-600 text-right">{{ course.rating_counts ? course.rating_counts[star] : 0 }}</span>
    </div>
  </div>
</div>
<div class="bg-gray-50 p-6 rounded-lg mb-8 border border-gray-200">
  </div>

<h3 class="text-xl font-bold mb-6">Other Reviews</h3>
<div v-if="course.reviews && course.reviews.length > 0" class="space-y-6">
  </div>

                <div class="bg-gray-50 p-6 rounded-lg mb-8 border border-gray-200">
                  <h3 class="text-lg font-semibold mb-3">Write Your Review</h3>

                  <div class="flex items-center space-x-2 mb-4">
                    <span class="text-gray-700">Your Rating:</span>
                    <div class="flex">
                      <button 
                        v-for="star in 5" 
                        :key="star" 
                        @click="newRating = star"
                        class="text-3xl focus:outline-none"
                        :class="star <= newRating ? 'text-yellow-400' : 'text-gray-300'"
                      >
                        ★
                      </button>
                    </div>
                  </div>

                  <textarea 
                    v-model="newComment"
                    rows="4"
                    placeholder="Write your thoughts about this course..."
                    class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-primary"
                  ></textarea>

                  <p v-if="reviewError" class="text-red-500 text-sm mt-2">{{ reviewError }}</p>

                  <button 
                    @click="handleSubmitReview"
                    class="mt-4 px-6 py-2 bg-brand-primary text-white font-semibold rounded-lg hover:bg-brand-secondary transition"
                  >
                    Submit Review
                  </button>
                </div>

                <h3 class="text-xl font-bold mb-6">Other Reviews</h3>
                <div v-if="course.reviews && course.reviews.length > 0" class="space-y-6">

                  <div v-for="review in course.reviews" :key="review.id" class="border-b border-gray-200 pb-6">
                    <div class="flex items-center justify-between mb-2">
                      <div class="flex items-center gap-3">
                        <div>
                          <h4 class="font-semibold text-gray-900">
                            {{ review.student?.first_name || 'Student' }} {{ review.student?.last_name }}
                          </h4>
                          <div class="flex text-yellow-400 text-sm">
                            <span v-for="i in review.rating" :key="i">★</span>
                            <span v-for="i in (5 - review.rating)" :key="i" class="text-gray-300">★</span>
                          </div>
                        </div>
                      </div>
                      <span class="text-sm text-gray-500">{{ new Date(review.created_at).toLocaleDateString() }}</span>
                    </div>
                    <p class="text-gray-700">{{ review.comment }}</p>
                  </div>

                </div>
                <p v-else class="text-gray-500">No reviews yet for this course.</p>
              </div>
            <div v-if="activeTab === 'curriculum'">
              <h2 class="text-2xl font-bold mb-4">Curriculum</h2>
              <div v-if="course.modules && course.modules.length > 0" class="space-y-6">
                <div v-for="module in course.modules" :key="module.id">
                  <h3 class="text-xl font-semibold mb-3 p-3 bg-gray-100 rounded">
                    {{ module.title }} (Module {{ module.order_index }})
                  </h3>
                  <ul v-if="module.lessons && module.lessons.length > 0" class="space-y-2 pl-4">
                    <li 
                      v-for="lesson in module.lessons" 
                      :key="lesson.id" 
                      class="border-b p-3 flex justify-between items-center"
                    >
                      <span>{{ lesson.order_index }}. {{ lesson.title }}</span>
                      <span class="text-sm text-gray-500">{{ lesson.duration || 'N/A' }} minutes</span>
                    </li>
                  </ul>
                  <p v-else class="pl-4 text-gray-500">No lessons in this module yet.</p>
                </div>
              </div>
              <p v-else>No modules available yet.</p>
            </div>
          </div>
        </div>

        <div class="lg:col-span-1">
          <div class="sticky top-8 space-y-6">
            
            <div class="bg-white rounded-lg shadow-lg p-6">
              <div class="flex items-baseline gap-3 mb-4">
                <span class="text-4xl font-bold text-teal-600">${{ course.price }}</span>
                <span v-if="course.originalPrice" class="text-2xl text-gray-400 line-through">${{ course.originalPrice }}</span>
              </div>
              <button class="w-full bg-brand-primary text-white py-3 rounded-lg font-semibold text-lg hover:bg-brand-secondary transition">
                Enroll Now
              </button>
            </div>
            
            <div class="bg-white rounded-lg shadow-lg p-6">
              <h4 class="text-xl font-bold mb-4">This course includes</h4>
              <ul class="space-y-3 text-gray-700">
                <li class="flex items-center gap-2">
                  <span>{{ course.modules?.length || 'Multiple' }} modules</span>
                </li>
                <li class="flex items-center gap-2">
                  <span>Duration: {{ course.duration || 'N/A' }} hours</span>
                </li>
                <li class="flex items-center gap-2">
                  <span>Access on any device</span>
                </li>
                <li class="flex items-center gap-2">
                  <span>Blockchain Certificate</span>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">

import type { Course, Review } from '../../types/course'
import { ref, onMounted, computed } from 'vue'

const { getCourseById, addReview } = useCourses()
const route = useRoute()


// State
const course = ref<Course | null>(null)
const loading = ref(true)
const error = ref<Error | null>(null)
const activeTab = ref('description') // Default to 'description' to match tabs
const newRating = ref(0)
const newComment = ref('')
const reviewError = ref('')

const tabs = [
  { id: 'description', name: 'Description' },
  { id: 'review', name: 'Review' },
  { id: 'curriculum', name: 'Curriculum' }
]

const courseId = route.params.id as string

onMounted(async () => {
  if (!courseId) return

  loading.value = true
  error.value = null
  try {
    // Call getCourseById function (now includes token)
    const result = await getCourseById(courseId)
    course.value = result
  } catch (err: any) {
    console.error('Error loading course details:', err)
    error.value = err
  } finally {
    loading.value = false
  }
})

const handleSubmitReview = async () => {
  if (!course.value) {
    reviewError.value = 'Error: Course not found. Please reload the page.'
    return
  }
  reviewError.value = ''
  if (newRating.value === 0) {
    reviewError.value = 'Please select a star rating.'
    return
  }

  try {
    // Send review and get full response
    const response = await addReview(courseId, {
      rating: newRating.value,
      comment: newComment.value
    }) 
    
    // Get actual review from response
    const newReview = response.review as Review

    // Ensure course.reviews is an array
    if (!course.value.reviews) {
      course.value.reviews = []
    }

    // Update review list
    const existingIndex = course.value.reviews.findIndex(
        r => r.student_id === newReview.student_id
    );

    if (existingIndex !== -1) {
        // Update existing review
        course.value.reviews[existingIndex] = newReview;
    } else {
        // Add new review to the beginning of the list
        course.value.reviews.unshift(newReview);
    }

    // Update stats (from 'response.course_stats')
    if (response.course_stats && course.value) { 
            course.value.average_rating = response.course_stats.average_rating
            course.value.review_count = response.course_stats.review_count
            // Important line to update chart:
            course.value.rating_counts = response.course_stats.rating_counts
        }

    // Clear form
    newRating.value = 0
    newComment.value = ''

  } catch (err: any) {
    reviewError.value = err.data?.message || 'Unable to submit review.'
  }
}

const ratingPercentages = computed(() => {
  const percentages: { [key: number]: number } = { 1: 0, 2: 0, 3: 0, 4: 0, 5: 0 }
  
  // Use .value because 'course' is a ref
  if (!course.value || !course.value.rating_counts || !course.value.review_count) {
    return percentages // Return 0% if no data
  }
  
  const totalReviews = course.value.review_count
  
  for (let i = 1; i <= 5; i++) {
    const count = course.value.rating_counts[i] || 0
    // Avoid division by 0 if totalReviews is 0
    percentages[i] = totalReviews > 0 ? (count / totalReviews) * 100 : 0
  }
  
  return percentages
})

// SEO
useHead({
  title: () => `${course.value?.title || 'Course Detail'} - CertChain`,
  meta: [
    { name: 'description', content: () => course.value?.description || 'Course Details' }
  ]
})
</script>