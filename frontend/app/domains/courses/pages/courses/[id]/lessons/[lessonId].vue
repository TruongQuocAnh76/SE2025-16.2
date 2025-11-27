<template>
  <div class="min-h-screen bg-background">
    <div v-if="loading" class="flex justify-center items-center min-h-[60vh]">
      <div class="animate-spin rounded-full h-16 w-16 border-b-4 border-brand-primary"></div>
    </div>

    <div v-else-if="error" class="text-center py-20">
      <h2 class="text-h2 font-bold text-red-600 mb-4">Unable to load lesson</h2>
      <p class="text-text-muted">{{ error.message }}</p>
      <NuxtLink :to="`/courses/${courseId}`" class="mt-4 inline-block text-brand-primary hover:underline">
        Back to course
      </NuxtLink>
    </div>

    <div v-else-if="lesson" class="container mx-auto px-6 py-8">
      <!-- Breadcrumb Navigation -->
      <nav class="flex items-center space-x-2 text-body-sm text-text-muted mb-6">
        <NuxtLink to="/courses" class="hover:text-brand-primary">Courses</NuxtLink>
        <span>›</span>
        <NuxtLink :to="`/courses/${courseId}`" class="hover:text-brand-primary">{{ course?.title }}</NuxtLink>
        <span>›</span>
        <span class="text-text-dark">{{ lesson.title }}</span>
      </nav>

      <div class="grid lg:grid-cols-4 gap-8">
        <!-- Main Lesson Content -->
        <div class="lg:col-span-3">
          <!-- Lesson Header -->
          <div class="mb-8">
            <h1 class="text-h1 font-bold text-text-dark mb-4">{{ lesson.title }}</h1>
            <div class="flex items-center space-x-4 text-body-sm text-text-muted">
              <span v-if="lesson.duration" class="flex items-center">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.414-1.414L11 9.586V6z" clip-rule="evenodd"/>
                </svg>
                {{ formatDuration(lesson.duration) }}
              </span>
              <span class="flex items-center">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
                </svg>
                {{ lesson.content_type }}
              </span>
              <span v-if="lesson.is_free" class="px-2 py-1 bg-accent-blue text-white text-caption rounded">
                FREE
              </span>
            </div>
          </div>

          <!-- Lesson Content Display -->
          <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            
            <!-- Video Content -->
            <div v-if="lesson.content_type === 'VIDEO'" class="aspect-video bg-black relative">
              <!-- Video Processing State -->
              <div v-if="processingVideo" class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-80 z-20">
                <div class="text-center text-white">
                  <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-brand-primary mx-auto mb-4"></div>
                  <h3 class="text-lg font-semibold mb-2">Processing Video</h3>
                  <p class="text-gray-300">Your video is being converted to adaptive streaming format...</p>
                  <p class="text-gray-400 text-sm mt-2">This may take a few minutes depending on video length</p>
                </div>
              </div>
              
              <!-- Video Loading State -->
              <div v-else-if="videoLoading" class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-50 z-10">
                <div class="text-center text-white">
                  <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-white mx-auto mb-4"></div>
                  <p>Loading video...</p>
                </div>
              </div>
              
              <!-- Video Error State -->
              <div v-else-if="videoError && !processingVideo" class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-50 z-10">
                <div class="text-center text-white">
                  <svg class="w-16 h-16 mx-auto mb-4 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                  </svg>
                  <p class="text-red-300 mb-2">{{ videoError }}</p>
                  <button 
                    @click="initializeVideo" 
                    class="px-4 py-2 bg-brand-primary text-white rounded hover:bg-brand-secondary transition"
                  >
                    Retry
                  </button>
                </div>
              </div>
              
              <video
                v-if="lesson && lesson.content_type === 'VIDEO'"
                ref="videoPlayer"
                controls
                preload="metadata"
                crossorigin="anonymous"
                class="w-full h-full"
                @loadedmetadata="onVideoLoaded"
                @timeupdate="onVideoTimeUpdate"
                @ended="onVideoEnded"
                @error="onVideoError"
                @loadstart="onVideoLoadStart"
                @canplay="onVideoCanPlay"
              >
                <!-- <source :src="lesson.content_url.replace(/localstack/g, 'localhost')" type="application/x-mpegURL" v-if="lesson.content_url.includes('.m3u8')" /> -->
                <!-- <source :src="lesson.content_url.replace(/localstack/g, 'localhost')" type="video/mp4"/> -->
                Your browser does not support the video tag.
              </video>
              <div v-else class="flex items-center justify-center h-full">
                <div class="text-center text-gray-400">
                  <svg class="w-16 h-16 mx-auto mb-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/>
                  </svg>
                  <p>Video content not available</p>
                </div>
              </div>
            </div>

            <!-- Text Content -->
            <div v-else-if="lesson.content_type === 'TEXT'" class="p-8">
              <div v-if="lesson.text_content" v-html="lesson.text_content" class="prose max-w-none"></div>
              <div v-else class="text-gray-400 text-center py-8">
                <p>Text content not available</p>
              </div>
            </div>

            <!-- PDF/Document Content -->
            <div v-else-if="['PDF', 'DOCUMENT'].includes(lesson.content_type)" class="p-8">
              <div v-if="lesson.content_url" class="text-center">
                <div class="mb-6">
                  <svg class="w-16 h-16 mx-auto text-red-500 mb-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                  </svg>
                  <h3 class="text-h3 font-semibold mb-2">{{ lesson.content_type }} Document</h3>
                </div>
                <a
                  :href="lesson.content_url"
                  target="_blank"
                  class="inline-flex items-center px-6 py-3 bg-brand-primary text-white rounded-lg hover:bg-brand-secondary transition"
                >
                  <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
                  </svg>
                  Download {{ lesson.content_type }}
                </a>
              </div>
              <div v-else class="text-gray-400 text-center py-8">
                <p>Document not available</p>
              </div>
            </div>

            <!-- Link Content -->
            <div v-else-if="lesson.content_type === 'LINK'" class="p-8">
              <div v-if="lesson.content_url" class="text-center">
                <div class="mb-6">
                  <svg class="w-16 h-16 mx-auto text-brand-primary mb-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M12.586 4.586a2 2 0 112.828 2.828l-3 3a2 2 0 01-2.828 0 1 1 0 00-1.414 1.414 4 4 0 005.656 0l3-3a4 4 0 00-5.656-5.656l-1.5 1.5a1 1 0 101.414 1.414l1.5-1.5zm-5 5a2 2 0 012.828 0 1 1 0 101.414-1.414 4 4 0 00-5.656 0l-3 3a4 4 0 105.656 5.656l1.5-1.5a1 1 0 10-1.414-1.414l-1.5 1.5a2 2 0 11-2.828-2.828l3-3z" clip-rule="evenodd"/>
                  </svg>
                  <h3 class="text-h3 font-semibold mb-2">External Resource</h3>
                </div>
                <a
                  :href="lesson.content_url"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="inline-flex items-center px-6 py-3 bg-brand-primary text-white rounded-lg hover:bg-brand-secondary transition"
                >
                  <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                  </svg>
                  Visit Resource
                </a>
              </div>
              <div v-else class="text-gray-400 text-center py-8">
                <p>External link not available</p>
              </div>
            </div>

            <!-- Lesson Description -->
            <div v-if="lesson.description" class="border-t border-gray-200 p-8">
              <h3 class="text-h3 font-semibold mb-4">About this Lesson</h3>
              <div v-html="lesson.description" class="prose max-w-none text-text-muted"></div>
            </div>
          </div>

          <!-- Lesson Actions -->
          <div class="mt-8 flex items-center justify-between">
            <button
              v-if="!lessonCompleted && userEnrolled"
              @click="markAsCompleted"
              :disabled="markingComplete"
              class="px-6 py-3 bg-accent-blue text-white rounded-lg hover:bg-blue-600 transition disabled:opacity-50"
            >
              {{ markingComplete ? 'Marking Complete...' : 'Mark as Complete' }}
            </button>
            
            <div v-else-if="lessonCompleted" class="flex items-center text-green-600">
              <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
              </svg>
              <span class="font-semibold">Completed</span>
            </div>

            <div class="flex space-x-4">
              <NuxtLink
                v-if="prevLesson"
                :to="`/courses/${courseId}/lessons/${prevLesson.id}`"
                class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition"
              >
                ← Previous
              </NuxtLink>
              <NuxtLink
                v-if="nextLesson"
                :to="`/courses/${courseId}/lessons/${nextLesson.id}`"
                class="px-4 py-2 bg-brand-primary text-white rounded-lg hover:bg-brand-secondary transition"
              >
                Next →
              </NuxtLink>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1">
          <!-- Progress -->
          <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h3 class="text-h4 font-semibold mb-4">Your Progress</h3>
            <div class="space-y-4">
              <div>
                <div class="flex justify-between text-body-sm mb-2">
                  <span>Course Progress</span>
                  <span>{{ Math.round(courseProgress) }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                  <div
                    class="bg-brand-primary h-2 rounded-full transition-all"
                    :style="{ width: `${courseProgress}%` }"
                  ></div>
                </div>
              </div>
            </div>
          </div>

          <!-- Course Module Navigation -->
          <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-h4 font-semibold mb-4">Course Contents</h3>
            <div v-if="modules" class="space-y-4">
              <div v-for="module in modules" :key="module.id" class="border rounded-lg overflow-hidden">
                <div class="bg-gray-50 px-4 py-3">
                  <h4 class="text-h5 font-medium">{{ module.title }}</h4>
                </div>
                <div class="divide-y">
                  <NuxtLink
                    v-for="moduleLesson in module.lessons"
                    :key="moduleLesson.id"
                    :to="`/courses/${courseId}/lessons/${moduleLesson.id}`"
                    :class="[
                      'block px-4 py-3 text-body-sm hover:bg-gray-50 transition',
                      moduleLesson.id === lesson.id ? 'bg-brand-primary/10 text-brand-primary font-semibold border-r-4 border-brand-primary' : 'text-text-muted'
                    ]"
                  >
                    <div class="flex items-center justify-between">
                      <span>{{ moduleLesson.title }}</span>
                      <div class="flex items-center space-x-2">
                        <span v-if="moduleLesson.is_free" class="text-accent-blue text-caption">FREE</span>
                        <svg
                          v-if="moduleLesson.completed"
                          class="w-4 h-4 text-green-500"
                          fill="currentColor"
                          viewBox="0 0 20 20"
                        >
                          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                      </div>
                    </div>
                  </NuxtLink>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount, nextTick, watch } from 'vue'
