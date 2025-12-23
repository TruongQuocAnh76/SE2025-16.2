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
        
        <!-- Loading state while checking enrollment -->
        <div v-if="checkingEnrollment" class="text-center py-20">
          <div class="animate-spin rounded-full h-16 w-16 border-b-4 border-blue-600 mx-auto mb-4"></div>
          <p class="text-slate-600">Checking enrollment status...</p>
        </div>

        <!-- Already enrolled message -->
        <div v-else-if="isAlreadyEnrolled" class="text-center py-20">
          <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg max-w-md mx-auto">
            <h3 class="font-bold text-lg mb-2">Already Enrolled</h3>
            <p>You are already enrolled in this course. Redirecting you to the course page...</p>
          </div>
        </div>

        <!-- Payment form (only show if not enrolled) -->
        <div v-else class="grid lg:grid-cols-3 gap-8">
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

                  <!-- PayPal -->
                  <button
                    @click="selectedCardType = 'PAYPAL'"
                    :class="[
                      'p-4 rounded-lg border-2 transition-all flex items-center justify-center',
                      selectedCardType === 'PAYPAL'
                        ? 'border-blue-500 bg-blue-50'
                        : 'border-slate-200 hover:border-slate-300'
                    ]"
                  >
                    <svg class="w-10 h-8" viewBox="0 0 24 24" fill="none">
                        <path d="M18.8 6.5C18.4 4.1 16.4 2.5 14 2.5H6.5C5.8 2.5 5.2 3 5.1 3.7L3 17.2C2.9 17.6 3.2 18 3.6 18H7.2L7.7 21.2C7.8 21.7 8.2 22 8.7 22H13C13.5 22 13.9 21.7 14 21.2L14.2 19.8L14.3 19.3L14.4 18.5C14.4 18.5 17.3 18.2 18.8 14.8C18.8 14.8 21.2 11.2 18.8 6.5Z" fill="#003087"/>
                        <path d="M8.9 17.9C9 17.7 9.2 17.5 9.4 17.5H9.9H9.6C9.4 17.5 9.2 17.7 9.2 17.9L8.7 20.8C8.7 21 8.6 21.1 8.4 21.1H6.2C5.9 21.1 5.7 20.9 5.8 20.6L6.2 18.4L6.7 15.6C6.7 15.3 6.5 15.1 6.3 15.1H2.9L3.8 3.8C3.8 3.4 4.2 3.1 4.6 3.1H9.1C12 3.1 15.2 3.1 15.9 7.5C16.1 8.8 15.9 9.9 15.2 10.7C15.7 11.4 15.9 12.2 15.7 13.2C15.4 15.1 14.3 16.1 12.4 16.1H10.2C9.9 16.1 9.7 16.3 9.7 16.6L8.9 17.9Z" fill="#009CDE"/>
                    </svg>
                    <span class="ml-2 font-bold text-slate-700">PayPal</span>
                  </button>
                </div>
              </div>

              <!-- PayPal Buttons Container -->
              <div v-show="selectedCardType === 'PAYPAL'" class="w-full mb-6">
                  <div id="paypal-button-container" class="w-full mt-4"></div>
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
                  <div class="text-right">
                    <template v-if="paymentType === 'COURSE' && discountPercent > 0">
                      <span class="block text-xs line-through text-gray-400">
                          ${{ originalPrice }}
                      </span>
                    </template>
                    <span>${{ totalAmount }}</span>
                  </div>
                </div>

                <div class="flex justify-between text-slate-600">
                  <span>Tax</span>
                  <span>$0.00</span>
                </div>

                <div class="flex justify-between items-end pt-3 border-t border-slate-200">
                  <span class="text-xl font-bold text-slate-900">Total</span>
                  <div class="flex flex-col items-end">
                      <span v-if="paymentType === 'COURSE' && discountPercent > 0" class="text-xs text-green-600 font-medium mb-1">
                        (Discount {{ discountPercent }}%)
                      </span>
                      <span class="text-xl font-bold text-slate-900">${{ totalAmount }}</span>
                  </div>
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
  
  // Check enrollment status first to prevent double payment
  checkEnrollmentStatus()
  
  console.log('Payment page mounted, selectedCardType:', selectedCardType.value)
  console.log('checkingEnrollment:', checkingEnrollment.value)
  console.log('isAlreadyEnrolled:', isAlreadyEnrolled.value)
  
  loadCourseData()
  
  // Initialize Stripe immediately for card payments
  if (selectedCardType.value === 'VISA' || selectedCardType.value === 'MASTERCARD') {
    console.log('💳 Initializing Stripe for card type:', selectedCardType.value)
    initializeStripe()
  } else if (selectedCardType.value === 'PAYPAL') {
      loadPayPal()
  }
})

