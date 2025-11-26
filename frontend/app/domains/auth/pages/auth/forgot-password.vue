<template>
  <div class="flex flex-col items-center justify-center min-h-screen bg-gray-50">
    <div class="w-full max-w-md p-8 bg-white rounded shadow">
      <h2 class="mb-6 text-2xl font-bold text-center">{{ hasToken ? 'Đặt lại mật khẩu' : 'Quên mật khẩu' }}</h2>
      <form v-if="!hasToken" @submit.prevent="submitEmail">
        <label class="block mb-2 text-sm font-medium">Email</label>
        <input v-model="email" type="email" required class="w-full px-3 py-2 mb-4 border rounded" placeholder="Nhập email của bạn" />
        <button type="submit" :disabled="loading" class="w-full py-2 font-semibold text-white bg-blue-600 rounded hover:bg-blue-700">
          {{ loading ? 'Đang gửi...' : 'Gửi yêu cầu' }}
        </button>
      </form>
      <form v-else @submit.prevent="submitReset">
        <label class="block mb-2 text-sm font-medium">Mật khẩu mới</label>
        <input v-model="password" type="password" required class="w-full px-3 py-2 mb-4 border rounded" placeholder="Nhập mật khẩu mới" />
        <label class="block mb-2 text-sm font-medium">Xác nhận mật khẩu</label>
        <input v-model="confirmPassword" type="password" required class="w-full px-3 py-2 mb-4 border rounded" placeholder="Xác nhận mật khẩu mới" />
        <button type="submit" :disabled="loading" class="w-full py-2 font-semibold text-white bg-blue-600 rounded hover:bg-blue-700">
          {{ loading ? 'Đang đặt lại...' : 'Đặt lại mật khẩu' }}
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
import { useRoute, useRouter } from 'vue-router'
const config = useRuntimeConfig()
const router = useRouter()

const route = useRoute()
const token = route.query.token as string | undefined
const hasToken = !!token

const email = ref('')
const password = ref('')
const confirmPassword = ref('')
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

const submitReset = async () => {
  loading.value = true
  message.value = ''
  error.value = ''
  if (password.value !== confirmPassword.value) {
    error.value = 'Mật khẩu xác nhận không khớp.'
    loading.value = false
    return
  }
  try {
    const res = await fetch(config.public.backendUrl + '/api/auth/reset-password', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ token, password: password.value })
    })
    if (!res.ok) {
      const data = await res.json().catch(() => ({}))
      throw new Error(data?.message || 'Có lỗi xảy ra, vui lòng thử lại.')
    }
    message.value = 'Đặt lại mật khẩu thành công! Đang chuyển hướng...'
    setTimeout(() => {
      router.push('/auth/signin')
    }, 1500)
  } catch (e: any) {
    error.value = e?.message || 'Có lỗi xảy ra, vui lòng thử lại.'
  } finally {
    loading.value = false
  }
}
</script>
