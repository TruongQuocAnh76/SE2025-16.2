<template>
  <div class="min-h-screen bg-gray-50">
    <div v-if="loading" class="flex justify-center items-center min-h-[60vh]">
      <div class="animate-spin rounded-full h-16 w-16 border-b-4 border-brand-primary"></div>
    </div>

    <div v-else-if="error" class="text-center py-20">
      <h2 class="text-2xl font-bold text-red-600 mb-4">Không thể tải khóa học</h2>
      <p class="text-text-muted">{{ error.message }}</p>
      <NuxtLink to="/courses" class="mt-4 inline-block text-brand-primary hover:underline">
        Quay lại danh sách
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
              <h2 class="text-2xl font-bold mb-4">Mô tả chi tiết</h2>
              <div v-html="course.description || 'Chưa có mô tả chi tiết.'"></div>
            </div>
              <div v-if="activeTab === 'review'">
                <h2 class="text-2xl font-bold mb-4">Đánh giá</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
  <div class="flex flex-col items-center justify-center bg-gray-50 p-6 rounded-lg">
    <div class="text-6xl font-bold text-brand-primary">
      {{ (course.average_rating || 0).toFixed(1) }}
    </div>
    <div class="flex text-yellow-400 text-2xl my-2">
      <span v-for="i in Math.round(course.average_rating || 0)" :key="`star-${i}`">★</span>
      <span v-for="i in (5 - Math.round(course.average_rating || 0))" :key="`empty-${i}`" class="text-gray-300">★</span>
    </div>
    <div class="text-gray-600">
      ({{ course.review_count || 0 }} đánh giá)
    </div>
  </div>
  
  <div class="flex flex-col justify-center">
    <div v-for="star in [5, 4, 3, 2, 1]" :key="`bar-${star}`" class="flex items-center gap-3 mb-2">
      <span class="w-16 text-sm font-medium text-gray-700">{{ star }} Sao</span>
      <div class="flex-1 bg-gray-200 rounded-full h-2.5">
        <div class="bg-brand-primary h-2.5 rounded-full" :style="{ width: `${ratingPercentages[star]}%` }"></div>
      </div>
      <span class="w-10 text-sm text-gray-600 text-right">{{ course.rating_counts ? course.rating_counts[star] : 0 }}</span>
    </div>
  </div>
</div>
<div class="bg-gray-50 p-6 rounded-lg mb-8 border border-gray-200">
  </div>

<h3 class="text-xl font-bold mb-6">Các đánh giá khác</h3>
<div v-if="course.reviews && course.reviews.length > 0" class="space-y-6">
  </div>

                <div class="bg-gray-50 p-6 rounded-lg mb-8 border border-gray-200">
                  <h3 class="text-lg font-semibold mb-3">Viết đánh giá của bạn</h3>

                  <div class="flex items-center space-x-2 mb-4">
                    <span class="text-gray-700">Đánh giá của bạn:</span>
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
                    placeholder="Viết cảm nhận của bạn về khóa học..."
                    class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-primary"
                  ></textarea>

                  <p v-if="reviewError" class="text-red-500 text-sm mt-2">{{ reviewError }}</p>

                  <button 
                    @click="handleSubmitReview"
                    class="mt-4 px-6 py-2 bg-brand-primary text-white font-semibold rounded-lg hover:bg-brand-secondary transition"
                  >
                    Gửi đánh giá
                  </button>
                </div>

                <h3 class="text-xl font-bold mb-6">Các đánh giá khác</h3>
                <div v-if="course.reviews && course.reviews.length > 0" class="space-y-6">

                  <div v-for="review in course.reviews" :key="review.id" class="border-b border-gray-200 pb-6">
                    <div class="flex items-center justify-between mb-2">
                      <div class="flex items-center gap-3">
                        <div>
                          <h4 class="font-semibold text-gray-900">
                            {{ review.student?.first_name || 'Học viên' }} {{ review.student?.last_name }}
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
                <p v-else class="text-gray-500">Chưa có đánh giá nào cho khóa học này.</p>
              </div>
            <div v-if="activeTab === 'curriculum'">
              <h2 class="text-2xl font-bold mb-4">Chương trình học</h2>
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
                      <span class="text-sm text-gray-500">{{ lesson.duration || 'N/A' }} phút</span>
                    </li>
                  </ul>
                  <p v-else class="pl-4 text-gray-500">Chưa có bài học trong học phần này.</p>
                </div>
              </div>
              <p v-else>Chưa có học phần nào.</p>
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
                Mua khóa học
              </button>
            </div>
            
            <div class="bg-white rounded-lg shadow-lg p-6">
              <h4 class="text-xl font-bold mb-4">Khóa học này bao gồm</h4>
              <ul class="space-y-3 text-gray-700">
                <li class="flex items-center gap-2">
                  <span>{{ course.modules?.length || 'Nhiều' }} học phần</span>
                </li>
                <li class="flex items-center gap-2">
                  <span>Thời lượng {{ course.duration || 'N/A' }} giờ</span>
                </li>
                <li class="flex items-center gap-2">
                  <span>Truy cập trên mọi thiết bị</span>
                </li>
                <li class="flex items-center gap-2">
                  <span>Chứng chỉ Blockchain</span>
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
// import { useCourses } from '../../composables/useCourses  ' // Import composable
import { ref, onMounted, computed } from 'vue'