// Test mode flag - set to false for production with real Stripe Elements
const STRIPE_TEST_MODE = false

// Payment data - use computed to make reactive
const paymentType = computed(() => route.query.type || 'MEMBERSHIP')
const courseId = computed(() => route.query.course_id || null)
const selectedCardType = ref('VISA')
const loading = ref(false)
const currentPaymentId = ref<string | null>(null)

// Enrollment check
const isAlreadyEnrolled = ref(false)
const checkingEnrollment = ref(false)

// Check if user is already enrolled in the course
const checkEnrollmentStatus = async () => {
  console.log('🔍 Checking enrollment, paymentType:', paymentType.value, 'courseId:', courseId.value)
  
  if (paymentType.value !== 'COURSE' || !courseId.value) {
    console.log('⚡ Not course payment or no courseId, skipping enrollment check')
    return
  }
  
  checkingEnrollment.value = true
  try {
    const tokenCookie = useCookie('auth_token')
    const token = tokenCookie.value
    
    if (!token) {
      console.log('❌ No auth token found')
      return
    }
    
    const config = useRuntimeConfig()
    const response = await $fetch(`/api/courses/${courseId.value}/enrollment/check`, {
      baseURL: config.public.backendUrl as string,
      headers: {
        'Authorization': `Bearer ${token}`
      }
    })
    
    console.log('Enrollment check response:', response)
    
    if (response.isEnrolled) {
      isAlreadyEnrolled.value = true
      console.log('User already enrolled, redirecting...')
      // Redirect to course page if already enrolled - use window.location.replace for clean navigation
      window.location.replace(`/courses/${courseId.value}`)
      return
    }
  } catch (error) {
    console.error(' Error checking enrollment status:', error)
  } finally {
    checkingEnrollment.value = false
    console.log(' Enrollment check finished, checkingEnrollment:', checkingEnrollment.value)
  }
}

// Stripe instance
let stripeInstance: any = null

// Function to initialize Stripe Elements
const initializeStripe = async () => {
  try {
    console.log('🔄 Initializing Stripe...')
    
    // Load Stripe if not already loaded
    if (!stripeInstance) {
      stripeInstance = await loadStripe(config.public.stripePublishableKey)
      console.log('✅ Stripe instance loaded:', !!stripeInstance)
    }
    
    if (!stripeInstance) {
      console.error('❌ Failed to load Stripe instance')
      return
    }
    
    // Wait for DOM to be ready
    await nextTick()
    
    // Check if element exists before mounting
    const stripeElementContainer = document.getElementById('stripe-card-element')
    console.log('🎯 Stripe card element container found:', !!stripeElementContainer)
    
    if (stripeElementContainer) {
      // Clear any existing content
      stripeElementContainer.innerHTML = ''
      
      // Create new elements
      elements = stripeInstance.elements()
      cardElement = elements.create('card', {
        style: {
          base: {
            fontSize: '16px',
            color: '#424770',
            '::placeholder': {
              color: '#aab7c4',
            },
          },
        },
      })
      
      cardElement.mount('#stripe-card-element')
      console.log('🎉 Stripe card element mounted successfully!')
      
      cardElement.on('change', (event: any) => {
        const errorDiv = document.getElementById('stripe-card-errors')
        if (errorDiv) {
          if (event.error) {
            errorDiv.textContent = event.error.message
          } else {
            errorDiv.textContent = ''
          }
        }
      })
    } else {
      console.log('❌ Stripe card element not found in DOM - retrying in 500ms')
      // Retry after a short delay
      setTimeout(() => {
        initializeStripe()
      }, 500)
    }
  } catch (error) {
    console.error('❌ Error initializing Stripe:', error)
  }
}