import type { Lesson, Module, Course } from '../../../../types/course'
import { useLessons } from '../../../../composables/useLessons'
import Hls from 'hls.js'

// Route parameters
const route = useRoute()
const courseId = route.params.id as string
const lessonId = route.params.lessonId as string

// Initialize composable
const {
  getLessonById,
  getCourseModulesWithLessons,
  getCourseProgress,
  markLessonCompleted,
  updateLessonTimeSpent,
  checkVideoProcessingStatus: checkVideoProcessingStatusComposable
} = useLessons()

// Reactive data
const lesson = ref<Lesson | null>(null)
const course = ref<Course | null>(null)
const modules = ref<Module[] | null>(null)
const loading = ref(true)
const error = ref<Error | null>(null)
const lessonCompleted = ref(false)
const markingComplete = ref(false)
const courseProgress = ref(0)
const userEnrolled = ref(false)
const videoPlayer = ref<HTMLVideoElement>()
const currentTime = ref(0)
const totalTime = ref(0)
const videoLoading = ref(false)
const videoError = ref<string | null>(null)
const processingVideo = ref(false)
let hls: Hls | null = null
let lastSentTime = 0 

// Computed properties
const prevLesson = computed(() => {
  if (!modules.value || !lesson.value) return null
  
  const allLessons: Lesson[] = []
  modules.value.forEach(module => {
    if (module.lessons) {
      allLessons.push(...module.lessons.sort((a, b) => a.order_index - b.order_index))
    }
  })
  
  const currentIndex = allLessons.findIndex(l => l.id === lesson.value?.id)
  return currentIndex > 0 ? allLessons[currentIndex - 1] : null
})

