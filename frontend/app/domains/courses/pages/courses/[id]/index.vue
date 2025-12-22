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

      <div class="h-96 bg-cover bg-center"
        :style="{ backgroundImage: `url(${course.thumbnail || '/placeholder-course.jpg'})` }">
      </div>

      <main class="container mx-auto px-4 grid lg:grid-cols-3 gap-6 py-12">

        <div class="lg:col-span-2">

          <h1 class="text-4xl font-bold text-text-dark mb-4">{{ course.title }}</h1>
          <p class="text-lg text-text-muted mb-6">{{ course.description }}</p>

          <div class="flex space-x-2 mb-6 border-b border-gray-200">
            <button v-for="tab in tabs" :key="tab.id" @click="activeTab = tab.id" :class="[
              'px-6 py-3 font-semibold transition-colors duration-200 focus:outline-none',
              activeTab === tab.id
                ? 'border-b-2 border-brand-primary text-brand-primary'
                : 'text-gray-500 hover:text-gray-700'
            ]">
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
                    <span v-for="i in (5 - Math.round(Number(course.average_rating) || 0))" :key="`empty-${i}`"
                      class="text-gray-300">★</span>
                  </div>
                  <div class="text-gray-600">
                    ({{ course.review_count || 0 }} reviews)
                  </div>
                </div>

                <div class="flex flex-col justify-center">
                  <div v-for="star in [5, 4, 3, 2, 1]" :key="`bar-${star}`" class="flex items-center gap-3 mb-2">
                    <span class="w-16 text-sm font-medium text-gray-700">{{ star }} Stars</span>
                    <div class="flex-1 bg-gray-200 rounded-full h-2.5">
                      <div class="bg-brand-primary h-2.5 rounded-full"
                        :style="{ width: `${ratingPercentages[star]}%` }"></div>
                    </div>
                    <span class="w-10 text-sm text-gray-600 text-right">{{ course.rating_counts ?
                      course.rating_counts[star] : 0 }}</span>
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
                    <button v-for="star in 5" :key="star" @click="newRating = star" class="text-3xl focus:outline-none"
                      :class="star <= newRating ? 'text-yellow-400' : 'text-gray-300'">
                      ★
                    </button>
                  </div>
                </div>

                <textarea v-model="newComment" rows="4" placeholder="Write your thoughts about this course..."
                  class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-primary"></textarea>

                <p v-if="reviewError" class="text-red-500 text-sm mt-2">{{ reviewError }}</p>

                <button @click="handleSubmitReview"
                  class="mt-4 px-6 py-2 bg-brand-primary text-white font-semibold rounded-lg hover:bg-brand-secondary transition">
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
              <h2 class="text-2xl font-bold mb-6">Curriculum</h2>

              <!-- Course Content Summary -->
              <div class="bg-gradient-to-r from-blue-50 to-green-50 border border-gray-200 rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold mb-3 text-gray-800">Course Content Overview</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                  <div class="bg-white rounded-lg p-3 shadow-sm">
                    <div class="text-2xl font-bold text-green-600">{{ course.modules?.length || 0 }}</div>
                    <div class="text-sm text-gray-600">Modules</div>
                  </div>
                  <div class="bg-white rounded-lg p-3 shadow-sm">
                    <div class="text-2xl font-bold text-teal-600">{{ totalLessonCount }}</div>
                    <div class="text-sm text-gray-600">Lessons</div>
                  </div>
                  <div class="bg-white rounded-lg p-3 shadow-sm">
                    <div class="text-2xl font-bold text-blue-600">{{ course.quizzes?.length || 0 }}</div>
                    <div class="text-sm text-gray-600">Quizzes</div>
                  </div>
                  <div class="bg-white rounded-lg p-3 shadow-sm">
                    <div class="text-2xl font-bold text-purple-600">{{ course.duration || 'N/A' }}</div>
                    <div class="text-sm text-gray-600">Hours</div>
                  </div>
                </div>
              </div>
              <div v-if="course.modules && course.modules.length > 0" class="space-y-6">
                <div v-for="module in course.modules" :key="module.id">
                  <h3 class="text-xl font-semibold mb-3 p-3 bg-green-50 rounded flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 7a2 2 0 012-2h4a2 2 0 012 2v2M7 7V5a2 2 0 012-2h6a2 2 0 012 2v2">
                      </path>
                    </svg>
                    {{ module.title }} (Module {{ module.order_index }})
                  </h3>
                  <div v-if="module.lessons && module.lessons.length > 0" class="space-y-2">
                    <div v-for="lesson in module.lessons" :key="lesson.id"
                      class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                      <div class="flex justify-between items-start">
                        <div class="flex-1">
                          <NuxtLink :to="`/courses/${course.id}/lessons/${lesson.id}`"
                            class="text-lg font-medium text-brand-primary hover:text-brand-secondary block mb-1">
                            {{ lesson.order_index }}. {{ lesson.title }}
                          </NuxtLink>
                          <p v-if="lesson.description" class="text-gray-600 text-sm mb-2">
                            {{ lesson.description }}
                          </p>
                          <div class="flex items-center gap-2 text-sm text-gray-500">
                            <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">
                              Lesson
                            </span>
                            <span v-if="lesson.duration" class="flex items-center gap-1">
                              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                              </svg>
                              {{ lesson.duration }} minutes
                            </span>
                            <span v-if="lesson.content_type" class="flex items-center gap-1">
                              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M7 4V2a1 1 0 011-1h4a1 1 0 011 1v2h4a1 1 0 011 1v14a2 2 0 01-2 2H6a2 2 0 01-2-2V5a1 1 0 011-1h4z">
                                </path>
                              </svg>
                              {{ lesson.content_type.charAt(0).toUpperCase() + lesson.content_type.slice(1) }}
                            </span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div v-else class="p-4 bg-gray-50 rounded-lg text-center">
                    <svg class="mx-auto h-8 w-8 text-gray-400 mb-2" fill="none" stroke="currentColor"
                      viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                      </path>
                    </svg>
                    <p class="text-gray-500">No lessons in this module yet.</p>
                  </div>
                </div>
              </div>
              <div v-else class="p-6 bg-gray-50 rounded-lg text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 7a2 2 0 012-2h4a2 2 0 012 2v2M7 7V5a2 2 0 012-2h6a2 2 0 012 2v2">
                  </path>
                </svg>
                <p class="text-gray-500">No modules available yet.</p>
              </div>

              <!-- Quizzes Section -->
              <div v-if="course.quizzes && course.quizzes.length > 0" class="mt-8">
                <h3 class="text-xl font-semibold mb-4 p-3 bg-blue-50 rounded flex items-center gap-2">
                  <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
                    </path>
                  </svg>
                  Course Quizzes ({{ course.quizzes.length }})
                </h3>
                <div class="space-y-3">
                  <div v-for="quiz in course.quizzes" :key="quiz.id"
                    class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-start mb-2">
                      <NuxtLink :to="`/courses/${course.id}/quizzes/${quiz.id}`"
                        class="flex-1 text-lg font-medium text-brand-primary hover:text-brand-secondary">
                        {{ quiz.title }}
                      </NuxtLink>
                      <span class="ml-4 px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">
                        Quiz
                      </span>
                    </div>
                    <p v-if="quiz.description" class="text-gray-600 text-sm mb-3">
                      {{ quiz.description }}
                    </p>
                    <div class="flex flex-wrap gap-4 text-sm text-gray-500">
                      <span v-if="quiz.duration" class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        {{ quiz.duration }} minutes
                      </span>
                      <span v-if="quiz.total_questions" class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                          </path>
                        </svg>
                        {{ quiz.total_questions }} questions
                      </span>
                      <span v-if="quiz.passing_score" class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Pass: {{ quiz.passing_score }}%
                      </span>
                      <span v-if="quiz.max_attempts" class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                          </path>
                        </svg>
                        {{ quiz.max_attempts }} attempts
                      </span>
                    </div>
                  </div>
                </div>
              </div>
              <div v-else-if="course.modules && course.modules.length > 0"
                class="mt-8 p-6 bg-gray-50 rounded-lg text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
                  </path>
                </svg>
                <p class="text-gray-500">No quizzes available for this course yet.</p>
              </div>
            </div>
          </div>
        </div>

        <div class="lg:col-span-1">
          <div class="sticky top-8 space-y-6">

            <div class="bg-white rounded-lg shadow-lg p-6">
              <div class="flex items-baseline gap-3 mb-4">
                <span class="text-4xl font-bold text-teal-600">${{ course.price }}</span>
                <span v-if="course.originalPrice" class="text-2xl text-gray-400 line-through">${{ course.originalPrice
                  }}</span>
              </div>

              <p v-if="!isEnrolled && enrollError" class="text-red-500 text-sm mb-3">{{ enrollError }}</p>
              <p v-if="!isEnrolled && enrollSuccess" class="text-green-500 text-sm mb-3">{{ enrollSuccess }}</p>

              <!-- Enrolled Status -->
              <div v-if="isEnrolled" class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                <div class="flex items-center gap-2 text-green-700 mb-2">
                  <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                  </svg>
                  <span class="font-semibold">You're enrolled!</span>
                </div>
                <p class="text-sm text-green-600">
                  Enrolled on {{ enrollmentDate ? new Date(enrollmentDate).toLocaleDateString() : 'N/A' }}
                </p>
              </div>

              <!-- Premium User Badge -->
              <div v-if="isPremiumUser && !isEnrolled" class="mb-3 p-3 bg-gradient-to-r from-yellow-50 to-yellow-100 border border-yellow-300 rounded-lg">
                <div class="flex items-center gap-2 text-yellow-800">
                  <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                  </svg>
                  <span class="font-semibold text-sm">Premium Member - All Courses Free!</span>
                </div>
              </div>

              <button 
                v-if="!isEnrolled"
                @click="handleEnroll" 
                :disabled="enrollLoading"
                :class="[
                  'w-full py-3 rounded-lg font-semibold text-lg transition disabled:opacity-50 disabled:cursor-not-allowed',
                  isPremiumUser 
                    ? 'bg-gradient-to-r from-yellow-500 to-yellow-600 text-white hover:from-yellow-600 hover:to-yellow-700' 
                    : 'bg-brand-primary text-white hover:bg-brand-secondary'
                ]">
                <span v-if="enrollLoading" class="flex items-center justify-center">
                  <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                  </svg>
                  Enrolling...
                </span>
                <span v-else-if="isPremiumUser" class="flex items-center justify-center gap-2">
                  <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                  </svg>
                  Enroll Free (Premium)
                </span>
                <span v-else>Enroll Now</span>
              </button>
              
              <button 
                v-else
                @click="goToFirstLesson"
                class="w-full bg-green-600 text-white py-3 rounded-lg font-semibold text-lg hover:bg-green-700 transition"
                :disabled="!course?.modules || course.modules.length === 0">
                Continue Learning
              </button>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
              <h4 class="text-xl font-bold mb-4">This course includes</h4>
              <ul class="space-y-3 text-gray-700">
                <li class="flex items-center gap-3">
                  <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 7a2 2 0 012-2h4a2 2 0 012 2v2M7 7V5a2 2 0 012-2h6a2 2 0 012 2v2">
                    </path>
                  </svg>
                  <span>{{ course.modules?.length || 0 }} modules</span>
                </li>
                <li v-if="totalLessonCount > 0" class="flex items-center gap-3">
                  <svg class="w-5 h-5 text-teal-600 flex-shrink-0" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                    </path>
                  </svg>
                  <span>{{ totalLessonCount }} lesson{{ totalLessonCount === 1 ? '' : 's' }}</span>
                </li>
                <li v-if="course.quizzes && course.quizzes.length > 0" class="flex items-center gap-3">
                  <svg class="w-5 h-5 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
                    </path>
                  </svg>
                  <span>{{ course.quizzes.length }} quiz{{ course.quizzes.length === 1 ? '' : 'zes' }}</span>
                </li>
                <li class="flex items-center gap-3">
                  <svg class="w-5 h-5 text-purple-600 flex-shrink-0" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                  <span>Duration: {{ course.duration || 'N/A' }} hours</span>
                </li>
                <li class="flex items-center gap-3">
                  <svg class="w-5 h-5 text-orange-600 flex-shrink-0" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                  </svg>
                  <span>Access on any device</span>
                </li>
                <li class="flex items-center gap-3">
                  <svg class="w-5 h-5 text-yellow-600 flex-shrink-0" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z">
                    </path>
                  </svg>
                  <span>Blockchain Certificate</span>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
  <!-- <NuxtPage /> -->
