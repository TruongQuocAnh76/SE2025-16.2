<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
    <!-- Header -->
    <header class="bg-white/80 backdrop-blur-md border-b border-slate-200 sticky top-0 z-50">
      <div class="container mx-auto px-6 py-4">
        <div class="flex items-center justify-end">
          <NuxtLink 
            :to="paymentType === 'COURSE' ? '/courses' : '/membership'" 
            class="text-slate-600 hover:text-slate-900 transition flex items-center gap-2"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>{{ paymentType === 'COURSE' ? 'Back to Courses' : 'Back to Membership' }}</span>
          </NuxtLink>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <div class="container mx-auto px-6 py-12">
      <div class="max-w-6xl mx-auto">
        <div class="grid lg:grid-cols-3 gap-8">
          <!-- Left Side - Payment Form -->
          <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl shadow-xl p-8">
              <h2 class="text-2xl font-bold text-slate-900 mb-6">Checkout</h2>
              
              <!-- Card Type Selection -->
              <div class="mb-6">
                <label class="block text-sm font-medium text-slate-700 mb-3">Card Type</label>
                <div class="grid grid-cols-4 gap-3">
                  
                  <!-- Visa -->
                  <button
                    @click="selectedCardType = 'VISA'"
                    :class="[
                      'p-4 rounded-lg border-2 transition-all flex items-center justify-center',
                      selectedCardType === 'VISA'
                        ? 'border-blue-600 bg-blue-50'
                        : 'border-slate-200 hover:border-slate-300'
                    ]"
                  >
                    <svg class="w-10 h-6" viewBox="0 0 48 16" fill="none">
                      <path d="M17.1 14.8L19.7 1.2H23.3L20.7 14.8H17.1Z" fill="#1434CB"/>
                      <path d="M32.9 1.5C32.2 1.2 31.1 1 29.7 1C26.2 1 23.7 2.8 23.7 5.4C23.7 7.3 25.5 8.3 26.8 8.9C28.2 9.5 28.7 9.9 28.7 10.4C28.7 11.2 27.7 11.6 26.8 11.6C25.5 11.6 24.8 11.4 23.7 10.9L23.2 10.7L22.7 13.7C23.5 14.1 25 14.5 26.5 14.5C30.3 14.5 32.7 12.7 32.8 9.9C32.8 8.4 31.8 7.2 29.6 6.3C28.4 5.7 27.7 5.3 27.7 4.7C27.7 4.2 28.3 3.6 29.6 3.6C30.7 3.6 31.5 3.8 32.2 4.1L32.5 4.2L32.9 1.5Z" fill="#1434CB"/>
                    </svg>
                  </button>
                  
                  <!-- Mastercard -->
                  <button
                    @click="selectedCardType = 'MASTERCARD'"
                    :class="[
                      'p-4 rounded-lg border-2 transition-all flex items-center justify-center',
                      selectedCardType === 'MASTERCARD'
                        ? 'border-orange-500 bg-orange-50'
                        : 'border-slate-200 hover:border-slate-300'
                    ]"
                  >
                    <svg class="w-10 h-8" viewBox="0 0 48 32">
                      <circle cx="16" cy="16" r="14" fill="#EB001B"/>
                      <circle cx="32" cy="16" r="14" fill="#F79E1B"/>
                      <path d="M24 6.4c-2.8 2.4-4.5 5.9-4.5 9.6s1.7 7.2 4.5 9.6c2.8-2.4 4.5-5.9 4.5-9.6s-1.7-7.2-4.5-9.6z" fill="#FF5F00"/>
                    </svg>
                  </button>
                </div>
              </div>

              <!-- Stripe Elements Card Form -->
              <template v-if="selectedCardType === 'VISA' || selectedCardType === 'MASTERCARD'">
                <div class="space-y-4">
                  <div id="stripe-card-element" class="mb-4"></div>
                  <div id="stripe-card-errors" class="text-red-500 text-sm"></div>
                  <button
                    @click="handlePayment"
                    :disabled="loading"
                    class="w-full bg-gradient-to-r from-teal-400 to-teal-500 text-white font-semibold py-4 rounded-xl hover:shadow-lg transition-all disabled:opacity-50 mt-6"
                  >
                    <span v-if="loading">Processing...</span>
                    <span v-else>Thanh toán Visa/MasterCard</span>
                  </button>
                </div>
              </template>

            </div>
          </div>

          <!-- Right Side - Order Summary -->
          <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl shadow-xl p-6 sticky top-24">
              <h2 class="text-xl font-semibold text-slate-900 mb-6">Order Summary</h2>

              <!-- Course Payment -->
              <div v-if="paymentType === 'COURSE' && courseData" class="space-y-4">
                <div class="flex gap-4">
                  <img 
                    :src="courseData.thumbnail || '/placeholder-course.jpg'" 
                    alt="Course" 
                    class="w-20 h-20 rounded-lg object-cover"
                  >
                  <div>
                    <h3 class="font-semibold text-slate-900">{{ courseData.title }}</h3>
                    <p class="text-sm text-slate-500">{{ courseData.description?.substring(0, 60) }}...</p>
                  </div>
                </div>
                <div class="mt-4 space-y-2">
                  <div class="flex items-center gap-2 text-sm text-slate-700">
                    <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                    </svg>
                    <span>{{ courseData.modules?.length || 0 }} modules</span>
                  </div>
                  <div class="flex items-center gap-2 text-sm text-slate-700">
                    <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                    </svg>
                    <span>Lifetime access</span>
                  </div>
                  <div class="flex items-center gap-2 text-sm text-slate-700">
                    <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                    </svg>
                    <span>Blockchain Certificate</span>
                  </div>
                </div>
              </div>

              <!-- Membership Payment -->
              <div v-if="paymentType === 'MEMBERSHIP'" class="space-y-4">
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-4">
                  <div class="flex items-center gap-3 mb-2">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                    </svg>
                    <div>
                      <h3 class="font-bold text-slate-900">Premium Membership</h3>
                      <p class="text-sm text-slate-600">Monthly subscription</p>
                    </div>
                  </div>
                  <div class="mt-3 space-y-2">
                    <div class="flex items-center gap-2 text-sm text-slate-700">
                      <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                      </svg>
                      <span>Unlimited course access</span>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-slate-700">
                      <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                      </svg>
                      <span>Priority support</span>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-slate-700">
                      <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                      </svg>
                      <span>Exclusive content</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Price Breakdown -->
              <div class="mt-6 pt-6 border-t border-slate-200 space-y-3">
                <div class="flex justify-between text-slate-600">
                  <span>Subtotal</span>
                  <span>${{ totalAmount }}</span>
                </div>
                <div class="flex justify-between text-slate-600">
                  <span>Tax</span>
                  <span>$0.00</span>
                </div>
                <div class="flex justify-between text-xl font-bold text-slate-900 pt-3 border-t border-slate-200">
                  <span>Total</span>
                  <span>${{ totalAmount }}</span>
                </div>
              </div>

              <!-- Money Back Guarantee -->
              <div class="mt-6 bg-green-50 border border-green-200 rounded-xl p-4">
                <p class="text-sm text-green-900 font-medium">30-Day Money-Back Guarantee</p>
                <p class="text-xs text-green-700 mt-1">Full refund if not satisfied</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 mt-20">
      <div class="container mx-auto px-6 py-8">
        <div class="text-center text-slate-600">
          <p>&copy; 2025 CertChain. All rights reserved.</p>
        </div>
      </div>
    </footer>
  </div>
