<template>
  <div class="flex flex-col items-center justify-center min-h-screen bg-gray-50">
    <div class="w-full max-w-md p-8 bg-white rounded shadow">
      <h2 class="mb-6 text-2xl font-bold text-center">Quên mật khẩu</h2>
      <form @submit.prevent="submitEmail">
        <label class="block mb-2 text-sm font-medium">Email</label>
        <input v-model="email" type="email" required class="w-full px-3 py-2 mb-4 border rounded" placeholder="Nhập email của bạn" />
        <button type="submit" :disabled="loading" class="w-full py-2 font-semibold text-white bg-blue-600 rounded hover:bg-blue-700">
          {{ loading ? 'Đang gửi...' : 'Gửi yêu cầu' }}
        </button>
      </form>
      <div v-if="message" class="mt-4 text-green-600">{{ message }}</div>
      <div v-if="error" class="mt-4 text-red-600">{{ error }}</div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ layout: false })
import { ref } from 'vue'
const config = useRuntimeConfig()

const email = ref('')
const loading = ref(false)
const message = ref('')
const error = ref('')

const submitEmail = async () => {
  loading.value = true
  message.value = ''
  error.value = ''
  try {
    const res = await fetch(config.public.backendUrl + '/api/auth/forgot-password', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ email: email.value })
    })
    if (!res.ok) {
      const data = await res.json().catch(() => ({}))
      throw new Error(data?.message || 'Có lỗi xảy ra, vui lòng thử lại.')
    }
    message.value = 'Vui lòng kiểm tra email để lấy link đặt lại mật khẩu.'
  } catch (e: any) {
    error.value = e?.message || 'Có lỗi xảy ra, vui lòng thử lại.'
  } finally {
    loading.value = false
  }
}
</script>
