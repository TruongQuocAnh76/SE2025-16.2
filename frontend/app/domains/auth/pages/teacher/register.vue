<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto">
      <!-- Header -->
      <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-2">Become a Teacher</h1>
        <p class="text-lg text-gray-600">Share your knowledge with students around the world</p>
      </div>

      <!-- Form -->
      <div class="bg-white rounded-2xl shadow-xl p-8">
        <form @submit.prevent="handleSubmit" class="space-y-6">
          
          <!-- Personal Information Section -->
          <div class="border-b border-gray-200 pb-6">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">Personal Information</h2>
            
            <!-- Name & Email -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Full Name <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.full_name"
                  type="text"
                  required
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="John Doe"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Email <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.email"
                  type="email"
                  required
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="john@example.com"
                />
              </div>
            </div>

            <!-- Gender & Phone -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Gender</label>
                <select
                  v-model="form.gender"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                  <option value="">Select Gender</option>
                  <option value="MALE">Male</option>
                  <option value="FEMALE">Female</option>
                  <option value="OTHER">Other</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                <input
                  v-model="form.phone"
                  type="tel"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="+1234567890"
                />
              </div>
            </div>

            <!-- Date of Birth & Country -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date of Birth</label>
                <input
                  v-model="form.date_of_birth"
                  type="date"
                  :max="today"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Country</label>
                <input
                  v-model="form.country"
                  type="text"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="United States"
                />
              </div>
            </div>

            <!-- Bio -->
            <div class="mt-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Bio / About You
              </label>
              <textarea
                v-model="form.bio"
                rows="4"
                maxlength="1000"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                placeholder="Tell us about your teaching experience and expertise..."
              ></textarea>
              <p class="text-sm text-gray-500 mt-1">{{ form.bio?.length || 0 }}/1000 characters</p>
            </div>
          </div>

          <!-- Certificate Information Section -->
          <div class="border-b border-gray-200 pb-6">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">Certificate Information</h2>
            
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Certificate Title <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.certificate_title"
                  type="text"
                  required
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="Master of Computer Science"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Issuer / Institution <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.issuer"
                  type="text"
                  required
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="Stanford University"
                />
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Issue Date <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="form.issue_date"
                    type="date"
                    required
                    :max="today"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Expiry Date (Optional)
                  </label>
                  <input
                    v-model="form.expiry_date"
                    type="date"
                    :min="form.issue_date"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>
              </div>

              <!-- Certificate File Upload -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Certificate Document <span class="text-red-500">*</span>
                </label>
                <div
                  @click="triggerCertificateUpload"
                  @drop.prevent="handleCertificateDrop"
                  @dragover.prevent
                  class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-blue-500 transition-colors cursor-pointer"
                >
                  <input
                    ref="certificateInput"
                    type="file"
                    accept=".pdf,.jpg,.jpeg,.png"
                    @change="handleCertificateChange"
                    class="hidden"
                  />
                  <div v-if="certificateFile">
                    <div class="flex items-center justify-center space-x-2">
                      <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                      </svg>
                      <div class="text-left">
                        <p class="text-sm font-medium text-gray-900">{{ certificateFile.name }}</p>
                        <p class="text-xs text-gray-500">{{ (certificateFile.size / 1024).toFixed(2) }} KB</p>
                      </div>
                      <button
                        type="button"
                        @click.stop="removeCertificate"
                        class="text-red-500 hover:text-red-700"
                      >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                      </button>
                    </div>
                  </div>
                  <div v-else>
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    <p class="mt-2 text-sm text-gray-600">
                      <span class="font-semibold">Click to upload</span> or drag and drop
                    </p>
                    <p class="text-xs text-gray-500 mt-1">PDF, JPG, PNG up to 10MB</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Messages -->
          <div v-if="errorMessage" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
            {{ errorMessage }}
          </div>

          <div v-if="successMessage" class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
            {{ successMessage }}
          </div>

          <!-- Submit Button -->
          <div class="flex gap-4">
            <button
              type="button"
              @click="router.push('/')"
              class="flex-1 px-6 py-3 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition-colors"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="loading"
              class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-lg hover:from-blue-700 hover:to-purple-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="loading">Submitting...</span>
              <span v-else>Submit Application</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'

const router = useRouter()
const config = useRuntimeConfig()

const form = ref({
  // Personal Information
  full_name: '',
  email: '',
  bio: '',
  gender: '',
  phone: '',
  date_of_birth: '',
  country: '',
  avatar_url: '',
  // Certificate Information
  certificate_title: '',
  issuer: '',
  issue_date: '',
  expiry_date: ''
})

const certificateInput = ref<HTMLInputElement | null>(null)
const certificateFile = ref<File | null>(null)

const loading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