// Lấy composable
const { getCourseById, addReview } = useCourses()
const route = useRoute()


// State
const course = ref<Course | null>(null)
const loading = ref(true)
const error = ref<Error | null>(null)
const activeTab = ref('description') // Mặc định là 'description' để khớp với tabs
const newRating = ref(0)
const newComment = ref('')
const reviewError = ref('')

const tabs = [
  { id: 'description', name: 'Description' },
  { id: 'review', name: 'Review' },
  { id: 'curriculum', name: 'Curriculum' }
]

// Lấy ID từ URL
const courseId = route.params.id as string

// Tải dữ liệu khóa học
onMounted(async () => {
  if (!courseId) return

  loading.value = true
  error.value = null
  try {
    // Gọi hàm getCourseById (giờ đã bao gồm cả token)
    const result = await getCourseById(courseId)
    course.value = result
  } catch (err: any) {
    console.error('Lỗi khi tải chi tiết khóa học:', err)
    error.value = err
  } finally {
    loading.value = false
  }
})

// Hàm xử lý khi submit review
const handleSubmitReview = async () => {
  if (!course.value) {
    reviewError.value = 'Lỗi: Không tìm thấy khóa học. Hãy tải lại trang.'
    return
  }
  reviewError.value = ''
  if (newRating.value === 0) {
    reviewError.value = 'Vui lòng chọn số sao.'
    return
  }

  try {
    // 1. Gửi review và nhận response đầy đủ
    const response = await addReview(courseId, {
      rating: newRating.value,
      comment: newComment.value
    }) 
    
    // 2. Lấy review thật từ response
    const newReview = response.review as Review

    // 3. Đảm bảo course.reviews là một mảng
    if (!course.value.reviews) {
      course.value.reviews = []
    }

    // 4. Cập nhật danh sách review (Code này giờ đã đúng)
    const existingIndex = course.value.reviews.findIndex(
        r => r.student_id === newReview.student_id
    );

    if (existingIndex !== -1) {
        // Cập nhật review cũ
        course.value.reviews[existingIndex] = newReview;
    } else {
        // Thêm review mới vào đầu danh sách
        course.value.reviews.unshift(newReview);
    }

    // 5. Cập nhật stats (lấy từ 'response.course_stats')
    if (response.course_stats && course.value) { 
            course.value.average_rating = response.course_stats.average_rating
            course.value.review_count = response.course_stats.review_count
            // Dòng quan trọng để cập nhật biểu đồ:
            course.value.rating_counts = response.course_stats.rating_counts
        }

    // 6. Xóa form
    newRating.value = 0
    newComment.value = ''

  } catch (err: any) {
    reviewError.value = err.data?.message || 'Không thể gửi đánh giá.'
  }
}

const ratingPercentages = computed(() => {
  const percentages: { [key: number]: number } = { 1: 0, 2: 0, 3: 0, 4: 0, 5: 0 }
  
  // Dùng .value vì 'course' là một ref
  if (!course.value || !course.value.rating_counts || !course.value.review_count) {
    return percentages // Trả về 0% nếu không có dữ liệu
  }
  
  const totalReviews = course.value.review_count
  
  for (let i = 1; i <= 5; i++) {
    const count = course.value.rating_counts[i] || 0
    // Tránh chia cho 0 nếu totalReviews là 0
    percentages[i] = totalReviews > 0 ? (count / totalReviews) * 100 : 0
  }
  
  return percentages
})

// SEO
useHead({
  title: () => `${course.value?.title || 'Course Detail'} - CertChain`,
  meta: [
    { name: 'description', content: () => course.value?.description || 'Chi tiết khóa học' }
  ]
})
</script>