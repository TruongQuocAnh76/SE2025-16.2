<template>
  <div class="min-h-screen bg-background">
    <div class="max-w-7xl mx-auto px-4 py-12">
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-text-dark mb-2">Add Course</h1>
      </div>

      <form @submit.prevent="handleSubmit">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          
          <div class="lg:col-span-2 space-y-6">
            
            <div>
              <label for="title" class="block text-sm font-medium text-text-dark mb-2">
                Name of Course <span class="text-red-500">*</span>
              </label>
              <input
                id="title"
                v-model="form.title"
                type="text"
                required
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-primary focus:border-brand-primary"
                placeholder="Enter name course"
                :class="{ 'border-red-500': errors.title }"
              />
              <p v-if="errors.title" class="mt-1 text-sm text-red-600">{{ errors.title }}</p>
            </div>

            <div>
              <label for="description" class="block text-sm font-medium text-text-dark mb-2">
                Short Description <span class="text-red-500">*</span>
              </label>
              <input
                id="description"
                v-model="form.description"
                type="text"
                required
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-primary focus:border-brand-primary"
                placeholder="Enter the short description"
                :class="{ 'border-red-500': errors.description }"
              />
              <p v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description }}</p>
            </div>
            
            <div>
              <label for="long_description" class="block text-sm font-medium text-text-dark mb-2">
                Description
              </label>
              <textarea
                id="long_description"
                v-model="form.long_description"
                rows="4"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-primary focus:border-brand-primary"
                placeholder="Enter the description"
              ></textarea>
            </div>

            <div>
              <label for="curriculum" class="block text-sm font-medium text-text-dark mb-2">
                Curriculum
              </label>
              <textarea
                id="curriculum"
                v-model="form.curriculum"
                rows="12"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-primary focus:border-brand-primary"
                placeholder="Enter detailed curriculum, e.g. numbered sections, multi-line..."
              ></textarea>
            </div>

            <div>
              <label for="duration" class="block text-sm font-medium text-text-dark mb-2">
                Duration (In hours)
              </label>
              <input
                id="duration"
                v-model.number="form.duration"
                type="number"
                min="1"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-primary focus:border-brand-primary"
                placeholder="E.g. 8 = 8 hours"
              />
            </div>

            <div class="grid grid-cols-2 gap-6">
              <div>
                <label for="category" class="block text-sm font-medium text-text-dark mb-2">
                  Category
                </label>
                <select
                  id="category"
                  v-model="form.category"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-primary focus:border-brand-primary bg-white"
                >
                  <option value="">Select an Category</option>
                  </select>
              </div>
              <div>
                <label for="language" class="block text-sm font-medium text-text-dark mb-2">
                  Language
                </label>
                <select
                  id="language"
                  v-model="form.language"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-primary focus:border-brand-primary bg-white"
                >
                  <option value="">Select Language</option>
                  </select>
              </div>
            </div> <div>
              <label for="tags" class="block text-sm font-medium text-text-dark mb-2">
                Tags (Hold Ctrl/Cmd to select multiple)
              </label>
              <select
                id="tags"
                v-model="form.tags"
                multiple 
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-primary focus:border-brand-primary bg-white"
                size="5" 
              >
                <option v-for="tag in allTags" :key="tag.id" :value="tag.id">
                  {{ tag.name }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-text-dark mb-2">
                Level <span class="text-red-500">*</span>
              </label>
              <div class="flex space-x-3">
                <button
                  type="button"
                  v-for="level in LEVELS" 
                  :key="level"
                  @click="form.level = level"
                  :class="[
                    'px-6 py-2 rounded-lg font-medium transition-colors',
                    form.level === level
                      ? 'bg-teal-500 text-white shadow-lg'
                      : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                  ]"
                >
                  {{ level.charAt(0) + level.slice(1).toLowerCase() }}
                </button>
              </div>
              <p v-if="errors.level" class="mt-1 text-sm text-red-600">{{ errors.level }}</p>
            </div>

            <div class="grid grid-cols-2 gap-6">
              <div>
                <label for="price" class="block text-sm font-medium text-text-dark mb-2">
                  Price
                </label>
                <input
                  id="price"
                  v-model.number="form.price"
                  type="number"
                  min="0"
                  step="0.01"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-primary focus:border-brand-primary"
                  placeholder="Enter the Price"
                />
              </div>
              <div>
                <label for="discount" class="block text-sm font-medium text-text-dark mb-2">
                  Discount
                </label>
                <input
                  id="discount"
                  v-model.number="form.discount"
                  type="number"
                  min="0"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-primary focus:border-brand-primary"
                  placeholder="Enter the Discount"
                />
              </div>
            </div>
            
          </div> <div class="lg:col-span-1">
            <label class="block text-sm font-medium text-text-dark mb-2">
              Upload Course Image
            </label>
            
            <div 
              @dragover.prevent="onDragOver"
              @dragleave.prevent="onDragLeave"
              @drop.prevent="onDrop"
              :class="['relative w-full h-80 border-2 border-dashed rounded-lg flex flex-col justify-center items-center text-center p-6 transition-colors',
                isDragging ? 'border-brand-primary bg-blue-50' : 'border-gray-300'
              ]"
            >
              <img v-if="thumbnailPreview" :src="thumbnailPreview" @load="onThumbnailPreviewLoad" @error="onThumbnailPreviewError" class="absolute inset-0 w-full h-full object-cover rounded-lg" />
              
              <div :class="['relative z-10', { 'bg-white/70 p-4 rounded-lg': thumbnailPreview }]">
                <div class="flex items-center justify-center space-x-3 mb-3">
                  <label class="inline-flex items-center">
                    <input type="radio" class="form-radio" value="upload" v-model="thumbnailType" />
                    <span class="ml-2">Upload</span>
                  </label>
                  <label class="inline-flex items-center">
                    <input type="radio" class="form-radio" value="url" v-model="thumbnailType" />
                    <span class="ml-2">External URL</span>
                  </label>
                </div>

                <div v-if="thumbnailType === 'url'" class="mb-3 w-full">
                  <input
                    v-model="thumbnailUrl"
                    type="url"
                    placeholder="https://example.com/image.jpg"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                    @input="onThumbnailUrlInput"
                  />
                  <p v-if="thumbnailUrl && !isValidUrl(thumbnailUrl)" class="text-sm text-red-600 mt-1">Please enter a valid image URL</p>
                </div>

                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                  <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <p class="mt-2 text-sm text-gray-600">
                  Browse and chose the image you want to upload from your computer
                </p>
                
                <button
                  type="button"
                  @click="triggerFileInput"
                  class="mt-4 px-4 py-2 bg-green-500 text-white rounded-lg font-semibold text-lg"
                >
                  +
                </button>
                
                <input
                  ref="thumbnailFileInput"
                  type="file"
                  accept="image/*"
                  @change="handleThumbnailFileSelect"
                  class="hidden"
                />
              </div>
            </div>
            
            <p v-if="selectedThumbnailFile" class="mt-2 text-sm text-brand-primary font-medium">
              Selected: {{ selectedThumbnailFile.name }}
            </p>
            <p v-if="thumbnailType === 'url' && thumbnailUrl" class="mt-2 text-sm text-brand-primary font-medium">
              External thumbnail URL set
            </p>
            <p v-if="thumbnailLoading" class="mt-2 text-sm text-gray-600">Loading thumbnail preview...</p>
            <p v-if="thumbnailLoadError" class="mt-2 text-sm text-red-600">{{ thumbnailLoadError }}</p>
          </div> </div> <div class="mt-12">
          <button
            type="submit"
            :disabled="isSubmitting"
            class="w-full py-4 bg-teal-500 text-white rounded-lg font-semibold text-lg hover:bg-teal-600 transition-colors disabled:opacity-50"
          >
            <span v-if="isSubmitting">Creating Course...</span>
            <span v-else>Add Course</span>
          </button>
        </div>
        
        <div v-if="errors.general" class="mt-4 text-center text-red-600">
          {{ errors.general }}
        </div>
        <div v-if="successMessage" class="mt-4 text-center text-green-600">
          {{ successMessage }}
        </div>

      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import type { CreateCourseData, Tag } from '../../types/course'