</template>


<script setup lang="ts">
import { loadStripe } from '@stripe/stripe-js'
import { onMounted, ref, computed } from 'vue'
import { useAuth } from '../../auth/composables/useAuth'

let elements: any = null;
let cardElement: any = null;

const route = useRoute()
const router = useRouter()
const config = useRuntimeConfig()

// Check authentication on page load
const { isAuthenticated } = useAuth()
onMounted(() => {
  if (!isAuthenticated.value) {
    router.replace({ path: '/auth/login', query: { redirect: route.fullPath } })
    return
  }
  loadCourseData()
  // Mount Stripe Elements nếu mặc định là VISA/MASTERCARD
  if (!STRIPE_TEST_MODE && (selectedCardType.value === 'VISA' || selectedCardType.value === 'MASTERCARD')) {
    nextTick().then(async () => {
      if (!stripeInstance) {
        stripeInstance = await loadStripe(config.public.stripePublishableKey)
      }
      if (stripeInstance) {
        elements = stripeInstance.elements()
        cardElement = elements.create('card')
        cardElement.mount('#stripe-card-element')
        cardElement.on('change', (event) => {
          const errorDiv = document.getElementById('stripe-card-errors')
          if (event.error) {
            errorDiv.textContent = event.error.message
          } else {
            errorDiv.textContent = ''
          }
        })
      }
    })
  }
})

// Test mode flag - set to false for production with real Stripe Elements
const STRIPE_TEST_MODE = false

