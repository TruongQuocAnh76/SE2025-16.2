import type { 
  VerificationRequest, 
  VerificationResponse, 
  VerificationResult,
  UploadedFile,
  VerificationState 
} from '../types'

export const useVerification = () => {
  const state = ref<VerificationState>({
    isLoading: false,
    result: null,
    error: null,
    validationErrors: []
  })

  const config = useRuntimeConfig()

  // Calculate SHA-256 hash of a file
  const calculateFileHash = async (file: File): Promise<string> => {
    const arrayBuffer = await file.arrayBuffer()
    const hashBuffer = await crypto.subtle.digest('SHA-256', arrayBuffer)
    const hashArray = Array.from(new Uint8Array(hashBuffer))
    const hashHex = hashArray.map(b => b.toString(16).padStart(2, '0')).join('')
    return hashHex
  }

  // This method is kept for backward compatibility but will show an error
  const verifyCertificate = async (certificateNumber: string): Promise<VerificationResult | null> => {
    state.value.isLoading = false
    state.value.error = 'PDF file is required for certificate verification'
    state.value.result = null
    return null
  }

  // Verify certificate with PDF file
  const verifyCertificateWithPdf = async (
    certificateNumber: string, 
    pdfFile: File
  ): Promise<VerificationResult | null> => {
    state.value.isLoading = true
    state.value.error = null
    state.value.validationErrors = []

    try {
      // Calculate file hash
      const pdfHash = await calculateFileHash(pdfFile)

      const response = await fetch(`${config.public.backendUrl}/api/certificates/verify`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          certificate_number: certificateNumber,
          pdf_hash: pdfHash
        })
      })

      if (!response.ok) {
        const errorData = await response.json().catch(() => ({}))
        throw new Error(errorData.message || `HTTP ${response.status}`)
      }

      const data: VerificationResponse = await response.json()

      if (data.success && data.data) {
        state.value.result = data.data
        return data.data
      } else {
        state.value.error = data.message || 'Verification failed'
        return null
      }
    } catch (error: any) {
      if (error.status === 422 && error.data?.errors) {
        // Validation errors
        state.value.validationErrors = Object.entries(error.data.errors).map(([field, messages]) => ({
          field,
          message: Array.isArray(messages) ? messages[0] : messages
        }))
      } else if (error.message) {
        state.value.error = error.message
      } else {
        state.value.error = 'Network error: Could not connect to verification service'
      }
      return null
    } finally {
      state.value.isLoading = false
    }
  }

  // Reset verification state
  const resetState = () => {
    state.value = {
      isLoading: false,
      result: null,
      error: null,
      validationErrors: []
    }
  }

  // Get validation error for specific field
  const getFieldError = (fieldName: string): string | null => {
    const error = state.value.validationErrors.find(err => err.field === fieldName)
    return error ? error.message : null
  }

  return {
    // State
    state: readonly(state),
    
    // Actions
    verifyCertificate,
    verifyCertificateWithPdf,
    resetState,
    calculateFileHash,
    
    // Computed
    isLoading: computed(() => state.value.isLoading),
    result: computed(() => state.value.result),
    error: computed(() => state.value.error),
    hasValidationErrors: computed(() => state.value.validationErrors.length > 0),
    validationErrors: computed(() => state.value.validationErrors),
    
    // Utilities
    getFieldError
  }
}