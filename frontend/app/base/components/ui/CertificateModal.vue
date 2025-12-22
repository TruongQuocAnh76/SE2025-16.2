<template>
  <div 
    v-if="isOpen" 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-75"
    @click.self="close"
  >
    <div class="bg-white rounded-lg shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden relative">
      <!-- Header -->
      <div class="flex justify-between items-center p-6 border-b border-neutral-200">
        <div>
          <h3 class="text-xl font-semibold text-neutral-900">Certificate Preview</h3>
          <p class="text-sm text-neutral-600 mt-1">{{ certificate?.course?.title }}</p>
        </div>
        <div class="flex items-center space-x-3">
          <!-- Save/Download Button -->
          <button 
            @click="downloadCertificate"
            :disabled="downloading"
            class="flex items-center space-x-2 px-4 py-2 bg-primary-500 text-white rounded-lg hover:bg-primary-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <svg v-if="downloading" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <span>{{ downloading ? 'Downloading...' : 'Save Certificate' }}</span>
          </button>
          
          <!-- Close Button -->
          <button 
            @click="close"
            class="p-2 rounded-lg hover:bg-neutral-100 transition-colors"
          >
            <svg class="w-6 h-6 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
      </div>

      <!-- Certificate Content -->
      <div class="flex-1 overflow-auto p-6">
        <!-- Loading State -->
        <div v-if="loading" class="flex items-center justify-center h-96">
          <div class="text-center">
            <svg class="w-12 h-12 animate-spin mx-auto mb-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
            <p class="text-neutral-600">Loading certificate...</p>
          </div>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="flex items-center justify-center h-96">
          <div class="text-center">
            <svg class="w-12 h-12 mx-auto mb-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p class="text-red-600">{{ error }}</p>
          </div>
        </div>

        <!-- Certificate Preview -->
        <div v-else-if="certificate" class="space-y-6">
          <!-- Certificate Details -->
          <div class="bg-neutral-50 rounded-lg p-6">
            <h4 class="text-lg font-semibold text-neutral-900 mb-4">Certificate Details</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-neutral-700 mb-1">Certificate Number</label>
                <p class="text-sm text-neutral-900 font-mono bg-white px-3 py-2 rounded border">{{ certificate.certificate_number }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-neutral-700 mb-1">Issued Date</label>
                <p class="text-sm text-neutral-900">{{ formatDate(certificate.issued_at) }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-neutral-700 mb-1">Course</label>
                <p class="text-sm text-neutral-900">{{ certificate.course?.title }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-neutral-700 mb-1">Final Score</label>
                <p class="text-sm text-neutral-900">{{ certificate.final_score }}%</p>
              </div>
            </div>
          </div>

          <!-- PDF Viewer -->
          <div v-if="certificate.pdf_url" class="border rounded-lg overflow-hidden bg-neutral-50">
            <div class="p-4 border-b bg-white">
              <h5 class="text-md font-medium text-neutral-900">Certificate PDF</h5>
              <p class="text-sm text-neutral-600">Click the button below to view or download the certificate</p>
            </div>
            
            <!-- PDF Embed with fallback -->
            <div class="relative">
              <!-- Try iframe first -->
              <iframe 
                :src="certificate.pdf_url + '#view=FitH'" 
                class="w-full h-96 border-0"
                frameborder="0"
                title="Certificate PDF"
                @error="showPdfFallback = true"
              ></iframe>
              
              <!-- Fallback for when iframe doesn't work -->
              <div v-if="showPdfFallback" class="absolute inset-0 bg-white flex items-center justify-center">
                <div class="text-center p-6">
                  <svg class="w-16 h-16 mx-auto mb-4 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                  </svg>
                  <p class="text-neutral-600 mb-4">PDF preview not available</p>
                  <a 
                    :href="certificate.pdf_url" 
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center space-x-2 px-4 py-2 bg-primary-500 text-white rounded-lg hover:bg-primary-600 transition-colors"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                    </svg>
                    <span>Open PDF in new tab</span>
                  </a>
                </div>
              </div>
            </div>
          </div>
          
          <!-- No PDF Available -->
          <div v-else class="border border-neutral-200 rounded-lg p-8 text-center bg-neutral-50">
            <svg class="w-12 h-12 mx-auto mb-4 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <p class="text-neutral-600">Certificate PDF is being generated</p>
            <p class="text-sm text-neutral-500 mt-1">Please check back later</p>
          </div>

          <!-- Blockchain Verification -->
          <div v-if="certificate.blockchain_transaction_hash" class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex items-center space-x-2 mb-2">
              <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
              </svg>
              <span class="text-sm font-medium text-green-800">Blockchain Verified</span>
            </div>
            <p class="text-xs text-green-700">
              Transaction Hash: 
              <span class="font-mono break-all">{{ certificate.blockchain_transaction_hash }}</span>
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
interface Certificate {
  id: string
  certificate_number: string
  course?: {
    title: string
  }
  student?: {
    first_name: string
    last_name: string
  }
  final_score: number
  pdf_url?: string
  issued_at: string
  blockchain_transaction_hash?: string
}

interface Props {
  isOpen: boolean
  certificateId?: string
}

const props = defineProps<Props>()

const emit = defineEmits<{
  close: []
}>()

const certificate = ref<Certificate | null>(null)
const loading = ref(false)
const error = ref<string | null>(null)
const downloading = ref(false)
const showPdfFallback = ref(false)

const close = () => {
  emit('close')
  // Reset state when closing
  certificate.value = null
  error.value = null
  showPdfFallback.value = false
}

const fetchCertificate = async () => {
  if (!props.certificateId) return

  loading.value = true
  error.value = null
  showPdfFallback.value = false

  try {
    const config = useRuntimeConfig()
    const response = await $fetch(`/api/certificates/${props.certificateId}`, {
      baseURL: config.public.backendUrl as string,
      headers: {
        'Authorization': `Bearer ${useCookie('auth_token').value}`
      }
    }) as Certificate
    certificate.value = response
  } catch (err) {
    console.error('Failed to fetch certificate:', err)
    error.value = 'Failed to load certificate. Please try again.'
  } finally {
    loading.value = false
  }
}

const downloadCertificate = async () => {
  if (!certificate.value?.pdf_url) return

  downloading.value = true
  
  try {
    // Create a temporary link to download the PDF
    const link = document.createElement('a')
    link.href = certificate.value.pdf_url
    link.download = `certificate-${certificate.value.certificate_number}.pdf`
    link.target = '_blank'
    
    // Trigger download
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
  } catch (err) {
    console.error('Failed to download certificate:', err)
  } finally {
    downloading.value = false
  }
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

// Watch for certificateId changes and fetch certificate
watch(() => props.certificateId, (newId) => {
  if (newId && props.isOpen) {
    fetchCertificate()
  }
}, { immediate: true })

// Fetch certificate when modal opens
watch(() => props.isOpen, (isOpen) => {
  if (isOpen && props.certificateId) {
    fetchCertificate()
  }
})

// Handle escape key
onMounted(() => {
  const handleEscape = (e: KeyboardEvent) => {
    if (e.key === 'Escape' && props.isOpen) {
      close()
    }
  }
  
  document.addEventListener('keydown', handleEscape)
  
  onUnmounted(() => {
    document.removeEventListener('keydown', handleEscape)
  })
})
</script>