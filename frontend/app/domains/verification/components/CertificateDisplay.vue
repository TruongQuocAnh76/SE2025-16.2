<template>
  <div class="w-full space-y-6">
    <div class="bg-white rounded-lg border border-gray-200 p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-h5 font-semibold text-text-dark">Certificate Information</h3>
        <div class="flex items-center space-x-2">
          <div
            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
            :class="statusBadgeClass"
          >
            <div class="w-2 h-2 rounded-full mr-2" :class="statusDotClass"></div>
            {{ statusText }}
          </div>
        </div>
      </div>

      <div class="grid md:grid-cols-2 gap-6">
        <!-- Certificate Details -->
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-text-muted mb-1">Certificate Number</label>
            <p class="text-body font-mono text-text-dark bg-gray-50 px-3 py-2 rounded-md">
              {{ result.certificate.certificate_number }}
            </p>
          </div>

          <div>
            <label class="block text-sm font-medium text-text-muted mb-1">Student Name</label>
            <p class="text-body text-text-dark">{{ result.certificate.student_name }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-text-muted mb-1">Course Title</label>
            <p class="text-body text-text-dark">{{ result.certificate.course_title }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-text-muted mb-1">Final Score</label>
            <p class="text-body text-text-dark">
              {{ result.certificate.final_score }}%
            </p>
          </div>
        </div>

        <!-- Blockchain & Status Details -->
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-text-muted mb-1">Issue Date</label>
            <p class="text-body text-text-dark">{{ formatDate(result.certificate.issued_date) }}</p>
          </div>

          <div v-if="result.expires_at">
            <label class="block text-sm font-medium text-text-muted mb-1">Expires</label>
            <p class="text-body text-text-dark">{{ formatDate(result.expires_at) }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-text-muted mb-1">Blockchain Status</label>
            <div class="flex items-center space-x-2">
              <div
                class="inline-flex items-center px-2 py-1 rounded text-xs font-medium"
                :class="blockchainStatusClass"
              >
                {{ result.certificate.blockchain_status }}
              </div>
            </div>
          </div>

          <div v-if="result.certificate.transaction_hash">
            <label class="block text-sm font-medium text-text-muted mb-1">Transaction Hash</label>
            <div class="flex items-center space-x-2">
              <p class="text-body font-mono text-text-dark bg-gray-50 px-3 py-2 rounded-md text-sm break-all">
                {{ truncateHash(result.certificate.transaction_hash) }}
              </p>
              <button
                @click="copyToClipboard(result.certificate.transaction_hash)"
                class="p-2 hover:bg-gray-100 rounded-md transition-colors"
                title="Copy transaction hash"
              >
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                        d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Verification Messages -->
      <div v-if="verificationMessages.length > 0" class="mt-6 space-y-3">
        <h4 class="text-body font-semibold text-text-dark">Verification Details:</h4>
        <div class="space-y-2">
          <div
            v-for="message in verificationMessages"
            :key="message.type"
            class="flex items-start space-x-3 p-3 rounded-lg"
            :class="message.className"
          >
            <div class="flex-shrink-0 mt-0.5">
              <component :is="message.icon" class="w-5 h-5" />
            </div>
            <div>
              <p class="font-medium">{{ message.title }}</p>
              <p class="text-sm opacity-90">{{ message.description }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- PDF Download -->
      <div v-if="result.certificate.pdf_url" class="mt-6 pt-4 border-t border-gray-200">
        <a
          :href="result.certificate.pdf_url"
          target="_blank"
          rel="noopener noreferrer"
          class="inline-flex items-center px-4 py-2 bg-brand-primary hover:bg-brand-secondary text-white rounded-lg transition-colors"
        >
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                  d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          Download Certificate PDF
        </a>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { VerificationResult } from '../types'

interface Props {
  result: VerificationResult
}

const props = defineProps<Props>()

// Status badge classes
const statusBadgeClass = computed(() => {
  if (props.result.is_revoked) {
    return 'bg-red-100 text-red-800'
  }
  if (props.result.is_valid) {
    return 'bg-green-100 text-green-800'
  }
  return 'bg-red-100 text-red-800'
})

const statusDotClass = computed(() => {
  if (props.result.is_revoked) {
    return 'bg-red-500'
  }
  if (props.result.is_valid) {
    return 'bg-green-500'
  }
  return 'bg-red-500'
})

const statusText = computed(() => {
  if (props.result.is_revoked) {
    return 'Revoked'
  }
  if (props.result.is_valid) {
    return 'Valid'
  }
  return 'Invalid'
})

// Blockchain status classes
const blockchainStatusClass = computed(() => {
  const status = props.result.certificate.blockchain_status?.toLowerCase()
  switch (status) {
    case 'confirmed':
      return 'bg-green-100 text-green-800'
    case 'pending':
      return 'bg-yellow-100 text-yellow-800'
    case 'failed':
      return 'bg-red-100 text-red-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
})

// Verification messages
const verificationMessages = computed(() => {
  const messages: Array<{
    type: string
    title: string
    description: string
    icon: string
    className: string
  }> = []

  if (props.result.is_valid && !props.result.is_revoked) {
    messages.push({
      type: 'valid',
      title: 'Certificate Verified',
      description: 'This certificate is valid and has been successfully verified on the blockchain.',
      icon: 'CheckCircleIcon',
      className: 'bg-green-50 text-green-800'
    })
  }

  if (props.result.is_revoked) {
    messages.push({
      type: 'revoked',
      title: 'Certificate Revoked',
      description: 'This certificate has been revoked and is no longer valid.',
      icon: 'XCircleIcon',
      className: 'bg-red-50 text-red-800'
    })
  }

  if (!props.result.is_valid && !props.result.is_revoked) {
    messages.push({
      type: 'invalid',
      title: 'Invalid Certificate',
      description: 'This certificate could not be verified or may have been tampered with.',
      icon: 'ExclamationTriangleIcon',
      className: 'bg-red-50 text-red-800'
    })
  }

  return messages
})

// Utility functions
const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const truncateHash = (hash: string, start = 10, end = 10) => {
  if (hash.length <= start + end) return hash
  return `${hash.slice(0, start)}...${hash.slice(-end)}`
}

const copyToClipboard = async (text: string) => {
  try {
    await navigator.clipboard.writeText(text)
    // You could add a toast notification here
  } catch (err) {
    console.error('Failed to copy text: ', err)
  }
}

// Icons (these would normally be imported from a proper icon library)
const CheckCircleIcon = 'svg'
const XCircleIcon = 'svg' 
const ExclamationTriangleIcon = 'svg'
</script>