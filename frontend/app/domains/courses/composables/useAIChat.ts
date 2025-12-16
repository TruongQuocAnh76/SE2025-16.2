import type { Ref } from 'vue'
import type { ChatMessage, AIChatResponse, SuggestedQuestionsResponse, AIStatusResponse } from '../types/aiChat'

export const useAIChat = () => {
  const config = useRuntimeConfig()

  const conversationHistory: Ref<ChatMessage[]> = ref([])
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  /**
   * Gửi tin nhắn đến Gemini AI
   */
  const sendMessage = async (
    message: string,
    courseId?: string | number
  ): Promise<AIChatResponse | null> => {
    if (!message.trim()) {
      error.value = 'Vui lòng nhập tin nhắn'
      return null
    }

    isLoading.value = true
    error.value = null

    try {
      // Thêm tin nhắn của user vào lịch sử
      const userMessage: ChatMessage = {
        role: 'user',
        content: message,
        timestamp: new Date()
      }
      conversationHistory.value.push(userMessage)

      // Gọi API
      const response = await $fetch<AIChatResponse>('/api/ai/chat', {
        baseURL: config.public.backendUrl as string,
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: {
          message,
          course_id: courseId,
          conversation_history: conversationHistory.value.slice(0, -1).map(msg => ({
            role: msg.role,
            content: msg.content
          }))
        }
      })

      if (response.success && response.message) {
        // Thêm câu trả lời của AI vào lịch sử
        const assistantMessage: ChatMessage = {
          role: 'assistant',
          content: response.message,
          timestamp: new Date()
        }
        conversationHistory.value.push(assistantMessage)
      } else {
        error.value = response.message || 'Không thể nhận câu trả lời từ AI'
        // Xóa tin nhắn user nếu request thất bại
        conversationHistory.value.pop()
      }

      return response

    } catch (err: any) {
      console.error('AI Chat Error:', err)
      
      // Better error messages based on HTTP status
      let errorMessage = 'Đã xảy ra lỗi khi gửi tin nhắn'
      
      if (err.status === 429) {
        errorMessage = 'Dịch vụ AI đạt giới hạn sử dụng. Vui lòng thử lại trong vài giây.'
      } else if (err.status === 422) {
        errorMessage = 'Dữ liệu không hợp lệ. Vui lòng kiểm tra tin nhắn và thử lại.'
      } else if (err.status === 500) {
        errorMessage = err.data?.message || 'Lỗi máy chủ. Vui lòng thử lại sau.'
      } else if (err.data?.message) {
        errorMessage = err.data.message
      } else if (err.message) {
        errorMessage = err.message
      }
      
      error.value = errorMessage
      
      // Xóa tin nhắn user nếu request thất bại
      conversationHistory.value.pop()
      
      return null
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Lấy câu hỏi gợi ý
   */
  const getSuggestedQuestions = async (
    courseId?: string | number
  ): Promise<string[]> => {
    try {
      const queryParams = courseId ? `?course_id=${courseId}` : ''
      const response = await $fetch<SuggestedQuestionsResponse>(
        `/api/ai/suggested-questions${queryParams}`,
        {
          baseURL: config.public.backendUrl as string,
          method: 'GET',
          headers: {
            'Accept': 'application/json'
          }
        }
      )

      return response.questions || []
    } catch (err: any) {
      console.error('Failed to get suggested questions:', err)
      return []
    }
  }

  /**
   * Kiểm tra trạng thái AI service
   */
  const checkStatus = async (): Promise<boolean> => {
    try {
      const response = await $fetch<AIStatusResponse>(
        '/api/ai/status',
        {
          baseURL: config.public.backendUrl as string,
          method: 'GET',
          headers: {
            'Accept': 'application/json'
          }
        }
      )
      return response.status === 'active'
    } catch (err) {
      console.error('Failed to check AI status:', err)
      return false
    }
  }

  /**
   * Xóa lịch sử hội thoại
   */
  const clearConversation = () => {
    conversationHistory.value = []
    error.value = null
  }

  /**
   * Xóa 2 tin nhắn cuối (user + assistant)
   */
  const undoLastExchange = () => {
    if (conversationHistory.value.length >= 2) {
      conversationHistory.value.splice(-2, 2)
    }
  }

  /**
   * Lưu lịch sử hội thoại vào localStorage
   */
  const saveToLocalStorage = (key: string = 'certchain_chat_history') => {
    try {
      const data = {
        history: conversationHistory.value,
        timestamp: new Date().toISOString()
      }
      localStorage.setItem(key, JSON.stringify(data))
    } catch (err) {
      console.error('Failed to save chat history:', err)
    }
  }

  /**
   * Tải lịch sử hội thoại từ localStorage
   */
  const loadFromLocalStorage = (key: string = 'certchain_chat_history') => {
    try {
      const stored = localStorage.getItem(key)
      if (stored) {
        const data = JSON.parse(stored)
        conversationHistory.value = data.history.map((msg: any) => ({
          ...msg,
          timestamp: new Date(msg.timestamp)
        }))
      }
    } catch (err) {
      console.error('Failed to load chat history:', err)
    }
  }

  return {
    conversationHistory,
    isLoading,
    error,
    sendMessage,
    getSuggestedQuestions,
    checkStatus,
    clearConversation,
    undoLastExchange,
    saveToLocalStorage,
    loadFromLocalStorage
  }
}