const today = computed(() => {
  const date = new Date()
  return date.toISOString().split('T')[0]
})

// Certificate Upload Handlers
const triggerCertificateUpload = () => {
  certificateInput.value?.click()
}

const handleCertificateChange = (event: Event) => {
  errorMessage.value = '' // Clear previous errors
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  if (file) {
    // Validate file size (max 10MB)
    if (file.size > 10 * 1024 * 1024) {
      errorMessage.value = 'Certificate file must be less than 10MB'
      return
    }
    certificateFile.value = file
  }
}

const handleCertificateDrop = (event: DragEvent) => {
  errorMessage.value = '' // Clear previous errors
  const file = event.dataTransfer?.files[0]
  if (file) {
    // Validate file size (max 10MB)
    if (file.size > 10 * 1024 * 1024) {
      errorMessage.value = 'Certificate file must be less than 10MB'
      return
    }
    certificateFile.value = file
  }
}

const removeCertificate = () => {
  certificateFile.value = null
  if (certificateInput.value) {
    certificateInput.value.value = ''
  }
}

const handleSubmit = async () => {
  errorMessage.value = ''
  successMessage.value = ''
  loading.value = true

  try {
    const token = useCookie('auth_token').value

    if (!token) {
      errorMessage.value = 'Please login to submit teacher application'
      loading.value = false
      return
    }

    // Validate certificate file is selected
    if (!certificateFile.value) {
      errorMessage.value = 'Please upload your certificate document'
      loading.value = false
      return
    }

    // Debug: Check certificate file
    console.log('Certificate file object:', certificateFile.value)
    console.log('Certificate file name:', certificateFile.value?.name)
    console.log('Certificate file size:', certificateFile.value?.size)
    console.log('Certificate file type:', certificateFile.value?.type)

    // Create FormData to handle file upload
    const formData = new FormData()
    
    // Personal Information
    formData.append('full_name', form.value.full_name)
    formData.append('email', form.value.email)
    if (form.value.bio) formData.append('bio', form.value.bio)
    if (form.value.gender) formData.append('gender', form.value.gender)
    if (form.value.phone) formData.append('phone', form.value.phone)
    if (form.value.date_of_birth) formData.append('date_of_birth', form.value.date_of_birth)
    if (form.value.country) formData.append('country', form.value.country)
    if (form.value.avatar_url) formData.append('avatar_url', form.value.avatar_url)
    
    // Certificate Information
    formData.append('certificate_title', form.value.certificate_title)
    formData.append('issuer', form.value.issuer)
    formData.append('issue_date', form.value.issue_date)
    if (form.value.expiry_date) formData.append('expiry_date', form.value.expiry_date)
    
    // Certificate File - make sure it's a File object
    if (certificateFile.value instanceof File) {
      formData.append('certificate_file', certificateFile.value, certificateFile.value.name)
      console.log('✓ Certificate file appended to FormData')
    } else {
      console.error('✗ Certificate file is not a File object:', typeof certificateFile.value)
      errorMessage.value = 'Invalid certificate file. Please select again.'
      loading.value = false
      return
    }

    // Debug: Log FormData entries
    console.log('FormData entries:')
    for (const [key, value] of formData.entries()) {
      if (value instanceof File) {
        console.log(`  ${key}: File(${value.name}, ${value.size} bytes)`)
      } else {
        console.log(`  ${key}: ${value}`)
      }
    }

    // Submit application with FormData
    const response = await $fetch('/api/teacher-applications/submit', {
      baseURL: config.public.backendUrl as string,
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`
        // Do NOT set Content-Type header - browser will set it automatically with boundary for multipart/form-data
      },
      body: formData
    })

    successMessage.value = 'Application submitted successfully! We will review it and notify you soon.'
    
    // Redirect to home page after 2 seconds
    setTimeout(() => {
      router.push('/')
    }, 2000)

  } catch (error: any) {
    console.error('Submit error:', error)
    console.error('Error data:', error.data)
    console.error('Error response:', error.response)
    
    // Prioritize error.data.error first (specific backend error message)
    if (error.data?.error) {
      errorMessage.value = error.data.error
    } else if (error.data?.errors) {
      // Display validation errors if available
      const errors = Object.values(error.data.errors).flat()
      errorMessage.value = errors.join(', ')
    } else if (error.data?.message) {
      errorMessage.value = error.data.message
    } else if (error.message) {
      errorMessage.value = error.message
    } else if (error.errors) {
      errorMessage.value = error.errors
    } else {
      errorMessage.value = 'Failed to submit application. Please try again.'
    }
  } finally {
    loading.value = false
  }
}

// SEO
useHead({
  title: 'Teacher Registration - CertChain',
  meta: [
    { name: 'description', content: 'Apply to become a teacher on CertChain platform' }
  ]
})
</script>
