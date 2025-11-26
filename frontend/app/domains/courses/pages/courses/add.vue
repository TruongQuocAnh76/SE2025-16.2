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
              <input
                id="curriculum"
                v-model="form.curriculum"
                type="text"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-primary focus:border-brand-primary"
                placeholder="E.g. B = 8 hours"
              />
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
            </div>

            <div>
              <label for="tags" class="block text-sm font-medium text-text-dark mb-2">
                Tags (search and select multiple)
              </label>

              <!-- Selected tags as chips -->
              <div v-if="selectedTags.length > 0" class="flex flex-wrap gap-2 mb-2">
                <span v-for="t in selectedTags" :key="t.id" class="inline-flex items-center px-3 py-1 bg-teal-100 text-teal-800 rounded-full text-sm">
                  {{ t.name }}
                  <button type="button" @click="removeTag(t.id)" class="ml-2 text-teal-600 hover:text-red-500 font-bold">&times;</button>
                </span>
              </div>

              <input
                type="text"
                v-model="tagSearch"
                placeholder="Search tags..."
                class="w-full mb-2 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-primary focus:border-brand-primary"
              />

              <select
                id="tags"
                v-model="form.tags"
                multiple
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-primary focus:border-brand-primary bg-white"
                size="10"
              >
                <option v-for="tag in filteredTags" :key="tag.id" :value="tag.id">
                  {{ tag.name }}
                </option>
              </select>
              
              <div class="mt-2 flex items-center gap-2">
                <button type="button" @click="showCreateTagModal = true" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                  + Create New Tag
                </button>
                <p class="text-sm text-gray-500">Can't find a tag? Create a new one!</p>
              </div>
              
              <p class="mt-1 text-sm text-gray-500">Tip: hold Ctrl/Cmd to multi-select, or use the search box above.</p>
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
            
          </div>

          <div class="lg:col-span-1">
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
              <img v-if="thumbnailPreview" :src="thumbnailPreview" class="absolute inset-0 w-full h-full object-cover rounded-lg" />
              
              <div :class="['relative z-10', { 'bg-white/70 p-4 rounded-lg': thumbnailPreview }]">
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
          </div>
        </div>

        <div class="mt-12">
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

      <!-- Create Tag Modal -->
      <div v-if="showCreateTagModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click.self="showCreateTagModal = false">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
          <h3 class="text-xl font-bold text-gray-900 mb-4">Create New Tag</h3>
          
          <div class="mb-4">
            <label for="newTagName" class="block text-sm font-medium text-gray-700 mb-2">Tag Name</label>
            <input
              id="newTagName"
              v-model="newTagName"
              type="text"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-primary focus:border-brand-primary"
              placeholder="Enter tag name (e.g., React, Node.js)"
              @keyup.enter="handleCreateTag"
            />
          </div>
          
          <div v-if="createTagError" class="mb-4 text-sm text-red-600">
            {{ createTagError }}
          </div>
          
          <div class="flex gap-3">
            <button
              type="button"
              @click="handleCreateTag"
              :disabled="!newTagName.trim() || isCreatingTag"
              class="flex-1 px-4 py-2 bg-teal-500 text-white rounded-lg hover:bg-teal-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="isCreatingTag">Creating...</span>
              <span v-else>Create Tag</span>
            </button>
            <button
              type="button"
              @click="showCreateTagModal = false; newTagName = ''; createTagError = ''"
              class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors"
            >
              Cancel
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import type { CreateCourseData, Tag } from '../../types/course'
const config = useRuntimeConfig()

const LEVELS = ['BEGINNER' , 'INTERMEDIATE' , 'ADVANCED' , 'EXPERT'] as const

const { createCourse, uploadCourseThumbnail, updateCourseThumbnail, getTags, createTag } = useCourses()
const router = useRouter()

// State cho Dropzone
const isDragging = ref(false)
const thumbnailPreview = ref<string | null>(null)

// Thumbnail handling
const thumbnailType = ref<'url' | 'upload'>('upload')
const selectedThumbnailFile = ref<File | null>(null)
const thumbnailFileInput = ref<HTMLInputElement | null>(null)

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
const tagSearch = ref('')
const showCreateTagModal = ref(false)
const newTagName = ref('')
const isCreatingTag = ref(false)
const createTagError = ref('')

const filteredTags = computed(() => {
  if (!tagSearch.value.trim()) return allTags.value
  const search = tagSearch.value.toLowerCase()
  return allTags.value.filter(tag => tag.name.toLowerCase().includes(search))
})
const selectedTags = computed(() => {
  const tags = form.value.tags || []
  return allTags.value.filter(tag => tags.includes(tag.id))
})

const removeTag = (tagId: string) => {
  if (!form.value.tags) return
  form.value.tags = form.value.tags.filter(id => id !== tagId)
}

const handleCreateTag = async () => {
  if (!newTagName.value.trim()) return
  
  isCreatingTag.value = true
  createTagError.value = ''
  
  try {
    const newTag = await createTag(newTagName.value.trim())
    if (newTag) {
      // Add new tag to the list
      allTags.value.push(newTag)
      // Auto-select the new tag
      if (!form.value.tags) form.value.tags = []
      form.value.tags.push(newTag.id)
      // Close modal and reset
      showCreateTagModal.value = false
      newTagName.value = ''
    }
  } catch (error: any) {
    console.error('Failed to create tag:', error)
    if (error.data?.errors?.name) {
      createTagError.value = error.data.errors.name[0]
    } else if (error.data?.message) {
      createTagError.value = error.data.message
    } else {
      createTagError.value = 'Failed to create tag. Please try again.'
    }
  } finally {
    isCreatingTag.value = false
  }
}

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
  selectedThumbnailFile.value = file
  const reader = new FileReader()
  reader.onload = (e) => {
    thumbnailPreview.value = e.target?.result as string
  }
  reader.readAsDataURL(file)
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