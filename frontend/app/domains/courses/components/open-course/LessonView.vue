<template>
  <div class="bg-white rounded-lg shadow">
    <!-- Lesson Header -->
    <div class="bg-teal-500 text-white p-6 rounded-t-lg">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-xl font-bold">{{ lesson.title }}</h2>
          <p class="text-teal-100 text-sm mt-1">{{ lesson.description || 'No description' }}</p>
        </div>
        <div v-if="isTeacher" class="flex gap-2">
          <button @click="$emit('edit', lesson)" class="bg-white text-teal-600 px-3 py-1 rounded text-sm hover:bg-teal-50">
            Edit
          </button>
          <button @click="$emit('delete', lesson)" class="bg-red-500 text-white px-3 py-1 rounded text-sm hover:bg-red-600">
            Delete
          </button>
        </div>
      </div>
    </div>

    <!-- Video Player -->
    <div v-if="lesson.content_type === 'VIDEO'" class="aspect-video bg-black relative">
      <video
        v-if="lesson.content_url"
        ref="videoPlayer"
        controls
        class="w-full h-full"
        :src="lesson.content_url"
        @ended="onVideoEnded"
      >
        Your browser does not support the video tag.
      </video>
      <div v-else class="absolute inset-0 flex items-center justify-center text-gray-400">
        <div class="text-center">
          <svg class="w-16 h-16 mx-auto mb-4" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/>
          </svg>
          <p>Video not available</p>
        </div>
      </div>
    </div>

    <!-- Text Content -->
    <div v-else-if="lesson.content_type === 'TEXT'" class="p-6">
      <div v-if="lesson.text_content" v-html="lesson.text_content" class="prose max-w-none"></div>
      <p v-else class="text-gray-400">No content available</p>
    </div>

    <!-- Lesson Info -->
    <div class="p-6 border-t">
      <div class="flex items-center mb-4">
        <img 
          :src="course?.teacher?.avatar || '/default-profile.png'" 
          :alt="teacherName"
          class="w-10 h-10 rounded-full mr-3"
        />
        <div>
          <h3 class="font-semibold">Lesson {{ lesson.order_index }}: {{ lesson.title }}</h3>
          <p class="text-sm text-gray-500">{{ teacherName }}</p>
        </div>
      </div>

      <div class="bg-gray-50 rounded-lg p-4 mb-6">
        <h4 class="font-semibold mb-2">Short Description</h4>
        <p class="text-gray-600 text-sm">{{ lesson.description || lesson.text_content || 'No description provided' }}</p>
      </div>

      <!-- Mark Complete Button -->
      <div v-if="!isTeacher" class="mb-6">
        <button 
          @click="markAsComplete"
          :disabled="isCompleted || markingComplete"
          :class="[
            'w-full py-3 rounded-lg font-semibold transition',
            isCompleted 
              ? 'bg-green-100 text-green-700 cursor-default' 
              : 'bg-teal-500 text-white hover:bg-teal-600'
          ]"
        >
          <span v-if="markingComplete">Marking...</span>
          <span v-else-if="isCompleted" class="flex items-center justify-center gap-2">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            Completed
          </span>
          <span v-else>Mark as Complete</span>
        </button>
      </div>

      <!-- Comments Section -->
      <div class="mt-6">
        <h4 class="font-semibold mb-4">Comments</h4>
        
        <!-- Add Comment -->
        <div class="flex gap-3 mb-6">
          <img src="/default-profile.png" class="w-8 h-8 rounded-full" />
          <div class="flex-1">
            <textarea 
              v-model="newComment"
              placeholder="Write a comment..."
              class="w-full border rounded-lg p-3 text-sm resize-none"
              rows="2"
            ></textarea>
            <button 
              @click="submitComment"
              :disabled="!newComment.trim()"
              class="mt-2 bg-teal-500 text-white px-4 py-2 rounded text-sm hover:bg-teal-600 disabled:opacity-50"
            >
              Post Comment
            </button>
          </div>
        </div>

        <!-- Comments List -->
        <div class="space-y-4">
          <div v-for="comment in comments" :key="comment.id" class="flex gap-3">
            <img :src="comment.user?.avatar || '/default-profile.png'" class="w-8 h-8 rounded-full" />
            <div class="flex-1 bg-gray-50 rounded-lg p-3">
              <div class="flex items-center gap-2 mb-1">
                <span class="font-semibold text-sm">{{ comment.user?.first_name }} {{ comment.user?.last_name }}</span>
                <span class="text-xs text-gray-400">{{ formatDate(comment.created_at) }}</span>
              </div>
              <p class="text-sm text-gray-700">{{ comment.content }}</p>
            </div>
          </div>
          
          <p v-if="comments.length === 0" class="text-gray-400 text-sm text-center py-4">
            No comments yet. Be the first to comment!
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'