// Payment data - use computed to make reactive
const paymentType = computed(() => route.query.type || 'MEMBERSHIP')
const courseId = computed(() => route.query.course_id || null)
const selectedCardType = ref('VISA')
const loading = ref(false)

// Stripe instance
let stripeInstance: any = null


// Stripe Elements chỉ mount khi chọn VISA/MASTERCARD

onMounted(() => {
  loadCourseData()
  // Mount Stripe Elements nếu mặc định là VISA/MASTERCARD
  if (!STRIPE_TEST_MODE && (selectedCardType.value === 'VISA' || selectedCardType.value === 'MASTERCARD')) {
    nextTick().then(async () => {
      if (!stripeInstance) {
        stripeInstance = await loadStripe(config.public.stripePublishableKey)
      }
      if (stripeInstance) {
        elements = stripeInstance.elements()
        cardElement = elements.create('card')
        cardElement.mount('#stripe-card-element')
        cardElement.on('change', (event) => {
          const errorDiv = document.getElementById('stripe-card-errors')
          if (event.error) {
            errorDiv.textContent = event.error.message
          } else {
            errorDiv.textContent = ''
          }
        })
      }
    })
  }
})

watch(selectedCardType, async (val, oldVal) => {
  if (!STRIPE_TEST_MODE && (val === 'VISA' || val === 'MASTERCARD')) {
    // Đợi DOM render xong
    await nextTick()
    if (!stripeInstance) {
      stripeInstance = await loadStripe(config.public.stripePublishableKey)
    }
    if (stripeInstance) {
      elements = stripeInstance.elements()
      cardElement = elements.create('card')
      cardElement.mount('#stripe-card-element')
      cardElement.on('change', (event: any) => {
        const errorDiv = document.getElementById('stripe-card-errors')
        if (event.error) {
          errorDiv.textContent = event.error.message
        } else {
          errorDiv.textContent = ''
        }
      })
    }
    console.log('Stripe initialized:', stripeInstance ? 'success' : 'failed')
  } else if (oldVal === 'VISA' || oldVal === 'MASTERCARD') {
    // Unmount Stripe Elements khi chuyển sang loại khác
    if (cardElement && cardElement.unmount) {
      cardElement.unmount()
      cardElement = null
    }
  }
})

// Card form data
const cardDetails = ref({
  name: '',
  number: '',
  expiry: '',
  cvc: ''
})
const saveInfo = ref(false)

// Course data (if payment is for course)
const courseData = ref(null)
const totalAmount = ref(0)

const loadCourseData = async () => {
  if (paymentType.value === 'COURSE' && courseId.value) {
    try {
      console.log('Loading course:', courseId.value)
      const response = await $fetch(`http://localhost:8000/api/courses/${courseId.value}`)
      console.log('Course response:', response)
      courseData.value = response.course // API returns { course: {...}, rating_counts: {...} }
      totalAmount.value = parseFloat(response.course.price) || 0
      console.log('Total amount:', totalAmount.value)
    } catch (error) {
      console.error('Failed to load course:', error)
    }
  } else if (paymentType.value === 'MEMBERSHIP') {
    totalAmount.value = 24.00 // Premium membership price
  }
}

// Watch for route changes
watch(() => route.query, () => {
  loadCourseData()
})

// Handle payment
const handlePayment = async () => {
  loading.value = true
  try {
    // Lấy token từ cookie thay vì localStorage
    const tokenCookie = useCookie('auth_token')
    const token = tokenCookie.value
    
    console.log('Token from cookie:', token) // Debug log
    
    if (!token) {
      alert('Please login first')
      router.push('/auth/login')
      loading.value = false
      return
    }

    // Map card type to payment method
    let paymentMethod = 'STRIPE'

    // Nếu là STRIPE thì xác thực qua Stripe Elements
    if (paymentMethod === 'STRIPE') {
      if (!stripeInstance || !cardElement) {
        alert('Stripe chưa được khởi tạo hoặc card element chưa sẵn sàng.')
        loading.value = false
        return
      }
      const { paymentMethod: stripePaymentMethod, error } = await stripeInstance.createPaymentMethod({
        type: 'card',
        card: cardElement,
      })
      if (error) {
        document.getElementById('stripe-card-errors').textContent = error.message
        loading.value = false
        return
      }
      // Tạo payment trên backend với payment_method_id
      const paymentData: any = {
        payment_type: paymentType.value,
        payment_method: paymentMethod,
        stripe_payment_method_id: stripePaymentMethod.id,
      }
      if (paymentType.value === 'COURSE') {
        paymentData.course_id = courseId.value
      } else {
        paymentData.membership_plan = 'PREMIUM'
      }
      
      console.log('Sending payment request with token:', token)
      
      const response = await $fetch('http://localhost:8000/api/payments/create', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: paymentData,
        credentials: 'include',
        onRequest({ request, options }) {
          // Debug log
          console.log('Request URL:', request)
          console.log('Headers:', options.headers)
          console.log('Token being sent:', token)
        },
        onResponseError({ response }) {
          console.error('Response error:', response.status, response.statusText)
          console.error('Response body:', response._data)
        }
      })
      console.log('Payment created:', response.payment)
      alert('Payment thành công!')
      // Redirect hoặc xử lý tiếp
      if (paymentType.value === 'COURSE' && courseId.value) {
        router.push(`/courses/${courseId.value}`)
      } else if (paymentType.value === 'MEMBERSHIP') {
        router.push('/membership')
      } else {
        router.push('/')
      }
    }
  } catch (error) {
    console.error('Payment failed:', error)
    alert('Payment failed. Please try lại.')
    loading.value = false
  }
}