const nextLesson = computed(() => {
  if (!modules.value || !lesson.value) return null
  
  const allLessons: Lesson[] = []
  modules.value.forEach(module => {
    if (module.lessons) {
      allLessons.push(...module.lessons.sort((a, b) => a.order_index - b.order_index))
    }
  })
  
  const currentIndex = allLessons.findIndex(l => l.id === lesson.value?.id)
  return currentIndex < allLessons.length - 1 ? allLessons[currentIndex + 1] : null
})

// Methods
const formatDuration = (seconds: number): string => {
  const hours = Math.floor(seconds / 3600)
  const minutes = Math.floor((seconds % 3600) / 60)
  const remainingSeconds = seconds % 60

  if (hours > 0) {
    return `${hours}h ${minutes}m ${remainingSeconds}s`
  } else if (minutes > 0) {
    return `${minutes}m ${remainingSeconds}s`
  } else {
    return `${remainingSeconds}s`
  }
}

const fetchLessonData = async () => {
  try {
    loading.value = true
    lastSentTime = 0 // Reset time tracking for new lesson
    
    // Fetch lesson details
    lesson.value = await getLessonById(lessonId)

    // Fetch course with modules
    const courseData = await getCourseModulesWithLessons(courseId)
    modules.value = courseData.modules || []
    course.value = courseData
    
    // Check enrollment and progress
    const progressResponse = await getCourseProgress(courseId)
    
    if (progressResponse) {
      userEnrolled.value = true
      courseProgress.value = progressResponse.progress_percent || 0
      
      // Check if lesson is completed
      if (progressResponse.modules) {
        const moduleData = progressResponse.modules.find((m: any) => 
          m.lessons?.some((l: any) => l.id === lessonId)
        )
        if (moduleData) {
          const lessonData = moduleData.lessons.find((l: any) => l.id === lessonId)
          lessonCompleted.value = lessonData?.completed || false
        }
      }
    } else {
      // Handle case where progress couldn't be fetched
      userEnrolled.value = false
      courseProgress.value = 0
    }

    // Initialize video after data is loaded
    await nextTick()
    
    // Check if video exists or if we need to check processing status
    if (lesson.value && lesson.value.content_type === 'VIDEO') {
      if (lesson.value.content_url) {
        // Try to initialize video (HLS or regular)
        // Wait a bit more to ensure video element is rendered
        setTimeout(() => {
          initializeVideo()
        }, 200)
      } else {
        // No content URL yet, check processing status
        checkVideoProcessingStatus()
      }
    }

  } catch (err: any) {
    error.value = err
    console.error('Error fetching lesson data:', err)
  } finally {
    loading.value = false
  }
}