const props = defineProps<{
  lesson: any
  course: any
  isTeacher: boolean
}>()

const emit = defineEmits(['edit', 'delete', 'completed'])

const config = useRuntimeConfig()
const token = useCookie('auth_token')

const newComment = ref('')
const comments = ref<any[]>([])
const isCompleted = ref(false)
const markingComplete = ref(false)

const teacherName = computed(() => {
  if (!props.course?.teacher) return 'Unknown'
  return `${props.course.teacher.first_name || ''} ${props.course.teacher.last_name || ''}`.trim() || 'Unknown'
})

const formatDate = (date: string) => {
  if (!date) return ''
  const d = new Date(date)
  const now = new Date()
  const diff = now.getTime() - d.getTime()
  const hours = Math.floor(diff / (1000 * 60 * 60))
  if (hours < 1) return 'Just now'
  if (hours < 24) return `${hours} hours ago`
  const days = Math.floor(hours / 24)
  return `${days} days ago`
}

const onVideoEnded = async () => {
  // Auto mark as complete when video ends
  if (!props.isTeacher && !isCompleted.value) {
    await markAsComplete()
  }
}

const markAsComplete = async () => {
  if (markingComplete.value || isCompleted.value) return
  
  markingComplete.value = true
  try {
    await $fetch(`/api/learning/lesson/${props.lesson.id}/complete`, {
      baseURL: config.public.backendUrl as string,
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token.value}`,
        'Accept': 'application/json'
      }
    })
    isCompleted.value = true
    emit('completed', props.lesson.id)
  } catch (err) {
    console.error('Failed to mark lesson as complete:', err)
  } finally {
    markingComplete.value = false
  }
}

const checkProgress = async () => {
  try {
    const response = await $fetch<any>(`/api/learning/lesson/${props.lesson.id}/progress`, {
      baseURL: config.public.backendUrl as string,
      headers: {
        'Authorization': `Bearer ${token.value}`,
        'Accept': 'application/json'
      }
    })
    isCompleted.value = response.is_completed || response.progress?.is_completed || false
  } catch {
    isCompleted.value = false
  }
}

const submitComment = async () => {
  if (!newComment.value.trim()) return
  
  try {
    const response = await $fetch<any>(`/api/lessons/${props.lesson.id}/comments`, {
      baseURL: config.public.backendUrl as string,
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token.value}`,
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      },
      body: { content: newComment.value }
    })
    
    comments.value.unshift(response.comment || response)
    newComment.value = ''
  } catch (err) {
    console.error('Failed to post comment:', err)
    alert('Failed to post comment')
  }
}

const fetchComments = async () => {
  try {
    const response = await $fetch<any>(`/api/lessons/${props.lesson.id}/comments`, {
      baseURL: config.public.backendUrl as string,
      headers: {
        'Authorization': `Bearer ${token.value}`,
        'Accept': 'application/json'
      }
    })
    comments.value = response.comments || response || []
  } catch {
    comments.value = []
  }
}

onMounted(() => {
  fetchComments()
  if (!props.isTeacher) {
    checkProgress()
  }
})
</script>
