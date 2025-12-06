<template>
  <div class="w-full space-y-6">
    <!-- Form Header -->
    <div class="text-center space-y-2">
      <h1 class="text-h2 font-bold text-text-dark">Certificate Verification</h1>
      <p class="text-body text-text-muted max-w-2xl mx-auto">
        Verify the authenticity of your certificate using our blockchain-based verification system. 
        Both the certificate number and PDF file are required for complete verification.
      </p>
    </div>

    <!-- Verification Form -->
    <div class="bg-white rounded-lg border border-gray-200 p-6 space-y-6">
      <!-- Certificate Number Input -->
      <div>
        <label for="certificateNumber" class="block text-sm font-medium text-text-dark mb-2">
          Certificate Number <span class="text-red-500">*</span>
        </label>
        <input
          id="certificateNumber"
          v-model="certificateNumber"
          type="text"
          placeholder="e.g. CERT-WEB-2024-001"
          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-primary focus:border-brand-primary transition-all"
          :class="{
            'border-red-300 focus:ring-red-500 focus:border-red-500': getFieldError('certificate_number')
          }"
        />
        <p v-if="getFieldError('certificate_number')" class="mt-1 text-sm text-red-600">
          {{ getFieldError('certificate_number') }}
        </p>
        <p class="mt-1 text-sm text-text-muted">
          Enter the certificate number found on your certificate
        </p>
      </div>

      <!-- PDF Upload Section -->
      <div>
        <label class="block text-sm font-medium text-text-dark mb-2">
          Certificate PDF <span class="text-red-500">*</span>
        </label>
        <PdfUpload ref="pdfUploadRef" />
        <p class="mt-1 text-sm text-text-muted">
          Upload your certificate PDF for verification against the blockchain hash
        </p>
      </div>

      <!-- Verify Button -->
      <div class="flex justify-center">
        <button
          @click="handleVerification"
          :disabled="!canVerify || isLoading"
          class="px-8 py-3 bg-brand-primary hover:bg-brand-secondary disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-medium rounded-lg transition-all duration-200 flex items-center space-x-2"
        >
          <svg 
            v-if="isLoading" 
            class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" 
            xmlns="http://www.w3.org/2000/svg" 
            fill="none" 
            viewBox="0 0 24 24"
          >
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <span>{{ isLoading ? 'Verifying...' : 'Verify Certificate' }}</span>
        </button>
      </div>
    </div>

    <!-- Verification Results -->
    <div v-if="result" class="animate-fadeIn">
      <CertificateDisplay :result="result" />
    </div>

    <!-- Error Messages -->
    <div v-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4 animate-fadeIn">
      <div class="flex items-center">
        <svg class="w-6 h-6 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div>
          <h3 class="text-red-800 font-medium">Verification Failed</h3>
          <p class="text-red-700">{{ error }}</p>
        </div>
      </div>
    </div>

    <!-- Help Section -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
      <h3 class="text-lg font-semibold text-blue-900 mb-2">Need Help?</h3>
      <div class="space-y-2 text-blue-800">
        <p class="flex items-start space-x-2">
          <span class="font-medium">•</span>
          <span>Both certificate number and PDF file are required for verification</span>
        </p>
        <p class="flex items-start space-x-2">
          <span class="font-medium">•</span>
          <span>PDF verification compares your file hash with blockchain records for maximum security</span>
        </p>
        <p class="flex items-start space-x-2">
          <span class="font-medium">•</span>
          <span>All certificates issued through Certchain are stored on blockchain for transparent verification</span>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useVerification } from '../composables/useVerification'
import type { UploadedFile } from '../types'

const {
  state,
  verifyCertificate,
  verifyCertificateWithPdf,
  resetState,
  isLoading,
  result,
  error,
  getFieldError
} = useVerification()

const certificateNumber = ref('')
const pdfUploadRef = ref()

// Form validation - both certificate number and PDF are required
const canVerify = computed(() => {
  const pdfFile = pdfUploadRef.value?.uploadedFile?.file
  return certificateNumber.value.trim().length > 0 && pdfFile
})

// Handle verification - always requires both certificate number and PDF
const handleVerification = async () => {
  if (!canVerify.value) return

  const pdfFile = pdfUploadRef.value?.uploadedFile?.file
  if (!pdfFile) {
    // This should not happen due to canVerify check, but add safety check
    return
  }

  // Reset previous results
  resetState()

  // Always verify with both certificate number and PDF
  await verifyCertificateWithPdf(certificateNumber.value.trim(), pdfFile)
}

// Reset form
const resetForm = () => {
  certificateNumber.value = ''
  pdfUploadRef.value?.clearFile()
  resetState()
}

// Keyboard shortcut for verification
const onKeyDown = (event: KeyboardEvent) => {
  if (event.key === 'Enter' && canVerify.value && !isLoading.value) {
    handleVerification()
  }
}

onMounted(() => {
  document.addEventListener('keydown', onKeyDown)
})

onUnmounted(() => {
  document.removeEventListener('keydown', onKeyDown)
})
</script>

<style>
.animate-fadeIn {
  animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>