</template>

<script setup lang="ts">

import type { Course, Review } from '../../../types/course'
import { ref, onMounted, computed, nextTick } from 'vue'

const { getCourseById, addReview, enrollInCourse } = useCourses()
const route = useRoute()
const router = useRouter()


// State
const course = ref<Course | null>(null)
const loading = ref(true)
const error = ref<Error | null>(null)
const activeTab = ref('description') // Default to 'description' to match tabs
const newRating = ref(0)
const newComment = ref('')
const reviewError = ref('')
const enrollLoading = ref(false)
const enrollError = ref('')
const enrollSuccess = ref('')
const isEnrolled = ref(false)
const enrollmentDate = ref<string | null>(null)
const isPremiumUser = ref(false)
const membershipExpiresAt = ref<string | null>(null)

const tabs = [
  { id: 'description', name: 'Description' },
  { id: 'review', name: 'Review' },
  { id: 'curriculum', name: 'Curriculum' }
]

const courseId = route.params.id as string

// Check for enrollment requirement from query params
const checkEnrollmentRequirement = () => {
  // Only show error if not enrolled
  if (route.query.enrollment_required === 'true' && !isEnrolled.value) {
    enrollError.value = 'You need to enroll in this course to access the lessons.'
  }
  if (route.query.payment_success === 'true') {
    enrollSuccess.value = 'Payment successful! You are now enrolled in this course.'
    // Clear the query param after showing message
    setTimeout(() => {
      router.replace({ query: {} })
    }, 3000)
  }
}