const markAsCompleted = async () => {
  if (!userEnrolled.value || markingComplete.value) return
  
  try {
    markingComplete.value = true
    
    await markLessonCompleted(lessonId)
    
    lessonCompleted.value = true
    
    // Update progress
    await fetchLessonData()
    
  } catch (err: any) {
    console.error('Error marking lesson as complete:', err)
  } finally {
    markingComplete.value = false
  }
}

const updateTimeSpent = async () => {
  if (!userEnrolled.value || !currentTime.value || currentTime.value <= 0) return
  
  const timeSpent = Math.floor(currentTime.value)
  
  // Don't send duplicate updates
  if (timeSpent === lastSentTime) return
  
  try {
    await updateLessonTimeSpent(lessonId, timeSpent)
    
    // Update the last sent time only on successful update
    lastSentTime = timeSpent
  } catch (err: any) {
    console.error('Error updating time spent:', err)
  }
}

// Video event handlers
const initializeVideo = () => {
  if (!videoPlayer.value) {
    // Try again after a short delay in case the element is still being rendered
    setTimeout(() => {
      if (videoPlayer.value) {
        initializeVideo()
      }
    }, 500)
    return
  }
  
  if (!lesson.value?.content_url) {
    return
  }

  const video = videoPlayer.value
  const videoUrl = lesson.value.content_url
  
  // Reset states
  videoError.value = null
  videoLoading.value = true

  // Clean up previous HLS instance if exists
  if (hls) {
    hls.destroy()
    hls = null
  }

  // Check if the video URL is an M3U8 playlist (HLS)
  if (videoUrl.includes('.m3u8')) {
    if (Hls.isSupported()) {
      hls = new Hls({
        enableWorker: false,
        debug: process.env.NODE_ENV === 'development',
        maxLoadingDelay: 4,
        maxBufferLength: 30,
        maxBufferSize: 60 * 1000 * 1000, // 60MB
        manifestLoadingTimeOut: 10000,
        fragLoadingTimeOut: 20000,
        manifestLoadingMaxRetry: 3,
        fragLoadingMaxRetry: 3,
        xhrSetup: (xhr, url) => {
          // Add CORS headers for cross-origin requests
          xhr.withCredentials = false
        }
      })
      
      hls.loadSource(videoUrl)
      hls.attachMedia(video)
      
      hls.on(Hls.Events.MANIFEST_PARSED, () => {
        videoLoading.value = false
        // Auto-play is usually blocked by browsers, so we don't auto-play
      })
      
      hls.on(Hls.Events.LEVEL_LOADED, (event, data) => {
        // Optional: Log fragment loading for debugging
        // console.log('Fragment loaded:', data.frag.url)
      })
      
      hls.on(Hls.Events.FRAG_LOADED, (event, data) => {
        // Optional: Log fragment loading for debugging
        // console.log('Fragment loaded:', data.frag.url)
      })
      
      hls.on(Hls.Events.ERROR, (event, data) => {
        if (data.fatal) {
          videoLoading.value = false
          
          switch (data.type) {
            case Hls.ErrorTypes.NETWORK_ERROR:
              // Check if this might be a processing issue
              if (data.details === 'manifestLoadError' || data.details === 'manifestLoadTimeOut') {
                checkVideoProcessingStatus()
                videoError.value = 'Video might still be processing. Checking status...'
              } else {
                videoError.value = 'Network error occurred while loading video. Click retry to try again.'
                // Attempt automatic recovery for network errors
                setTimeout(() => {
                  if (hls && !processingVideo.value) {
                    hls.startLoad()
                  }
                }, 1000)
              }
              break
            case Hls.ErrorTypes.MEDIA_ERROR:
              videoError.value = 'Media error occurred while playing video. Click retry to try again.'
              if (hls) {
                hls.recoverMediaError()
              }
              break
            default:
              videoError.value = 'Fatal error occurred while loading video. Click retry to try again.'
              hls?.destroy()
              hls = null
              break
          }
        }
      })
      
      hls.on(Hls.Events.MEDIA_ATTACHED, () => {
      })
      
    } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
      video.src = videoUrl
    } else {
      videoError.value = 'HLS streaming is not supported in this browser. Please use a modern browser like Chrome, Firefox, or Safari.'
      videoLoading.value = false
    }
  } else {
    // Regular MP4 video
    video.src = videoUrl
  }
}

