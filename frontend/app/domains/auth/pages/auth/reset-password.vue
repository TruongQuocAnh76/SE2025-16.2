<template>
  <div class="flex flex-col items-center justify-center min-h-screen bg-gray-50">
    <div class="bg-white p-8 rounded shadow w-full max-w-md">
      <h2 class="text-2xl font-bold mb-4 text-center">Đặt lại mật khẩu</h2>
      <form @submit.prevent="submit">
        <div class="mb-4">
          <label class="block mb-1 font-medium">Mật khẩu mới</label>
          <input v-model="password" type="password" class="w-full border rounded px-3 py-2" required />
        </div>
        <div class="mb-4">
          <label class="block mb-1 font-medium">Xác nhận mật khẩu</label>
          <input v-model="confirmPassword" type="password" class="w-full border rounded px-3 py-2" required />
        </div>
        <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded font-semibold">Đặt lại mật khẩu</button>
      </form>
      <div v-if="error" class="text-red-500 mt-4 text-center">{{ error }}</div>
      <div v-if="success" class="text-green-500 mt-4 text-center">{{ success }}</div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRoute } from 'vue-router'
const config = useRuntimeConfig()

const route = useRoute()
const token = route.query.token as string
const password = ref('')
const confirmPassword = ref('')
const error = ref('')
const success = ref('')

const submit = async () => {
  error.value = ''
  success.value = ''
  if (password.value !== confirmPassword.value) {
    error.value = 'Mật khẩu xác nhận không khớp.'
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
    success.value = 'Đặt lại mật khẩu thành công! Bạn có thể đăng nhập.'
  } catch (e: any) {
    error.value = e?.message || 'Có lỗi xảy ra, vui lòng thử lại.'
  }
}
</script>

<style scoped>
body {
  background: #f9fafb;
}
</style>
