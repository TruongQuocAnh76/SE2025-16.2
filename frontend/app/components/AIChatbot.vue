<template>
  <div class="fixed bottom-6 right-6 z-[9999]">
    <!-- Chat Toggle Button -->
    <Transition name="bounce">
      <button
        v-if="!isOpen"
        @click="toggleChat"
        class="bg-gradient-to-r from-teal-500 to-teal-600 hover:from-teal-600 hover:to-teal-700 text-white rounded-full p-4 shadow-2xl transition-all duration-300 hover:scale-110 group relative"
        aria-label="Mở chat AI"
      >
        <!-- AI Icon -->
        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
        </svg>
        
        <!-- Pulse Animation -->
        <span class="absolute -top-1 -right-1 flex h-6 w-6">
          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-teal-400 opacity-75"></span>
          <span class="relative inline-flex rounded-full h-6 w-6 bg-teal-500 items-center justify-center">
            <span class="text-white text-xs font-bold">AI</span>
          </span>
        </span>

        <!-- Tooltip -->
        <span class="absolute bottom-full right-0 mb-2 px-3 py-1 bg-gray-900 text-white text-sm rounded-lg whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200">
          Hỏi AI về khóa học
        </span>
      </button>
    </Transition>

    <!-- Chat Window -->
    <Transition name="slide-up">
      <div
        v-if="isOpen"
        class="bg-white rounded-2xl shadow-2xl w-[400px] h-[650px] flex flex-col overflow-hidden border border-gray-200"
      >
        <!-- Header -->
        <div class="bg-gradient-to-r from-teal-500 to-teal-600 text-white p-5 flex justify-between items-center">
          <div class="flex items-center space-x-3">
            <div class="relative">
              <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center backdrop-blur-sm">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                </svg>
              </div>
              <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-400 rounded-full border-2 border-white animate-pulse"></div>
            </div>
            <div>
              <h3 class="font-semibold text-lg">CertChain AI</h3>
              <p class="text-xs text-teal-100">Trợ lý thông minh</p>
            </div>
          </div>
          <div class="flex space-x-2">
            <button
              @click="handleClearConversation"
              class="hover:bg-white/20 rounded-lg p-2 transition-colors group relative"
              title="Xóa hội thoại"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </button>
            <button
              @click="toggleChat"
              class="hover:bg-white/20 rounded-lg p-2 transition-colors"
              title="Đóng"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Messages Container -->
        <div ref="messagesContainer" class="flex-1 overflow-y-auto p-5 space-y-4 bg-gradient-to-b from-gray-50 to-white">
          <!-- Welcome Message -->
          <div v-if="conversationHistory.length === 0" class="space-y-4 animate-fade-in">
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
              <div class="flex items-start space-x-3 mb-4">
                <div class="w-8 h-8 bg-gradient-to-br from-teal-400 to-teal-600 rounded-full flex items-center justify-center flex-shrink-0">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                  </svg>
                </div>
                <div>
                  <p class="text-gray-800 font-medium mb-1">👋 Xin chào!</p>
                  <p class="text-gray-600 text-sm leading-relaxed">
                    Tôi là <span class="font-semibold text-teal-600">CertChain AI</span>, trợ lý thông minh được hỗ trợ bởi Google Gemini. Tôi có thể giúp bạn tìm hiểu về các khóa học và giải đáp thắc mắc! 🚀
                  </p>
                </div>
              </div>
              
              <!-- Suggested Questions -->
              <div v-if="suggestedQuestions.length > 0" class="space-y-2">
                <p class="text-sm text-gray-500 font-medium flex items-center">
                  <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  Câu hỏi gợi ý:
                </p>
                <div class="space-y-2">
                  <button
                    v-for="(question, index) in suggestedQuestions"
                    :key="index"
                    @click="handleSuggestedQuestion(question)"
                    class="block w-full text-left text-sm text-gray-700 hover:text-teal-600 hover:bg-teal-50 rounded-xl p-3 transition-all duration-200 border border-transparent hover:border-teal-200 group"
                  >
                    <span class="flex items-center">
                      <span class="text-teal-500 mr-2 group-hover:scale-110 transition-transform">→</span>
                      {{ question }}
                    </span>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Chat Messages -->
          <TransitionGroup name="message">
            <div
              v-for="(message, index) in conversationHistory"
              :key="index"
              :class="[
                'flex animate-fade-in',
                message.role === 'user' ? 'justify-end' : 'justify-start'
              ]"
            >
              <div
                :class="[
                  'max-w-[85%] rounded-2xl p-4 shadow-sm',
                  message.role === 'user'
                    ? 'bg-gradient-to-br from-teal-500 to-teal-600 text-white'
                    : 'bg-white text-gray-800 border border-gray-100'
                ]"
              >
                <!-- Avatar for AI messages -->
                <div v-if="message.role === 'assistant'" class="flex items-start space-x-2 mb-2">
                  <div class="w-6 h-6 bg-gradient-to-br from-teal-400 to-teal-600 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                  </div>
                  <span class="text-xs font-medium text-teal-600">AI Assistant</span>
                </div>

                <p class="text-sm whitespace-pre-wrap leading-relaxed" v-html="formatMessage(message.content)"></p>
                
                <p :class="['text-xs mt-2', message.role === 'user' ? 'text-teal-100' : 'text-gray-400']">
                  {{ formatTime(message.timestamp) }}
                </p>
              </div>
            </div>
          </TransitionGroup>

          <!-- Loading Indicator -->
          <div v-if="isLoading" class="flex justify-start animate-fade-in">
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 flex items-center space-x-3">
              <div class="w-6 h-6 bg-gradient-to-br from-teal-400 to-teal-600 rounded-full flex items-center justify-center">
                <svg class="w-4 h-4 text-white animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
              </div>
              <div class="flex space-x-1">
                <div class="w-2 h-2 bg-teal-400 rounded-full animate-bounce" style="animation-delay: 0s"></div>
                <div class="w-2 h-2 bg-teal-400 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                <div class="w-2 h-2 bg-teal-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
              </div>
              <span class="text-sm text-gray-500">Đang suy nghĩ...</span>
            </div>
          </div>

          <!-- Error Message -->
          <Transition name="shake">
            <div v-if="error" class="bg-red-50 border-l-4 border-red-500 rounded-lg p-4 animate-fade-in">
              <div class="flex items-start">
                <svg class="w-5 h-5 text-red-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
                <div>
                  <p class="text-sm font-medium text-red-800">Đã có lỗi xảy ra</p>
                  <p class="text-sm text-red-600 mt-1">{{ error }}</p>
                </div>
              </div>
            </div>
          </Transition>
        </div>

        <!-- Input Area -->
        <div class="border-t border-gray-200 p-4 bg-white">
          <form @submit.prevent="handleSendMessage" class="flex space-x-2">
            <input
              v-model="messageInput"
              type="text"
              placeholder="Nhập câu hỏi của bạn..."
              class="flex-1 px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-sm transition-all duration-200"
              :disabled="isLoading"
              maxlength="2000"
            />
            <button
              type="submit"
              :disabled="isLoading || !messageInput.trim()"
              class="bg-gradient-to-r from-teal-500 to-teal-600 hover:from-teal-600 hover:to-teal-700 text-white px-5 py-3 rounded-xl transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg hover:shadow-xl transform hover:scale-105 disabled:transform-none"
            >
              <svg v-if="!isLoading" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
              </svg>
              <svg v-else class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
            </button>
          </form>
          <p class="text-xs text-gray-400 mt-2 text-center">
            Powered by <span class="font-semibold text-teal-600">Google Gemini</span>
          </p>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch, nextTick, computed } from 'vue'

