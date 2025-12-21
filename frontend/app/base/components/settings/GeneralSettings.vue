<template>
  <form @submit.prevent="updateProfile">
    <div class="shadow sm:rounded-md sm:overflow-hidden">
      <div class="bg-white py-6 px-4 space-y-6 sm:p-6">
        <div>
          <h3 class="text-lg leading-6 font-medium text-gray-900">Personal Information</h3>
        </div>

        <div class="grid grid-cols-6 gap-6">
          <div class="col-span-6">
            <label class="block text-sm font-medium text-gray-700">Photo</label>
            <div class="mt-1 flex items-center">
              <span class="inline-block h-12 w-12 rounded-full overflow-hidden bg-gray-100 relative group">
                <img v-if="form.avatar" :src="form.avatar" alt="Avatar" class="h-full w-full object-cover">
                <svg v-else class="h-full w-full text-gray-300" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <!-- Loading Overlay -->
                <div v-if="isUploading" class="absolute inset-0 bg-black/50 flex items-center justify-center">
                  <svg class="animate-spin h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                </div>
              </span>
              <div class="ml-5 rounded-md shadow-sm sm:flex">
                <input
                  type="file"
                  ref="fileInput"
                  class="hidden"
                  accept="image/*"
                  @change="handleFileUpload"
                />
                <button
                  type="button"
                  @click="triggerFileInput"
                  class="bg-white py-2 px-3 border border-gray-300 rounded-md shadow-sm text-sm leading-4 font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-primary"
                >
                  Change Photo
                </button>
              </div>
            </div>
            <p class="mt-2 text-sm text-gray-500">
              JPG, GIF or PNG. Max 2MB.
            </p>
          </div>

          <div class="col-span-6 sm:col-span-4">
            <label for="username" class="block text-sm font-medium text-gray-700">Username</label>

              <input
                type="text"
                id="username"
                v-model="form.username"
                disabled
                class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md bg-gray-50 text-gray-500 cursor-not-allowed"
              />
          </div>

          <div class="col-span-6 sm:col-span-2">
            <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
            <input
              type="text"
              id="role"
              v-model="form.role"
              disabled
              class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md bg-gray-50 text-gray-500 cursor-not-allowed"
            />
          </div>

          <div class="col-span-6 sm:col-span-3">
            <label for="first-name" class="block text-sm font-medium text-gray-700">First name</label>
            <input
              type="text"
              id="first-name"
              v-model="form.first_name"
              autocomplete="given-name"
              class="mt-1 focus:ring-brand-primary focus:border-brand-primary block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
            />
          </div>


          <div class="col-span-6 sm:col-span-3">
            <label for="last-name" class="block text-sm font-medium text-gray-700">Last name</label>
            <input
              type="text"
              id="last-name"
              v-model="form.last_name"
              autocomplete="family-name"
              class="mt-1 focus:ring-brand-primary focus:border-brand-primary block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
            />
          </div>

          <div class="col-span-6 sm:col-span-4">
            <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
            <input
              type="text"
              id="email"
              v-model="form.email"
              disabled
              class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md bg-gray-50 text-gray-500 cursor-not-allowed"
            />
          </div>


          <div class="col-span-6">
            <label for="bio" class="block text-sm font-medium text-gray-700">Bio</label>
            <div class="mt-1">
              <textarea
                id="bio"
                v-model="form.bio"
                rows="3"
                class="shadow-sm focus:ring-brand-primary focus:border-brand-primary mt-1 block w-full sm:text-sm border border-gray-300 rounded-md"
                placeholder="Brief description for your profile."
              ></textarea>
            </div>
          </div>
        </div>
      </div>
      <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
        <button
          type="submit"
          :disabled="isLoading || isUploading"
          class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-brand-primary hover:bg-brand-secondary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-primary disabled:opacity-50"
        >
          <svg v-if="isLoading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          {{ isLoading ? 'Saving...' : 'Save' }}
        </button>
      </div>
    </div>
  </form>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { useAuth } from '~/domains/auth/composables/useAuth'
import { useMedia } from '~/composables/useMedia'

const { user, getUser } = useAuth()
const { uploadFile } = useMedia()
const config = useRuntimeConfig()
const isLoading = ref(false)
const isUploading = ref(false)
const { $toast } = useNuxtApp()
const fileInput = ref<HTMLInputElement | null>(null)

const form = ref({
  username: '',
  role: '',
  first_name: '',
  last_name: '',
  email: '',
  bio: '',
  avatar: ''
})

const initForm = () => {
  if (user.value) {
    form.value = {
      username: user.value.username || '',
      role: user.value.role || '',
      first_name: user.value.first_name || '',
      last_name: user.value.last_name || '',
      email: user.value.email || '',
      bio: user.value.bio || '',
      avatar: user.value.avatar || ''
    }
  }
}

onMounted(() => {
  initForm()
})

// Watch for user changes to keep form in sync (e.g. after refresh)
watch(user, () => {
  initForm()
})

const triggerFileInput = () => {
  fileInput.value?.click()
}

const handleFileUpload = async (event: Event) => {
  const input = event.target as HTMLInputElement
  if (!input.files || input.files.length === 0) return

  const file = input.files[0]
  if (!file) return
  
  // Basic validation (size < 2MB)
  if (file.size > 2 * 1024 * 1024) {
    $toast.error('File size must be less than 2MB')
    return
  }

  // Validate file type
  if (!['image/jpeg', 'image/png', 'image/gif'].includes(file.type)) {
    $toast.error('Only JPG, PNG and GIF files are allowed')
    return
  }

  isUploading.value = true

  try {
    // Use composable to handle upload via presigned URL
    const { url } = await uploadFile(file, 'avatars')

    if (url) {
      form.value.avatar = url
      $toast.success('Avatar uploaded successfully. Click Save to apply changes.')
    }
  } catch (error: any) {
    console.error('Upload failed', error)
    const message = error.message || 'Failed to upload image'
    $toast.error(message)
  } finally {
    isUploading.value = false
    // Reset input so same file can be selected again if needed
    if (fileInput.value) fileInput.value.value = ''
  }
}

const updateProfile = async () => {
  if (!user.value?.id) return

  isLoading.value = true
  try {
    const token = useCookie('auth_token').value
    
    const response = await $fetch(`${config.public.backendUrl}/api/users/${user.value.id}`, {
      method: 'PUT',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(form.value)
    })

    // Update local user state
    await getUser()
    
    $toast.success('Profile updated successfully')
  } catch (error: any) {
    console.error('Update failed', error)
    const message = error.data?.message || 'Failed to update profile'
    $toast.error(message)
  } finally {
    isLoading.value = false
  }
}
</script>