const checkUserMembershipStatus = async () => {
  try {
    const token = useCookie('auth_token').value
    if (!token) return
    
    const config = useRuntimeConfig()
    const user = await $fetch('/api/auth/me', {
      baseURL: config.public.backendUrl as string,
      headers: {
        'Authorization': `Bearer ${token}`
      }
    })
    
    isPremiumUser.value = user.membership_tier === 'PREMIUM'
    membershipExpiresAt.value = user.membership_expires_at
  } catch (err) {
    console.error('Error checking membership status:', err)
  }
}

const checkEnrollmentStatus = async () => {
  try {
    const token = useCookie('auth_token').value
    if (!token) {
      console.log('No token found for enrollment check')
      return
    }
    
    const config = useRuntimeConfig()
    const response = await $fetch(`/api/courses/${courseId}/enrollment/check`, {
      baseURL: config.public.backendUrl as string,
      headers: {
        'Authorization': `Bearer ${token}`
      }
    })
    
    console.log('Enrollment check response:', response)
    
    isEnrolled.value = response.isEnrolled
    if (response.enrollment) {
      enrollmentDate.value = response.enrollment.enrolled_at
    }
    
    console.log('Updated isEnrolled:', isEnrolled.value)
    console.log('Updated enrollmentDate:', enrollmentDate.value)
  } catch (err) {
    console.error('Error checking enrollment:', err)
  }
}