interface Props {
  courseId?: string | number
}

const props = defineProps<Props>()

const { 
  conversationHistory, 
  isLoading, 
  error, 
  sendMessage, 
  getSuggestedQuestions,
  clearConversation,
  saveToLocalStorage,
  loadFromLocalStorage
} = useAIChat()

const isOpen = ref(false)
const messageInput = ref('')
const messagesContainer = ref<HTMLElement | null>(null)
const suggestedQuestions = ref<string[]>([])

const toggleChat = () => {
  isOpen.value = !isOpen.value
  if (isOpen.value) {
    if (conversationHistory.value.length === 0) {
      loadSuggestedQuestions()
      loadFromLocalStorage()
    }
    nextTick(() => scrollToBottom())
  }
}

const handleSendMessage = async () => {
  if (!messageInput.value.trim() || isLoading.value) return

  const message = messageInput.value
  messageInput.value = ''

  await sendMessage(message, props.courseId)
  saveToLocalStorage()
  scrollToBottom()
}

const handleSuggestedQuestion = async (question: string) => {
  messageInput.value = question
  await handleSendMessage()
}

const handleClearConversation = () => {
  if (conversationHistory.value.length === 0) return
  
  if (confirm('Bạn có chắc muốn xóa toàn bộ hội thoại?')) {
    clearConversation()
    localStorage.removeItem('certchain_chat_history')
    loadSuggestedQuestions()
  }
}