watch(selectedCardType, async (val, oldVal) => {
  console.log('Card type changed from', oldVal, 'to', val)
  
  if (val === 'PAYPAL') {
      // Unmount Stripe
      if (cardElement && cardElement.unmount) {
          cardElement.unmount()
          cardElement = null
      }
      await nextTick()
      loadPayPal()
  } else if (val === 'VISA' || val === 'MASTERCARD') {
    // Initialize Stripe for card payments
    console.log('Switching to Stripe for card type:', val)
    await initializeStripe()
  } else if ((oldVal === 'VISA' || oldVal === 'MASTERCARD') && val !== 'PAYPAL') {
    // Unmount Stripe Elements khi chuyển sang loại khác
    if (cardElement && cardElement.unmount) {
      cardElement.unmount()
      cardElement = null
    }
  }
})

// PAYPAL LOGIC
const loadPayPal = () => {
    const PAYPAL_CLIENT_ID = config.public.paypalClientId; 
    const scriptSrc = `https://www.paypal.com/sdk/js?client-id=${PAYPAL_CLIENT_ID}&currency=USD&intent=capture`;

    const existingScript = document.getElementById('paypal-sdk') as HTMLScriptElement;
    if (existingScript) {
        if (existingScript.src === scriptSrc) {
            renderPayPalButtons();
            return;
        } else {
            existingScript.remove();
            (window as any).paypal = undefined;
        }
    }

    const script = document.createElement('script');
    script.src = scriptSrc;
    script.id = 'paypal-sdk';
    script.onload = () => {
        renderPayPalButtons();
    };
    script.onerror = (err) => {
        console.error('PayPal SDK failed to load', err);
        alert('Could not load PayPal SDK');
    };
    document.body.appendChild(script);
}

const renderPayPalButtons = () => {
    if ((window as any).paypal) {
        // Clear previous buttons if any
        const container = document.getElementById('paypal-button-container');
        if (container) container.innerHTML = '';

        (window as any).paypal.Buttons({
            fundingSource: (window as any).paypal.FUNDING.PAYPAL,
            createOrder: async (data: any, actions: any) => {
                try {
                    const tokenCookie = useCookie('auth_token')
                    const token = tokenCookie.value
                    
                    const paymentData: any = {
                        payment_type: paymentType.value,
                        payment_method: 'PAYPAL',
                        amount: totalAmount.value 
                    }
                    if (paymentType.value === 'COURSE') {
                        paymentData.course_id = courseId.value
                    } else {
                        paymentData.membership_plan = 'PREMIUM'
                    }

                    const response: any = await $fetch(`${config.public.backendUrl}/api/payments/create`, {
                        method: 'POST',
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Content-Type': 'application/json',
                        },
                        body: paymentData
                    })

                    if (response.success) {
                        currentPaymentId.value = response.payment.id;
                        console.log('PayPal Order ID:', response.paypal_order_id);
                        return response.paypal_order_id;
                    } else {
                        throw new Error(response.message || 'Failed to create PayPal order');
                    }
                } catch (error) {
                    console.error('Create PayPal Order Error:', error);
                    alert('Could not initiate PayPal payment');
                }
            },
            onApprove: async (data: any, actions: any) => {
                try {
                     const tokenCookie = useCookie('auth_token')
                     const token = tokenCookie.value
                     
                     if (!currentPaymentId.value) {
                       // Should be set by createOrder
                     }

                     const response: any = await $fetch(`${config.public.backendUrl}/api/payments/${currentPaymentId.value}/paypal/capture`, {
                         method: 'POST',
                         headers: {
                             'Authorization': `Bearer ${token}`,
                             'Content-Type': 'application/json',
                         },
                         body: {
                             paypal_order_id: data.orderID
                         }
                     })

                     if (response.success) {
                         // Store payment success in localStorage
                         localStorage.setItem('payment_success', 'true')
                         localStorage.setItem('payment_type', paymentType.value)
                         
                         alert('Payment successful!');
                         
                         // Navigate back to the previous page (course or membership page)
                         window.history.back()
                     } else {
                         alert('Payment capture failed: ' + response.message);
                     }
                } catch (error) {
                   console.error('PayPal Capture Error:', error);
                   alert('Payment capture failed');
                }
            },
            onError: (err: any) => {
                console.error('PayPal Button Error:', err);
                alert('An error occurred with PayPal');
            }
        }).render('#paypal-button-container');
    }
}



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