onMounted(async () => {
  if (!courseId) return

  loading.value = true
  error.value = null
  
  try {
    // Call getCourseById function (now includes token)
    const result = await getCourseById(courseId)
    course.value = result
    
    // Fetch modules with lessons (requires auth)
    const token = useCookie('auth_token').value
    if (token) {
      try {
        const config = useRuntimeConfig()
        const modulesData = await $fetch(`/api/courses/${courseId}/modules`, {
          baseURL: config.public.backendUrl as string,
          headers: { 
            'Accept': 'application/json',
            'Authorization': `Bearer ${token}`
          }
        })
        
        // Add modules to course
        if (course.value && modulesData) {
          course.value.modules = modulesData
          console.log('Loaded modules:', modulesData)
        }
      } catch (modulesErr) {
        console.error('Failed to load modules:', modulesErr)
        // Continue without modules if fetch fails
      }
    }
    
    // Check user membership and enrollment status first
    await Promise.all([
      checkUserMembershipStatus(),
      checkEnrollmentStatus()
    ])
    
    // Then check enrollment requirement (will use isEnrolled value)
    checkEnrollmentRequirement()
    
    // Clear error message if already enrolled
    if (isEnrolled.value) {
      enrollError.value = ''
    }
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

const handleEnroll = async () => {
  if (!course.value) {
    enrollError.value = 'Course not found.'
    return
  }

  enrollLoading.value = true
  enrollError.value = ''
  enrollSuccess.value = ''

  try {
    const token = useCookie('auth_token').value
    const config = useRuntimeConfig()
    
    console.log('Starting enrollment process...')
    console.log('isPremiumUser:', isPremiumUser.value)
    console.log('course.price:', course.value.price)
    
    // If user is Premium, enroll for free
    if (isPremiumUser.value) {
      console.log('Enrolling as premium user...')
      const response = await $fetch(`/api/courses/${courseId}/enroll-free`, {
        baseURL: config.public.backendUrl as string,
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`
        }
      })

      console.log('Premium enroll response:', response)
      
      // Reload enrollment status first
      await checkEnrollmentStatus()
      
      console.log('After checkEnrollmentStatus, isEnrolled:', isEnrolled.value)
      
      // Clear success message immediately so UI shows enrolled status
      enrollSuccess.value = ''
      return
    }
    
    // If course is free (price = 0), use regular enroll
    if (course.value.price === 0 || !course.value.price) {
      console.log('Enrolling in free course...')
      const response = await enrollInCourse(courseId)

      console.log('Free course enroll response:', response)

      // Check if enroll was successful (response.success might be undefined, so check response existence)
      if (response && (response.success === true || response.message)) {
        // Reload enrollment status to get fresh data
        await checkEnrollmentStatus()
        
        console.log('After checkEnrollmentStatus, isEnrolled:', isEnrolled.value)
        
        // Force UI update by using nextTick
        await nextTick()
        
        // Don't show success message, let the enrolled status show instead
        enrollSuccess.value = ''
      } else {
        enrollError.value = response?.message || 'Failed to enroll in the course.'
      }
    } else {
      // Course has price and user is not Premium, redirect to payment
      console.log('Redirecting to payment...')
      router.push(`/payment?type=COURSE&course_id=${courseId}`)
    }

  } catch (err: any) {
    console.error('Enrollment error:', err)
    enrollError.value = err.data?.message || 'Failed to enroll in the course. Please try again.'
  } finally {
    enrollLoading.value = false
    console.log('Enrollment process finished. isEnrolled:', isEnrolled.value)
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

const totalLessonCount = computed(() => {
  if (!course.value || !course.value.modules) {
    return 0
  }

  return course.value.modules.reduce((total, module) => {
    return total + (module.lessons ? module.lessons.length : 0)
  }, 0)
})

const goToFirstLesson = () => {
  // Navigate to Open Course page
  router.push(`/courses/${courseId}/open`)
}

// SEO
useHead({
  title: () => `${course.value?.title || 'Course Detail'} - CertChain`,
  meta: [
    { name: 'description', content: () => course.value?.description || 'Course Details' }
  ]
})
</script>