const onVideoLoaded = () => {
  if (videoPlayer.value) {
    totalTime.value = videoPlayer.value.duration
  }
}

const onVideoTimeUpdate = () => {
  if (videoPlayer.value) {
    currentTime.value = videoPlayer.value.currentTime
    
    // Update time spent every 30 seconds of progress, but only if we've made progress
    const flooredTime = Math.floor(currentTime.value)
    if (flooredTime > 0 && flooredTime % 30 === 0 && flooredTime !== lastSentTime) {
      updateTimeSpent()
    }
  }
}

const onVideoEnded = () => {
  updateTimeSpent()
  // Auto-mark as completed when video ends
  if (!lessonCompleted.value) {
    markAsCompleted()
  }
}

const onVideoError = (event: Event) => {
  const video = event.target as HTMLVideoElement
  const errorMessage = video.error ? 
    `Video error: ${video.error.message} (Code: ${video.error.code})` :
    'Unknown video error occurred'
  
  videoError.value = errorMessage
  videoLoading.value = false
  
  console.error('Video error occurred:', {
    error: video.error,
    networkState: video.networkState,
    readyState: video.readyState,
    currentSrc: video.currentSrc
  })
}

const onVideoLoadStart = () => {
  videoLoading.value = true
  videoError.value = null
}

const onVideoCanPlay = () => {
  videoLoading.value = false
}

const checkVideoProcessingStatus = async () => {
  if (!lesson.value?.id) return
  
  try {
    const response = await checkVideoProcessingStatusComposable(lesson.value.id)
    
    if (response && response.status === 'completed' && response.hls_url) {
      // Update the lesson's content URL and reinitialize video
      if (lesson.value) {
        lesson.value.content_url = response.hls_url
        processingVideo.value = false
        await nextTick()
        initializeVideo()
      }
    } else if (response && (response.status === 'processing' || response.status === 'pending')) {
      processingVideo.value = true
      // Check again in 5 seconds
      setTimeout(checkVideoProcessingStatus, 5000)
    }
  } catch (error) {
    console.error('Error checking video processing status:', error)
    processingVideo.value = false
  }
}

// Lifecycle
onMounted(() => {
  fetchLessonData()
})

// Watch for lesson content URL changes to reinitialize video
watch(() => lesson.value?.content_url, (newUrl, oldUrl) => {
  if (newUrl && newUrl !== oldUrl && lesson.value?.content_type === 'VIDEO') {
    nextTick(() => {
      // Add a small delay to ensure the video element is fully rendered
      setTimeout(() => {
        initializeVideo()
      }, 100)
    })
  }
})

// Watch for lesson data changes to initialize video when both lesson and video element are ready
watch(() => lesson.value, (newLesson) => {
  if (newLesson && newLesson.content_type === 'VIDEO' && newLesson.content_url) {
    nextTick(() => {
      setTimeout(() => {
        initializeVideo()
      }, 100)
    })
  }
}, { immediate: true })

// Update time spent before leaving
onBeforeUnmount(() => {
  updateTimeSpent()
  // Clean up HLS instance
  if (hls) {
    hls.destroy()
    hls = null
  }
})

// SEO
useHead({
  title: computed(() => lesson.value ? `${lesson.value.title} - ${course.value?.title}` : 'Loading...'),
  meta: [
    { name: 'description', content: computed(() => lesson.value?.description || 'Course lesson') }
  ]
})
</script>