const config = useRuntimeConfig()

const LEVELS = ['BEGINNER' , 'INTERMEDIATE' , 'ADVANCED' , 'EXPERT'] as const

const { createCourse, uploadCourseThumbnail, updateCourseThumbnail, getTags } = useCourses()
const router = useRouter()

// State cho Dropzone
const isDragging = ref(false)
const thumbnailPreview = ref<string | null>(null)

// Thumbnail handling
const thumbnailType = ref<'url' | 'upload'>('upload')
const selectedThumbnailFile = ref<File | null>(null)
const thumbnailFileInput = ref<HTMLInputElement | null>(null)
const thumbnailUrl = ref<string>('')
const thumbnailLoading = ref<boolean>(false)
const thumbnailLoadError = ref<string | null>(null)

// Form data
const form = ref<CreateCourseData>({
  title: '',
  description: '',
  long_description: '', 
  curriculum: '',
  category: '',
  language: '',
  discount: undefined,
  level: '' as any,
  price: undefined,
  duration: undefined,
  passing_score: 70,
  tags: [] 
})

const allTags = ref<Tag[]>([])

// Fetch all tags on mount
onMounted(async () => {
  allTags.value = await getTags()
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
    setFile(file)
  }
}

const triggerFileInput = () => {
  thumbnailType.value = 'upload'
  thumbnailFileInput.value?.click()
}

