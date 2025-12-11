export interface Certificate {
  certificate_number: string
  student_name: string
  course_title: string
  final_score: number
  blockchain_status: string
  transaction_hash: string
  issued_date: string
  pdf_url: string
}

export interface VerificationResult {
  is_valid: boolean
  is_revoked: boolean
  issued_at: string | null
  expires_at: string | null
  certificate: Certificate
}

export interface VerificationResponse {
  success: boolean
  message: string
  data?: VerificationResult
  error?: string
  errors?: Record<string, string[]>
}

export interface VerificationRequest {
  certificate_number: string
  pdf_hash?: string
}

export interface ValidationError {
  field: string
  message: string
}

export interface UploadedFile {
  file: File
  name: string
  size: number
  hash?: string
}

export interface VerificationState {
  isLoading: boolean
  result: VerificationResult | null
  error: string | null
  validationErrors: ValidationError[]
}