// Handle Stripe payment with card
const handleStripePayment = async (paymentId: string) => {
  try {
    const tokenCookie = useCookie('auth_token')
    const token = tokenCookie.value

    if (STRIPE_TEST_MODE) {
      // TEST MODE: Auto-complete payment (no real Stripe API call)
      console.log('Processing Stripe payment (TEST MODE)...')
      console.log('Card details:', cardDetails.value)
      
      // Simulate payment processing
      await new Promise(resolve => setTimeout(resolve, 1500))
      
      // Mark as completed with test transaction ID
      const response = await $fetch(`http://localhost:8000/api/payments/${paymentId}/stripe/complete`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
        },
        body: {
          payment_intent_id: 'TEST_STRIPE_' + Date.now(),
        },
      })

      console.log('Stripe payment completed (TEST):', response)
      alert('Payment successful! (Test Mode)')
      
      // Redirect
      if (paymentType.value === 'COURSE' && courseId.value) {
        router.push(`/courses/${courseId.value}`)
      } else if (paymentType.value === 'MEMBERSHIP') {
        router.push('/membership')
      } else {
        router.push('/')
      }
  } else {
    // PRODUCTION MODE: Gửi dữ liệu thẻ lên backend để xử lý với Stripe PHP SDK
    try {
      console.log('Processing Stripe payment (PRODUCTION, direct card input)...')
      const paymentData = {
        payment_type: paymentType.value,
        payment_method: 'STRIPE',
        card: {
          name: cardDetails.value.name,
          number: cardDetails.value.number.replace(/\s/g, ''),
          expiry: cardDetails.value.expiry,
          cvc: cardDetails.value.cvc
        },
        course_id: paymentType.value === 'COURSE' ? courseId.value : undefined,
        membership_plan: paymentType.value === 'MEMBERSHIP' ? 'PREMIUM' : undefined
      }
      const tokenCookie = useCookie('auth_token')
      const token = tokenCookie.value
      const response = await $fetch('http://localhost:8000/api/payments/create', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
        },
        body: paymentData
      })
      if (response.success) {
        alert('Payment successful!')
        // ...redirect logic...
      } else {
        // Hiển thị lỗi chi tiết nếu có
        let errorMsg = response.message || response.error || response.errors || 'Unknown error';
        if (typeof errorMsg === 'object') errorMsg = JSON.stringify(errorMsg);
        alert('Payment failed: ' + errorMsg);
      }
    } catch (err) {
      console.error('Production Stripe flow error:', err)
      alert('Stripe payment failed. See console for details.')
    }
  }
  } catch (error) {
    console.error('Stripe payment failed:', error)
    alert('Stripe payment failed. Please try again.')
    throw error
  }
}

// Complete Stripe payment
const completeStripePayment = async (paymentId: string, paymentIntentId: string) => {
  try {
    const tokenCookie = useCookie('auth_token')
    const token = tokenCookie.value
    
    const response = await $fetch(`http://localhost:8000/api/payments/${paymentId}/stripe/complete`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
      },
      body: {
        payment_intent_id: paymentIntentId,
      },
    })

    console.log('Stripe payment completed:', response)
    alert('Payment successful! Thank you for your purchase.')
    
    // Redirect based on payment type
    if (paymentType.value === 'COURSE' && courseId.value) {
      router.push(`/courses/${courseId.value}`)
    } else if (paymentType.value === 'MEMBERSHIP') {
      router.push('/membership')
    } else {
      router.push('/')
    }
  } catch (error) {
    console.error('Failed to complete Stripe payment:', error)
    alert('Failed to complete payment')
  }
}

</script>
