export interface ChatMessage {
  role: 'user' | 'assistant'
  content: string
  timestamp: Date
}

export interface AIChatResponse {
  success: boolean
  message: string
  usage?: {
    input_tokens?: number
    output_tokens?: number
    total_tokens?: number
  }
  provider?: string
  model?: string
  error?: string
}

export interface SuggestedQuestionsResponse {
  questions: string[]
}

export interface AIStatusResponse {
  status: 'active' | 'inactive' | 'error'
  provider?: string
  model?: string
  message: string
}