const originalPrice = ref(0)
const discountPercent = ref(0)

const loadCourseData = async () => {
  if (paymentType.value === 'COURSE' && courseId.value) {
    try {
      console.log('Loading course:', courseId.value)
      const response: any = await $fetch(`${config.public.backendUrl}/api/courses/${courseId.value}`)
      console.log('Course response:', response)
      
      courseData.value = response.course
      
      // 1. Lấy giá gốc và phần trăm giảm giá
      const price = parseFloat(response.course.price) || 0
      const discount = parseFloat(response.course.discount) || 0
      
      // Lưu lại giá gốc và % để hiển thị ở template
      originalPrice.value = price
      discountPercent.value = discount

      // 2. Tính toán Total Amount (Số tiền thực phải trả)
      if (discount > 0 && discount < 100) {
        // Công thức: Giá gốc * (1 - %giảm/100)
        // Dùng Math.round để làm tròn số nguyên như bạn muốn
        totalAmount.value = Math.round(price * (1 - discount / 100))
      } else {
        totalAmount.value = price
      }

      console.log('Original Price:', originalPrice.value)
      console.log('Discount:', discount)
      console.log('Total amount to pay:', totalAmount.value)

    } catch (error) {
      console.error('Failed to load course:', error)
    }
  } else if (paymentType.value === 'MEMBERSHIP') {
    totalAmount.value = 24.00 // Premium membership price
    originalPrice.value = 24.00
    discountPercent.value = 0
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
      
      const response = await $fetch(`${config.public.backendUrl}/api/payments/create`, {
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
      
      // Store payment success in localStorage
      localStorage.setItem('payment_success', 'true')
      localStorage.setItem('payment_type', paymentType.value)
      
      alert('Payment thành công!')
      
      // Navigate back to the previous page (course or membership page)
      // This is cleaner than replacing with a new URL
      window.history.back()
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
      const response = await $fetch(`${config.public.backendUrl}/api/payments/${paymentId}/stripe/complete`, {
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
      
      // Store payment success in localStorage
      localStorage.setItem('payment_success', 'true')
      localStorage.setItem('payment_type', paymentType.value)
      
      alert('Payment successful! You can now access the course content.')
      
      // Navigate back to the previous page (course or membership page)
      window.history.back()
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
      const response = await $fetch(`${config.public.backendUrl}/api/payments/create`, {
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
    
    const response = await $fetch(`${config.public.backendUrl}/api/payments/${paymentId}/stripe/complete`, {
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
    
    // Store payment success in localStorage
    localStorage.setItem('payment_success', 'true')
    localStorage.setItem('payment_type', paymentType.value)
    
    alert('Payment successful! You can now access the course content.')
    
    // Navigate back to the previous page (course or membership page)
    window.history.back()
  } catch (error) {
    console.error('Failed to complete Stripe payment:', error)
    alert('Failed to complete payment')
  }
}

</script>
