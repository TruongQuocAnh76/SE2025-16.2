<template>
  <form @submit.prevent="updatePassword">
    <div class="shadow sm:rounded-md sm:overflow-hidden">
      <div class="bg-white py-6 px-4 space-y-6 sm:p-6">
        <div>
          <h3 class="text-lg leading-6 font-medium text-gray-900">Security</h3>
          <p class="mt-1 text-sm text-gray-500">Update your password to keep your account secure.</p>
        </div>

        <!-- Social Login Info Banner -->
        <div v-if="isSocialLogin" class="rounded-md bg-blue-50 p-4">
          <div class="flex">
            <div class="flex-shrink-0">
              <i class="fas fa-info-circle text-blue-400"></i>
            </div>
            <div class="ml-3 flex-1 md:flex md:justify-between">
              <p class="text-sm text-blue-700">
                You are currently logged in for <strong>{{ providerName }}</strong>.
                You can set a password to check in via Email and Password.
              </p>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-6 gap-6">
          <div v-if="hasPassword" class="col-span-6 sm:col-span-4">
            <label for="current_password" class="block text-sm font-medium text-gray-700">Current Password</label>
            <input
              type="password"
              id="current_password"
              v-model="form.current_password"
              autocomplete="current-password"
              class="mt-1 focus:ring-brand-primary focus:border-brand-primary block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
            />
          </div>

          <div class="col-span-6 sm:col-span-4">
            <label for="password" class="block text-sm font-medium text-gray-700">
              {{ !hasPassword ? 'New Password' : 'New Password' }}
            </label>
            <input
              type="password"
              id="password"
              v-model="form.password"
              autocomplete="new-password"
              class="mt-1 focus:ring-brand-primary focus:border-brand-primary block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
            />
          </div>

          <div class="col-span-6 sm:col-span-4">
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
            <input
              type="password"
              id="password_confirmation"
              v-model="form.password_confirmation"
              autocomplete="new-password"
              class="mt-1 focus:ring-brand-primary focus:border-brand-primary block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
            />
          </div>
        </div>
      </div>
      <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
        <button
          type="submit"
          :disabled="isLoading || !isFormValid"
          class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-brand-primary hover:bg-brand-secondary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-primary disabled:opacity-50"
        >
          <svg v-if="isLoading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          {{ isLoading ? 'Saving...' : (!hasPassword ? 'Set Password' : 'Change Password') }}
        </button>
      </div>
    </div>
  </form>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useAuth } from '~/domains/auth/composables/useAuth'

const { user, getUser } = useAuth()
const config = useRuntimeConfig()
const isLoading = ref(false)
const { $toast } = useNuxtApp()

const form = ref({
  current_password: '',
  password: '',
  password_confirmation: ''
})

const hasPassword = computed(() => {
  return user.value?.has_password
})

const isSocialLogin = computed(() => {
  // Only purely social login users (no password set) get special treatment
  return user.value?.auth_provider && user.value.auth_provider !== 'EMAIL' && !hasPassword.value
})

const providerName = computed(() => {
  if (!user.value?.auth_provider) return ''
  // Capitalize first letter logic or specific mapping
  const provider = user.value.auth_provider
  return provider.charAt(0).toUpperCase() + provider.slice(1).toLowerCase()
})

const isFormValid = computed(() => {
  const isPasswordValid = form.value.password.length >= 6 && form.value.password === form.value.password_confirmation
  
  if (!hasPassword.value) {
    return isPasswordValid
  }
  
  // If user has password, require current password
  return isPasswordValid && form.value.current_password.length > 0
})

const updatePassword = async () => {
  if (!user.value?.id) return
  if (!isFormValid.value) {
    if (form.value.password.length < 6) {
      $toast.error('Password must be at least 6 characters')
    } else if (form.value.password !== form.value.password_confirmation) {
      $toast.error('Passwords do not match')
    } else if (hasPassword.value && !form.value.current_password) {
      $toast.error('Current password is required')
    }
    return
  }
// ... continuing logic update
  isLoading.value = true
  try {
    const token = useCookie('auth_token').value
    
    // Call API directly
    const response = await $fetch(`${config.public.backendUrl}/api/users/${user.value.id}`, {
      method: 'PUT',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        password: form.value.password,
        current_password: hasPassword.value ? form.value.current_password : undefined
      })
    })

    const msg = !hasPassword.value 
      ? 'Password set successfully. You can now login with email.' 
      : 'Password changed successfully'
    $toast.success(msg)
    
    // Refresh user data to update has_password status if it was false
    await getUser()

    form.value.current_password = ''
    form.value.password = ''
    form.value.password_confirmation = ''
  } catch (error: any) {
    console.error('Update failed', error)
    const message = error.data?.message || error.data?.error || 'Failed to update password'
    $toast.error(message)
  } finally {
    isLoading.value = false
  }
}
</script>
