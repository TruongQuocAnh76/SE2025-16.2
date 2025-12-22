<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
      <!-- Header -->
      <div class="bg-teal-500 text-white px-6 py-4 rounded-t-lg flex justify-between items-center">
        <h2 class="text-xl font-bold">{{ isEditing ? 'Edit Lesson' : 'Add New Lesson' }}</h2>
        <button @click="$emit('close')" class="text-white hover:text-gray-200">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <!-- Form -->
      <form @submit.prevent="handleSubmit" class="p-6 space-y-6">
        <!-- Title -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Lesson Title *</label>
          <input
            v-model="form.title"
            type="text"
            placeholder="Enter lesson title"
            class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
            required
          />
        </div>

        <!-- Description -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
          <textarea
            v-model="form.description"
            placeholder="Describe what students will learn"
            rows="3"
            class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
          ></textarea>
        </div>

        <!-- Content / Text -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Lesson Content</label>
          <textarea
            v-model="form.content"
            placeholder="Write your lesson content here..."
            rows="6"
            class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
          ></textarea>
        </div>

        <!-- Video Upload -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Video</label>
          
          <!-- Current Video -->
          <div v-if="form.video_url && !newVideo" class="mb-4 bg-gray-100 rounded-lg p-4">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <svg class="w-8 h-8 text-teal-500" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zm12.553 1.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"/>
                </svg>
                <span class="text-sm text-gray-600">Current video attached</span>
              </div>
              <button 
                type="button"
                @click="removeVideo"
                class="text-red-500 hover:text-red-700 text-sm"
              >
                Remove
              </button>
            </div>
          </div>

          <!-- Upload Zone -->
          <div 
            @click="videoInput?.click()"
            @dragover.prevent="dragOver = true"
            @dragleave="dragOver = false"
            @drop.prevent="handleDrop"
            :class="[
              'border-2 border-dashed rounded-lg p-8 text-center cursor-pointer transition',
              dragOver ? 'border-teal-500 bg-teal-50' : 'border-gray-300 hover:border-teal-400'
            ]"
          >
            <input
              ref="videoInput"
              type="file"
              accept="video/*"
              @change="handleFileSelect"
              class="hidden"
            />
            
            <div v-if="newVideo">
              <svg class="w-12 h-12 mx-auto text-teal-500 mb-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
              </svg>
              <p class="text-sm text-gray-600">{{ newVideo.name }}</p>
              <p class="text-xs text-gray-400">{{ formatFileSize(newVideo.size) }}</p>
            </div>
            <div v-else>
              <svg class="w-12 h-12 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
              </svg>
              <p class="text-sm text-gray-600">Drag and drop a video file, or click to browse</p>
              <p class="text-xs text-gray-400 mt-1">MP4, WebM, or MOV up to 500MB</p>
            </div>
          </div>
        </div>

        <!-- Module Selection -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Module (Optional)</label>
          <select
            v-model="form.module_id"
            class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
          >
            <option :value="null">No module</option>
            <option v-for="module in modules" :key="module.id" :value="module.id">
              {{ module.title }}
            </option>
          </select>
        </div>

        <!-- Order -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Display Order</label>
          <input
            v-model.number="form.order"
            type="number"
            min="1"
            class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
          />
        </div>

        <!-- Duration -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Duration (minutes)</label>
          <input
            v-model.number="form.duration"
            type="number"
            min="1"
            placeholder="Estimated duration"
            class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
          />
        </div>

        <!-- Buttons -->
        <div class="flex justify-end gap-3 pt-4 border-t">
          <button
            type="button"
            @click="$emit('close')"
            class="px-6 py-2 border rounded-lg hover:bg-gray-50"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="submitting"
            class="px-6 py-2 bg-teal-500 text-white rounded-lg hover:bg-teal-600 disabled:opacity-50"
          >
            {{ submitting ? 'Saving...' : (isEditing ? 'Update Lesson' : 'Create Lesson') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed } from 'vue'

const props = defineProps<{
  courseId: number | string
  lesson?: any
  modules?: any[]
}>()

const emit = defineEmits(['close', 'saved'])

const config = useRuntimeConfig()
const token = useCookie('auth_token')

const isEditing = computed(() => !!props.lesson?.id)
const submitting = ref(false)
const dragOver = ref(false)
const newVideo = ref<File | null>(null)
const videoInput = ref<HTMLInputElement | null>(null)
const modules = ref(props.modules || [])

const form = reactive({
  title: props.lesson?.title || '',
  description: props.lesson?.description || '',
  content: props.lesson?.content || '',
  video_url: props.lesson?.video_url || '',
  module_id: props.lesson?.module_id || null,
  order: props.lesson?.order || 1,
  duration: props.lesson?.duration || null
})

const handleFileSelect = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (target.files?.[0]) {
    newVideo.value = target.files[0]
  }
}

const handleDrop = (event: DragEvent) => {
  dragOver.value = false
  if (event.dataTransfer?.files?.[0]) {
    newVideo.value = event.dataTransfer.files[0]
  }
}

const removeVideo = () => {
  form.video_url = ''
  newVideo.value = null
}

const formatFileSize = (bytes: number) => {
  if (bytes < 1024) return bytes + ' B'
  if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB'
  return (bytes / (1024 * 1024)).toFixed(1) + ' MB'
}

const handleSubmit = async () => {
  if (!form.title.trim()) {
    alert('Please enter a lesson title')
    return
  }

  submitting.value = true

  try {
    const formData = new FormData()
    formData.append('title', form.title)
    formData.append('description', form.description || '')
    formData.append('content', form.content || '')
    formData.append('order', String(form.order))
    
    if (form.duration) {
      formData.append('duration', String(form.duration))
    }
    if (form.module_id) {
      formData.append('module_id', String(form.module_id))
    }
    if (newVideo.value) {
      formData.append('video', newVideo.value)
    }

    const url = isEditing.value 
      ? `/api/courses/${props.courseId}/lessons/${props.lesson.id}`
      : `/api/courses/${props.courseId}/lessons`
    
    const method = isEditing.value ? 'PUT' : 'POST'

    // For PUT with FormData, use POST with _method
    if (isEditing.value) {
      formData.append('_method', 'PUT')
    }

    const response = await $fetch(url, {
      baseURL: config.public.backendUrl as string,
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token.value}`,
        'Accept': 'application/json'
      },
      body: formData
    })

    emit('saved', response)
    emit('close')
  } catch (err: any) {
    console.error('Failed to save lesson:', err)
    alert(err?.data?.message || 'Failed to save lesson')
  } finally {
    submitting.value = false
  }
}
</script>