const onDragOver = (event: DragEvent) => { isDragging.value = true }
const onDragLeave = (event: DragEvent) => { isDragging.value = false }
const onDrop = (event: DragEvent) => {
  isDragging.value = false
  const file = event.dataTransfer?.files[0]
  if (file && file.type.startsWith('image/')) {
    setFile(file)
  }
}

const setFile = (file: File) => {
  thumbnailType.value = 'upload'
  thumbnailLoadError.value = null
  thumbnailLoading.value = false
  selectedThumbnailFile.value = file
  const reader = new FileReader()
  reader.onload = (e) => {
    thumbnailPreview.value = e.target?.result as string
  }
  reader.readAsDataURL(file)
}

const isValidUrl = (val: string) => {
  try {
    const u = new URL(val)
    return ['http:', 'https:'].includes(u.protocol)
  } catch (e) {
    return false
  }
}

const tryNormalizeUrl = (val: string) => {
  if (!val) return ''
  // If user provided a URL without protocol, try https
  try {
    new URL(val)
    return val
  } catch (e) {
    // If it looks like a domain or path, try adding https://
    if (/^[^\s]+\.[^\s]+/.test(val)) {
      return 'https://' + val
    }
    return val
  }
}

const onThumbnailPreviewLoad = () => {
  thumbnailLoading.value = false
  thumbnailLoadError.value = null
}

const onThumbnailPreviewError = () => {
  thumbnailLoading.value = false
  thumbnailLoadError.value = 'Failed to load image. Please check the URL or try another image.'
  thumbnailPreview.value = null
}

const onThumbnailUrlInput = () => {
  thumbnailLoadError.value = null
  if (!thumbnailUrl.value) {
    thumbnailPreview.value = null
    return
  }

  const normalized = tryNormalizeUrl(thumbnailUrl.value.trim())
  if (normalized !== thumbnailUrl.value) {
    thumbnailUrl.value = normalized
  }

  if (isValidUrl(thumbnailUrl.value)) {
    // set preview and mark as loading until image fires load event
    thumbnailLoading.value = true
    selectedThumbnailFile.value = null
    thumbnailPreview.value = thumbnailUrl.value
  } else {
    thumbnailPreview.value = null
  }
}

// Form validation

  // sample curriculum helpers removed — curriculum input restored to single-line
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
  if (form.value.passing_score < 0 || form.value.passing_score > 100) {
    errors.value.passing_score = 'Passing score must be between 0 and 100'
  }
  // Validate thumbnail URL if selected
  if (thumbnailType.value === 'url' && thumbnailUrl.value) {
    if (!isValidUrl(thumbnailUrl.value) || thumbnailLoadError.value) {
      errors.value.thumbnail = 'Please provide a valid thumbnail URL or switch to upload.'
    }
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
    // If user provided an external thumbnail URL, include it in course creation
    if (thumbnailType.value === 'url' && thumbnailUrl.value && isValidUrl(thumbnailUrl.value)) {
      // backend accepts `thumbnail` as a URL in create API
      (courseData as any).thumbnail = thumbnailUrl.value
    }
    if (courseData.price === undefined) delete courseData.price
    if (courseData.duration === undefined) delete courseData.duration

    const result = await createCourse(courseData)

    if (result) {
      if (selectedThumbnailFile.value && result.thumbnailUploadUrl) {
        const uploadSuccess = await uploadCourseThumbnail(result.thumbnailUploadUrl, selectedThumbnailFile.value)
        // if (uploadSuccess) {
        //   const thumbnailPath = `courses/thumbnails/${result.course.id}.jpg`
        //   const finalThumbnailUrl = `${config.public.awsEndpoint}/${config.public.awsBucket}/${thumbnailPath}`
        // await updateCourseThumbnail(result.course.id, finalThumbnailUrl)
        // } else {
        //   console.warn('Thumbnail upload failed, but course was created successfully')
        // }
      }

      successMessage.value = 'Course created successfully! Redirecting...'
      
      // Reset form
      form.value = {
        title: '',
        description: '',
        long_description: '', 
        curriculum: '',
        category: '',
        language: '',
        discount: undefined,
        level: '' as any,
        price: undefined,
        duration: undefined,
        passing_score: 70,
        tags: []
      }
      thumbnailType.value = 'upload'
      selectedThumbnailFile.value = null
      thumbnailPreview.value = null 
      if (thumbnailFileInput.value) {
        thumbnailFileInput.value.value = ''
      }

      // Redirect to courses list after a short delay
      setTimeout(() => {
        router.push('/courses')
      }, 2000)
    }
  } catch (error: any) {
    console.error('Failed to create course:', error)
    if (error.response?.data?.errors) {
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