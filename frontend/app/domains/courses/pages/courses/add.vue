<template>
  <div class="min-h-screen bg-background">
    <div class="max-w-4xl mx-auto px-4 py-8">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-h1 font-bold text-text-dark mb-2">Create New Course</h1>
        <p class="text-body text-text-muted">Fill in the details below to create your course</p>
      </div>

      <!-- Form -->
      <form @submit.prevent="handleSubmit" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="space-y-6">
          <!-- Title -->
          <div>
            <label for="title" class="block text-body font-medium text-text-dark mb-2">
              Course Title <span class="text-red-500">*</span>
            </label>
            <input
              id="title"
              v-model="form.title"
              type="text"
              required
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-primary focus:border-brand-primary transition-colors"
              placeholder="Enter course title"
              :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': errors.title }"
            />
            <p v-if="errors.title" class="mt-1 text-sm text-red-600">{{ errors.title }}</p>
          </div>

          <!-- Description -->
          <div>
            <label for="description" class="block text-body font-medium text-text-dark mb-2">
              Description <span class="text-red-500">*</span>
            </label>
            <textarea
              id="description"
              v-model="form.description"
              required
              rows="4"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-primary focus:border-brand-primary transition-colors resize-vertical"
              placeholder="Describe what students will learn in this course"
              :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': errors.description }"
            ></textarea>
            <p v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description }}</p>
          </div>

          <!-- Thumbnail -->
          <div>
            <label class="block text-body font-medium text-text-dark mb-2">
              Course Thumbnail
            </label>
            
            <!-- Thumbnail Type Selection -->
            <!-- <div class="mb-3">
              <div class="flex space-x-4">
                <label class="flex items-center">
                  <input
                    v-model="thumbnailType"
                    type="radio"
                    value="url"
                    class="text-brand-primary focus:ring-brand-primary"
                  />
                  <span class="ml-2 text-sm text-text-dark">Use URL</span>
                </label>
                <label class="flex items-center">
                  <input
                    v-model="thumbnailType"
                    type="radio"
                    value="upload"
                    class="text-brand-primary focus:ring-brand-primary"
                  />
                  <span class="ml-2 text-sm text-text-dark">Upload Image</span>
                </label>
              </div>
            </div> -->

            <!-- URL Input -->
            <!-- <div v-if="thumbnailType === 'url'">
              <input
                id="thumbnail"
                v-model="form.thumbnail"
                type="url"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-primary focus:border-brand-primary transition-colors"
                placeholder="https://example.com/thumbnail.jpg"
              />
              <p class="mt-1 text-sm text-text-muted">Provide a URL for the course thumbnail image</p>
            </div> -->

            <!-- File Upload -->
            <div>
              <input
                ref="thumbnailFileInput"
                type="file"
                accept="image/*"
                @change="handleThumbnailFileSelect"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-primary focus:border-brand-primary transition-colors file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-gray-50 file:text-gray-700 hover:file:bg-gray-100"
              />
              <p class="mt-1 text-sm text-text-muted">
                Upload a thumbnail image (JPG, PNG, etc.)
                <span v-if="selectedThumbnailFile" class="text-brand-primary font-medium">
                  Selected: {{ selectedThumbnailFile.name }}
                </span>
              </p>
            </div>
          </div>

          <!-- Level and Price Row -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Level -->
            <div>
              <label for="level" class="block text-body font-medium text-text-dark mb-2">
                Difficulty Level <span class="text-red-500">*</span>
              </label>
              <select
                id="level"
                v-model="form.level"
                required
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-primary focus:border-brand-primary transition-colors bg-white"
                :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': errors.level }"
              >
                <option value="" disabled>Select level</option>
                <option value="BEGINNER">Beginner</option>
                <option value="INTERMEDIATE">Intermediate</option>
                <option value="ADVANCED">Advanced</option>
                <option value="EXPERT">Expert</option>
              </select>
              <p v-if="errors.level" class="mt-1 text-sm text-red-600">{{ errors.level }}</p>
            </div>

            <!-- Price -->
            <div>
              <label for="price" class="block text-body font-medium text-text-dark mb-2">
                Price (USD)
              </label>
              <input
                id="price"
                v-model.number="form.price"
                type="number"
                min="0"
                step="0.01"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-primary focus:border-brand-primary transition-colors"
                placeholder="0.00"
              />
              <p class="mt-1 text-sm text-text-muted">Leave empty for free course</p>
            </div>
          </div>

          <!-- Duration and Passing Score Row -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Duration -->
            <div>
              <label for="duration" class="block text-body font-medium text-text-dark mb-2">
                Duration (hours)
              </label>
              <input
                id="duration"
                v-model.number="form.duration"
                type="number"
                min="1"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-primary focus:border-brand-primary transition-colors"
                placeholder="10"
              />
              <p class="mt-1 text-sm text-text-muted">Estimated course duration in hours</p>
            </div>

            <!-- Passing Score -->
            <div>
              <label for="passing_score" class="block text-body font-medium text-text-dark mb-2">
                Passing Score (%) <span class="text-red-500">*</span>
              </label>
              <input
                id="passing_score"
                v-model.number="form.passing_score"
                type="number"
                min="0"
                max="100"
                required
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-primary focus:border-brand-primary transition-colors"
                placeholder="70"
                :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': errors.passing_score }"
              />
              <p v-if="errors.passing_score" class="mt-1 text-sm text-red-600">{{ errors.passing_score }}</p>
              <p class="mt-1 text-sm text-text-muted">Minimum score required to pass the course</p>
            </div>
          </div>
        </div>

        <!-- Submit Button -->
        <div class="mt-8 flex justify-end space-x-4">
          <NuxtLink
            to="/courses"
            class="px-6 py-3 border border-gray-300 text-text-dark rounded-lg hover:bg-gray-50 transition-colors"
          >
            Cancel
          </NuxtLink>
          <button
            type="submit"
            :disabled="isSubmitting"
            class="px-6 py-3 bg-brand-primary text-white rounded-lg hover:bg-brand-secondary transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="isSubmitting">Creating Course...</span>
            <span v-else>Create Course</span>
          </button>
        </div>
      </form>

      <!-- Success Message -->
      <div v-if="successMessage" class="mt-6 p-4 bg-green-50 border border-green-200 rounded-lg">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="ml-3">
            <p class="text-sm font-medium text-green-800">{{ successMessage }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import type { CreateCourseData } from '../../types/course'

const { createCourse, uploadCourseThumbnail, updateCourseThumbnail } = useCourses()

// Thumbnail handling
// const thumbnailType = ref<'url' | 'upload'>('url')
const thumbnailType = ref<'url' | 'upload'>('upload')
const selectedThumbnailFile = ref<File | null>(null)
const thumbnailFileInput = ref<HTMLInputElement | null>(null)

// Form data
const form = ref<CreateCourseData>({
  title: '',
  description: '',
  // thumbnail: '',
  level: '' as any,
  price: undefined,
  duration: undefined,
  passing_score: 70
})

// Form state
const errors = ref<Record<string, string>>({})
const isSubmitting = ref(false)
const successMessage = ref('')

// Thumbnail file selection handler
const handleThumbnailFileSelect = (event: Event) => {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  if (file) {
    selectedThumbnailFile.value = file
  }
}

// Form validation
const validateForm = (): boolean => {
  errors.value = {}

  if (!form.value.title.trim()) {
    errors.value.title = 'Course title is required'
  }

  if (!form.value.description.trim()) {
    errors.value.description = 'Course description is required'
  }

  if (!form.value.level) {
    errors.value.level = 'Please select a difficulty level'
  }

  // Validate thumbnail
  // if (thumbnailType.value === 'url' && form.value.thumbnail && !form.value.thumbnail.match(/^https?:\/\/.+/)) {
  //   errors.value.thumbnail = 'Please provide a valid URL starting with http:// or https://'
  // } else if (thumbnailType.value === 'upload' && !selectedThumbnailFile.value) {
  //   errors.value.thumbnail = 'Please select an image file to upload'
  // }
  // if (!selectedThumbnailFile.value) {
  //   errors.value.thumbnail = 'Please select an image file to upload'
  // }

  if (form.value.passing_score < 0 || form.value.passing_score > 100) {
    errors.value.passing_score = 'Passing score must be between 0 and 100'
  }

  return Object.keys(errors.value).length === 0
}

// Submit handler
const handleSubmit = async () => {
  if (!validateForm()) {
    return
  }

  isSubmitting.value = true
  errors.value = {}
  successMessage.value = ''

  try {
    const courseData = { ...form.value }
    // Remove empty strings for optional fields
    if (!courseData.thumbnail) delete courseData.thumbnail
    if (courseData.price === undefined || courseData.price === null) delete courseData.price
    if (courseData.duration === undefined || courseData.duration === null) delete courseData.duration

    const result = await createCourse(courseData)

    if (result) {
      // Handle thumbnail upload if a file was selected
      // if (thumbnailType.value === 'upload' && selectedThumbnailFile.value && result.thumbnailUploadUrl) {
      if (selectedThumbnailFile.value && result.thumbnailUploadUrl) {
        const uploadSuccess = await uploadCourseThumbnail(result.thumbnailUploadUrl, selectedThumbnailFile.value)
        if (uploadSuccess) {
          // Construct the final thumbnail URL for storage
          // The URL should be the public S3 URL, not the pre-signed upload URL
          const thumbnailPath = `courses/thumbnails/${result.course.id}.jpg`
          const finalThumbnailUrl = `https://certchain-dev.s3.amazonaws.com/${thumbnailPath}`
          
          // Update the course with the final thumbnail URL
          await updateCourseThumbnail(result.course.id, finalThumbnailUrl)
        } else {
          console.warn('Thumbnail upload failed, but course was created successfully')
        }
      }

      successMessage.value = 'Course created successfully! Redirecting...'
      
      // Reset form
      form.value = {
        title: '',
        description: '',
        // thumbnail: '',
        level: '' as any,
        price: undefined,
        duration: undefined,
        passing_score: 70
      }
      // thumbnailType.value = 'url'
      thumbnailType.value = 'upload'
      selectedThumbnailFile.value = null
      if (thumbnailFileInput.value) {
        thumbnailFileInput.value.value = ''
      }

      // Redirect to courses list after a short delay
      setTimeout(() => {
        navigateTo('/courses')
      }, 2000)
    }
  } catch (error: any) {
    console.error('Failed to create course:', error)
    if (error.response?.data?.errors) {
      // Handle validation errors from backend
      errors.value = error.response.data.errors
    } else {
      errors.value.general = error.message || 'Failed to create course. Please try again.'
    }
  } finally {
    isSubmitting.value = false
  }
}

// Page meta
definePageMeta({
  layout: 'default'
})

// SEO
useHead({
  title: 'Create Course - CertChain',
  meta: [
    { name: 'description', content: 'Create a new course on CertChain platform' }
  ]
})
</script>