const loadSuggestedQuestions = async () => {
  suggestedQuestions.value = await getSuggestedQuestions(props.courseId)
}

const scrollToBottom = async () => {
  await nextTick()
  if (messagesContainer.value) {
    messagesContainer.value.scrollTo({
      top: messagesContainer.value.scrollHeight,
      behavior: 'smooth'
    })
  }
}

const formatTime = (date: Date): string => {
  return new Intl.DateTimeFormat('vi-VN', {
    hour: '2-digit',
    minute: '2-digit'
  }).format(date)
}

const formatMessage = (content: string): string => {
  // Convert markdown-like formatting
  let formatted = content
    .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>') // Bold
    .replace(/\*(.*?)\*/g, '<em>$1</em>') // Italic
    .replace(/\n/g, '<br>') // Line breaks
  
  return formatted
}

// Auto scroll when new messages arrive
watch(() => conversationHistory.value.length, () => {
  scrollToBottom()
})

// Save conversation to localStorage when it changes
watch(() => conversationHistory.value.length, () => {
  if (conversationHistory.value.length > 0) {
    saveToLocalStorage()
  }
}, { deep: true })

onMounted(() => {
  loadSuggestedQuestions()
})
</script>

<style scoped>
/* Animations */
@keyframes fade-in {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fade-in {
  animation: fade-in 0.3s ease-out;
}

/* Transitions */
.bounce-enter-active {
  animation: bounce-in 0.5s;
}

.bounce-leave-active {
  animation: bounce-in 0.5s reverse;
}

@keyframes bounce-in {
  0% {
    transform: scale(0);
  }
  50% {
    transform: scale(1.1);
  }
  100% {
    transform: scale(1);
  }
}

.slide-up-enter-active,
.slide-up-leave-active {
  transition: all 0.3s ease;
}

.slide-up-enter-from {
  opacity: 0;
  transform: translateY(20px) scale(0.95);
}

.slide-up-leave-to {
  opacity: 0;
  transform: translateY(20px) scale(0.95);
}

.message-enter-active {
  animation: message-in 0.3s ease-out;
}

@keyframes message-in {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.shake-enter-active {
  animation: shake 0.5s;
}

@keyframes shake {
  0%, 100% {
    transform: translateX(0);
  }
  10%, 30%, 50%, 70%, 90% {
    transform: translateX(-5px);
  }
  20%, 40%, 60%, 80% {
    transform: translateX(5px);
  }
}

/* Custom scrollbar */
.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 10px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #14b8a6;
  border-radius: 10px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #0d9488;
}
</style>
